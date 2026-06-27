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
- **Blade templates** — every view is a self-contained HTML file (no shared layouts)
- **Tailwind CSS v4** — dark mode supported in all views
- **Vanilla JS + SweetAlert2** — AJAX form submissions; no Vue/React/Livewire
- **Vite 7** — asset bundling
- **barryvdh/laravel-dompdf** — PDF exports
- **MAMP** on Windows — local dev environment

## Documentation Index

| File | Contents |
|---|---|
| [`business.md`](business.md) | Domain glossary, bet types, draw lifecycle, risk management, financial flow |
| [`architecture.md`](architecture.md) | Stack, folder structure, request lifecycle, controllers, settings system |
| [`database.md`](database.md) | All tables, column definitions, model notes, migration list |
| [`coding-style.md`](coding-style.md) | PHP style, Blade conventions, Thai date handling, CSV export pattern, security |
| [`api.md`](api.md) | All routes, HTTP methods, JSON response format, internal API endpoints |

## Key Concepts to Know

1. **Draw date is the primary key** — `LotteryBet` and `LotteryDraw` relate via `draw_date` (date), not an integer id.
2. **All user-facing text is Thai** — error messages, column headers, CSV headers, UI labels.
3. **No service layer** — business logic (win calculation, liability) lives in controllers.
4. **`User.php` contains 4 models** — `User`, `Config`, `LotteryDraw`, `LotteryBet` are all defined in `app/Models/User.php`.
5. **Delete requires a PIN** — deleting any bet requires a 6-digit delete code configured in Risk Settings.
6. **Thai date conversion** — UI may send dates in พ.ศ. (Buddhist era, year > 2500). `convertThaiDateToYmd()` in `LotteryDrawController` handles conversion.
