# Database

**Engine:** MySQL (MAMP). `.env` → `DB_CONNECTION=mysql`, `DB_DATABASE=projects`, `DB_USERNAME=root`, `DB_PASSWORD=root`. Some migrations use MySQL-specific SQL.

## Tables Overview

| Table | Model | Status |
|---|---|---|
| `users` | `User` | Active — auth + role |
| `lottery_draws` | `LotteryDraw` | Active — one row per draw date |
| `lottery_bets` | `LotteryBet` | Active — all bets; soft-delete via trait |
| `settings` | `Setting` | Active — typed key/value with cache (**authoritative config**) |
| `number_statistics` | `NumberStatistic` | Migrated, but written only by the orphaned `LotteryResultController` |
| `configs` | `Config` | **Dead** — only `/admin/config` writes it; nothing reads it. See [`known-issues.md`](known-issues.md) #1 |
| `bets` | `Bet` | **Not created** — its migration is a disabled `.zip`. First-generation table; the `Bet` model is orphaned |
| `lottery_results` | `LotteryResult` | First-generation table; used only by orphaned controllers |

---

## Schema

### `users`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | Display name |
| `username` | string unique | Used for login (not email) |
| `password` | string | Hashed |
| `role` | string | `admin` or `general` |
| `is_active` | boolean | |
| `remember_token` | string nullable | |
| `created_at`, `updated_at` | timestamps | |

### `lottery_draws`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `draw_date` | date unique | Primary identifier for a draw |
| `close_time` | datetime nullable | If set, no new bets after this time |
| `result_3_top` | char(3) nullable | 3-digit top result |
| `result_2_top` | char(2) nullable | 2-digit top result |
| `result_2_bottom` | char(2) nullable | 2-digit bottom result |
| `result_3_bottom` | string(200) nullable | Comma-separated list e.g. `"355,108,868"` |
| `is_announced` | boolean | Default false |
| `announced_at` | datetime nullable | |
| `announced_by` | FK → users nullable | |
| `created_at`, `updated_at` | timestamps | |

**Accessor:** `getResult3BottomArrayAttribute()` splits the comma string into an array.

### `lottery_bets`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `draw_date` | date | FK to `lottery_draws.draw_date` (not id) |
| `customer_name` | string | Free text, no FK |
| `number` | string | 2 or 3 digit string |
| `amount_top` | decimal(10,2) | Stake for top bet |
| `amount_bottom` | decimal(10,2) | Stake for bottom bet |
| `amount_toad` | decimal(10,2) | Stake for toad bet |
| `amount_bottom_3` | decimal(10,2) nullable | Stake for 3-digit bottom |
| `payout_top` | decimal(10,2) | Filled after announcement |
| `payout_bottom` | decimal(10,2) | |
| `payout_toad` | decimal(10,2) | |
| `payout_bottom_3` | decimal(10,2) nullable | |
| `is_win_top` | boolean | |
| `is_win_bottom` | boolean | |
| `is_win_toad` | boolean | |
| `is_win_bottom_3` | boolean nullable | |
| `created_by` | FK → users | |
| `deleted_by` | FK → users nullable | Who soft-deleted |
| `deleted_at` | timestamp nullable | Soft delete marker |
| `created_at`, `updated_at` | timestamps | |

**Indexes:** `(draw_date, customer_name)`, `created_by`, `deleted_at`

**Relationships join on `draw_date` (date), not `id`** — `LotteryBet::draw()` and `LotteryDraw::bets()` both use `draw_date` as the foreign/local key.

**Computed attributes on LotteryBet:**
- `total_amount` = sum of all four amount fields
- `total_payout` = sum of all four payout fields
- `net_profit` = `total_payout - total_amount`

### `settings`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `key` | string unique | |
| `value` | text | Always stored as string |
| `type` | string | `string`, `integer`, `decimal`, `boolean` |
| `description` | text nullable | |
| `group` | string | `general`, `risk`, `payout`, `security` |
| `created_at`, `updated_at` | timestamps | |

**Default settings (seeded across several migrations):**

