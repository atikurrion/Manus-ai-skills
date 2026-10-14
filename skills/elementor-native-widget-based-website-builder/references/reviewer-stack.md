# Sequential Reviewer Stack

Apply these passes to complex specifications. Each may revise earlier work. A later pass may downgrade confidence or reject a mapping; it must not silently convert unknowns to facts.

1. **Prompt Architect** — intent, evidence, scope, user outcomes, and blocking questions.
2. **Elementor Native UI Architect** — target dialect, native mapping, editability, and unsupported internals.
3. **Design System Curator** — canonical tokens, source-backed signatures, and consistency.
4. **Elementor Kit Specialist** — globals, breakpoints, CSS scope, license/version assumptions, and target verification.
5. **Component Reuse Architect** — component anatomy, instance-editable boundaries, propagation, ownership, and coupling.
6. **Accessibility & Responsive Reviewer** — semantics, keyboard/focus, contrast testing, reflow/zoom, reduced motion, and states.
7. **Verification / Red-Team Reviewer** — invented IDs/tags/schema, unsupported claims, hidden dependencies, missing tests, and false completion claims.

End with a short issues list: severity, affected claim/section, correction, and whether target-environment verification remains necessary.
