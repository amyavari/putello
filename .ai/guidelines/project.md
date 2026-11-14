# Putello

Putello is a shared home inventory for households, built as a portfolio app. The docs in `docs/` are the source of truth.

## Workflow

- At the start of every session, read `docs/02-progress.md` and `docs/01-workflow.md`, and follow the rules in `docs/01-workflow.md`.
- Before any UI work, read `docs/phase-2/design.md`. It links the canvas (screens and flows) and the design system. Read both with the Artifact tool.

## Coding rules

- Keep the code simple, readable and close to framework conventions. A reviewer should understand any file in one pass.
- Use the Actions pattern: one small, tested action per use case, created with `php artisan make:action` (for example `MoveItem`, `LendItem`).
- Use free Flux components only. Build anything Flux free does not have (for example autocomplete) as a small custom Livewire component.
- Put every UI string in the `lang/en` translation files. Keep layouts RTL-ready.
- Write American English everywhere: the app's text, the code and the docs ("color", "canceled", "gray").
- Tests run on SQLite locally and on PostgreSQL in CI. Do not write database-specific SQL.
- Every feature has Pest tests, and `composer test` must pass.
