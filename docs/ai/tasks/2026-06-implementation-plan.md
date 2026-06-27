# Implementation Plan — 3 Requirements (2026-06)

**Status:** DONE — implemented and verified (2026-06-27). All 3 phases passed user acceptance. Docs updated in coding-style.md, api.md, business.md.
**Prerequisite reading:** [`../known-issues.md`](../known-issues.md), [`../coding-style.md`](../coding-style.md), [`../api.md`](../api.md). Active models are `LotteryBet` / `LotteryDraw` (never the orphaned `Bet`/`LotteryResult`). Config is read via `Setting::`, never `Config::`.

This file is the agreed spec from the requirements discussion. All four open questions have been answered and locked.

---

## Locked Decisions

| Req | Decision | Scope |
|---|---|---|
| 1 — leading zeros in Excel | **tab-prefix** (`"\t".$value`) on number-only cells | `เลข` column in 3 exports |
| 2 — group toad numbers | permutation order `abc, acb, bac, bca, cab, cba`, dedupe | Over-limit table + Top-10 toad table + Excel **(NOT heatmap)** |
| 3 — view deleted bets | admin only, **view-only (no restore)**, filter default = active data | pages `admin/reports/summary/{id}` + `/bets/history` |

---

## Requirement 1 — Preserve leading zeros in Excel export

**Cause:** `number` is already stored as a string ("045" is correct in DB). Excel auto-converts CSV numeric-looking cells on open. Fix = force the number cell to be treated as text via a leading tab.

**Approach:** tab-prefix. Add a tiny helper (e.g. `excelText($v) => "\t".$v`) and apply it only to cells that are a bare number.

**Files / methods:**

| File | Method | Column |
|---|---|---|
| `app/Http/Controllers/LotteryBetController.php` | `exportExcel()` | `เลข` (`$bet->number`) |
| `app/Http/Controllers/ReportController.php` | `exportExcel()` | `เลข` (`$bet->number`) |
| `app/Http/Controllers/ReportController.php` | `exportOverLimit()` | `เลข` (`$item['number']`) — **only data rows**, not section headers or `-` placeholder rows |

**Do NOT change** `ReportController::exportCustomerSummary()` — its "เลขที่ถูก" column is a composed string containing parentheses/letters, so Excel does not auto-convert it; leading zeros are already safe.

**Verify:** open each exported file in real Excel; confirm `045`, `007`, `089` are not stripped to `45`, `7`, `89`.

---

## Requirement 2 — Group toad (โต๊ด) permutations in display

**Ordering rule:** let the digits be `a, b, c` (by position). Output permutations in index-lexicographic order and drop duplicates (keep first occurrence):

```
abc, acb, bac, bca, cab, cba
```

Confirmed against user examples:
- `157` → 157, 175, 517, 571, 715, 751
- `871` → 871, 817, 781, 718, 187, 178
- `669` → 669, 696, 966   (duplicates removed)

> This is **positional permutation order, NOT numeric sort.** A numeric sort would be wrong (e.g. 871 must start with 871, not 178).

**Scope:** Over-limit toad table, Top-10 toad table, and the Excel over-limit export. **Heatmap is explicitly out of scope** — do not touch it.

**Files / points (`app/Http/Controllers/ReportController.php`):**
- `generatePermutations()` / `getToadNumbers()` — emit the order above.
- `topThreeToadExposure` and `overLimit3Toad` — re-order into **grouped** form (all permutations of one digit-set adjacent, internal order per the rule).
- `exportOverLimit()` → `addToadSection` — same grouped order.
- `resources/views/admin/reports/summary.blade.php` — Top-10 toad (~line 225) and over-limit toad (~line 275) tables render in the new order.

**Decisions for aggregate tables (no single typed number exists there):**
1. Canonical `a, b, c` = the digit-set sorted **ascending** (e.g. group {1,7,8} displays as 178, 187, 718, 781, 817, 871).
2. **Group ordering** = by liability descending (preserve the risk-report priority — highest exposure group first).
3. **Top-10** may cut mid-group (all permutations share the same spread liability); keep the 10-row cap by default.

> ⚠️ **Display layer only.** Do not change `calculateThreeToadLiability` or the ceiling-% math. Totals and percentages must stay identical — only the row order changes.

---

## Requirement 3 — View deleted bets (admin only, view-only)

**No new routes.** Add a query param (e.g. `record_status=active|deleted`, default `active`) to two existing pages.

| File | Point | Change |
|---|---|---|
| `app/Http/Controllers/LotteryBetController.php` | `history()` (query ~line 56) | if `deleted` **and** user is admin → use `onlyTrashed()` instead of `whereNull('deleted_at')` |
| `app/Http/Controllers/ReportController.php` | `summary()` — **only the `$betsHistory` query (~line 165)** | same toggle |
| `resources/views/bets/history.blade.php` | UI | filter control + "ลบโดย / เวลาลบ" columns (admin-only) |
| `resources/views/admin/reports/summary.blade.php` | UI | filter control in the bet-history section + deleter/deleted-at columns |

**Guardrails:**
- **Summary page money math is untouched.** Only `$betsHistory` toggles. Every financial/liability/sales/result query stays `whereNull('deleted_at')` — otherwise totals corrupt (risk R5).
- **Admin-only enforcement at two levels:** both pages are visible to `general`/`staff`, so hide the filter UI **and** ignore the `record_status=deleted` param when the user is not admin (prevent URL bypass — risk R6).
- `deleter()` relationship and `deleted_at` already exist. Both delete paths (`LotteryBetController::destroy`, `ReportController::deleteBet`) record `deleted_by`, so `onlyTrashed()` catches both.
- Preserve the filter across pagination (`->appends(...)`).
- View-only — **no restore action.**

---

## Build Order

1. **Req 1** (highest technical risk) → helper + 3 exports → **open files in real Excel** to confirm leading zeros survive.
2. **Req 3** (additive, low blast radius) → query + UI on 2 pages → verify: general blocked/hidden, admin sees deleted, summary money unchanged when toggling.
3. **Req 2** (rule now locked) → permutation order + group 2 tables + Excel → verify totals/% unchanged, only order differs.
4. **Docs** (per the Documentation Update Policy in `CLAUDE.md`):
   - `coding-style.md` — CSV tab-prefix convention for number cells; toad display-order rule.
   - `api.md` — `record_status` param on `bets.history` and `admin.reports.summary`.
   - `business.md` / `design-decisions.md` — toad display ordering; admin-only visibility of deleted bets.
   - Remove this file or mark it **DONE** once shipped.

---

## Risks to Watch

| # | Risk |
|---|---|
| R1 | tab-prefix must be verified in real Excel; a stray tab may appear on copy (acceptable) |
| R5 | summary page: filter must not affect money math — list only |
| R6 | admin-only must be enforced in UI **and** at param level |
| R7 | no automated tests — verify everything manually |

---

## Open Sub-Decisions (defaults chosen, easy to flip)

- Req 2 aggregate-table canonical `a,b,c` = ascending digits (stated above). Flip only if the user wants a different representative.
- Req 2 group ordering = liability desc. Could switch to numeric group order if preferred.
