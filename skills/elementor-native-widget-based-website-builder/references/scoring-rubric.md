# Prompt Quality Scoring Rubric

Score each dimension independently from 1–5. A high score is not runtime proof. For any dimension below 4, name one concrete correction.

| Dimension | 1 | 3 | 5 |
|---|---|---|---|
| Intent and evidence | Unclear or mixed facts | Clear, some gaps | Scope, sources, confidence, and blockers explicit |
| Native Elementor fit | Custom-first or opaque | Mostly native | Target-aware native-first with justified exceptions |
| Design-system depth | Token-poor | Core tokens/components | Tokens, states, responsive behavior, dark mode when relevant, signatures, drift, and accessibility |
| Mapping accuracy | Unsupported/duplicated | Mostly coherent | Verified or clearly conceptual; no fabricated internals |
| Kit alignment | Ignores Kit | Partial globals | Globals/layout/breakpoints aligned and testable |
| Component reuse | No boundaries | Candidates listed | Editability, propagation, ownership, and fallback explicit |
| Signature preservation | Missing/generic | Listed | 3+ source-backed details mapped and preserved responsively |
| Responsive and accessible states | Static/desktop-only | Basics | Breakpoint/state matrix, keyboard/focus, contrast, reflow, reduced motion |
| Verification and handoff | Claims without tests | Some tests | Observable tests, dependencies, owners, unresolved items |

A score measures specification quality only. It never establishes that the target site has been implemented, imported, rendered, or tested.
