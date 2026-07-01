# Known Issues & Traps

This file records landmines in the codebase that an AI assistant must be aware of before making changes. Items marked **ข้อสังเกตจากโค้ด (observation from code)** are inferences from reading the source, not confirmed intentions — verify with the user before acting on them.

---

## 1. Dual configuration systems — `Config` vs `Setting` ⚠️ HIGH IMPACT

There are **two** key/value config models:

| Model | Table | Used by | Read by calculations? |
|---|---|---|---|
| `Setting` | `settings` | RiskSettings, Report, LotteryDraw, LotteryBet | ✅ Yes — this is authoritative |
| `Config` | `configs` | **Only** `AdminController::config` / `updateConfig` (`/admin/config`) | ❌ No |

**The trap:** The `/admin/config` page lets an admin set payout rates (`rate_2_top`, etc.), but it writes them to the `configs` table via `Config::set()`. **No winning or liability calculation ever reads from `Config`** — they all read `Setting::get(...)`. So editing rates on `/admin/config` has **no effect** on actual payouts.

`CHANGELOG.md` item #3 documents the migration from `Config` to `Setting`. The `/admin/config` route and `AdminController::config/updateConfig` were left behind.

**Guidance:** Always use `Setting::get()` / `Setting::set()`. Treat `/admin/config`, the `Config` model, and the `configs` table as deprecated. The authoritative rate-editing page is `/admin/risk-settings`.

---

## 2. Orphaned legacy bet subsystem ⚠️ HIGH IMPACT

A complete parallel subsystem exists but is **not wired to any route**:

| Component | Status |
|---|---|
| `BetController` | Orphaned — no route references it; references view `bets.sales` which does not exist |
| `LotteryResultController` | Orphaned — no route references it |
| `Bet` model (`bets` table) | Migration is **`.zip` (disabled)** — table is never created by `migrate` |
| `LotteryResult` model | Minimally used; comment in the file says it may be removable |
| `NumberStatistic` model | Table migrated, but only the orphaned controllers write to it |

**Guidance:** The active bet flow is `LotteryBet` + `LotteryDraw` (multi-column amounts, `draw_date`-keyed). Do **not** extend `Bet` / `LotteryResult` / `NumberStatistic` or their controllers. If asked to work on "bets," confirm it is the `LotteryBet` flow.

---

## 3. `User.php` contains four model classes ⚠️ MEDIUM

`app/Models/User.php` defines `User`, `Config`, `LotteryDraw`, and `LotteryBet` in a single file with repeated `namespace App\Models;` blocks. This works because Composer's PSR-4 autoloader resolves `App\Models\LotteryDraw` to... actually, PSR-4 expects one class per file named after the class. **ข้อสังเกตจากโค้ด:** these extra classes are likely loaded because the file is autoloaded when `User` is referenced, or via classmap. This is fragile.

**Guidance:** Do not split these into separate files casually — verify autoloading still resolves (`composer dump-autoload` and a smoke test) before/after. Do not assume `grep`-ing for `class LotteryDraw` in `LotteryDraw.php` will find anything; it lives in `User.php`.

---

## 4. Dead settings still seeded

The `settings` migration seeds `auto_transfer_enabled` and `auto_transfer_threshold`, but `CHANGELOG.md` item #4 records that the Auto Transfer feature was **removed**. No code reads these keys anymore (confirmed by grep).

**Guidance:** Ignore these keys. Do not build on them without re-confirming the feature is wanted.

---

## 5. Inconsistent migration filenames ⚠️ MEDIUM

Not all migrations follow Laravel's `YYYY_MM_DD_HHMMSS_name.php` convention:

- `migration_add_3bottom.php` — no timestamp; sorts **after** all `2026_*` files alphabetically.
- `1_migration_add_max_payout_3_toad.php` — sorts **before** all `2026_*` files.
- `2026_01_14_055025_create_bets_table.zip` and `2026_02_11_000001_create_users_table.zip` — `.zip` files are **not** run by `migrate` (disabled old versions).

**ข้อสังเกตจากโค้ด:** Laravel runs migrations in filename string order. Because `migration_add_3bottom.php` (which first creates `result_3_bottom` at `varchar(60)`) sorts after `2026_04_25_..._fix_result_3_bottom_column_size.php` (which `->change()`s it to 200), the run order on a fresh database could be fragile. On the existing database this has already resolved to `varchar(200)`. Verify column size on any fresh setup.

**Guidance:** New migrations must use a correct timestamp prefix. Do not rename or delete existing migrations.

---

## 6. `Setting::clearCache()` flushes the entire cache

`Setting::clearCache()` calls `Cache::flush()`, not a targeted forget. It wipes **all** cached data, not just settings. `RiskSettingsController::update()` calls it on every save.

**Guidance:** Acceptable for this app's scale, but be aware if caching is added elsewhere.

---

## 7. No automated tests

There is no test suite (`tests/` contains only the Laravel skeleton; `phpunit.xml` exists but no project tests). All verification is manual.

**Guidance:** Verify changes by running the app and exercising the happy path. Consider adding tests only if the user asks.

---

## 8. Stray files in the repository

- `general` and `jaenaja,` — empty 0-byte files at the project root, **ข้อสังเกตจากโค้ด:** likely created accidentally (e.g. a mistyped shell redirect). Not referenced anywhere.
- `resources/views/bets/index.blade.phpbak` — a backup file.
- `resources/views/admin/reports/index-old.blade.php` — an old version kept alongside the new one.

**Guidance:** These are cleanup candidates. Do not rely on them. Confirm with the user before deleting (this task does not delete source files).

---

## 9. `APP_LOCALE=en` but the app is Thai

The app locale is `en`, yet all UI is Thai. Thai date formatting is done explicitly via `ThaiDateHelper` / global helpers and per-call `->locale('th')`, not via the global locale.

**Guidance:** Use the Thai date helpers for display; do not rely on `APP_LOCALE`.

---

## 10. `parseShortFormat` `*6` literal matched `*6xx` substrings (FIXED 2026-07-01)

3-digit number with amounts like `600*60` or `600*600` was incorrectly expanded to 6 reverse permutations, because the `*6` literal check used `amounts.includes('*6')` which matched any substring starting with `*6` (e.g. `*60`, `*600`, `*6500`).

- **Rule** (per user, 2026-07-01): "6 กลับ" syntax is strictly `xxx*6` (the second value is exactly the single digit `6`). Anything with more digits after the `*6` is treated as "บน*โต๊ด" per normal.
- **Fix:** replaced `amounts.includes('*6')` with `amounts.endsWith('*6')` in `parseShortFormat()`.
- **Guardrail:** Do not weaken this back to `includes()`. If a new "6 กลับ" syntax is ever added, the check must remain at-end-of-string.
