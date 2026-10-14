#!/usr/bin/env node
/**
 * acf-exec — single transport runner for the acf skill.
 *
 * The ACF logic lives entirely in ./snippets/<name>.php and is IDENTICAL across
 * transports. This runner only chooses HOW the PHP is executed:
 *
 *   --mode wp        Run via WP-CLI: `wp eval-file <tmp>` (default, no extra tooling)
 *   --mode novamira  Render the final PHP to a file and print the exact
 *                     instruction for executing it through the Novamira MCP
 *                     server (https://github.com/use-novamira/novamira). This
 *                     runner cannot call an MCP tool itself — the agent does.
 *   --detect         Report whether WP-CLI is usable, so the agent can choose.
 *
 * Security model:
 *   - On non-Windows, child processes are spawned with `shell: false` so the
 *     `--wp` binary path and `--path` WordPress root cannot be shell-injected.
 *   - On Windows, `shell: true` is required to invoke `wp.bat`/`wp.cmd` shims,
 *     so every interpolated argument is pre-quoted for cmd.exe.
 *   - Inputs are passed as JSON and exposed to the snippet via acf_skill_args();
 *     they are never string-interpolated into PHP.
 *
 * Usage:
 *   node acf-exec.mjs --detect [--wp <bin>] [--path <wp-root>]
 *   node acf-exec.mjs --mode wp --snippet verify-env [--path <wp-root>]
 *   node acf-exec.mjs --mode wp --snippet get-value --args '{"post_id":12,"selector":"subtitle"}'
 *   node acf-exec.mjs --mode novamira --snippet get-value --args '{...}'
 *   node acf-exec.mjs --mode wp --php ./custom.php --args '{...}'   # ad-hoc snippet
 *   node acf-exec.mjs --mode wp --snippet verify-env --timeout 30   # seconds, default 60
 */

import { readFileSync, writeFileSync, mkdtempSync, existsSync, rmSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join, dirname, resolve, isAbsolute } from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = dirname(fileURLToPath(import.meta.url));
const SNIPPET_DIR = join(HERE, 'snippets');
const OUT_RE = /@@ACF_JSON@@([\s\S]*?)@@END@@/;
const DEFAULT_TIMEOUT_MS = 60_000;
const IS_WINDOWS = process.platform === 'win32';

function parseArgv(argv) {
  const o = { mode: 'auto', wp: 'wp', args: '{}', timeoutMs: DEFAULT_TIMEOUT_MS };
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--detect') o.detect = true;
    else if (a === '--render-only') o.renderOnly = true;
    else if (a === '--mode') o.mode = argv[++i];
    else if (a === '--snippet') o.snippet = argv[++i];
    else if (a === '--php') o.php = argv[++i];
    else if (a === '--args') o.args = argv[++i];
    else if (a === '--wp') o.wp = argv[++i];
    else if (a === '--path') o.path = argv[++i];
    else if (a === '--timeout') o.timeoutMs = Math.max(1, parseInt(argv[++i], 10) || 0) * 1000;
    else if (a === '-h' || a === '--help') o.help = true;
  }
  return o;
}

function die(msg, code = 1) {
  console.error(JSON.stringify({ ok: false, error: msg }));
  process.exit(code);
}

/**
 * Quote a single argument for cmd.exe. Used only when shell:true is required
 * (Windows .bat/.cmd shims). Wraps in double quotes when needed, escapes inner
 * quotes, doubles trailing backslashes, and neutralises cmd metacharacters
 * (% and ! are special even inside double quotes).
 */
