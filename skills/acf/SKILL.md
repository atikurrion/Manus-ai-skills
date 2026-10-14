---
name: acf
description: Operate Advanced Custom Fields (ACF) on a live WordPress site (Pro features auto-detected) — register field groups/fields/options pages/ACF Blocks in code, read and write field values including repeater/flexible-content/clone traversal, and query posts by ACF meta. Runs the ACF PHP API through WP-CLI (default) or the Novamira MCP server (fallback). Use when the user mentions ACF, Advanced Custom Fields, acf_add_local_field_group, get_field/update_field, ACF repeater/flexible content/clone/options page, or ACF Blocks.
---

# ACF Pro

Drive ACF Pro on a user's own WordPress install. **The ACF PHP API is the single source of truth and is identical in both execution modes** — only the transport that runs the PHP differs.

## Golden rules

1. **Never assume the installed ACF version or feature set.** ACF's API drifts between versions. Always run the verification step first and adapt to what it reports.
2. **Same ACF logic, two transports.** Write the operation once as a PHP snippet; run it via WP-CLI or via Novamira. Do not re-implement per transport.
3. **Register in code via hooks**, not at arbitrary times: field groups/fields on `acf/init` (or `acf/include_fields`); ACF Blocks on `acf/init`.
4. Treat any API detail you are unsure of as unverified — confirm it on the live site (the verify snippet lists installed field types) rather than guessing option keys.
5. **For any destructive operation, follow the safety protocol in §Step 4.**

## Resolving the runner path

The runner is `scripts/acf-exec.mjs` **relative to this SKILL.md**, not to the user's cwd. Pick one:

- **Resolve from this file** (preferred when invoking directly): `node "<dir of SKILL.md>/scripts/acf-exec.mjs" ...`. The skill ships at `skills/acf/scripts/acf-exec.mjs` inside its package.
- **Via the npx CLI**: `npx acf-skill run -- <acf-exec args>` — handles path resolution for you and works from any cwd.
- **Print the canonical path**: `npx acf-skill path` returns the bundled skill dir; `<that path>/scripts/acf-exec.mjs` is the runner.

The examples below use `node scripts/acf-exec.mjs` for readability; substitute the resolved absolute path when actually invoking.

## Step 1 — Pick the execution mode

Detect, don't assume:

- Run `scripts/acf-exec.mjs --detect`. If `wp` is on PATH and a WordPress root is reachable → **WP-CLI mode** (default; no extra tooling, Novamira NOT required).
- Otherwise, or if the user prefers it → **Novamira mode**: an MCP server giving agents PHP execution + filesystem access to WordPress. Setup/docs: https://github.com/use-novamira/novamira (point the user there; do not invent install steps).
- If ambiguous, ask the user which mode to use.

## Step 2 — Verify the live environment (mandatory, before any operation)

```
node scripts/acf-exec.mjs --mode <wp|novamira> --snippet verify-env
```

It reports: ACF active + version (`acf_get_setting('version')`), Pro vs free (`acf_is_pro()`), whether options pages are available, and the list of installed field types (`acf_get_field_types_info()`). **Read the result and adapt** — e.g. do not use a Pro field if `acf_is_pro()` is false; do not use a field type not in the reported list.

## Step 3 — Run the operation

Use the snippet that matches the task; pass inputs as JSON (never string-interpolate user data into PHP):

| Task | Snippet | Notes |
|---|---|---|
| Read a value (incl. sub-field path) | `get-value` | `get_field` / `get_sub_field` traversal |
| Write a value / row ops | `update-value` | `update_field`, `add_row`, `update_sub_field`, … — **destructive, see §Step 4** |
| Dump repeater / flexible / clone | `dump-rows` | `have_rows`/`the_row`/`get_row_layout` |
| Query posts by ACF meta | `query-meta` | `WP_Query` `meta_query` |
| Register group/fields/options/block | `register` | `acf_add_local_field_group` etc. on `acf/init` — **`persist:"db"` is destructive, see §Step 4** |

Run scripts via `node scripts/acf-exec.mjs --mode <wp|novamira> --snippet <name> --args '<json>'`.
See [scripts/README.md](scripts/README.md) for arguments and the Novamira hand-off.

## Step 4 — Safety protocol for destructive operations

A "destructive" operation is anything that mutates the live database: any `update-value` op (`update_field`, `delete_field`, `add_row`, `update_row`, `delete_row`, `*_sub_*`) and `register` with `persist:"db"`. The runner has no undo and no automatic backup.

**Required sequence:**

1. **Preview first.** Call `update-value` with `"dry_run": true` (no mutation; returns `current_value` plus a description of what would change). For `register`, leave `persist` at its default `"none"` — this only generates the code-paste, no DB write.
2. **Show the user the diff.** Present `current_value` → `new_value` (or "would delete row N of selector X") and the post/options target. Quote what would change verbatim.
3. **Get explicit confirmation** in this turn (or rely on prior, in-session, scoped authorisation). Do not infer consent from earlier unrelated approvals.
4. **Then execute** without `dry_run`, or with `persist:"db"`.

For `--php <file>` (ad-hoc snippet, runs arbitrary PHP against the live site): treat as destructive by default. Walk the user through the snippet contents and ask before running.

If the user wants to skip the preview for routine writes ("just update it"), do so once they've said so explicitly — do not silently default to skipping it.

## API & options reference (verified against ACF Pro source)

Load only what the task needs:

- Value read/write + repeater/flexible/clone traversal → [references/value-api.md](references/value-api.md)
- Registering groups, fields, options pages → [references/registration-api.md](references/registration-api.md)
- Field types and their option keys (incl. Pro fields) → [references/field-types.md](references/field-types.md)
- ACF Blocks registration → [references/blocks.md](references/blocks.md)
- Hooks (load/format/validate/save, `acf/init`) → [references/hooks.md](references/hooks.md)
- WP-CLI `wp acf json` subcommands → [references/wp-cli.md](references/wp-cli.md)

> Reference files were verified against ACF Pro **6.8.1** source. The verify step always re-checks the live site; if it reports a different version, trust the live site and confirm any detail before relying on it.
