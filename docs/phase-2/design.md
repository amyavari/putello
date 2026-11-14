# Design (Phase 2)

- **Canvas:** https://claude.ai/artifact/SC6sy1LSeAbKuxzn9bMB6r. Two pages: **Screens** and **Flows**.
- **Design system:** https://claude.ai/code/artifact/27b3f5be-41a4-4456-a3e1-a2ebb433b43d. Its README, tokens and component guidelines are the rules for color, type, spacing, wording, icons and components.
- **Status:** every screen and flow is designed, at 390 px and 1280 px, in light (dark is shown on the two "Search · dark" boards). Reviewed and signed off on 2026-11-14.
- **Language:** American English. Dates are month first ("Nov 3"); times are 12-hour ("6:40 PM").

## Visual direction: "Hearth & Home"
Cream background, terracotta for the main action, olive for the second action, gold for "Lent". Instrument Sans, 16 px body. Flat cards with borders, 44 px controls, corners 10 px (controls) and 14 px (cards). Light and dark share token names.

| Token | Light | Dark | Used for |
|---|---|---|---|
| `bg` | `#F5F0E6` | `#211F1B` | Page background |
| `bg-subtle` | `#EEE7DA` | `#1C1A17` | Sidebar, mobile header, neutral badges |
| `surface` | `#FFFCF6` | `#2D2A25` | Cards, inputs, modals |
| `border` | `#DED4C5` | `#494238` | Card and divider lines |
| `border-strong` | `#8E8170` | `#8D8070` | Control edges |
| `ink` / `ink-muted` | `#37312B` / `#6E6257` | `#F4EEE3` / `#C4B9A8` | Text / secondary text |
| `accent` (terracotta) | `#A6533E` | `#C8755C` | Primary button, links, focus ring |
| `accent-soft` | `#EFE2D8`, text `#743727` | | Selected: current menu item, chosen place in the picker |
| `secondary` (olive) | `#5F7157`, white text | `#85977B` | Secondary button, checked controls |
| `secondary-soft` | `#E8EBDF` | `#333A2E` | The chosen option in a form |
| `success-soft` / `success-ink` | `#E9EFE4` / `#42613F` | | Success message, "Returned" badge |
| `lent-soft` / `lent-ink` | `#F5EBD8` / `#76521E` | | "Lent" badge, warnings |
| `danger` / `danger-ink` / `danger-soft` | `#B3261E` / `#973F34` / `#F7E8E4` | | Destructive buttons and items, errors, "Overdue" |

## The household, your label, and paths
- A household has a **name** ("Home" in the mockups), set by its Owner.
- A member who joined a household can give it **their own label**, which only they see ("Parents' home"). The Owner has none; they rename the household instead. It is offered (optional) when accepting an invitation and changed on the Household page under "Your label".
- **Wherever a household is named, the app shows your label if you set one, otherwise the name:** header, menu, Households list, breadcrumbs, messages and modals. The Households list also shows "Real name: Home" under a label.
- **Paths start below the household:** "Kitchen › Left cupboard › Drawer 2", in search results, item and place pages, loans, activity entries and messages. The header already says which household you are in.
- **The household is the top of the hierarchy** in the Place picker and the Places tree: their top row, with the total count ("Home · 7 items"). It is not a place: an item can't be put directly in it. Where a place has no parent, "Inside" shows the household. The words "top level" never appear.
- **Breadcrumbs show only the pages above;** the title is the current page. In the places area (Place view, Item view, Lend, Add place, Archived) the first crumb is the household's name or your label, and it opens Places: "Home › John's room" above "Medal". Elsewhere the first crumb is the section: "Loans › Borrowers", "Households › Home".

## Navigation
- **Menu:** Search · Places · Loans · Activity · Manage “Home”, a divider, then Households. The first five belong to the household you are in; Households lists all of them, with a count while an invitation is pending.
- **Desktop:** the sidebar shows the app name with the household under it ("Home · Viewer" for a Viewer), the menu, and the account button (Settings, Log out).
- **Mobile header:** menu button, "Putello – Home" (the household cut with "…" when long), and a search button on every page except Search.
- Opening the app or logging in lands on **Search of the household used last**. Tapping a household on the Households list opens its Search. Nothing on that list is marked as current.
- A person with no household sees only Households, and the header shows just "Putello".
- Archived is reached from a link at the bottom of Places and of Borrowers.

## Page or modal
- **A page** for a main form with several inputs, or any form with a date: Add / edit item, Add place, Lend, Manage members, Settings.
- **A modal** for one simple action: Place picker, Rename place, Rename borrower, Close loan, Edit member, Remove member, Rename household, Your label, Transfer ownership, Leave household, Delete household, Delete place, Invite shared message, Accept invitation, Add household.
- **One modal does one thing.** Never a modal on top of a modal.
- **Every modal has a close (X) button** as well as "Cancel".
- **Archive item has no modal:** it happens at once, and the message offers "Undo".

