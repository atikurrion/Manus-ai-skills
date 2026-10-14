# Registration API (verified, ACF Pro 6.8.1)

Register on the `acf/init` action (or `acf/include_fields`). Do not call these at arbitrary load times.

## Field groups

`acf_add_local_field_group( $field_group )` — required array argument. `register_field_group()` is an alias. Returns `false` if the `key` is already registered.

Top-level keys read/normalized by the source (defaults shown):

| Key | Default | Notes |
|---|---|---|
| `key` | `''` | required; convention `group_xxx`. Auto-derived from `title` if empty |
| `title` | `''` | |
| `fields` | `array()` | array of field arrays (see below) |
| `location` | `array()` | rule groups (see shape below) |
| `menu_order` | `0` | |
| `position` | `'normal'` | `normal` \| `acf_after_title` \| `side` |
| `style` | `'default'` | `default` \| `seamless` |
| `label_placement` | `'top'` | `top` \| `left` |
| `instruction_placement` | `'label'` | `label` \| `field` |
| `hide_on_screen` | `array()` | e.g. `permalink`, `the_content`, `excerpt`, `custom_fields`, `discussion`, `comments`, `slug`, `author`, `format`, `page_attributes`, `featured_image`, `revisions`, `categories`, `tags`, `send-trackbacks` |
| `active` | `true` | |
| `description` | `''` | |
| `show_in_rest` | `false` | |

### Location shape

Array of OR-groups; each OR-group is an array of AND-rules; each rule is `array( 'param' => , 'operator' => , 'value' => )`:

```php
'location' => array(
  array( // OR group
    array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ), // AND rule
  ),
)
```

For an ACF Block, use a rule `array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/your-block' )`.

## Individual fields

`acf_add_local_field( $field, $prepared = false )` and `acf_add_local_fields( $fields = array() )`.

Field array defaults (validated by `acf_validate_field`): `ID => 0`, `key => ''`, `label => ''`, `name => ''`, `prefix => ''`, `type => 'text'`, `value => null`, `menu_order => 0`, `instructions => ''`, `required => false`, `id => ''`, `class => ''`, `conditional_logic => false`, `parent => 0`, `wrapper => array( 'width' => '', 'class' => '', 'id' => '' )`. Type-specific keys: see [field-types.md](field-types.md).

## Options pages (Pro only — gate on `acf_is_pro()`)

`acf_add_options_page( $page = '' )` and `acf_add_options_sub_page( $page = '' )`. A string argument is treated as the page title. Returns the page array, or `false` if the `menu_slug` already exists.

Keys read (defaults): `page_title => ''`, `menu_title => ''` (falls back to `page_title`), `menu_slug => ''` (auto `acf-options-{slug}`), `capability => 'edit_posts'`, `parent_slug => ''`, `position => null`, `icon_url => false`, `redirect => true`, `post_id => 'options'`, `autoload => false`, `update_button`, `updated_message`. Legacy keys `title`/`menu`/`slug`/`parent` are migrated to the above. Read options-page values with `get_field( 'name', 'option' )`.

## Management / introspection helpers

- `acf_get_field_group( $id )`, `acf_get_field_groups( $filter = array() )`, `acf_update_field_group( $field_group )`
- `acf_get_field( $id )`, `acf_get_fields( $parent )`, `acf_update_field( $field, $specific = array() )`
- `acf_get_options_page( $slug )`, `acf_get_options_pages()`, `acf_update_options_page( $slug, $data = array() )`

## Not available as a code API

There is **no** `acf_register_post_type()` / `acf_register_taxonomy()` function. ACF-managed post types/taxonomies are created via the admin UI or Local JSON and exposed only through CRUD helpers (`acf_get_post_type`, `acf_update_post_type`, `acf_get_taxonomy`, `acf_update_taxonomy`, …). Do not claim a registration function exists.
