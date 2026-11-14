# Workflow and rules

## Phases
| # | Phase | Where it happens | Output (exit criteria) |
|---|---|---|---|
| 1 | Plan and finalize features | Claude chat, in this Project | `docs/phase-1/features.md` approved: name, stack, v1 scope, data model, screen list |
| 2 | Design (UI/UX) | Claude Design | All v1 screens and main flows designed and approved; link and notes saved in `docs/phase-2/design.md` |
| 3 | Implement | Claude Code (CLI) in the app's repo | v1 built and tested, with a portfolio-ready README. Deployment comes later (see the decision log). |

A phase starts only after the previous phase meets its exit criteria and the owner signs off.

## Source of truth
- **Phases 1 and 2:** the docs in this Project (`docs/…`).
- **Phase 3:** the repo. At the start of Phase 3, these docs are copied into the repo's `docs/` folder, and a `CLAUDE.md` is created with the coding rules and a pointer to `docs/`. From then on Claude Code keeps `docs/02-progress.md` in the repo up to date. This Project's copy is updated at milestones, with a note saying the repo is current.

## Handoffs
- **1 → 2:** give Claude Design `docs/00-overview.md`, `docs/phase-1/features.md` (especially the screen list and the UI kit), and any style notes.
- **2 → 3:** record the design link and decisions in `docs/phase-2/design.md`, then create the repo from the starter kit and write its `CLAUDE.md`.

## Rules Claude follows (all phases)
1. At the start of every session, read `docs/02-progress.md` and this file before doing anything else.
2. **Ask, don't assume.** If anything important is vague, contradictory or looks like a dictation mistake, ask before acting. Never assume something that affects the result. Asking several times is fine.
3. Propose one step at a time and wait for the owner's approval. Nothing is marked done until it's approved. (Exception for the end of Phase 2: see the owner rule of 2026-11-11 below.)
4. After each approved step, update `docs/02-progress.md`: check the item off, add the date, and log any decision with a one-line reason.
5. When the owner states a new rule or workflow, add it to "Owner rules" below with the date, without being asked.
6. Keep the docs short and current. Edit what's there instead of piling on notes.
7. Assume the latest versions of every tool and framework unless the owner says otherwise.
8. **Write American English everywhere:** the app's text, the designs, the code, the docs and the chat. "Color", "canceled", "gray", "Nov 3".

## Owner rules (added as stated)
- 2026-11-10: This is a portfolio project. Code, UI and docs must be simple, clean and easy to follow. The owner reviews and approves every step (and, in Phase 3, every line of code).
- 2026-11-10: Tracking is automatic. Claude keeps progress, decisions and rules up to date without being asked.
- 2026-11-10: The owner uses voice dictation, so messages may have wrong, missing or extra words. When something important is vague or seems off, ask. Don't guess. Ask as many times as needed.
- 2026-11-11: Everything must match. The same job uses the same pattern and the same words on every screen, and the design must leave no open question for the implementation. When a new screen breaks a pattern already used, change it to match or raise it.
- 2026-11-11: For the rest of Phase 2, Claude designs all remaining screens and flows without asking for confirmation, following the rules already agreed. The owner then reviews the whole design once, together, to make everything match. Choices Claude made alone are listed in `docs/phase-2/design.md` for that review.
- 2026-11-11: American English only, for everything (see rule 8).