## Buttons
| Style | Look | Used for |
|---|---|---|
| Primary | Filled terracotta | The one main action: "Add item", "Move", "Move here", "Save changes", "Borrowers" on Loans, "Manage members", "Invite", "Accept invitation" |
| Secondary | Filled olive | A second action: "Add place", "Lend", "Close loan", "Change" on a place field, "Add “…”" when nothing matches, "Accept", "Restore" |
| Outline | Cream with a gray border | "More actions" and icon-only row buttons |
| Plain | No border or fill | "Cancel", "Decline", the menu button |
| Danger | Filled red | The final button of a destructive modal |
| Danger outline | Red border and text | A button that opens a destructive modal in a danger zone, and "Cancel invitation" |

- At most one primary button per screen; a screen with no clear main action has none (Borrowers, Borrower page, Activity, Archived).
- **The main action comes first,** top to bottom and start to end.
- An empty card never repeats the button beside the page title.
- Only "Add …" buttons carry an icon (a plus). Button text is weight 600.
- While saving, the button is disabled and reads "Saving…".

## Menus and destructive actions
- Two or more extra actions go in a **"More actions" menu** (three dots on a row): Place (Rename, Move, Delete), Item (Edit, Archive), Borrower (Rename, Archive), Member (Edit, Remove), account (Settings, Log out).
- A divider separates the destructive items. **Delete, Remove, Archive and Log out are red.**
- **"This can't be undone."** is bold and red (Delete household, Delete place, Delete account).

## Small screens
- Nothing wraps under the text of a row or card; a row's action sits at its end or in the card's top corner.
- Row actions are icon-only on mobile (three dots, X, pencil), each with a 44 px tap area and an accessible name. On desktop, single actions keep their words.
- Unrelated buttons are never squeezed side by side.
- Form and modal buttons share one line: the main button about 70%, "Cancel" about 30%. "Accept" and "Decline" too.
- Danger zone buttons are full width, one under the other, with their explanation above.
- Sections are evenly spaced, and every section heading row has the same height.
- Small text links have a 44 px tall tap area.

## Screens and states
| Screen | States |
|---|---|
| Search (home) | normal, empty query, no results, Viewer, dark |
| Places (tree) | normal, empty, Viewer |
| Place view | normal, more actions open, empty, Viewer |
| Item view | normal, lent, Viewer |
| Add / edit item (page) | add from a place, errors, edit |
| Place picker (modal) | browse, search, no match, moving a place |
| Add place (page), Rename place (modal) | add inside a place, add from the picker, error, rename |
| Lend (page) | empty, suggestions, new borrower, error |
| Close loan (modal) | nothing chosen, Returned, Lost |
| Loans | normal, search, no match, empty, Viewer |
| Borrowers | normal, search, no match, empty |
| Borrower page | normal, more actions, no open loans, rename, Viewer |
| Activity feed | normal, filtered, empty |
| Households | several households, one household |
| Household page | Owner, Editor, Viewer |
| Manage members (page, Owner only) | normal, no pending invitations, more actions open, edit a member, remove a member |
| Household modals | rename, transfer ownership, leave, delete (name not typed, typed), invite shared message (and copied), your label |
| Households: first run and invitations | first run, first run with an invitation, member with an invitation, accept an invitation, add household |
| Archived | normal, empty, Viewer |
| Auth | log in, log in error, sign up, verify email, forgot password, reset password |
| Settings (page) | owns a household, owns none |
| Small states | item more actions, archived with Undo, delete an empty place, message after a move, loading, saving |
| Menus | mobile menu, desktop account menu |

## Flows (Flows page)
1. **First run:** sign up → verify email → no household yet → add a household → Places is empty → add the first places → add the first item → the item is saved.
2. **Find something:** open the app → type "dr" → open the item.
3. **Move an item:** press Move → choose the place → moved, with the new path.
4. **Lend and close:** press Lend → type "re", pick the borrower → the badge shows in Search → press Close loan → choose Returned.
5. **Invite a member:** open the household → Manage members, fill in Invite → copy the message → the invitee logs in and presses Accept → they can add their own label → they appear in Members.

