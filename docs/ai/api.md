# Routes & API Reference

All routes are defined in `routes/web.php`. There is no `routes/api.php` — the two `/api/*` endpoints below are plain web routes.

> **Orphaned controllers:** `BetController` and `LotteryResultController` exist but are **not referenced by any route** and are dead code. Ignore them. See [`known-issues.md`](known-issues.md) #2.

---

## Public Routes

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/login` | `AuthController@showLogin` | `login` |
| POST | `/login` | `AuthController@login` | `login.post` |
| POST | `/logout` | `AuthController@logout` | `logout` |

---

## Auth-Protected Routes (`auth` middleware)

### Dashboard

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/` | `DashboardController@index` | `dashboard` |
| GET | `/api/open-draws` | `DashboardController@getOpenDraws` | `api.open-draws` |
| GET | `/api/realtime-sales` | `DashboardController@realtimeSales` | `api.realtime-sales` |

### Bets (`/bets`)

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/bets` | `LotteryBetController@index` | `bets.index` |
| POST | `/bets/store` | `LotteryBetController@store` | `bets.store` |
| GET | `/bets/history` | `LotteryBetController@history` | `bets.history` |
| DELETE | `/bets/{id}` | `LotteryBetController@destroy` | `bets.destroy` |
| GET | `/bets/export-excel` | `LotteryBetController@exportExcel` | `bets.export-excel` |

---

## Staff or Admin Routes (`staff_or_admin` middleware)

### Reports

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/reports` | `ReportController@index` | `admin.reports.index` |
| GET | `/admin/reports/summary/{drawId}` | `ReportController@summary` | `admin.reports.summary` |

---

## Admin-Only Routes (`admin` middleware)

### User Management

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/users` | `AdminController@users` | `admin.users` |
| POST | `/admin/users` | `AdminController@createUser` | `admin.users.create` |
| PUT | `/admin/users/{id}` | `AdminController@updateUser` | `admin.users.update` |
| DELETE | `/admin/users/{id}` | `AdminController@deleteUser` | `admin.users.delete` |

### Config ⚠️ LEGACY / DEAD

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/config` | `AdminController@config` | `admin.config` |
| POST | `/admin/config` | `AdminController@updateConfig` | `admin.config.update` |

> These routes are still registered but **deprecated**. They read/write payout rates via the `Config` model (table `configs`), which **no calculation reads**. The dashboard menu no longer links here (CHANGELOG #3). Use `/admin/risk-settings` instead. See [`known-issues.md`](known-issues.md) #1.

### Risk Settings

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/risk-settings` | `RiskSettingsController@index` | `admin.risk-settings` |
| PUT | `/admin/risk-settings` | `RiskSettingsController@update` | `admin.risk-settings.update` |

### Draws

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/draws` | `LotteryDrawController@index` | `admin.draws` |
| POST | `/admin/draws` | `LotteryDrawController@store` | `admin.draws.store` |
| GET | `/admin/draws/{id}/results` | `LotteryDrawController@results` | `admin.draws.results` |

### Admin Reports

| Method | URI | Controller | Name |
|---|---|---|---|
| GET | `/admin/reports/pdf/{drawId}` | `ReportController@exportPDF` | `admin.reports.pdf` |
| GET | `/admin/reports/statistics` | `ReportController@statistics` | `admin.reports.statistics` |
| DELETE | `/admin/reports/bets/{betId}` | `ReportController@deleteBet` | `admin.reports.bets.delete` |
| GET | `/admin/reports/export-excel/{drawId}` | `ReportController@exportExcel` | `admin.reports.export-excel` |
| GET | `/admin/reports/export-customer-summary/{drawId}` | `ReportController@exportCustomerSummary` | `admin.reports.export-customer-summary` |
| GET | `/admin/reports/export-over-limit/{drawId}` | `ReportController@exportOverLimit` | `admin.reports.export-over-limit` |

---

## JSON Response Convention

All POST/PUT/DELETE routes return JSON:

```json
// Success
{ "success": true, "message": "บันทึกข้อมูลสำเร็จ" }

// Failure
{ "success": false, "message": "ข้อความผิดพลาด" }
```

HTTP status codes: 200 for success, 400 for business logic errors, 401 for auth failure, 403 for permission/delete-code mismatch, 422 for validation errors, 500 for unexpected exceptions.

---

## Notable Internal APIs

### `GET /api/open-draws`

Returns draws that are not yet announced and have `draw_date >= today`:
```json
{
  "success": true,
  "draws": [
    { "value": "2026-06-27", "label": "27 มิถุนายน 2569", "is_announced": false }
  ]
}
```

### `GET /api/realtime-sales`

Returns today's running totals:
```json
{
  "totalSales": 12500.00,
  "totalTop": 5000.00,
  "totalBottom": 4000.00,
  "totalToad": 2500.00,
  "totalBottom3": 1000.00
}
```
