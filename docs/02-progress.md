# Progress

**Current phase:** 3 (Implement)
**Next step:** finish the first Phase 3 step: copy the rest of the docs (`docs/phase-1/`, `docs/phase-2/`) into the repo, finish `CLAUDE.md`, then make the first commit.

## Phase 1: Plan
- [x] Set up project docs and workflow (2026-11-10)
- [x] Choose the name: "Putello" for now (2026-11-11)
- [x] Choose the stack (2026-11-10)
- [x] Finalize v1 scope (in and out) (2026-11-10)
- [x] Finalize rules and edge cases (2026-11-10)
- [x] Data model approved as a draft; details are finalized at the start of Phase 3 (2026-11-10)
- [x] Finalize the screen list for Design: `docs/phase-2/design-brief.md` (2026-11-10)
- [x] Phase 1 sign-off (2026-11-10)

## Phase 2: Design
- [x] Paste `docs/phase-2/design-brief.md` into Claude Design (2026-11-10)
- [x] Pick a visual direction: the owner's "Hearth & Home" palette (2026-11-11)
- [x] Design every screen at 390 px and 1280 px, with their states (2026-11-11)
- [x] Design the 5 key flows (2026-11-11)
- [x] Apply the UX review, the small-screen rules and the owner's review notes (2026-11-11)
- [x] Bring the design system, the canvas and every doc in line with the current design and the name "Putello" (2026-11-11)
- [x] Owner's final review of the whole design, and the fixes from it (2026-11-14)
- [x] Phase 2 sign-off (2026-11-14)

