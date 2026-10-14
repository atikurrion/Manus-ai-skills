# scripts/

`acf-exec.mjs` is the single transport runner. **All ACF logic lives in `snippets/*.php` and is identical for both transports** — the runner only changes how the PHP is executed.

Requires Node 18+. No npm dependencies.

## Commands

```bash
# Which transport is usable here?
node acf-exec.mjs --detect [--wp <wp-bin>] [--path <wp-root>]

# Verify the live site FIRST (always)
node acf-exec.mjs --mode wp --snippet verify-env --path /var/www/site

# Read / write / query
node acf-exec.mjs --mode wp --snippet get-value   --args '{"post_id":42,"selector":"subtitle"}'
node acf-exec.mjs --mode wp --snippet dump-rows   --args '{"post_id":42,"selector":"slides"}'
node acf-exec.mjs --mode wp --snippet update-value --args '{"post_id":42,"op":"add_row","selector":"slides","row":{"title":"Hi"}}'
node acf-exec.mjs --mode wp --snippet query-meta  --args '{"post_type":"product","meta_query":[{"key":"featured","value":"1","compare":"="}]}'
node acf-exec.mjs --mode wp --snippet register    --args '{"field_group":{...},"persist":"db"}'
```

## Arguments

| Flag | Meaning |
|---|---|
| `--mode wp\|novamira\|auto` | transport; `auto` uses WP-CLI if present, else novamira |
| `--snippet <name>` | a file in `snippets/` (without `.php`) |
| `--php <file>` | run an ad-hoc snippet instead of a bundled one |
| `--args '<json>'` | inputs; exposed to PHP via `acf_skill_args()` (never interpolated) |
| `--wp <bin>` | WP-CLI binary/path (default `wp`) |
| `--path <wp-root>` | WordPress root passed as `--path=` to WP-CLI |
| `--detect` | report transport availability and exit |
| `--render-only` | print the final PHP without executing |

## Output contract

Every snippet prints exactly one JSON object between `@@ACF_JSON@@` and `@@END@@`:
`{"ok":true,"data":{...}}` or `{"ok":false,"error":"..."}`. The runner extracts and prints it; exit code is non-zero on `ok:false`.

## Novamira mode

`--mode novamira` cannot call an MCP tool itself. It writes the fully-rendered PHP to a temp file and prints it plus instructions. Execute that PHP through the Novamira MCP server (https://github.com/use-novamira/novamira) against the user's WordPress install (its PHP-execution or filesystem tool), then read the `@@ACF_JSON@@…@@END@@` block from stdout. The PHP is byte-for-byte the same ACF logic as WP-CLI mode; only the inputs are inlined instead of read from an env var.

## Adding a snippet

Drop a `.php` file in `snippets/`. Read inputs with `acf_skill_args()`; return with `acf_skill_return($data)` or `acf_skill_error($msg)`. Do not add `<?php` handling for args/output — the runner injects the preamble and a try/catch wrapper. A single leading `<?php` is stripped automatically.
