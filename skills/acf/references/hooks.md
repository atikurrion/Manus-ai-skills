# Hooks (verified, ACF Pro 6.8.1)

Exact tags and argument order as they appear in source.

## Lifecycle actions (`do_action`)

| Hook | Arg | Use for |
|---|---|---|
| `acf/init` | `ACF_MAJOR_VERSION` (6) | register field groups / options pages / blocks here |
| `acf/include_fields` | `ACF_FIELD_API_VERSION` (5) | alternative registration point for field groups |
| `acf/include_field_types` | `ACF_FIELD_API_VERSION` (5) | register custom field types |
| `acf/include_location_rules` | `ACF_FIELD_API_VERSION` (5) | register custom location rules |
| `acf/include_post_types` | `ACF_MAJOR_VERSION` (6) | |
| `acf/include_taxonomies` | `ACF_MAJOR_VERSION` (6) | |

`acf/init` is action-only — there is no `apply_filters( 'acf/init' )`.

## Value & field filters (`apply_filters`)

Argument order is exactly as listed. Each also fires `/type={$type}`, `/name={$name}`, `/key={$key}` variants.

| Filter | Args (in order) |
|---|---|
| `acf/load_value` | `$value, $post_id, $field` |
| `acf/format_value` | `$value, $post_id, $field, $escape_html` |
| `acf/update_value` | `$value, $post_id, $field, $original_value` |
| `acf/load_field` | `$field` |
| `acf/prepare_field` | `$field` |
| `acf/validate_value` | `$valid, $value, $field, $input` (variants written explicitly: `/type=`, `/name=`, `/key=`) |

Pre-emptive short-circuits exist: `acf/pre_load_value`, `acf/pre_format_value`, `acf/pre_update_value`, `acf/pre_load_metadata`, `acf/pre_update_metadata` (return non-`null` to bypass ACF's default).

`acf/render_field` is an action (`$field`), with `/type=`, `/name=`, `/key=` variants.

## Save actions

| Hook | Args | Notes |
|---|---|---|
| `acf/save_post` | `$post_id` | ACF's own value writer runs at **priority 10**. Use priority **< 10** to act *before* values are written, **> 10** to act *after*. |
| `acf/pre_save_post` | `$post_id, $form` (filter, front-end forms) | return/modify the post ID before save |
| `acf/options_page/save` | `$post_id, $menu_slug` | fired when an options page is saved (Pro) |
