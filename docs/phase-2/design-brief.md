# Design brief: Putello

> **How to use:** paste this whole document into Claude Design as the prompt. It's self-contained. It was first used on 2026-11-10 and updated on 2026-11-11 to match the finished design; the full design decisions are in `docs/phase-2/design.md`.

## 1. The product
A web app for a household (family, roommates, a dorm) to remember **where things are**. You build a tree of the places in your home, put items in places, and find any item instantly with search. It also tracks **who moved what** and **what you lent to whom**.

The name is "Putello". Use it as plain text and don't design a logo around it.

This is an open-source portfolio project. The design must be **simple, clean, and easy to understand for a first-time user**. Avoid anything fancy, dense or clever. Reviewers should be able to grasp every screen in a few seconds. All text is in **American English**.

## 2. Hard constraints
- **Component library: Flux UI (free tier)** on Tailwind CSS, from the Laravel Livewire starter kit. Design with the kind of components Flux free provides: button, input, textarea, select, checkbox, radio, switch, field, modal, dropdown/menu, card, badge, callout, breadcrumbs, navbar, sidebar, navlist, table, avatar, heading/text, separator, tooltip, skeleton, pagination.
- **Not available:** autocomplete or combobox, date picker, command palette, tabs, popover, charts. Two simple custom pieces are allowed:
  1. **Suggestion input:** a text input with a list of matches under it (used for borrower names and the place picker).
  2. **Tree list:** an indented, expandable list of places.
- Keep the starter kit's look and layout (sidebar on desktop, which collapses into a header menu on mobile). Customize the colors and spacing, but don't fight the library.
- **Mobile first.** People use it while standing in a room looking for something. Design at **390 px** (mobile) and **1280 px** (desktop). The mobile layout is designed on its own, not the desktop one squeezed.
- **RTL-ready:** v1 is English only, but Persian and Arabic will be added later. Use start/end alignment, keep icons and layouts direction-neutral (arrows and chevrons flip), and keep text in labels rather than in images.
- **Accessible:** clear labels, good contrast, tap targets of at least 44 px.

## 3. Concepts (use these exact words in the UI)
| Term | Meaning |
|---|---|
| Household | A shared home with a name, such as "Home". A user can belong to several. |
| Label | A member's own name for a household they joined, seen only by them ("Parents' home"). Shown instead of the name wherever the household is named. |
| Owner | The person who manages the household: its name, members, roles, ownership transfer, deleting it. |
| Member | A person in a household: **Viewer** (can only look and search) or **Editor** (can change things). |
| Place | A spot in the home, nested as deep as needed. Its path reads Kitchen › Left cupboard › Drawer 2; it does not start with the household's name. |
| Item | A thing you want to find again. It's in exactly one place. |
| Borrower | A saved name of someone you lend things to. |
| Loan | An item lent to a borrower. It's closed as **Returned** or **Lost**. |
| Archive | Hide an item or borrower while keeping its history. It can be restored. |
| Activity | The log of who did what, and when. |

