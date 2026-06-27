# Project Overview

## What is this?

A **Thai underground lottery (หวย) back-office system** for a single operator. It is an internal web application — not public-facing — used by staff to:

- Record customer bets per lottery draw
- Manage draw rounds and announce results
- Calculate winnings automatically after result entry
- Monitor financial risk exposure per number
- Generate reports and exports for each draw

## Technology

- **Laravel 12 / PHP 8.2** — standard MVC, no service layer
- **MySQL** (MAMP) — database `projects`; not SQLite despite the skeleton script
- **Blade templates** — every view is a self-contained HTML file (no shared layouts)
- **Tailwind CSS v4** — dark mode supported in all views
- **Vanilla JS + SweetAlert2** — AJAX form submissions; no Vue/React/Livewire
- **Vite 7** — asset bundling
- **barryvdh/laravel-dompdf** — PDF exports
- **MAMP** on Windows — local dev environment

## Documentation Index

| File | Contents |
|---|---|
| [`business.md`](business.md) | Domain glossary, bet types, draw schedule & lifecycle, risk management, financial flow |
| [`architecture.md`](architecture.md) | Stack, folder structure, request lifecycle, controllers, settings system |
| [`database.md`](database.md) | All tables, column definitions, seeder, model notes, migration list |
| [`api.md`](api.md) | All routes, HTTP methods, JSON response format, internal API endpoints |
| [`coding-style.md`](coding-style.md) | PHP style, Blade conventions, Thai date handling, CSV export pattern, security |
| [`workflow.md`](workflow.md) | How to approach a task (before / during / after) + local environment |
| [`known-issues.md`](known-issues.md) | Dead code, dual config systems, traps — **read before changing anything** |
| [`design-decisions.md`](design-decisions.md) | Why the code is shaped the way it is (two generations, denormalized results, etc.) |
| [`tasks/2026-06-implementation-plan.md`](tasks/2026-06-implementation-plan.md) | **Pending task** — approved spec for 3 features (Excel leading zeros, toad grouping, view deleted bets) |

## Key Concepts to Know

1. **Two generations of code exist.** The active bet flow is `LotteryBet` / `LotteryDraw`. The first-generation `Bet` / `LotteryResult` / `NumberStatistic` subsystem (and `BetController` / `LotteryResultController`) is orphaned — do not extend it. See [`known-issues.md`](known-issues.md) #2.
2. **`Setting` is the authoritative config**, not `Config`. The `/admin/config` page is dead. See [`known-issues.md`](known-issues.md) #1.
3. **Draw date is the relationship key** — `LotteryBet` and `LotteryDraw` relate via `draw_date` (date), not an integer id.
4. **All user-facing text is Thai** — error messages, column headers, CSV headers, UI labels.
5. **No service layer** — business logic (win calculation, liability) lives in controllers.
6. **`User.php` contains 4 models** — `User`, `Config`, `LotteryDraw`, `LotteryBet` are all defined in `app/Models/User.php`.
7. **Delete requires a PIN** — deleting any bet requires a 6-digit delete code configured in Risk Settings.
8. **Thai date conversion** — UI may send dates in พ.ศ. (Buddhist era, year > 2500). `convertThaiDateToYmd()` in `LotteryDrawController` handles conversion.
9. **Draws follow the 1st/16th Thai Government Lottery schedule** (by convention, not enforced).
