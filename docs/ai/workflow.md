# Working Workflow

How an AI assistant (or any developer) should approach a task in this repository.

---

## Before Starting

1. **Read `CLAUDE.md`** at the project root — it is the entry point and links here.
2. **Read the relevant `docs/ai/` files** for the area you are touching (see the table in `CLAUDE.md`).
3. **Read [`known-issues.md`](known-issues.md)** — this codebase has several traps (dead code, dual config systems). Check whether your task touches one of them.
4. **Verify against the actual code.** Documentation can drift. If a doc and the code disagree, the code is the source of truth — fix the doc as part of your task.
5. **Confirm the data model you are touching.** There are two parallel bet subsystems (see [`design-decisions.md`](design-decisions.md)). The **active** one is `LotteryBet` / `LotteryDraw`. The legacy `Bet` / `LotteryResult` / `NumberStatistic` subsystem is orphaned — do not extend it.

---

## During Work

1. **Match the surrounding style.** This codebase uses a deliberately dense one-line style in controllers. See [`coding-style.md`](coding-style.md).
2. **All user-facing text must be Thai.** Error messages, flash messages, validation messages, CSV headers, UI labels.
3. **Read settings via `Setting::get()`**, never `Config::get()`. The `Config` model and `/admin/config` page are legacy and their values are never read by any calculation.
4. **Keep changes minimal and local.** No service layer, no repositories, no form requests — follow existing patterns even if they are not "best practice."
5. **Migrations are additive.** Never edit an existing migration file; create a new one with a proper `YYYY_MM_DD_HHMMSS_` timestamp prefix.
6. **Do not add Composer/npm packages** without asking the user first.
7. **Whitelist any column** that comes from user input before passing it to `orderBy()` or `whereRaw()`.
8. **Respect the announced-draw lock.** Once `is_announced = true`, bets in that draw must not be created, edited, or deleted.

---

## After Work

Run through the [Quality Checklist in `CLAUDE.md`](../../CLAUDE.md#quality-checklist-before-handing-off) before handing off. At minimum:

1. **Self-review the diff** — no debug output, no commented-out code, no unused imports.
2. **Confirm Thai text** for anything the user sees.
3. **Update documentation** if you changed behavior, schema, routes, or conventions (see [Documentation Update Policy in `CLAUDE.md`](../../CLAUDE.md#documentation-update-policy)).
4. **Manually verify** the happy path if possible — there is no automated test suite (see [`known-issues.md`](known-issues.md)).
5. **Summarize** what changed, why, and anything left for the user to confirm.

---

## Local Environment

- **Stack:** MAMP on Windows, PHP 8.2, MySQL (database name `projects`, user `root`).
- **Run dev server:** `composer run dev` (runs `php artisan serve`, queue listener, `pail` logs, and Vite concurrently).
- **Build assets:** `npm run build`.
- **Migrate:** `php artisan migrate`.
- **Seed default admin:** `php artisan db:seed` (creates user `admin` — see [`database.md`](database.md)).
- **Clear caches after settings changes:** `php artisan cache:clear` (note: `Setting::clearCache()` calls `Cache::flush()`, which clears the entire cache store).

> **Note:** The app is run through MAMP's document root (`C:\MAMP\htdocs\tt`), so the public URL is typically `http://localhost/tt/public` or a configured vhost — confirm with the user.
