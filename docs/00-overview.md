# Putello, overview

## What it is
A shared home inventory for households. You set up the places in your home as a tree, record which item is in which place, and find anything right away. It also keeps track of who moved what and what you lent to whom. v1 is in English, and it's built so other languages (including RTL ones like Persian) can be added later.

> "Mom, where is my medal?" → search "medal" → **Medal (Taekwondo 2019)**: John's room › Wardrobe › Top shelf › Blue box

## Why it exists
1. **Portfolio.** It's a real, small, useful Laravel app that shows how I work: clean code, conventions, tests, and a structure that's easy to follow. It sits next to my three GitHub packages.
2. **A real need.** Things get put away "somewhere safe" and then forgotten, or a family member moves them. Lent items get forgotten too.
3. **Open for others.** Anyone can fork it and self-host it for their own country or users, in their own language.

## Principles (apply to every phase)
- **Simple over clever.** Keep the scope small and do it well. When in doubt, leave it out.
- **Readable.** A reviewer should understand any file in one pass. Follow framework conventions and avoid surprises.
- **Useful.** Every feature has to solve a real household problem.
- **Easy UI.** Fast and easy to navigate, nothing fancy. A first-time user should get it without help. RTL-ready.
- **History never breaks.** Logs keep what was true at the time.
- **Owner approval.** Claude proposes, the owner reviews and approves each step (and, in Phase 3, each change).
- **American English** everywhere: the app, the code and the docs.

## Glossary
| Term | Meaning |
|---|---|
| Household | A shared home (family, roommates, dorm), with a name such as "Home". A user can create or join many. |
| Label | A member's own name for a household they joined, which only they see ("Parents' home" for a household named "Home"). Optional. |
| Owner | The household's creator (or whoever ownership was transferred to). Only the owner manages members and roles, and only the owner can rename or delete the household. |
| Member | A user in a household, with the role **Viewer** (see and search) or **Editor** (see and change). |
| Place | A node in the household's tree, with no depth limit. Its path reads Kitchen › Left cupboard › Drawer 2. |
| Item | A thing you want to find again. An active item sits in exactly one place. |
| Borrower | A saved name you lend items to. |
| Loan | A record that an item was lent to a borrower. It's closed as **Returned** or **Lost** (lost items are archived). |
| Archive | Hides an item or borrower while keeping its history. It can be restored. |
| Activity | A log entry: who did what, and when, with names saved as they were at that moment. |
