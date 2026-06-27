# Architecture

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 / PHP 8.2 |
| Database | **MySQL** (MAMP) — `.env` uses `DB_CONNECTION=mysql`, database `projects`, user `root`. The `close_time` migration uses MySQL-specific SQL (`DAY()`, `DATE_SUB`, `INTERVAL`). |
| Frontend | Blade templates + Tailwind CSS v4 + Vanilla JS |
| Build tool | Vite 7 + laravel-vite-plugin |
| PDF | barryvdh/laravel-dompdf |
| JS alerts | SweetAlert2 (CDN) |
| Font | Sarabun (Google Fonts, Thai-first) |
| Dev server | MAMP (Windows) |

> Although `composer.json`'s skeleton script touches `database/database.sqlite`, the active connection is **MySQL** — do not assume SQLite.

No SPA, no Vue/React, no Livewire. Page loads are full Blade renders; form submissions are JSON AJAX calls handled by Vanilla JS + SweetAlert2.

---

## Folder Structure

```
app/
  Http/
    Controllers/          9 controllers + base Controller, flat (no subdirectories)
                          ⚠️ BetController + LotteryResultController are ORPHANED (no routes)
    Middleware/           AdminMiddleware, StaffOrAdminMiddleware
  Models/                 note: User.php contains 4 classes (User, Config, LotteryDraw, LotteryBet)
  Helpers/
    ThaiDateHelper.php    Static class for Thai date formatting
    helpers.php           Global function wrappers (autoloaded via composer.json files[])
  Providers/
    AppServiceProvider.php  Empty; nothing registered
bootstrap/
  app.php               Middleware aliases registered here (Laravel 12, no Kernel.php)
resources/
  views/
    auth/               login.blade.php
    bets/               index, history, statistics (+ index.blade.phpbak backup)
    lottery/            index  (used only by the orphaned LotteryResultController)
    admin/              draws, draw-results, users, config, risk-settings
                        reports/ (index, summary, pdf, statistics, index-old)
    dashboard.blade.php
    welcome.blade.php
  css/app.css
  js/app.js
routes/
  web.php               Single file, all active routes
  console.php           Only the skeleton `inspire` command
database/
  migrations/           11 active .php migrations (+ 2 disabled .zip files)
  seeders/
    DatabaseSeeder.php  ACTIVE — seeds a default `admin` user (id 1)
docs/
  ai/                   Project documentation for AI assistants
```

> See [`known-issues.md`](known-issues.md) for the orphaned controllers, the dual `Config`/`Setting` systems, and stray root files (`general`, `jaenaja,`).

---

## Request Lifecycle

```
Browser → web.php route → [auth middleware] → [role middleware] → Controller → View/JSON
```

- GET requests → controller returns `view(...)` with compact'd data
- POST/PUT/DELETE requests → controller returns `response()->json([...])`
- Frontend JS reads the JSON and shows SweetAlert2 success/error dialogs, then reloads or redirects

---

## Authentication

- Session-based (Laravel default)
- Login uses `username` field, not `email`
- `Auth::attempt(['username' => ..., 'password' => ...])` in `AuthController`
- No email verification, no password reset flow

---

## Middleware

| Alias | Class | Guards |
|---|---|---|
| `auth` | Laravel built-in | Must be logged in |
| `admin` | `AdminMiddleware` | Role must be `admin` |
| `staff_or_admin` | `StaffOrAdminMiddleware` | Role must be `admin` or `general` |

Registered in `bootstrap/app.php` via `$middleware->alias([...])` (Laravel 12 style, no `Kernel.php`).

---

## Controllers

### Active controllers (wired in `routes/web.php`)

| Controller | Route prefix | Main responsibility |
|---|---|---|
| `AuthController` | `/login`, `/logout` | Session auth |
| `DashboardController` | `/` | Today's stats, open-draws API, realtime-sales API |
| `LotteryBetController` | `/bets` | Record bets, history, soft-delete, CSV export |
| `LotteryDrawController` | `/admin/draws` | Manage draws, announce results, calculate winnings |
| `ReportController` | `/admin/reports` | Summary, statistics, PDF, CSV exports |
| `RiskSettingsController` | `/admin/risk-settings` | Payout rates, ceilings, commission, delete code |
| `AdminController` | `/admin/users`, `/admin/config` | User CRUD; `/admin/config` is **legacy/dead** (writes to unused `configs` table) |

### Orphaned controllers (NOT wired to any route — do not extend)

| Controller | Why it exists |
|---|---|
| `BetController` | First-generation bet flow using the `Bet` model. No route references it. References a `bets.sales` view that does not exist. |
| `LotteryResultController` | First-generation result flow using `LotteryResult` + `NumberStatistic`. No route references it. |

See [`design-decisions.md`](design-decisions.md) and [`known-issues.md`](known-issues.md) for the two-generation history.

**No service layer.** All business logic (winning calculation, liability computation, toad permutations) lives directly in controllers.

---

## Settings System

The `Setting` model provides a typed key/value store with 1-hour cache:

```php
Setting::get('rate_3_top', 900)   // read with default, casts by `type`
Setting::set('rate_3_top', 850, 'decimal', 'description', 'payout')  // write + Cache::forget that key
Setting::getGroup('payout')       // all settings in a group, cast
Setting::clearCache()             // ⚠️ calls Cache::flush() — wipes the ENTIRE cache store
```

Settings are grouped: `general`, `risk`, `payout`, `security`. Values are cast on read by their `type` (`integer`, `decimal`/`float`, `boolean`, `json`, else string).

> **`Setting` is authoritative.** The older `Config` model (table `configs`) is dead — only `/admin/config` writes to it and nothing reads it for calculation. Always use `Setting`. See [`known-issues.md`](known-issues.md) #1.

---

## Exports

| Format | Method | Implementation |
|---|---|---|
| PDF | `ReportController::exportPDF()` | DomPDF via `view('admin.reports.pdf')` |
| CSV (bets) | `LotteryBetController::exportExcel()` | Manual `fputcsv` with UTF-8 BOM |
| CSV (customer summary) | `ReportController::exportCustomerSummary()` | Manual `fputcsv` with UTF-8 BOM |
| CSV (over-limit) | `ReportController::exportOverLimit()` | Manual `fputcsv` with UTF-8 BOM |

UTF-8 BOM (`\xEF\xBB\xBF`) is prepended to all CSV files so Microsoft Excel opens them correctly.

---

## What Does NOT Exist

- No queues / jobs
- No events / listeners
- No observers
- No form request classes (validation is inline `$request->validate()`)
- No repository pattern
- No `routes/api.php` (no API routes file; the two `/api/*` endpoints live in `web.php`)
- No project tests (only the Laravel skeleton in `tests/`)
- No Blade components or layouts (every view is a self-contained HTML document)
- No real `console.php` commands (only the skeleton `inspire`)
