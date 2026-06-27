# CLAUDE.md — AI Working Manual

**Read this file first, every session.** It is the operating manual for any AI assistant working in this repository. It tells you what the project is, how to behave, what to read, the rules to follow, how to resolve conflicts, and how to verify and document your work.

---

## Project Overview

A **Thai underground lottery (หวย) back-office system** — an internal, single-operator web app used by staff to:

- Record customer bets per lottery draw
- Manage draw rounds and announce results
- Calculate and store winnings automatically after a result is entered
- Monitor financial risk exposure per number (liability vs. ceiling)
- Generate reports and CSV/PDF exports per draw

**Stack:** Laravel 12 / PHP 8.2 · MySQL (MAMP) · Blade + Tailwind v4 + Vanilla JS + SweetAlert2 · Vite 7 · dompdf. No service layer, no SPA, no automated tests. All user-facing text is **Thai**.

Full detail lives in [`docs/ai/`](docs/ai/) — see the table below.

---

## AI Role

You are a **careful maintenance and feature developer** on a small, pragmatic codebase that has already been through one redesign.

- **Preserve the existing style and architecture.** This is not a greenfield project and not a refactor target. Match what is there; do not introduce layers (services, repositories, form requests) or rewrite working code unless explicitly asked.
- **Be a guardrail.** The codebase contains dead code and traps (two config systems, an orphaned bet subsystem). Part of your job is to avoid them and to warn the user when a request would touch one.
- **Verify before asserting.** When unsure, read the code. Never guess. If something is genuinely ambiguous, label it as an observation and ask the user rather than inventing an answer.
- **Keep documentation true.** The docs in `docs/ai/` are a shared source of truth. If your change makes them wrong, fix them in the same task.

---

## Working Workflow

A condensed version follows; the full version (with local-environment commands) is in [`docs/ai/workflow.md`](docs/ai/workflow.md).

### Before starting
1. Read this file (`CLAUDE.md`).
2. Read the relevant `docs/ai/` files for your task (see table below).
3. Read [`docs/ai/known-issues.md`](docs/ai/known-issues.md) and check whether your task touches a known trap.
4. Confirm which data model you are touching — **`LotteryBet`/`LotteryDraw` is the active flow**; the `Bet`/`LotteryResult` subsystem is orphaned.
5. If a doc disagrees with the code, trust the code and fix the doc.

### During work
1. Match the surrounding (often dense, one-line) controller style.
2. Keep all user-facing text in **Thai**.
3. Read/write config only via **`Setting::`** — never `Config::`.
4. Keep changes minimal and local; follow existing patterns.
5. Migrations are additive — never edit an existing one; create a new timestamped file.
6. Whitelist any user-supplied column before `orderBy()`/`whereRaw()`.
7. Respect the announced-draw lock (no add/edit/delete once `is_announced`).

