# Phase 1: Data model (APPROVED as a draft on 2026-11-10, updated with the design decisions of 2026-11-11; details are finalized at the start of Phase 3)

## Tables
All tables have `id` and `timestamps` unless noted.

### users (from the starter kit, plus one column)
| Column | Type | Notes |
|---|---|---|
| name, email, email_verified_at, password, … | | Unchanged from the starter kit. `User implements MustVerifyEmail`. |
| current_household_id | FK households, nullable | The household used last. Opening the app or logging in lands on its Search. Set to null if that household is deleted or left. |

### households
| Column | Type | Notes |
|---|---|---|
| name | string | Set and changed by the owner. |
| owner_id | FK users | **restrict on delete**, so the database itself blocks deleting a user who still owns a household |

### household_user (pivot: membership)
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | |
| user_id | FK, cascade | |
| role | string → `Role` enum | `viewer` or `editor` |
| label | string, nullable | The member's own name for the household, seen only by them. Always null for the owner. |
| | | unique (household_id, user_id) |

**Ownership lives in exactly one place: `households.owner_id`.** The owner also has a membership row with role `editor`. So:
- "Can edit?" means owner **or** editor. "Is owner?" means `owner_id = user.id`.
- Transferring ownership just changes `owner_id`. The old owner is already an editor, which matches the decision.
- **Displayed household name** for a user: their `label` if set, otherwise `households.name`.

### invitations (pending only)
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | |
| invited_by | FK users, nullable, null on delete | |
| name | string | The invited person's name, used in the shared message and the pending list. Not checked against anything. |
| email | string | unique (household_id, email) |
| role | `Role` enum | |

The table only holds **pending** invitations. Accepting creates the membership (with the optional label) and deletes the invitation; declining or canceling deletes it. What happened is recorded in the activity log, so there's no status column.

### places
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | |
| parent_id | FK places, nullable, **restrict on delete** | null means the place sits directly in the household. The database blocks deleting a place that still has child places. |
| name | string | Unique among siblings (checked in validation) |

### items
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | Needed because archived items have no place |
| place_id | FK places, nullable, **restrict on delete** | null only while archived. An active item always has a real place. The database blocks deleting a place that still has items. |
| name | string | |
| notes | text, nullable | |
| deleted_at | | `SoftDeletes` = archived |

### borrowers
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | |
| name | string | unique (household_id, name), archived ones included |
| deleted_at | | `SoftDeletes` = archived |

### loans
| Column | Type | Notes |
|---|---|---|
| item_id | FK, cascade | |
| borrower_id | FK, cascade | |
| lent_at | date | |
| due_at | date, nullable | |
| note | text, nullable | |
| closed_at | timestamp, nullable | null means the loan is open. An item has at most one open loan. |
| outcome | `LoanOutcome` enum, nullable | `returned` or `lost`. Set when the loan is closed. |

### activities (no updated_at; entries are never edited)
| Column | Type | Notes |
|---|---|---|
| household_id | FK, cascade | |
| user_id | FK users, nullable, null on delete | |
| user_name | string | snapshot |
| type | `ActivityType` enum | e.g. `item.moved`, `item.path_changed`, `place.renamed`, `loan.lost`, `member.role_changed` |
| item_id | FK, nullable | link only |
| place_id | FK, nullable, null on delete | link only |
| subject_name | string | snapshot: the item, place or member the entry is about |
| from_value | string, nullable | snapshot: old path, old name, old role… |
| to_value | string, nullable | snapshot: new path, new name, borrower name, new role… |
| created_at | | |

- Entries store **data, not sentences**. The entry is built when it's displayed, from a translation string per `type` (e.g. `__('activity.item.moved', [...])`), as a stacked list: the action line, then one labeled line per detail. That keeps the log translatable later.
- The household feed hides `item.path_changed`, which only shows in an item's own history.

## Enums
`Role` (viewer, editor) · `LoanOutcome` (returned, lost) · `ActivityType`

## How the key parts work
- **Paths:** load the household's places in **one query** and build the paths in memory with a small `PlaceTree` class. A home has a few hundred places at most, so this is fast. It works the same on SQLite and PostgreSQL and needs no recursive SQL. A path is made of places only; it does not start with the household's name.
- **Search:** Laravel's `whereLike(…, caseSensitive: false)` turns into `LIKE` on SQLite and `ILIKE` on PostgreSQL, so it's case-insensitive on both. There's no search engine or full-text index; the scope is one household, which is small.
- **Integrity at two levels:** validation gives friendly messages, and foreign keys (`restrict`) are the safety net.
- **Household delete:** foreign key cascades remove everything. It runs in one transaction inside a `DeleteHousehold` action.

## Extra rules (approved 2026-11-10)
1. **No cycles:** a place can't be moved inside itself or one of its own child places.
2. **Archived borrower names:** typing the name of an archived borrower while lending restores them, instead of failing as a duplicate.
3. **Already a member:** you can't invite an email that already belongs to a member, or that already has a pending invitation to that household.

## To check at the start of Phase 3
- `restrict` on `places.parent_id` and `items.place_id` might block the household-delete cascade when places are nested (PostgreSQL checks `RESTRICT` immediately). Write a test for deleting a household with nested places on both databases, and if needed use `NO ACTION` or delete in order inside `DeleteHousehold`.
