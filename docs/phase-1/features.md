# Phase 1: Features (scope APPROVED 2026-11-10, updated with the design decisions of 2026-11-11)

## Stack (decided)
- Starter kit: `amyavari/laravel-skeleton-strict-configs` (Laravel 13, Livewire 4, Flux UI **free**, Fortify auth, Pest 5, PHPStan level 6, Pint, Rector)
- Architecture: Actions pattern (`make:action`): one small, tested action per use case (e.g. `MoveItem`, `LendItem`)
- Database: **SQLite** locally, **PostgreSQL** on the server. CI runs the tests on **both**.
- UI: free Flux components only. Anything Flux free doesn't have (e.g. autocomplete) is a small custom Livewire component.
- Deployment (later): `amyavari/laravel-frankenphp-docker` on a VPS

## Project principles (decided)
- **UI:** simple, fast, easy to navigate. Nothing fancy. A first-time user should understand every screen without help.
- **Language: English only in v1, in American English.** But it's ready for more:
  - Every UI string goes through Laravel translation files (`lang/en/…`) from day one, so adding a language means adding files.
  - The design and the markup are **RTL-ready**: logical start/end sides instead of left/right, and no direction-dependent layouts. Persian and Arabic can then be added later without a redesign.
  - Users can type anything (any language) into text fields. Search matches what was typed, with no special handling for other languages in v1.
- **Forkable:** anyone can fork and self-host it for their own country or users.

## v1 features
**F1. Accounts, households and members**
- **Accounts:** sign up, log in, and reset a forgotten password (Fortify). **The email address must be verified** before using the app, with **Fortify's built-in verification link**.
- A user can create **and** join any number of households. The **Households** list shows them all; tapping one opens its Search. Opening the app or logging in lands on Search of the household used last.
- The creator is the household's **owner**. Only the owner renames the household, adds and removes members and changes their roles.
- **Roles:** Viewer (see and search) or Editor (see and change). The owner chooses the role when inviting someone and can change it at any time.
- **Inviting (in-app only, the app sends no email):** the owner enters the person's name, email address and role. The app shows a ready-made message addressed to that name ("Hi John!") that the owner copies and sends any way they like. When the invited person logs in with that (verified) email, they see the invitation on the Households list and accept or decline it. The owner can cancel a pending invitation.
- **Label:** a member can give a household they joined their own label, which only they see. It is offered (optional) when accepting an invitation and can be changed later. Wherever the household is named for that member, the label is shown instead of the name. The owner has no label; they rename the household instead.
- **Transferring ownership:** the owner can make another member the owner at any time. The old owner becomes an Editor.
- **Leaving:** any member except the owner can leave. An owner must first transfer ownership, or delete the household.
- **Deleting a household (owner only):** permanently deletes everything in it: places, items (archived ones too), borrowers, loans, activity and invitations. The owner confirms by typing the household's name.
- **Deleting an account:** only allowed once the user doesn't own any household.

**F2. Places (tree)**
Editors can create, rename, move and delete places, with no depth limit. Every place shows its path: Kitchen › Left cupboard › Drawer 2. A path does not start with the household's name, because the header always shows which household you are in. Opening a place shows its items and its child places.

**F3. Items**
Name (required) and notes (optional). Each active item is in exactly one real place (never directly in the household), and it links to that place, so it always shows the place's current name. Adding an item from inside a place puts it there automatically. Items can be archived, at once and with "Undo", and restored.

**F4. Search (the main screen)**
A single search box that updates results as you type. It matches item names, item notes and place names, and ignores upper/lower case (the same on SQLite and PostgreSQL). Each result shows the item and its current path, plus a "Lent to …" badge if it's lent out. Matching places appear in their own group.

**F5. Move**
Move an item to another place with a searchable place picker. Every move is logged with its from and to paths. If no place matches, a new place can be added from the picker.

**F6. Lending (lending only)**
- Mark an item as lent: the borrower, the date, an optional due date and a note.
- **Borrower input:** type a name. Matching saved borrowers appear instantly as you type. Pick one, or keep what you typed to create a new borrower.
- A loan is closed as **Returned** or **Lost**. Lost archives the item automatically, and the log says "Lost while lent to Sara".
- While lent, the item keeps its home place, so search shows both "Drawer 2" and "Lent to Sara · since Nov 3".
- **Loans** is a searchable list of the open loans: overdue first, then by due date.

**F7. Borrowers**
A saved list of the household's borrowers. You can search, view, rename and archive them. A borrower's page shows what they have now and their past loans.

**F8. Activity log**
- **Household feed:** item created, edited, moved, lent, returned, lost, archived and restored; place created, renamed, moved and deleted; member joined, left and role changed. Grouped by day, with a filter (All activity, Items, Places, Members).
- **Item history:** everything that happened to one item, including "path changed" entries.
- Each entry records who and when.

## Rules and edge cases (decided 2026-11-10)
**History never breaks**
- Each activity entry stores **text snapshots**: the user's name, the item's name, and the from and to paths **as they were at that moment**. Optional IDs make links possible. Later renames and deletes never change old entries.
- **Items and borrowers are archived, never deleted one by one** (Laravel `SoftDeletes`, labeled "Archive" in the UI). Archived records are hidden from search, lists and suggestions, and can be restored. The only permanent delete is deleting a whole household.
- Archiving an item takes it out of its place. Restoring it asks for a place.
- A borrower with an open loan can't be archived. The loan has to be closed first (returned or lost).

**When a place is renamed or moved**
- One entry goes in the household feed: "Ali renamed John's room → Sara's room".
- Each item inside, at any depth, gets a **"path changed"** entry in its own history only: "John's room → Sara's room (Ali renamed a place)". These entries are not shown in the household feed.

**Other rules**
- Place names must be unique among siblings only.
- A place can only be deleted when it has no child places and no items.
- Borrower names must be unique within a household.
- More rules found while modeling the data are in `data-model.md`.

## Out of scope for v1
Photos · QR labels · due-date reminders · tags and categories · items borrowed from others · invitation emails · data export / download · languages other than English (RTL-ready only) · language-aware search · native mobile app · offline mode · import · deployment

## Data model
See `docs/phase-1/data-model.md`.

## Screens
The final screen list, with every state, is in `docs/phase-2/design.md`.

## Name
"Putello" for now (2026-11-11). It replaced the working name "Moooom!".