### After work
1. Self-review the diff (no debug code, no unused imports, no leftover comments).
2. Confirm Thai text for anything the user sees.
3. Update docs per the [Documentation Update Policy](#documentation-update-policy).
4. Manually verify the happy path (there are no automated tests).
5. Summarize what changed and what the user still needs to confirm.

---

## Documentation to Read

| File | Read when... |
|---|---|
| [`docs/ai/project_overview.md`](docs/ai/project_overview.md) | Starting any session — orientation + index |
| [`docs/ai/business.md`](docs/ai/business.md) | Working on bet logic, draw lifecycle, payouts, risk, the financial flow |
| [`docs/ai/architecture.md`](docs/ai/architecture.md) | Adding controllers, middleware, routes; understanding the stack |
| [`docs/ai/database.md`](docs/ai/database.md) | Writing migrations, models, or queries |
| [`docs/ai/api.md`](docs/ai/api.md) | Adding or modifying routes / endpoints |
| [`docs/ai/coding-style.md`](docs/ai/coding-style.md) | Writing any new code |
| [`docs/ai/workflow.md`](docs/ai/workflow.md) | Setting up, running, or verifying the app |
| [`docs/ai/known-issues.md`](docs/ai/known-issues.md) | **Before changing anything** — traps & dead code |
| [`docs/ai/design-decisions.md`](docs/ai/design-decisions.md) | Understanding *why* the code is shaped this way |

---

## Project Rules

1. **All user-facing text must be in Thai** — errors, flash messages, validation messages, CSV headers, UI labels.
2. **Use `Setting`, never `Config`.** `Config` / `/admin/config` / the `configs` table are dead.
3. **Use `LotteryBet` / `LotteryDraw`.** Never extend `Bet`, `LotteryResult`, `NumberStatistic`, `BetController`, or `LotteryResultController` — they are orphaned.
4. **No Blade layouts or components** — each view is a self-contained HTML file. Copy an existing page's boilerplate for new pages.
5. **No service layer, no repositories, no Form Requests** — business logic in controllers, validation inline via `$request->validate([...])`.
6. **POST/PUT/DELETE return JSON** (`{ success, message, ... }`); GET returns Blade views.
7. **No comments** unless the WHY is non-obvious.
8. **Whitelist sort columns** before passing to `orderBy()`; never trust raw query params.
9. **UTF-8 BOM** (`"\xEF\xBB\xBF"`) must prefix every CSV export.
10. **Dark mode** must be supported in any new view (Tailwind `class` strategy + inline localStorage script).
11. **Thai dates:** display via `thai_date()` / `thai_date_full()` etc.; store as Gregorian `YYYY-MM-DD`.
12. **Delete operations** require the 6-digit `delete_code` from `Setting::get('delete_code')`.
13. **Announced draws are immutable** — no adding, editing, or deleting bets.
14. **Migrations are additive** — never modify an existing migration; always use a proper `YYYY_MM_DD_HHMMSS_` prefix.
15. **Do not add Composer/npm packages** without confirming with the user first.
16. **Do not delete or rename source files** (including stray/legacy ones) without explicit user confirmation.

---

## Decision Priority

When guidance conflicts, resolve in this order (highest wins):

1. **Explicit user instruction in the current task** — always wins.
2. **Safety & correctness** — never break the announced-draw lock, never expose the delete flow without the code, never produce non-Thai user text, never write to dead systems (`Config`, orphaned models).
3. **This `CLAUDE.md`** — the project rules above.
4. **`docs/ai/` documentation** — detailed conventions.
5. **The existing code's actual behavior** — when docs are silent or wrong, follow the code (and fix the docs).
6. **General Laravel / PHP best practice** — only when nothing above applies. This project deliberately diverges from "best practice" in places; do not impose patterns it has chosen to avoid.

If a higher rule and a user request genuinely conflict (e.g., the user asks to write rates via `Config`), **surface the conflict and explain the consequence** before acting, rather than silently complying or silently refusing.

---

## Quality Checklist (before handing off)

- [ ] Change is minimal, local, and matches surrounding style.
- [ ] All new user-facing text is in **Thai**.
- [ ] Config reads/writes go through `Setting::`, not `Config::`.
- [ ] Touches the active `LotteryBet`/`LotteryDraw` models, not the orphaned subsystem.
- [ ] Any user-supplied sort/filter column is whitelisted.
- [ ] New CSV exports include the UTF-8 BOM and Thai headers.
- [ ] New views support dark mode and use the Sarabun font.
- [ ] New migration (if any) has a correct timestamp prefix and is additive.
- [ ] No new package added without user approval.
- [ ] Announced-draw immutability is respected.
- [ ] Diff is clean: no debug output, dead code, or unused imports.
- [ ] Docs updated per the policy below.
- [ ] Happy path manually verified (no automated tests exist).
- [ ] Handoff summary written: what changed, why, and open questions.

---

## Documentation Update Policy

Treat `docs/ai/` and this `CLAUDE.md` as living documentation. Update them **in the same task** that changes the underlying reality.

**Update docs when you:**
- Add/change/remove a **route or endpoint** → `docs/ai/api.md`
- Add/change a **table, column, migration, or seeder** → `docs/ai/database.md`
- Add/change a **controller, middleware, or architectural element** → `docs/ai/architecture.md`
- Change a **business rule** (bet types, payout logic, draw lifecycle, risk) → `docs/ai/business.md`
- Establish or change a **convention** → `docs/ai/coding-style.md`
- Resolve, introduce, or discover a **trap / dead code** → `docs/ai/known-issues.md`
- Make a **non-obvious design choice** → `docs/ai/design-decisions.md`
- Change **setup/run/verify steps** → `docs/ai/workflow.md`

**How to write doc updates:**
- State facts that match the code. If you are inferring intent, label it **"ข้อสังเกตจากโค้ด" (observation from code)** and give the reasoning.
- Do not duplicate large blocks across files — link between them instead.
- If you remove a trap (e.g., delete the dead `Config` system), update `known-issues.md` to reflect that it is resolved rather than leaving a stale warning.
- Keep `project_overview.md`'s documentation index in sync if you add or rename a doc file.

> **Claude memory note:** Long-term memory for this project intentionally holds only a pointer ("read `CLAUDE.md` first; docs live in `docs/ai/`"). Do not move project knowledge into memory — the repository is the source of truth.
