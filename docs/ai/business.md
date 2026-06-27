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

**Constraints enforced at bet entry:**
- A 2-digit number cannot have a toad amount or a 3-digit-bottom amount.
- A 3-digit number cannot have a 2-digit-bottom amount.
- Number must be exactly 2 or 3 digits.

---

## Draw Lifecycle

1. **Open:** A draw exists (or is implicitly created on first bet) with `is_announced = false`.
2. **Close time:** Optional `close_time` field; after this timestamp the draw is considered closed for new bets.
3. **Announce:** Admin enters the result numbers and submits. System sets `is_announced = true`, records `announced_at` and `announced_by`, then calculates winnings for all bets in that draw.
4. **Immutable:** Once announced, no bets can be added or deleted for that draw.

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

Settings are stored in the `settings` table and can be changed live from the Risk Settings admin page.

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