| Key | Default | Group | Seeded by |
|---|---|---|---|
| `max_payout_2_digit` | 50000 | risk | create_settings |
| `max_payout_3_digit` | 100000 | risk | create_settings |
| `max_payout_3_toad` | 50000 | risk | `1_migration_add_max_payout_3_toad` |
| `max_payout_3_bottom` | 50000 | risk | `migration_add_3bottom` |
| `rate_2_top` | 90 | payout | create_settings |
| `rate_2_bottom` | 90 | payout | create_settings |
| `rate_3_top` | 900 | payout | create_settings |
| `rate_3_toad` | 120 | payout | create_settings |
| `rate_3_bottom` | 500 | payout | `migration_add_3bottom` |
| `commission_rate` | 10 | general | create_settings |
| `delete_code` | (empty) | security | `add_delete_code_to_settings` |
| `auto_transfer_enabled` | false | risk | create_settings — **DEAD** (feature removed, CHANGELOG #4) |
| `auto_transfer_threshold` | 100 | risk | create_settings — **DEAD** (feature removed, CHANGELOG #4) |

> The `auto_transfer_*` keys are still seeded but no code reads them. Ignore them.

---

## Model Notes

### Multi-class `User.php`

`app/Models/User.php` defines four classes in a single file: `User`, `Config`, `LotteryDraw`, `LotteryBet`. This is non-standard but works because PHP autoloads based on the file, not each class individually. Do not move these to separate files without ensuring autoload still resolves correctly.

### Soft Deletes on LotteryBet

`LotteryBet` uses `SoftDeletes` trait. All queries that should exclude deleted rows must add `.whereNull('deleted_at')` or rely on the trait's global scope. Additionally, `deleted_by` is set manually before calling `delete()`.

### Draw–Bet Relationship

Both sides join on the `draw_date` date column, not the `id`:
```php
// LotteryDraw
$this->hasMany(LotteryBet::class, 'draw_date', 'draw_date');

// LotteryBet
$this->belongsTo(LotteryDraw::class, 'draw_date', 'draw_date');
```

---

## Seeder

`database/seeders/DatabaseSeeder.php` is **active** and `updateOrCreate`s a default admin:

| Field | Value |
|---|---|
| `username` | `admin` |
| `role` | `admin` |
| `id` | 1 |
| `password` | bcrypt hash (seeded literally) |

Run with `php artisan db:seed`. This is the only seeded data.

---

## Migration Files

Run order is **filename string order** — note the inconsistent naming (see [`known-issues.md`](known-issues.md) #5).

| File | Runs? | Purpose |
|---|---|---|
| `1_migration_add_max_payout_3_toad.php` | ✅ (sorts first) | Seeds `max_payout_3_toad` setting |
| `2026_01_14_055025_create_bets_table.zip` | ❌ `.zip` disabled | First-gen `bets` table (never created) |
| `2026_01_14_055030_create_number_statistics_table.php` | ✅ | `number_statistics` table |
| `2026_02_11_000001_create_users_table.zip` | ❌ `.zip` disabled | Old users migration (superseded) |
| `2026_02_11_020700_create_users_table.php` | ✅ | Users |
| `2026_02_11_020712_create_configs_table.php` | ✅ | Legacy `configs` (now dead) |
| `2026_02_11_020720_create_lottery_draws_table.php` | ✅ | Draws |
| `2026_02_11_020729_create_lottery_bets_table.php` | ✅ | Bets (without `*_bottom_3` cols) |
| `2026_02_15_000000_create_settings_table.php` | ✅ | Settings + seed defaults |
| `2026_02_23_000000_add_delete_code_to_settings.php` | ✅ | Seeds `delete_code` |
| `2026_02_24_000000_add_close_time_to_lottery_draws.php` | ✅ | Adds `close_time` (MySQL-specific back-fill for days 1 & 16) |
| `2026_04_25_000000_fix_result_3_bottom_column_size.php` | ✅ | `result_3_bottom` varchar(60) → varchar(200) |
| `migration_add_3bottom.php` | ✅ (sorts last) | Adds `result_3_bottom`(60) + `amount/payout/is_win_bottom_3` cols + `rate_3_bottom`/`max_payout_3_bottom` settings |

> **ข้อสังเกตจากโค้ด:** `migration_add_3bottom.php` first creates `result_3_bottom` at `varchar(60)`, while `fix_result_3_bottom_column_size` enlarges it to 200. Because of the filename ordering, on a **fresh** database the `fix` runs *before* `add_3bottom`, which could leave the column at 60. On the existing DB it is already 200. Verify column size on any fresh setup.