function quoteForCmd(s) {
  const str = String(s);
  if (str === '') return '""';
  if (!/[\s"&|<>^()%!,;=]/.test(str)) return str;
  let escaped = str.replace(/(\\*)"/g, '$1$1\\"').replace(/(\\*)$/, '$1$1');
  escaped = escaped.replace(/%/g, '%%').replace(/!/g, '^!');
  return '"' + escaped + '"';
}

/**
 * Safe spawn for the wp binary. Never uses shell on POSIX. On Windows uses
 * shell:true (required for .bat/.cmd) and pre-joins the already-quoted command
 * line as a single string — under Node's DEP0190 the args[]+shell:true form is
 * deprecated precisely because Node concatenates without escaping, so we do
 * the escaping ourselves and pass one string.
 */
function spawnWp(bin, wpArgs, extra = {}) {
  if (IS_WINDOWS) {
    const line = [bin, ...wpArgs].map(quoteForCmd).join(' ');
    return spawnSync(line, { encoding: 'utf8', shell: true, ...extra });
  }
  return spawnSync(bin, wpArgs, { encoding: 'utf8', shell: false, ...extra });
}

function loadSnippetBody(opts) {
  let file;
  if (opts.php) file = isAbsolute(opts.php) ? opts.php : resolve(process.cwd(), opts.php);
  else if (opts.snippet) file = join(SNIPPET_DIR, `${opts.snippet}.php`);
  else die('Provide --snippet <name> or --php <file>.');
  if (!existsSync(file)) die(`Snippet not found: ${file}`);
  // Strip a single leading PHP open tag so the body can be concatenated after
  // a transport-specific preamble while still being editable/lintable on disk.
  return readFileSync(file, 'utf8').replace(/^﻿?\s*<\?php\s*/, '');
}

function buildPhp(body, mode, argsJson) {
  // Validate JSON early; fail loudly rather than feeding garbage to PHP.
  try { JSON.parse(argsJson || '{}'); } catch (e) { die(`--args is not valid JSON: ${e.message}`); }

  const common = `
function acf_skill_return($d){ echo "\\n@@ACF_JSON@@".(function_exists('wp_json_encode')?wp_json_encode(array('ok'=>true,'data'=>$d)):json_encode(array('ok'=>true,'data'=>$d)))."@@END@@\\n"; }
function acf_skill_error($m){ echo "\\n@@ACF_JSON@@".json_encode(array('ok'=>false,'error'=>(string)$m))."@@END@@\\n"; }
`;
  let argsFn;
  if (mode === 'wp') {
    argsFn = `function acf_skill_args(){ $j=getenv('ACF_ARGS'); $d=$j?json_decode($j,true):array(); return is_array($d)?$d:array(); }`;
  } else {
    // Novamira / render: inline the args (no env dependency). base64 keeps the
    // PHP literal quote-safe regardless of the JSON content.
    const b64 = Buffer.from(argsJson || '{}', 'utf8').toString('base64');
    argsFn = `function acf_skill_args(){ $d=json_decode(base64_decode('${b64}'),true); return is_array($d)?$d:array(); }`;
  }
  return `<?php\n${argsFn}\n${common}\ntry {\n${body}\n} catch (\\Throwable $e) { acf_skill_error($e->getMessage()); }\n`;
}

function extractResult(stdout) {
  const m = stdout.match(OUT_RE);
  if (!m) {
    return { ok: false, error: 'No ACF result delimiter in output. Raw output follows.', raw: stdout.slice(-4000) };
  }
  try { return JSON.parse(m[1]); }
  catch (e) { return { ok: false, error: `Result JSON parse failed: ${e.message}`, raw: m[1].slice(0, 4000) }; }
}

function detect(opts) {
  const wpArgs = ['--version'];
  if (opts.path) wpArgs.push(`--path=${opts.path}`);
  const r = spawnWp(opts.wp, wpArgs, { timeout: opts.timeoutMs });
  const wpAvailable = r.status === 0 && /WP-CLI/i.test(r.stdout || '');
  let wpAcf = false;
  if (wpAvailable) {
    const args2 = ['eval', 'echo function_exists("acf_get_setting")?"ACF_OK":"ACF_MISSING";'];
    if (opts.path) args2.push(`--path=${opts.path}`);
    const r2 = spawnWp(opts.wp, args2, { timeout: opts.timeoutMs });
    wpAcf = /ACF_OK/.test(r2.stdout || '');
  }
  console.log(JSON.stringify({
    ok: true,
    data: {
      wp_cli_available: wpAvailable,
      acf_loadable_via_wp: wpAcf,
      recommended_mode: wpAvailable ? 'wp' : 'novamira',
      novamira_docs: 'https://github.com/use-novamira/novamira',
      note: wpAvailable
        ? 'WP-CLI works. Novamira is NOT required.'
        : 'WP-CLI not usable here. Use Novamira mode, or pass --wp/--path if WP-CLI exists elsewhere.',
    },
  }, null, 2));
}

function writeTmpPhp(php) {
  const dir = mkdtempSync(join(tmpdir(), 'acf-skill-'));
  const file = join(dir, 'snippet.php');
  writeFileSync(file, php, 'utf8');
  return { dir, file };
}

function cleanup(dir) {
  try { rmSync(dir, { recursive: true, force: true }); }
  catch { /* best-effort */ }
}

function runWp(opts, php) {
  const { dir, file } = writeTmpPhp(php);
  const args = ['eval-file', file];
  if (opts.path) args.push(`--path=${opts.path}`);
  let result;
  try {
    const r = spawnWp(opts.wp, args, {
      env: { ...process.env, ACF_ARGS: opts.args || '{}' },
      maxBuffer: 32 * 1024 * 1024,
      timeout: opts.timeoutMs,
    });
    if (r.error) die(`Failed to spawn WP-CLI ("${opts.wp}"): ${r.error.message}`);
    if (r.signal === 'SIGTERM' || (r.error && r.error.code === 'ETIMEDOUT')) {
      die(`WP-CLI timed out after ${opts.timeoutMs / 1000}s (raise with --timeout <seconds>).`);
    }
    result = extractResult(r.stdout || '');
    if (!result.ok && r.stderr) result.stderr = r.stderr.slice(-2000);
  } finally {
    cleanup(dir);
  }
  console.log(JSON.stringify(result, null, 2));
  process.exit(result.ok ? 0 : 1);
}

function emitNovamira(opts, php) {
  // We deliberately keep the rendered file on disk so the agent can hand it to
  // the Novamira MCP server. We do NOT echo `rendered_php` inline — the args
  // are base64-inlined into that PHP and may contain sensitive field values.
  // The file lives under the OS temp dir; the agent is responsible for cleanup
  // once it has transmitted it.
  const { file } = writeTmpPhp(php);
  console.log(JSON.stringify({
    ok: true,
    mode: 'novamira',
    rendered_php_file: file,
    instructions: [
      'This runner cannot call MCP tools. Execute the rendered PHP through the',
      'Novamira MCP server (https://github.com/use-novamira/novamira) against the',
      "user's WordPress install — use its PHP-execution tool, or its filesystem",
      'tool to drop the file in and include it within WP context.',
      'Capture stdout, then find the JSON between @@ACF_JSON@@ and @@END@@ —',
      'that is the structured result ({ok,data} or {ok:false,error}).',
      'The ACF logic is identical to WP-CLI mode; only the transport differs.',
      'Delete the rendered_php_file after use — it may contain sensitive field values.',
    ],
  }, null, 2));
}

function main() {
  const opts = parseArgv(process.argv.slice(2));
  if (opts.help) { console.log(readFileSync(fileURLToPath(import.meta.url), 'utf8').split('*/')[0]); return; }
  if (opts.detect) return detect(opts);

  let mode = opts.mode;
  if (mode === 'auto') {
    const r = spawnWp(opts.wp, ['--version'], { timeout: opts.timeoutMs });
    mode = (r.status === 0 && /WP-CLI/i.test(r.stdout || '')) ? 'wp' : 'novamira';
  }
  if (mode !== 'wp' && mode !== 'novamira') die(`Unknown --mode "${opts.mode}" (use wp | novamira | auto).`);

  const body = loadSnippetBody(opts);
  const php = buildPhp(body, mode, opts.args);

  if (opts.renderOnly) { process.stdout.write(php); return; }
  if (mode === 'wp') return runWp(opts, php);
  return emitNovamira(opts, php);
}

main();
