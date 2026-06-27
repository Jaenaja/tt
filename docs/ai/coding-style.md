# Coding Style & Conventions

## General

- **PHP 8.2** — use arrow functions `fn()`, named arguments, match expressions, and readonly properties where appropriate.
- **No comments by default.** The codebase mixes two styles: some files have no comments, others have inline `// [FIX #N]` tags tracking specific past bug fixes. New code should follow the no-comment default unless the WHY is non-obvious.
- **All user-facing text is in Thai.** Error messages, flash messages, validation messages, column headers, CSV headers — everything the user reads must be Thai.
- **No trailing whitespace, no unused imports.**

---

## Controllers

- Validation is **inline** using `$request->validate([...])` — no Form Request classes.
- Return `response()->json([...])` for all POST/PUT/DELETE routes.
- Return `view('...', compact(...))` for GET routes.
- Business logic lives in private controller methods — no service classes exist.
- Sort whitelists are explicit arrays before use (never pass raw `$request->sort_by` to `orderBy`).

Example pattern:
```php
$allowedSort = ['created_at', 'customer_name', 'number'];
$sortBy  = in_array($request->get('sort_by'), $allowedSort) ? $request->get('sort_by') : 'created_at';
$sortOrd = in_array($request->get('sort_order'), ['asc','desc']) ? $request->get('sort_order') : 'desc';
```

- Duplicate-submission guard in `LotteryBetController::store()` checks for identical bets created within the last 10 seconds.

---

## Models

- `$fillable` is always defined explicitly — never use `$guarded = []`.
- `$casts` is defined for all non-string columns.
- Scopes follow the `scope{Name}($query)` convention.
- Computed attributes use `get{Name}Attribute()` accessors.
- `Setting::get($key, $default)` / `Setting::set(...)` are the canonical way to read/write config values. **Never use the `Config` model** — it is dead (see [`known-issues.md`](known-issues.md) #1).
- The active bet/draw models are `LotteryBet` and `LotteryDraw`. **Never use `Bet`, `LotteryResult`, or `NumberStatistic`** — they belong to the orphaned first-generation subsystem.
- Note `app/Models/User.php` defines four classes (`User`, `Config`, `LotteryDraw`, `LotteryBet`); `LotteryDraw.php`/`LotteryBet.php` files do not exist.

---

## Blade / Frontend

- **No Blade layouts or components.** Every view is a fully self-contained HTML file with its own `<html>`, `<head>`, and `<body>`.
- **Dark mode** is supported in all views via Tailwind's `class` strategy. The toggle is stored in `localStorage.theme`. All views begin with an inline script that applies `dark` class before page render to avoid flash.
- **Tailwind** is used for all styling. Some views load Tailwind from CDN (`<script src="https://cdn.tailwindcss.com">`); some use the Vite-compiled `app.css`. Do not mix both in the same view.
- **SweetAlert2** (CDN) handles all JS confirmation dialogs and toast notifications.
- **Font:** Sarabun from Google Fonts. Applied globally via `* { font-family: 'Sarabun', sans-serif; }` in a `<style>` block.
- AJAX calls use `fetch()` with `X-CSRF-TOKEN` header from `document.querySelector('meta[name="csrf-token"]').content`.
- Forms that submit via AJAX do **not** use the native HTML form submit — they call `event.preventDefault()` and use `fetch`.

---

## Dates & Thai Calendar

- Database stores dates as `YYYY-MM-DD` in Gregorian (ค.ศ.).
- Display uses Thai Buddhist year (พ.ศ.) via `ThaiDateHelper` or the global helper functions:
  - `thai_date($date)` → `"11 มี.ค. 66"`
  - `thai_date_full($date)` → `"11 มีนาคม 2566"`
  - `thai_date_slash($date)` → `"11/3/66"`
  - `thai_datetime($datetime)` → `"11 มี.ค. 66 เวลา 14:30 น."`
- Input from the draw form may arrive as `DD/MM/YYYY` with a พ.ศ. year (> 2500). The `convertThaiDateToYmd()` method in `LotteryDrawController` handles the conversion: subtract 543 if year > 2500.

---

## CSV Exports

All CSV exports follow this pattern:
```php
$handle = fopen('php://temp', 'r+');
fwrite($handle, "\xEF\xBB\xBF");  // UTF-8 BOM for Excel
foreach ($rows as $r) fputcsv($handle, $r);
rewind($handle);
$csv = stream_get_contents($handle);
fclose($handle);
return response($csv, 200, [
    'Content-Type' => 'text/csv; charset=UTF-8',
    'Content-Disposition' => 'attachment; filename="..."',
]);
```

Thai filenames use `rawurlencode()` and the `filename*=UTF-8''...` RFC 5987 format for browser compatibility.

**Leading-zero preservation for number columns:** Lottery numbers such as `"06"` or `"089"` would be auto-converted to integers by Excel. To prevent this, prefix the value with a tab character: `"\t" . $bet->number`. When `fputcsv` wraps this in quotes the cell becomes `"	06"`, which Excel treats as text and preserves the leading zero. Apply this to any cell whose value is a lottery number string (not to section headers or placeholder `"-"` rows).

**Toad permutation display order in exports:** When listing toad numbers in the over-limit export (or any ordered toad display), permutations must be emitted in **positional index order**: `[0,1,2], [0,2,1], [1,0,2], [1,2,0], [2,0,1], [2,1,0]` applied to the canonical sorted digits. For canonical key `abc` (digits sorted ascending) this produces: `abc, acb, bac, bca, cab, cba`. Duplicate permutations (repeated digits) are skipped. Groups of the same digit-set must be kept contiguous; inter-group order is by liability descending.

**Over-limit export columns (7 columns):** `exportOverLimit()` outputs two extra columns appended after `% ของเพดาน`:

| Column | Formula (non-toad) | Formula (toad group) |
|---|---|---|
| `ยอดจ่ายเกิน (฿)` | `liability − เพดาน` | `group_liability − เพดาน` (first row of group only) |
| `ยอดซื้อส่งต่อ (฿)` | `total_amount − floor(เพดาน ÷ rate)` | `round(group_liability ÷ rate) − floor(เพดาน ÷ rate)` (first row only) |

- `$addSection` closure signature is `($title, $data, $maxPayout, $rate)` — `$rate` is the payout rate for that bet type, used to compute the retained-stake amount.
- `$addToadSection` detects group boundaries by re-computing the canonical key (`sort(str_split($number))`) per row and comparing to `$prevKey`. Non-first rows in a group show `''` for both transfer columns.
- Assumption: the big bookmaker pays at the same rate as the operator. If big bookmaker rates differ, the "ยอดซื้อส่งต่อ" formula must be revised.
- All rates and ceilings are read from `Setting::` (never hardcoded); using wrong defaults will produce wrong transfer amounts.

---

## Security

- Soft-delete requires a 6-digit numeric delete code (`Setting::get('delete_code')`). If no code is set, deletion is blocked entirely.
- Sort columns are whitelisted before use in `orderBy()`.
- All database writes go through Eloquent or `DB::table()` — no raw string concatenation into queries.
- `Hash::make()` is used for all passwords.

---

## Dense Code Style

The codebase uses a deliberately compact style in some controllers — multiple statements on one line, short variable names (`$b`, `$d`, `$n`), and chained ternaries. This is intentional to keep controllers readable at a glance when scrolling. When adding new code, match the density level of the surrounding context.
