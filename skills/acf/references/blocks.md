# ACF Blocks (verified, ACF Pro 6.8.1)

ACF Blocks require **ACF Pro** — gate on `acf_is_pro()`. Register on `acf/init`.

## PHP registration

`acf_register_block_type( $block )` — single settings array; `acf_register_block( $block )` is an alias. Applies `acf/register_block_type_args` filter, requires `name`, rejects duplicates, then calls core `register_block_type()`.

Settings keys read by the source (defaults):

| Key | Default | Notes |
|---|---|---|
| `name` | — | required; namespaced `acf/your-block` if no `/` |
| `title` | — | |
| `description` | `''` | |
| `category` | `'common'` | |
| `icon` | — | dashicon, SVG, or array |
| `mode` | `'preview'` | `preview` \| `edit` \| `auto` |
| `keywords` | `array()` | |
| `supports` | merged with `array( 'align' => true, 'html' => false, 'mode' => true )` | |
| `post_types` | `array()` | |
| `uses_context` | defaults `array( 'postId', 'postType' )` | |
| `render_template` | `false` | path to a PHP template |
| `render_callback` | `false` | callable (ACF overwrites the internal callback) |
| `enqueue_style` | `false` | |
| `enqueue_script` | `false` | |
| `enqueue_assets` | `false` | callable |
| `acf_block_version` | `1` for PHP-registered blocks | controls editor behavior |
| `api_version` | `3` if `acf_block_version >= 3` and WP ≥ 6.3, else `2` | |

Attach fields by registering a field group whose location rule is `array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/your-block' )` (see [registration-api.md](registration-api.md)), or by passing an inline `fields` array (with optional `field_group_title`).

```php
add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_register_block_type' ) || ! acf_is_pro() ) {
        return;
    }
    acf_register_block_type( array(
        'name'            => 'testimonial',
        'title'           => 'Testimonial',
        'render_template' => get_stylesheet_directory() . '/blocks/testimonial.php',
        'category'        => 'formatting',
        'mode'            => 'preview',
        'supports'        => array( 'align' => true, 'mode' => true ),
    ) );
} );
```

In the render template, read fields with `get_field( 'name' )` (ACF sets the block as the current post context).

## block.json registration

A `block.json` is treated as an ACF block when it has a truthy top-level `acf` key. Default `acf_block_version` for block.json is `2`. ACF reads these from the `acf` object: `renderCallback` → `render_callback`, `renderTemplate` → `render_template`, `mode`, `blockVersion` → `acf_block_version`, `postTypes` → `post_types`, `validate`, `validateOnLoad`, `usePostMeta`, `hideFieldsInSidebar`, `expandedEditorButtons`, `autoInlineEditing`, plus `acf.fields` / `acf.fieldGroupTitle` for inline field groups. `apiVersion` in block.json wins if set; else 3 (acf_block_version ≥ 3 and WP ≥ 6.3) or 2.
