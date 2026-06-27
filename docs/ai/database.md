# Database

## Tables Overview

| Table | Model | Notes |
|---|---|---|
| `users` | `User` | Auth + role |
| `lottery_draws` | `LotteryDraw` | One row per draw date |
| `lottery_bets` | `LotteryBet` | All bets; manual soft-delete |
| `settings` | `Setting` | Typed key/value with cache |
| `configs` | `Config` | Legacy key/value (simpler, no cache) |
| `number_statistics` | `NumberStatistic` | Minimally used |

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

**Default settings seeded in migration:**

| Key | Default | Group |
|---|---|---|
| `max_payout_2_digit` | 50000 | risk |
| `max_payout_3_digit` | 100000 | risk |
| `max_payout_3_toad` | 50000 | risk |
| `max_payout_3_bottom` | 50000 | risk |
| `rate_2_top` | 90 | payout |
| `rate_2_bottom` | 90 | payout |
| `rate_3_top` | 900 | payout |
| `rate_3_toad` | 120 | payout |
| `rate_3_bottom` | 500 | payout |
| `commission_rate` | 10 | general |
| `delete_code` | (empty) | security |

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

## Migration Files

| File | Purpose |
|---|---|
| `2026_02_11_020700_create_users_table.php` | Users |
| `2026_02_11_020712_create_configs_table.php` | Legacy configs |
| `2026_02_11_020720_create_lottery_draws_table.php` | Draws |
| `2026_02_11_020729_create_lottery_bets_table.php` | Bets (original) |
| `2026_02_15_000000_create_settings_table.php` | Settings + seed defaults |
| `2026_02_23_000000_add_delete_code_to_settings.php` | Adds delete_code setting |
| `2026_02_24_000000_add_close_time_to_lottery_draws.php` | Adds close_time column |
| `2026_04_25_000000_fix_result_3_bottom_column_size.php` | Expands result_3_bottom to varchar(200) |
| `migration_add_3bottom.php` | Adds amount_bottom_3 / payout_bottom_3 / is_win_bottom_3 columns |
| `1_migration_add_max_payout_3_toad.php` | Adds max_payout_3_toad setting |