## 4. Navigation
Menu: **Search** (the home screen) · **Places** · **Loans** · **Activity** · **Manage “Home”** (the household's page), then a divider, then **Households** (the list of all your households, with pending invitations).
The household you are in is always shown: under the app name on desktop, and as "Putello – Home" in the mobile header. Account menu: Settings, Log out.

## 5. Screens
Each screen needs its **normal**, **empty** and **Viewer** (read-only, with no edit controls) states where they apply.

1. **Search (home):** a large search box at the top, focused automatically, with results updating as you type. Each result shows the item name, its path, and a **"Lent to Sara · since Nov 3"** badge if it's lent out. Matching places appear in a separate small group. With an empty query, show recent activity or a short hint. Show a clear "no results" state with an "Add …" button.
2. **Places (tree):** the whole household tree, expandable, with an item count per place. Editors can add a place directly in the household.
3. **Place view:** breadcrumbs, the items in this place, then its child places. Editor actions: add item here, add a place inside, and in a "More actions" menu: rename, move, delete (disabled with an explanation when the place isn't empty).
4. **Item view:** name, notes, current path, loan status, and **history** (including "path changed" entries). Editor actions: move, lend or close the loan, and in "More actions": edit, archive.
5. **Add / edit item (page):** name (required) and notes. The place is pre-filled when adding from a place view; otherwise the place picker opens.
6. **Place picker (modal):** a search box that filters places by name and shows each match with its path, plus browsing the tree, with the household at the top. Used by add, move, restore and move place. When moving a place, its own child places are disabled. With no match, it offers to add the place.
7. **Lend (page):** borrower (suggestion input: pick a saved borrower or keep the typed name to create a new one), lent date (defaults to today), optional due date and optional note. **Close loan (modal):** two clear choices, **Returned** or **Lost** (lost archives the item, so warn about that).
8. **Loans:** a searchable list of open-loan cards, overdue first, plus a button to Borrowers.
9. **Borrowers:** a searchable list with an open-loan count each. **Borrower page:** current loans and past loans; rename and archive (archive is disabled while they have an open loan).
10. **Activity feed:** grouped by day, each entry stacked: "Ali moved **Medal**", then "From: Kitchen › Left cupboard › Drawer 2", "To: John's room", then the time. A filter: All activity, Items, Places, Members.
11. **Households:** the list of all your households (with your label, the member count and your role), "Add household", and pending invitations with Accept and Decline. Accepting asks for an optional label.
12. **Household page:** name (rename, Owner only), members (read-only), your label (members only), and the danger zone: the Owner sees transfer ownership and delete household (confirmed by typing its name); everyone else sees leave.
13. **Manage members (page, Owner only):** members with a menu each (edit the role, remove), pending invitations (cancel), and invite a member (name, email, role). After inviting, show a ready-made message with a copy button: "Hi John! I added you to our household “Home” on Putello. Sign up or log in with this email: john@example.com at [YOUR APP LINK]".
14. **First run:** a user with no household sees the Households list with "You have no households yet" and any invitations.
15. **Auth and Settings:** log in, sign up, verify email, forgot or reset password, and Settings (Profile, Appearance, Password, Delete account). These are mostly the starter kit's screens; only align their style.
16. **Archived:** a simple list of archived items and borrowers with Restore buttons (restoring an item opens the place picker).

## 6. Key flows to show step by step
1. **First run:** sign up → verify email → add a household → add the first places → add the first item.
2. **Find something:** open the app → type "dr" → see the results with their paths → open one.
3. **Move an item:** item → Move → place picker → choose → message with the new path.
4. **Lend and close:** item → Lend → type "re" → pick "Neighbor Reza" → save → the badge shows in search → later, Close loan → Returned.
5. **Invite a member:** Manage “Home” → Manage members → Invite (name, email, role) → copy the shared message → the invitee logs in → accepts, with an optional label → appears in the members list.

## 7. Sample data for the mockups
Household "Home" (owner Ali; members Sara (Editor) and Mom (Viewer)).
Places: Kitchen › Left cupboard › Drawer 1/2/3 · John's room › Wardrobe › Top shelf › Blue box · Living room › TV stand · Storage room › Shelf A.
Items: Medal (Taekwondo 2019), Passport folder, Spare house keys, Drill, Winter blankets, Board games, Camera charger.
Loans: Drill → Neighbor Reza (since Nov 3, due Nov 17); Board games → Sara's friend Mina (overdue).

## 8. What I want back
- A short **visual direction** first (colors, type, spacing, light and dark), as 1–2 options for me to pick from.
- Then every screen above at mobile and desktop sizes, with the states listed.
- The 5 flows.
- A short list of the components used, mapped to Flux free components plus the 2 custom ones.

## 9. Out of scope (don't design these)
Photos · QR labels · reminders and notifications · tags · borrowing from others · data export · languages other than English · native app
