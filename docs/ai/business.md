# Business Domain

This is a **Thai underground lottery (หวย) back-office system** for a single operator. It is not a public-facing application — it is an internal tool used by staff to record bets, manage draw rounds, announce results, calculate winnings, and monitor financial risk.

---

## Glossary

| Thai | English | Meaning |
|---|---|---|
| หวย | Lottery | Underground lottery |
| งวด | Draw | One lottery round, keyed by date |
| แทง | Bet | To place a bet |
| บน | Top | Bet on the top (primary) result |
| ล่าง | Bottom | Bet on the bottom (secondary) result |
| โต๊ด | Toad | Any permutation of a 3-digit number wins |
| 3 ตัวล่าง | 3-digit bottom | Bet against a set of announced 3-digit numbers |
| อัตราจ่าย | Payout rate | Multiplier applied to stake if bet wins |
| เพดาน | Ceiling | Max payout the operator accepts per number |
| ยอดจ่าย / Liability | Exposure | Total payout the operator would owe if a number wins |
| ส่วนลด / Commission | Commission | Percentage deducted from gross sales as operator fee |

---

## Bet Types

Each bet is linked to a draw date and a customer name. A single bet row can have amounts across multiple types simultaneously (fields are separate columns, not separate rows).

| Type | Number length | Wins when | Default rate |
|---|---|---|---|
| 2-digit top (2 ตัวบน) | 2 | Exact match with `result_2_top` | 90× |
| 2-digit bottom (2 ตัวล่าง) | 2 | Exact match with `result_2_bottom` | 90× |
| 3-digit top (3 ตัวตรง) | 3 | Exact match with `result_3_top` | 900× |
| 3-digit toad (3 ตัวโต๊ด) | 3 | Any permutation of digits matches `result_3_top` | 120× |
| 3-digit bottom (3 ตัวล่าง) | 3 | Exact match with any number in `result_3_bottom` list | 500× |

**Toad logic:** For a toad bet on `123`, the system generates all permutations (`123`, `132`, `213`, `231`, `312`, `321`) and checks if any match the announced top result. Liability is spread across all permutations in the risk heatmap.

**Toad display order in reports and exports:** Numbers from the same digit-set (e.g., `{1,4,7}`) are always displayed **contiguously**, sorted by positional index order: `abc, acb, bac, bca, cab, cba` (where `a<b<c` are the sorted digits). Groups are ordered by **liability descending**. Duplicate permutations (caused by repeated digits, e.g., `{1,1,2}`) are deduplicated while preserving the positional order. This is implemented in `ReportController::groupAndSortToadExposure()` and `getToadNumbers()`.

**Constraints enforced at bet entry:**
- A 2-digit number cannot have a toad amount or a 3-digit-bottom amount.
- A 3-digit number cannot have a 2-digit-bottom amount.
- Number must be exactly 2 or 3 digits.

---

## Draw Schedule

**ข้อสังเกตจากโค้ด:** The `add_close_time_to_lottery_draws` migration special-cases draws on the **1st and 16th of the month**, matching the **Thai Government Lottery (สลากกินแบ่งรัฐบาล)** schedule, which draws twice monthly on those dates. The system is built around this cadence, but the schema does **not** enforce it — any `draw_date` can be created. See [`design-decisions.md`](design-decisions.md).

## Draw Lifecycle

1. **Open:** A draw exists (or is implicitly created on the first bet for that date) with `is_announced = false`.
2. **Close time:** Optional `close_time` field; after this timestamp `LotteryDraw::isClosed()` returns true. The `scopeOpen()` query treats a draw as open if `close_time` is null or in the future. When `close_time` was back-filled by migration, draws on the 1st/16th close at 23:59:59 the day before; other draws close at 23:59:59 on the draw date.
3. **Announce:** Admin enters the result numbers and submits. System sets `is_announced = true`, records `announced_at` and `announced_by`, then calculates and **stores** winnings (`payout_*`, `is_win_*`) on every bet row in that draw.
4. **Immutable:** Once announced, no bets can be added or deleted for that draw (enforced in `LotteryBetController::store/destroy` and `ReportController::deleteBet`).

---

## Result Fields

| Field | Format | Example |
|---|---|---|
| `result_3_top` | 3-digit string | `"456"` |
| `result_2_top` | 2-digit string | `"56"` (last 2 digits of 3-top) |
| `result_2_bottom` | 2-digit string | `"78"` |
| `result_3_bottom` | Comma-separated 3-digit strings | `"355,108,868,424"` |

---

## Risk Management

The operator sets **max payout ceilings** per number type. The report summary page shows:
- A heatmap of liability across all possible numbers (00–99 for 2-digit, 000–999 for 3-digit).
- Top-10 exposure lists per bet type.
- **Over-limit** numbers where projected payout exceeds 100% of the ceiling — these require manual action (e.g. transfer the bet to another operator).

Settings are stored in the `settings` table and can be changed live from the **Risk Settings** admin page (`/admin/risk-settings`).

**Over-limit Excel export (`/admin/reports/export-over-limit/{drawId}`)** includes two additional columns to assist manual transfer decisions:

- `ยอดจ่ายเกิน (฿)` = `liability − เพดาน` — the payout amount the operator cannot cover alone.
- `ยอดซื้อส่งต่อ (฿)` = `total_stake − floor(เพดาน ÷ rate)` — the minimum stake amount to hand to the big bookmaker so that the operator's retained exposure equals exactly the ceiling. Formula assumes the big bookmaker pays at the **same rate** as the operator.

For **toad groups**, both columns are computed at group level (not per-permutation) and shown only on the **first row** of each contiguous group:
- `group_liability` = all permutations share the same liability value (spread equally by `calculateThreeToadLiability`).
- `ยอดซื้อส่งต่อ` = `round(group_liability ÷ rate_3_toad) − floor(เพดาน ÷ rate_3_toad)`.
- Remaining rows in the group show `''` in both columns.

> The older `/admin/config` page also shows "payout rates" but writes to a dead `configs` table that no calculation reads. Always configure rates via Risk Settings. See [`known-issues.md`](known-issues.md) #1.

> An **Auto Transfer** (automatic over-limit cut) feature once existed but was removed (CHANGELOG #4). Over-limit handling is now manual/informational only.

---

## Financial Flow

```
Gross sales (ยอดขาย)
  − Commission (ส่วนลด, default 10%)
  = Net sales after discount
  − Total payouts (รางวัล)
  = Net profit (กำไรสุทธิ)
```

The customer summary report shows each customer's position: how much they bet, their discount, their payout, and whether the operator owes them money or they owe the operator.

---

## User Roles

| Role | Thai | Permissions |
|---|---|---|
| `admin` | ผู้ดูแล | Full access: user management, draw results, risk settings, all reports, delete |
| `general` | พนักงาน | Record bets, view bet history, view reports (read-only) |

**Delete code:** Deleting any bet requires entering a 6-digit numeric code configured by the admin in Risk Settings. This acts as a second-factor confirmation regardless of role.

**Admin-only: viewing soft-deleted bets:** Admins can view deleted bet records (view-only, no restore) on two pages by selecting "รายการที่ลบแล้ว" from the แสดงรายการ dropdown:
- `/bets/history?record_status=deleted` — switches the bet list to `onlyTrashed()`, shows ลบโดย / เวลาลบ column, hides delete button.
- `/admin/reports/summary/{id}?record_status=deleted` — switches the bet detail list only; all financial totals (risk, exposure, heatmap, payout) continue to be calculated from **active bets only** and are unaffected by the param.

The param is enforced server-side: non-admin users always see active records regardless of what they pass.
