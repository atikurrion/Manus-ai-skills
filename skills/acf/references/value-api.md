# Value read/write API (verified, ACF Pro 6.8.1)

Signatures are exactly as defined in the ACF source. `$post_id` accepts: a numeric post ID; the string `'option'`/`'options'` (options pages); `'user_{ID}'`; `'term_{ID}'`; `'comment_{ID}'`; `'block_{id}'`. ACF resolves these via `acf_decode_post_id()` — when unsure which form a target needs, verify on the live site.

## Reading

| Function | Signature | Returns |
|---|---|---|
| `get_field` | `get_field( $selector, $post_id = false, $format_value = true, $escape_html = false )` | formatted value, or `false` |
| `the_field` | `the_field( $selector, $post_id = false, $format_value = true )` | echoes; arrays imploded with `, ` |
| `get_field_object` | `get_field_object( $selector, $post_id = false, $format_value = true, $load_value = true, $escape_html = false )` | full field array (incl. `value`) or `false` |
| `get_fields` | `get_fields( $post_id = false, $format_value = true, $escape_html = false )` | assoc `name => value` or `false` |
| `get_field_objects` | `get_field_objects( $post_id = false, $format_value = true, $load_value = true, $escape_html = false )` | assoc `name => field array` or `false` |

`get_field()` returns `false` if you pass `$escape_html = true` with `$format_value = false`.

## Writing

| Function | Signature | Returns |
|---|---|---|
| `update_field` | `update_field( $selector, $value, $post_id = false )` | bool |
| `delete_field` | `delete_field( $selector, $post_id = false )` | bool |
| `update_sub_field` | `update_sub_field( $selector, $value, $post_id = false )` | bool |
| `delete_sub_field` | `delete_sub_field( $selector, $post_id = false )` | bool (delegates to `update_sub_field(..., null, ...)`) |

`update_field` resolves the field loosely; if the field has never been saved, pass the **field key** (`field_abc123`) as `$selector` so ACF can attach the field reference, otherwise a bare name may save only post meta without the `_name` reference.

## Repeater / Flexible Content traversal

Repeater and Flexible Content store an indexed array of rows and are traversed identically:

```php
if ( have_rows( $selector, $post_id ) ) {
    while ( have_rows( $selector, $post_id ) ) {
        the_row();
        $layout = get_row_layout();          // Flexible Content: layout name; Repeater: false
        $val    = get_sub_field( 'sub_name' );
        // nested: if ( have_rows('nested_repeater') ) { while(...) { the_row(); ... } }
    }
}
```

| Function | Signature | Notes |
|---|---|---|
| `have_rows` | `have_rows( $selector, $post_id = false )` | manages a global loop stack; supports nesting |
| `the_row` | `the_row( $format = false )` | advances the active loop, returns the row |
| `get_row` | `get_row( $format = false )` | current row data |
| `get_row_index` | `get_row_index()` | 1-based by default (`row_index_offset`) |
| `get_row_layout` | `get_row_layout()` | Flexible Content layout name; `false` for repeater |
| `get_sub_field` | `get_sub_field( $selector = '', $format_value = true, $escape_html = false )` | requires an active loop |
| `get_sub_field_object` | `get_sub_field_object( $selector, $format_value = true, $load_value = true, $escape_html = false )` | full sub-field array |
| `the_sub_field` | `the_sub_field( $field_name, $format_value = true )` | echoes |
| `reset_rows` | `reset_rows()` | no arguments; clears the active loop |

### Row mutation (no loop required)

| Function | Signature |
|---|---|
| `add_row` | `add_row( $selector, $row = false, $post_id = false )` → new row count |
| `add_sub_row` | `add_sub_row( $selector, $row = false, $post_id = false )` |
| `update_row` | `update_row( $selector, $i = 1, $row = false, $post_id = false )` (`$i` is 1-based) |
| `update_sub_row` | `update_sub_row( $selector, $i = 1, $row = false, $post_id = false )` |
| `delete_row` | `delete_row( $selector, $i = 1, $post_id = false )` |
| `delete_sub_row` | `delete_sub_row( $selector, $i = 1, $post_id = false )` |

`$row` for repeater is `array( 'sub_name' => value, ... )`; for flexible content include `'acf_fc_layout' => 'layout_name'`. `add_sub_row`/`update_sub_row` with a string selector require an active row loop; the array-selector form `array( $parent_selector, $row_i, $sub_selector )` does not.

## Clone field values

Clone defaults: `display => 'seamless'`, `prefix_name => 0`. With the defaults a clone is transparent — read its sub-fields by their **original sub-field name** via `get_field()` / `get_sub_field()`. If the clone field has `prefix_name` enabled, values are stored/read under `cloneName_subName`. `display => 'group'` makes the clone behave like a single group value (loop it with `have_rows()` — clone sets `have_rows = 'single'`).

## Low-level metadata (bypasses formatting & field reference)

| Function | Signature |
|---|---|
| `acf_get_metadata` | `acf_get_metadata( $post_id = 0, $name = '', $hidden = false )` |
| `acf_update_metadata` | `acf_update_metadata( $post_id = 0, $name = '', $value = '', $hidden = false )` |

`$hidden = true` targets the underscore-prefixed reference key (e.g. `_field_name`). Prefer `get_field`/`update_field` unless you specifically need raw meta.
