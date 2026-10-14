# Manus AI Skills

Reusable copies of the nine skills from the **Personal** skills collection in Manus.

## Included skills

- `project-handoff-bundler`
- `acf`
- `siteagent-elementor-studio`
- `wp-plugin-development`
- `figma-to-elementor`
- `wordpress-pro`
- `wordpress-elementor`
- `ui-ux-pro-max`
- `elementor-native-widget-based-website-builder`

Each skill remains self-contained under `skills/<skill-name>/` and includes its `SKILL.md`, references, scripts, data, and test fixtures where applicable. To use a skill in another Manus environment, copy its directory into that environment's skills directory and restart or refresh the skill registry.

## Repository layout

```text
skills/
├── <skill-name>/
│   ├── SKILL.md
│   ├── references/
│   ├── scripts/
│   └── data/
```

Only the skill packages are included; no credentials, local environment files, or generated caches are committed.