## Phase 3: Implement
- [ ] Create the repo from `amyavari/laravel-skeleton-strict-configs`, copy `docs/` into it, write `CLAUDE.md` (include "Notes for Phase 3" in `docs/phase-2/design.md` and the American English rule)
- [ ] Finalize the data model details (see "To check at the start of Phase 3" in `data-model.md`)
- [ ] Add a PostgreSQL job to CI next to SQLite
- [ ] Features in the order set in Phase 1 (each one with Pest tests, and passing `composer test`)
- [ ] All UI strings in `lang/en` translation files
- [ ] Demo seed data
- [ ] Portfolio README (what it is, screenshots, how it's built, how to run it, how to fork it and add a language)
- [ ] Phase 3 sign-off
- [ ] Later: deploy a live demo on a VPS (`amyavari/laravel-frankenphp-docker`)
- [ ] Later: Persian (RTL) translation

## Decision log
Screen-level design decisions are in `docs/phase-2/design.md`. This log keeps the ones that affect the product or the workflow.

| Date | Decision | Why |
|---|---|---|
| 2026-11-10 | Three phases: Plan in Claude chat, Design in Claude Design, Implement in Claude Code | Each phase uses the tool built for it |
| 2026-11-10 | Project docs are the source of truth until Phase 3, then the repo | Claude Code works in the repo and can't edit Project docs |
| 2026-11-10 | Stack: owner's starter kit `amyavari/laravel-skeleton-strict-configs` (Laravel 13, Livewire 4, Flux, Fortify, Pest 5, PHPStan level 6, Pint, Rector, Actions pattern) | It's the owner's own strict setup and shows how they work |
| 2026-11-10 | Lending only. Items borrowed from others are left out. | Owner's choice, keeps v1 small |
| 2026-11-10 | VPS and deployment deferred. The code comes first. | Owner's priority |
| 2026-11-10 | SQLite locally, PostgreSQL on the server, and CI tests on both | Owner's choice, and it catches database-specific bugs before production |
| 2026-11-10 | Free Flux only. UI must be simple, fast and easy to navigate. | Owner's choice. Missing parts (e.g. autocomplete, which is Pro) get built as small custom components |
| 2026-11-10 | Multiple households per user (create and join any number) | Owner prefers it, and the per-membership role table needs the same structure anyway |
| 2026-11-10 | Only the owner manages members and roles (Viewer or Editor) and can change roles at any time. Ownership can be transferred at any time; the old owner becomes an Editor. | Owner's rules |
| 2026-11-10 | Invite by email address. The invitation shows in the app after login, where the person accepts or declines it. The owner shares a ready-made message themselves; the app sends no invitation emails. | Simple, works with any messenger |
| 2026-11-10 | Email verification is required for every account, with **Fortify's built-in verification link**. Account emails (verification, password reset) are sent normally. | Proves email ownership with no custom code |
| 2026-11-10 | Deleting an account is only allowed once the user owns no household | Every household always keeps an owner |
| 2026-11-10 | The owner can delete a household, which permanently deletes everything in it (confirmed by typing its name). Data export is out of scope. | Privacy. Export isn't needed now. |
| 2026-11-10 | Borrowers are a saved, manageable list, with a suggest-as-you-type input | Lets you see per-borrower history and reuse names |
| 2026-11-10 | Loans close as Returned or Lost. Lost archives the item automatically. | Some items never come back |
| 2026-11-10 | History never breaks: log entries store text snapshots (names and paths as they were), and items show their current place by link | Renames and deletes never rewrite history |
| 2026-11-10 | Items and borrowers are archived (SoftDeletes), never deleted one by one | Keeps loan history and logs whole |
| 2026-11-10 | A place rename or move gives one feed entry, plus a "path changed" entry in each affected item's history | Every item stays traceable without flooding the feed |
| 2026-11-10 | **English only in v1**. All strings go through `lang/` files, and the design is RTL-ready. Persian and RTL come later. | The app stays easy to translate and forkable |
| 2026-11-10 | Any language may be typed into text fields. Search matches what was typed (case-insensitive), with no language-specific handling. | Only English is officially supported in v1 |
| 2026-11-10 | v1 scope approved: F1–F8 plus the out-of-scope list | Owner sign-off |
| 2026-11-10 | Extra rules approved: no place cycles, an archived borrower is restored when their name is reused, no duplicate or member invitations | Found while modeling the data |
| 2026-11-10 | Data model approved as a draft. Design comes before code; the model details are finalized in Phase 3. | Owner wants to design the UI/UX first |
| 2026-11-10 | Design brief approved and Phase 1 signed off | Owner sign-off |
| 2026-11-10 | Screens are designed in light only; dark is shown on two boards | Dark uses the same tokens |
| 2026-11-10 | A place's item count includes everything inside it, at any depth | A collapsed place never looks empty when it isn't. Affects the query in Phase 3. |
| 2026-11-10 | Edit item changes only the name and notes; the place changes only through Move | Every place change is logged as a move |
| 2026-11-10 | A place can be added from the Place picker when no place matches | So a move is not interrupted by a missing place |
| 2026-11-11 | The household is not a place: an item always needs a real place, and "top level" never appears in the app | Owner's clarification |
| 2026-11-11 | Page or modal: a main form with several inputs, or any form with a date, is a page; one simple action is a modal; never a modal on a modal; one modal does one thing | Owner's rule. A date picker from another library may open its own pop-up. |
| 2026-11-11 | Loans is a searchable list of loan cards, overdue first, not grouped by borrower | The item name matters most and must be searchable |
| 2026-11-11 | The household area is three pages: Households (list and invitations), Household page, Manage members (Owner only) | One long settings page was too big |
| 2026-11-11 | Danger zone: the Owner sees Transfer ownership and Delete household; everyone else sees only Leave household | Simpler than a disabled Leave button |
| 2026-11-11 | Opening the app or logging in lands on Search of the household used last | Searching is what people do most |
| 2026-11-11 | Menu: Search · Places · Loans · Activity · Manage “Home”, a divider, then Households | The first five belong to the household you are in |
| 2026-11-11 | Palette "Hearth & Home": cream, terracotta primary, olive secondary, gold for "Lent" | The owner's choice |
| 2026-11-11 | Archiving an item needs no confirmation and can be undone; every action shows one kind of success message | From the UX review |
| 2026-11-11 | Small screens get their own layouts: row actions as icons at the end of the row, main button and "Cancel" on one line (70/30) | The mobile boards were not designed for small screens |
| 2026-11-11 | **Household label:** a member can give a household they joined their own label, seen only by them and shown wherever the household is named. The Owner has none. | Two households can share a name ("Home") |
| 2026-11-11 | **Invitations have a name,** used in the shared message and the pending list, and not validated | The message may be sent through any app |
| 2026-11-11 | Edit member and Remove member are separate modals, opened from a three-dots menu | Mixing them in one modal invites mistakes |
| 2026-11-11 | Destructive items are red everywhere; "This can't be undone." is bold and red | So people notice before they act |
| 2026-11-11 | Settings is one page: Profile, Appearance, Password, Danger zone | The owner's order |
| 2026-11-11 | American English everywhere: app text, designs, code, docs, chat | Owner's rule |
| 2026-11-11 | The app is named "Putello" for now | Owner's choice |
| 2026-11-11 | **Paths do not start with the household's name;** the Place picker and the Places tree keep it as the top of the hierarchy | The header already shows the household, and shorter paths make shorter lists |
| 2026-11-11 | **The Places tree keeps the household as its top row,** with the total count | Owner confirmed; it matches the Place picker |
| 2026-11-11 | **Breadcrumbs in the places area start with the household's name or label** | Owner's rule: a trail must start somewhere, and it should be the household |
| 2026-11-11 | The design backup and the separate UX review doc were removed; the review's Phase 3 notes live in `design.md` | Owner wants a clean output |
| 2026-11-14 | Phase 2 signed off. Phase 3 starts. | Owner sign-off |