## How each screen works
- **Search:** no page title, only the field's label. Items first, then matching places. An empty query shows recent activity.
- **Place counts** include everything inside the place. A place with nothing inside shows "Empty". Empty lists inside a page use one muted line.
- **Place view:** "Items in this place" with "Add item" first, then "Places inside" with "Add place".
- **Item view:** one row under the title: "Move", then "Lend" (or "Close loan" while lent), then "More actions". The Place, Loan and Notes cards hold information only.
- **Add / edit item:** Name and "Notes (optional)". The place is one line with "Change". Edit changes only the name and notes; the place changes only through Move, so it is always logged.
- **Forms:** optional fields say "(optional)" in their label. Password fields have a show/hide button. "Keep me logged in" starts unchecked.
- **Place picker, no match:** a secondary "Add “…”" opens Add place with the name filled in; after saving, the picker returns with the new place selected.
- **Close loan:** nothing is pre-selected.
- **Loans:** a searchable list of loan cards (by item or borrower), overdue first, then the earliest due date. A **loan card** shows the item name, then "Borrower:", "Since:" and "Due:" lines with the "Overdue" badge on the Due line, and a compact "Close loan" in its top corner. A past loan shows "Closed:" with "Returned" or "Lost".
- **Borrowers:** no "Add borrower"; a borrower is saved the first time you lend to them. The Borrower page shows current loans, then past loans; Archive is disabled while a loan is open.
- **Activity:** grouped by day, filtered by "Show" (All activity, Items, Places, Members; lending counts as Items). Each entry is stacked: who did what, one labeled line per detail ("From:", "To:", "Borrower:"), then the time. Today's entries show "2h ago".
- **Lists:** simple records are rows in one card (search results, Borrowers, Households, members). Records with three or more lines or their own button are separate cards (loans, archived things, invitations).
- **Households:** each row shows the name or your label, the member count and your role. Invitations sit at the top, with "Accept" and "Decline".
- **Household page:** the name as the title with "Rename" (Owner only), the members (read-only) with "Manage members" (Owner only), "Your label" (members only), and the danger zone: the Owner sees Transfer ownership and Delete household; everyone else sees only Leave household.
- **Manage members:** the members, each with a three-dots menu (Edit: change the role; Remove: its own confirmation), then pending invitations (name, email, role and date, with "Cancel invitation"), then Invite a member (Name, Email, Role). The name is not checked against anything.
- **Invite shared message:** "Hi John! I added you to our household “Home” on Putello. Sign up or log in with this email: john@example.com at [YOUR APP LINK]". It says to send it to the person any way you like. "Copy message" reads "Copied" for a moment. The link stays a placeholder until the app has an address.
- **Accept invitation:** a modal with "You are joining “Home” as an Editor", an optional label, "Accept invitation" and "Cancel".
- **Add household:** a modal with one Name field. After adding, the app opens Places of the new household.
- **Transfer ownership** picks the new Owner and confirms in one modal. **Delete household** stays disabled until the name is typed exactly.
- **Settings:** one page: Profile, Appearance (Light, Dark, Same as my device), Password, Danger zone (Delete account, disabled while you own a household).
- **Archived:** "Items" and "Borrowers" sections with "Restore" on each card; restoring an item opens the Place picker.
- **Message after an action:** a green note at the top of the page ("Medal moved. It is now in John's room.") with "Dismiss", and "Undo" where possible. It goes away after 5 seconds.
- **Loading:** gray placeholder rows in the shape of the results.

## Choices made without the owner, for the final review
- **Your label replaces the name in breadcrumbs too.**
- **Settings stays one page,** in the owner's order.
- **"Log out" is red;** the owner's note had question marks.
- **Danger red** is `#B3261E`, not the palette's `#973F34`, which is too close to terracotta for a button.
- **Secondary buttons are filled olive,** as the palette says.
- **Invitations** sit on the Households list, not in the account menu.
- **Auth:** the app's name in plain type, one heading, one card. "Sign up" is the word used everywhere.
- The boards were checked by reading their markup, not by looking at them rendered.

## Notes for Phase 3 (code, not design)
They are now in `.ai/guidelines/ui.md`, which Boost copies into `CLAUDE.md`.

## Sample data used in the mockups
- Where each item is: Spare house keys in Drawer 1 · Medal in John's room · Passport folder in Blue box · Camera charger and Board games in TV stand · Drill and Winter blankets in Shelf A.
- Loans: Drill to Neighbor Reza, since Nov 3, due Nov 17. Board games to Sara's friend Mina, since Oct 12, was due Oct 26 (overdue).
- Invented, for single screens only: borrower "Uncle Hamid" and two past loans; "Sara renamed Top shelf" and "Mom joined Home" in the Activity feed; the members' example.com emails and the pending invitation to John; a second household labeled "Parents' home" whose real name is also "Home"; a new user "John"; an invitation from "Reza" to "Workshop"; archived items "Umbrella" and "Old phone charger" and archived borrower "Cousin Leila".
