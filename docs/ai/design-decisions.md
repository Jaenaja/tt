# Design Decisions

Why the codebase is shaped the way it is. Items marked **ข้อสังเกตจากโค้ด (observation from code)** are inferred from the source/CHANGELOG, not confirmed by the author.

---

## Two generations of the bet model

The project evidently went through a redesign. The **first generation** modelled a bet as a single row with one `amount`, one `bet_type` (`two_digit` / `three_digit`), and a `status` (`pending` / `won` / `lost`):

- Models: `Bet`, `LotteryResult`, `NumberStatistic`
- Controllers: `BetController`, `LotteryResultController`

The **second (current) generation** models a bet as one row per `(customer, number, draw)` carrying **separate amount columns** for each play type (`amount_top`, `amount_bottom`, `amount_toad`, `amount_bottom_3`) plus matching `payout_*` and `is_win_*` columns:

- Models: `LotteryBet`, `LotteryDraw`
- Controllers: `LotteryBetController`, `LotteryDrawController`, `ReportController`

**Decision:** The second generation won. The first generation is orphaned (see [`known-issues.md`](known-issues.md) #2). The new model lets one customer ticket carry multiple play types on the same number, and stores the result/payout denormalized on each bet row so reports do not recompute wins on every read.

---

## `Config` → `Setting` migration

`CHANGELOG.md` item #3 records a deliberate switch from the `Config` model to a richer `Setting` model. `Setting` adds `type` (for casting), `group`, `description`, and a cache layer. All calculations were repointed to `Setting`. The `Config` model and `/admin/config` page were left in place but are now dead (see [`known-issues.md`](known-issues.md) #1).

**Decision:** `Setting` is the single source of truth for rates, ceilings, commission, and the delete code.

---

## Denormalized results on the bet row

When a draw is announced, `LotteryDrawController::calculateWinnings()` iterates every bet in the draw and writes `payout_top/bottom/toad/bottom_3` and `is_win_*` directly onto each `lottery_bets` row.

**Why:** Reports, exports, and the customer summary read these columns directly without re-deriving wins from the draw result each time. The trade-off is that re-announcing or editing a result requires recalculation, and the announced-draw lock exists partly to protect this denormalized state.

---

## `draw_date` as the relationship key

`LotteryBet` and `LotteryDraw` relate on the `draw_date` **date** column, not on an integer foreign key:

```php
LotteryDraw::hasMany(LotteryBet::class, 'draw_date', 'draw_date');
LotteryBet::belongsTo(LotteryDraw::class, 'draw_date', 'draw_date');
```

**Why (ข้อสังเกตจากโค้ด):** A bet is created with just a date string from the UI; a draw is created implicitly on the first bet for that date (`LotteryBetController::store()` does `LotteryDraw::firstOrCreate`-style logic). Keying on the date avoids a lookup/round-trip to get a draw id before inserting bets, and makes the date the natural business key. The cost is that `draw_date` must be unique on `lottery_draws` (it is) and joins are on a date rather than an indexed integer PK.

---

## Toad (โต๊ด) wins on all permutations

`CHANGELOG.md` item #1 records a fix: a toad bet wins if the result equals **any** permutation of the chosen digits, **including the exact order**. So a toad bet on `123` wins on `123, 132, 213, 231, 312, 321`. Liability for a toad bet is spread across all distinct permutations in the risk heatmap, while `bet_count` / `total_amount` are attributed only to the exact number the customer entered.

**Why:** Matches how โต๊ด is actually paid in Thai lottery, and gives the risk report an accurate per-number exposure picture.

---

## Draw schedule: 1st and 16th of the month

**ข้อสังเกตจากโค้ด:** The `add_close_time_to_lottery_draws` migration special-cases `DAY(draw_date) = 1` and `DAY(draw_date) = 16` when back-filling `close_time`. This matches the **Thai Government Lottery (สลากกินแบ่งรัฐบาล)** schedule, which draws twice a month on the 1st and 16th. This strongly suggests the system is built around that calendar.

**Important:** The schema does **not** enforce this — any `draw_date` can be created. Treat the 1st/16th cadence as the expected usage pattern, not a hard rule.

---

## Manual CSV instead of a spreadsheet library

Exports are written by hand with `fputcsv` plus a UTF-8 BOM, rather than using a package like PhpSpreadsheet.

**Why (ข้อสังเกตจากโค้ด):** Keeps the dependency list tiny and outputs a file Excel opens correctly with Thai text. The BOM is essential — without it, Excel garbles Thai characters. PDF (which needs real layout) is the only export that uses a library (`barryvdh/laravel-dompdf`).

---

## Self-contained Blade views (no layouts)

Every view is a full HTML document with its own `<head>`, inline dark-mode bootstrap script, Tailwind (CDN or Vite), and Sarabun font. There is no shared layout or Blade component.

**Why (ข้อสังเกตจากโค้ด):** Likely a product of rapid, page-by-page development (and possibly AI-assisted generation). It keeps each page independently editable at the cost of duplication. New pages are expected to copy an existing page's head/boilerplate rather than extend a layout.

---

## Inline validation, no service layer

All validation is inline `$request->validate()`; all business logic lives in controllers (often in private methods). There are no Form Requests, services, repositories, events, or jobs.

**Why:** The app is small and single-operator. The team optimized for "everything about an action is in one method you can read top to bottom" over layered abstraction. New code is expected to follow this, not introduce layers.
