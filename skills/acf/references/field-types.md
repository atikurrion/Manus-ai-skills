# Field types & option keys (verified, ACF Pro 6.8.1)

The `type` string and option keys below are read from each field class's `initialize()`. The live verify step (`acf_get_field_types_info()`) is authoritative for what is actually installed — always prefer it over this list.

## Standard fields

| `type` | Key option keys (beyond the common field keys) |
|---|---|
| `text` | `default_value`, `maxlength`, `placeholder`, `prepend`, `append` |
| `textarea` | `default_value`, `new_lines`, `maxlength`, `placeholder`, `rows` |
| `number` | `default_value`, `min`, `max`, `step`, `placeholder`, `prepend`, `append` |
| `range` | `default_value`, `min`, `max`, `step`, `prepend`, `append` |
| `email` | `default_value`, `placeholder`, `prepend`, `append` |
| `url` | `default_value`, `placeholder` |
| `password` | `placeholder`, `prepend`, `append` |
| `image` | `return_format`, `preview_size`, `library`, `min_width`, `min_height`, `min_size`, `max_width`, `max_height`, `max_size`, `mime_types` |
| `file` | `return_format`, `library`, `min_size`, `max_size`, `mime_types` |
| `wysiwyg` | `tabs`, `toolbar`, `media_upload`, `default_value`, `delay` |
| `oembed` | `width`, `height` |
| `select` | `multiple`, `allow_null`, `choices`, `default_value`, `ui`, `ajax`, `placeholder`, `return_format`, `create_options`, `save_options` |
| `checkbox` | `layout`, `choices`, `default_value`, `allow_custom`, `save_custom`, `toggle`, `return_format`, `custom_choice_button_text` |
| `radio` | `layout`, `choices`, `default_value`, `other_choice`, `save_other_choice`, `allow_null`, `return_format` |
| `button_group` | `choices`, `default_value`, `allow_null`, `return_format`, `layout` |
| `true_false` | `default_value`, `message`, `ui`, `ui_on_text`, `ui_off_text` |
| `link` | `return_format` |
| `post_object` | `post_type`, `taxonomy`, `allow_null`, `multiple`, `return_format`, `ui`, `bidirectional_target` |
| `page_link` | `post_type`, `taxonomy`, `allow_null`, `multiple`, `allow_archives` |
| `relationship` | `post_type`, `taxonomy`, `min`, `max`, `filters`, `elements`, `return_format`, `bidirectional_target` |
| `taxonomy` | `taxonomy`, `field_type`, `multiple`, `allow_null`, `return_format`, `add_term`, `load_terms`, `save_terms`, `bidirectional_target` |
| `user` | `role`, `multiple`, `allow_null`, `return_format`, `bidirectional_target` |
| `google_map` | `height`, `center_lat`, `center_lng`, `zoom` |
| `date_picker` | `display_format`, `return_format`, `first_day`, `default_to_current_date` |
| `date_time_picker` | `display_format`, `return_format`, `first_day`, `default_to_current_date` |
| `time_picker` | `display_format`, `return_format` |
| `color_picker` | `default_value`, `enable_opacity`, `custom_palette_source`, `palette_colors`, `show_color_wheel`, `return_format` |
| `icon_picker` | `library`, `tabs`, `return_format`, `default_value` (nested array `array( 'type' => null, 'value' => null )`) |
| `message` | `message`, `esc_html`, `new_lines` |
| `accordion` | `open`, `multi_expand`, `endpoint` |
| `tab` | `placement`, `endpoint`, `selected` |
| `group` | `sub_fields`, `layout` |
| `separator` | (no extra options) |

## Pro fields (gate on `acf_is_pro()`)

### `repeater`
Defaults: `sub_fields => array()`, `min => 0`, `max => 0`, `rows_per_page => 20`, `layout => 'table'` (`table`\|`block`\|`row`), `button_label => ''`, `collapsed => ''`. Optional `pagination`. `supports.bindings = false`.

### `flexible_content`
Defaults: `layouts => array()`, `min => ''`, `max => ''`, `button_label => __( 'Add Row', 'acf' )`.
Each layout: `name => ''`, `label => ''`, `display => 'block'` (`block`\|`table`\|`row`), `sub_fields => array()`, `min => ''`, `max => ''`. `supports.bindings = false`.

### `gallery`
Defaults: `return_format => 'array'`, `preview_size => 'medium'`, `insert => 'append'`, `library => 'all'`, `min => 0`, `max => 0`, `min_width`, `min_height`, `min_size`, `max_width`, `max_height`, `max_size` (all `0`), `mime_types => ''`. `supports.bindings = false`.

### `clone`
Defaults: `clone => ''` (field/group keys to clone), `prefix_label => 0`, `prefix_name => 0`, `display => 'seamless'` (`seamless`\|`group`), `layout => 'block'`. Sets `have_rows = 'single'`. See clone value access in [value-api.md](value-api.md). `supports.bindings = false`.

> All four Pro fields set `supports.bindings = false`, so they cannot be used as ACF Block Bindings sources.
