# Elementor Design System Specification

Use this as a schema for extracting known information and marking gaps. Do not fill unknowns with guesses. Label each item CONFIRMED, LIKELY, ASSUMED, UNVERIFIED, or VERSION-DEPENDENT.

## Metadata and environment
- Project/design-system name, version, source of truth, owner, last updated.
- WordPress and Elementor/Pro versions; V3/V4/hybrid dialect; theme; license; installed add-ons; ACF/dynamic sources.
- Existing Kit, globals, classes/variables, actual Site Settings breakpoints, dark-mode applicability.
- Evidence and confidence notes; distinguish existing-site facts from proposed decisions.

## Design philosophy
- Core visual/interaction character, immutable interface rules, intended emotional tone, and source-backed references.

## Tokens
- **Color:** primitive palette, semantic roles, component tokens, dark-mode overrides.
- **Typography:** families, weights, roles, line heights, letter spacing, and fluid type constraints.
- **Spacing:** base unit, scale, section rhythm, gaps, responsive scaling.
- **Shape/depth:** radii, borders, elevation, shadows.
- **Motion:** durations, easing, reduced-motion behavior.
- **Breakpoints:** exact values are CONFIRMED only when read from target Site Settings; otherwise proposals are ASSUMED.
- If fluid typography is requested, use `clamp(min, rem + vw, max)`, never pure `vw`, and keep the max/min ratio at or below 2.5 for positive sizes. This is a project constraint, not WCAG proof; test 200% zoom/reflow separately.

## Components and layout
For each component record anatomy, semantics, tokens, states, responsive behavior, editable fields, Elementor mapping, and availability/license. Include relevant buttons, cards, inputs/forms, navigation, modals/popups, badges, tables/lists, and signature components.

Record content max width, horizontal padding, section rhythm, grid/flex discipline, asymmetry, and mobile order.

## Signature details
Document at least three details grounded in supplied evidence: name, evidence/confidence, purpose, implementation, tokens, locations, and mobile behavior.

## Effects and interaction states
Specify entrance/hover/focus/active/disabled behavior, loading/error/empty states, scroll behavior, and reduced-motion alternatives.

## Iconography
Record source/library, stroke/weight, size, color rules, decorative vs informative use, and accessible naming.

## Responsive strategy
Document confirmed breakpoints, intermediate-width expectations, type/spacing changes, layout changes, touch targets, hidden elements, image crop, overflow, long-content behavior, and fixed/sticky behavior.

## Accessibility
Record measured contrast pairs/results only when actually tested; focus visibility, headings, landmarks, alt-text ownership, keyboard order, reduced motion, form errors, language/RTL, and zoom/reflow expectations.

## Dark mode
Record applicability, trigger/default, semantic overrides, CSS-variable scope, transition/reduced-motion behavior, image treatment, and contrast re-check. Mark not applicable when unsupported by the design brief.

## Elementor mapping and Kit checklist
Map Global Colors, Global Fonts, CSS variables, container structure, widget mapping, reuse, and exceptions. For each Kit item mark **Confirmed / Proposed / Not applicable / Needs verification**:
- Global colors and fonts
- Layout/content width
- Site Settings breakpoints
- Theme Style, buttons, and images
- CSS variables/custom CSS scope
- Dark mode
- Sample widget verification
- Version, license, and add-on availability
- Kit import/export testing (do not claim importability until target-tested)

## Drift and versioning
State source of truth, version/change log, deprecation and migration path, owner, drift detection/triage, propagation, testing, and exception recording.
