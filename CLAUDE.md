# CLAUDE.md

Read this file first. It defines how to work in this repository.

## Documentation

Comprehensive project documentation lives in `docs/ai/`. Read the relevant files before making changes:

| File | Read when... |
|---|---|
| [`docs/ai/project_overview.md`](docs/ai/project_overview.md) | Starting a new session or task |
| [`docs/ai/business.md`](docs/ai/business.md) | Working on bet logic, draw lifecycle, payouts, risk |
| [`docs/ai/architecture.md`](docs/ai/architecture.md) | Adding controllers, middleware, routes, or services |
| [`docs/ai/database.md`](docs/ai/database.md) | Writing migrations, models, or queries |
| [`docs/ai/coding-style.md`](docs/ai/coding-style.md) | Writing any new code |
| [`docs/ai/api.md`](docs/ai/api.md) | Adding or modifying routes |

## Rules

- **All user-facing text must be in Thai.** Error messages, flash messages, validation messages, CSV headers — everything the user reads.
- **No Blade layouts or components.** Each view is a self-contained HTML file.
- **No service layer.** Business logic goes in controllers, following existing patterns.
- **No Form Request classes.** Use inline `$request->validate([...])`.
- **No comments** unless the WHY is non-obvious. Do not describe what the code does.
- **Whitelist sort columns** before passing to `orderBy()` — never trust raw query params.
- **UTF-8 BOM** must be prepended to all CSV exports (`"\xEF\xBB\xBF"`).
- **Dark mode** must be supported in any new view (Tailwind `class` strategy + inline localStorage script).
- **Dates in Thai:** use `thai_date()`, `thai_date_full()` etc. for display. Store as Gregorian `YYYY-MM-DD` in the database.
- **Delete operations** require the 6-digit `delete_code` from `Setting::get('delete_code')`.
- **Migrations are additive** — never modify existing migration files. Create new ones.
- **Do not add packages** without confirming with the user first.

## Project Type

Laravel 12 / PHP 8.2 web application. Thai underground lottery back-office. Internal tool, single operator. See `docs/ai/business.md` for domain details.
