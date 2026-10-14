# WP-CLI `wp acf` commands (verified, ACF Pro 6.8.1)

ACF registers exactly **one** WP-CLI command namespace: `wp acf json`. There is **no** built-in WP-CLI command to read/write field values or to CRUD field groups. For value/registration operations, run the ACF PHP API via `wp eval-file` (this is what `scripts/acf-exec.mjs` does in WP-CLI mode).

`wp acf json` subcommands (all `@since 6.8`):

| Command | Purpose | Key options |
|---|---|---|
| `wp acf json status` | Pending Local-JSON-vs-DB sync counts | `--type=<field-group\|post-type\|taxonomy\|options-page>`, `--detailed`, `--format=<table\|json\|yaml\|csv>` |
| `wp acf json sync` | Import pending Local JSON into the DB | `--type=<...>`, `--key=<acf_key>`, `--dry-run` |
| `wp acf json import` | Import items from a JSON export file | positional `<file>` (must be `.json`) |
| `wp acf json export` | Export items to JSON | `--field-groups=<keys>`, `--post-types=<keys>`, `--taxonomies=<keys>`, `--options-pages=<keys>` (Pro), and exactly one of `--dir=<dir>` / `--stdout` |

Type slugs map internally: `field-group` → `acf-field-group`, `post-type` → `acf-post-type`, `taxonomy` → `acf-taxonomy`, `options-page` → `acf-ui-options-page`. Options-page operations require ACF Pro.
