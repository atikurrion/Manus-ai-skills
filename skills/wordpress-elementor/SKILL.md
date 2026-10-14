---
name: wordpress-elementor
description: Edit and troubleshoot Elementor-built WordPress pages and templates. Use when the user asks to update Elementor page text, images, buttons, layout, sections, templates, or Elementor content stored in WordPress.
---

# WordPress Elementor

Help the user make accurate, reversible changes to Elementor pages and templates. Use the connected WordPress integration or browser session when available. Use WP-CLI only when it is available in the authorized workspace and connected to the intended site.

## Before editing

1. Identify the exact WordPress site, page or template, requested changes, and whether the user wants a draft or a live update. If any of those are unclear, ask a focused question before changing content.
2. Check the available WordPress connector, browser session, or WP-CLI access. Do not assume access or ask the user to paste passwords, API keys, or other secrets into chat. Use an authorized sign-in or credential flow if one is available.
3. Confirm Elementor is installed and active when the tools permit it. If setup is missing, explain that the separate `wordpress-setup` skill in the source repository covers WordPress access; do not claim this Elementor skill configures WordPress connections.
4. Inspect the existing page and make a backup before changing stored Elementor data. For WP-CLI, save `_elementor_data` and relevant page settings for the target post before edits.

## Choose an editing method

- Use Elementor's visual editor through an authorized browser session for layout, styling, adding/removing sections, changing widgets, or applying templates.
- For a narrowly scoped text or URL replacement in an existing widget, WP-CLI can be appropriate when it is connected to the correct site and the replacement has been dry-run and checked.
- Prefer a draft or staging page when the user has not clearly asked for a live change.
- Avoid directly editing Elementor JSON for structural changes. Elementor stores page structure in the `_elementor_data` post meta as nested sections/containers, columns, widgets, and settings; direct edits can break IDs or layout.

## Visual editor workflow

1. Find the page and confirm its title and URL with the user if several candidates match.
2. Open the page in Elementor using the authorized browser session.
3. Make only the requested edits. For text widgets, select the widget and update its Content fields. For images, use the media picker. For buttons, check both the label and link. For layout or styling, use the relevant widget or container controls.
4. Save/update only within the user's requested scope. If the user asked for a draft, keep it a draft. Do not publish or overwrite a live page unless the request clearly authorizes that outcome and any required confirmation workflow is satisfied.
5. Reopen or preview the page and verify the visible result, links, and responsive layout where practical. Report what changed and anything that could not be verified.

## WP-CLI text replacement workflow

Use the site's configured WP-CLI alias when one exists. Substitute actual site alias and post ID; never run example placeholders literally.

```bash
# Back up the Elementor JSON for the target page
wp @site post meta get POST_ID _elementor_data > /tmp/elementor-backup-POST_ID.json

# Preview the intended replacement first
wp @site search-replace 'OLD TEXT' 'NEW TEXT' wp_postmeta \
  --include-columns=meta_value --dry-run --precise
```

Review the dry-run count and scope. If it is broader than the requested page or otherwise unexpected, stop and use a page-specific method instead. Only execute after confirming the replacement targets the correct content:

```bash
wp @site search-replace 'OLD TEXT' 'NEW TEXT' wp_postmeta \
  --include-columns=meta_value --precise
wp @site elementor flush-css
```

A database-wide search/replace can affect other pages or shared/global widgets. Prefer a page-scoped operation when available. If the Elementor flush command is unavailable, check the installed Elementor version's supported cache-clearing method rather than deleting unrelated options or metadata. Verify the page afterward.

## Templates

- List saved templates using `post_type=elementor_library` if authorized WP-CLI access exists.
- For import, export, or applying templates, prefer Elementor's UI and verify display conditions, target page, and whether the template is global or shared.
- Treat global widgets and Theme Builder templates as shared content: an edit may affect multiple pages. Explain that scope before applying changes when it is not obvious.

## Safety and verification

- Keep a recoverable backup before editing stored Elementor data.
- Do not expose credentials, tokens, cookies, or private site data in the response.
- Never treat a successful save response alone as proof the page looks correct; preview the page when possible.
- After WP-CLI changes, clear the appropriate Elementor CSS cache and verify the page.
- Summarize the target, changes made, draft/live status, and verification result. Clearly state if access prevented a step.
