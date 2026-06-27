# Architecture

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 / PHP 8.2 |
| Frontend | Blade templates + Tailwind CSS v4 + Vanilla JS |
| Build tool | Vite 7 + laravel-vite-plugin |
| PDF | barryvdh/laravel-dompdf |
| JS alerts | SweetAlert2 (CDN) |
| Font | Sarabun (Google Fonts, Thai-first) |
| Dev server | MAMP (Windows) |

No SPA, no Vue/React, no Livewire. Page loads are full Blade renders; form submissions are JSON AJAX calls handled by Vanilla JS + SweetAlert2.

---

## Folder Structure

```
app/
  Http/
    Controllers/          8 controllers, flat (no subdirectories)
    Middleware/           AdminMiddleware, StaffOrAdminMiddleware
  Models/                 All models — note: User.php contains 4 classes
  Helpers/
    ThaiDateHelper.php    Static class for Thai date formatting
    helpers.php           Global function wrappers (autoloaded via composer.json)
  Providers/
    AppServiceProvider.php  Empty; nothing registered
bootstrap/
  app.php               Middleware aliases registered here
resources/
  views/
    auth/               login.blade.php
    bets/               index, history, statistics
    admin/              draws, draw-results, users, config, risk-settings
                        reports/ (index, summary, pdf, statistics)
    dashboard.blade.php
    welcome.blade.php
  css/app.css
  js/app.js
routes/
  web.php               Single file, all routes
database/
  migrations/           ~10 migrations
  seeders/              (none active)
docs/
  ai/                   Project documentation for AI assistants
```

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

| Controller | Route prefix | Main responsibility |
|---|---|---|
| `AuthController` | `/login`, `/logout` | Session auth |
| `DashboardController` | `/` | Today's stats, open-draws API, realtime-sales API |
| `LotteryBetController` | `/bets` | Record bets, history, soft-delete, CSV export |
| `LotteryDrawController` | `/admin/draws` | Manage draws, announce results, calculate winnings |
| `ReportController` | `/admin/reports` | Summary, statistics, PDF, CSV exports |
| `RiskSettingsController` | `/admin/risk-settings` | Payout rates, ceilings, commission, delete code |
| `AdminController` | `/admin/users`, `/admin/config` | User CRUD, legacy config |
| `BetController` | (legacy, check routes) | May be superseded by LotteryBetController |

**No service layer.** All business logic (winning calculation, liability computation, toad permutations) lives directly in controllers.

---

## Settings System

The `Setting` model provides a typed key/value store with 1-hour cache:

```php
Setting::get('rate_3_top', 900)   // read with default
Setting::set('rate_3_top', 850, 'decimal', 'description', 'payout')  // write + clear cache
Setting::clearCache()             // manual cache bust
```

Settings are grouped: `general`, `risk`, `payout`, `security`.

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
- No API routes file (`routes/api.php` is unused)
- No tests
- No Blade components or layouts (every view is a self-contained HTML document)
