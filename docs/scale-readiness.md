# 50,000-user readiness review

Reviewed and fixes verified on 9 October 2026. Scope: this local React/PHP/MariaDB application. **The app is not yet certified for production at 50,000 users.** Code improvements below are applied locally; production infrastructure and several application redesigns remain.

50,000 registered accounts is a storage/data-volume target. Concurrent sessions, requests per second, write frequency, media traffic and monthly billing peaks determine server capacity. No assumption that all registered users arrive simultaneously is warranted. The supplied tests distinguish these targets.

## Problems and disposition

| Priority | Problem at scale | Evidence and troubleshooting | Fix/status |
|---|---|---|---|
| Critical | Single development server becomes a request bottleneck and outage point | `tools/start.ps1` launches `php -S`; PHP documents it as unsuitable for production | **Deployment required:** production PHP-FPM with Nginx or an equivalent worker-based server, HTTPS, process supervision and health checks. Local launcher remains a local preview. |
| High | Hundreds of SQL round trips per staff dashboard | Workspace loops queried invoice items, request events, order lines, signatures, delivery/customer addresses and each inspection room separately | **Fixed:** authorized-parent batch queries in `server/batch.php` and `workspace.php`; nested inspection trees need two child queries regardless of room count. |
| High | Property search makes permission queries for every listing | `public_property()` previously checked appointments and tenancy membership independently for each search result | **Fixed:** one batch permission query for the selected page. Owners/staff retain access; unauthorized viewers retain redacted addresses. Single-property checks remain. |
| High | Small invoice reads could materialize global item/payment aggregates | Original balance view joined grouped derived tables | **Fixed:** balance view uses indexed invoice-specific sums, plus `(invoice_id,status)` payment index. Confirmed payments alone reduce balances. Query-plan check confirms indexed child access. Broad financial analytics still require profiling. |
| High | Admin loads every account and complete reports into memory/browser | `admin/overview` returned all users and reports had no limit | **Fixed:** bounded overview account sample, server account search with 100-record pages, debounced search, paged reports and page-specific CSV exports. Read permissions and password concealment preserved. |
| High | Missing composite indexes amplify scans/sorts | Notification history, payment state, room membership and appointment authorization/reminders lacked matching composite indexes | **Fixed:** 14 non-destructive indexes in `database/scale-indexes.sql`; applied through repeatable `server/migrate.php`; fresh setup installs them too. Indexes increase storage/write cost. Run DDL in a maintenance window on large production tables. |
| High | File-session lock serializes reads and photo downloads for the same person | Session stayed open throughout the request | **Fixed for reads:** API GET and file GET release the session lock before expensive reads/streaming, after persisting suspended-user removal. Session-changing mutations retain locking. Strict session-ID mode enabled. |
| High | Concurrent first charges can race to create the same monthly invoice | Locking a missing invoice alone is insufficient to serialize creation consistently | **Hardened:** lock the existing tenancy before monthly invoice lookup/create. Payment submission explicitly locks its base invoice before checking confirmed/pending balances. True multi-worker race tests remain required; local server serializes HTTP execution. |
| High | Jobs load all tenancies into memory and overlapping schedulers can fail on duplicate markers | `run-jobs.php` fetched full result sets and checked marker existence before insertion | **Fixed:** 250-record keyset batches, database-wide runner lock, atomic marker plus effects, duplicate-marker skip, and joined notification preferences. Repeated-run/overlap checks passed. Install an actual production scheduler. |
| Medium | Hung requests leave the UI waiting; proxy HTML produces confusing JSON errors | Fetch had no timeout and assumed every response was JSON | **Fixed:** 30-second API timeout, 120-second upload timeout and readable error handling. Mutations are not automatically retried; timeout tells users to check status before resubmitting. |
| High | Staff dashboards lose older data while some collections still grow without bounds | Workspace has 200-record caps without navigation, dependent scopes based on the selected tenancy IDs, and unbounded subscriptions/visits/referrals/agent/provider selectors | **Pending application redesign:** individual paginated workspace endpoints with permission predicates independent of the displayed parent page, server-side operational totals, searchable selectors and route-specific loading. Adding an arbitrary limit would hide work, so this review does not claim these collections are fixed. |
| High | Catalog/lookups become a large download on every startup | `lookups` returns all products, providers and meal plans; substring search and distance calculations can scan many records | **Pending:** separate stable location/category lookups from paged provider/catalog search; location/category filters and geographic candidate bounding before precise distance checks. Validate with representative provider/catalog volumes. |
| High | Horizontal scaling loses sessions and uploaded files | Sessions/uploads reside on local disk and bootstrap selects the local session directory | **Deployment and integration required:** configure shared sessions explicitly, shared private object storage, authorization-preserving download URLs and a CDN for suitable public media. Do not cache private APIs or documents publicly. |
| High | Signup/login/upload abuse consumes CPU, disk and database capacity | Login failures have a basic IP window; registration and upload lack robust independent distributed budgets | **Pending:** per-identity and per-IP limits, registration verification, upload quotas and trusted proxy configuration. Shared-network users must not all be locked out by one coarse IP policy. |
| Critical | Lost records/files or undetected incidents | No verified off-host backup/restore, failover, alerting or permanent scheduler in this repository | **Operational setup required:** encrypted database and upload backups, restore drill, agreed recovery objectives, metrics for p95/error rate/database connections/slow queries/disk/queue age, and scheduled-job failure alerts. |
| High | Manual review/dispatch cannot keep up with demand | Payment/SMS/push/payout and real-person dispatch are explicitly not integrated | **Product/integration work required:** real providers, queued deliveries with retries and deduplication, staffing and escalation processes. Traffic fixes cannot guarantee one-hour service arrival. |
| Medium | Audit, authentication attempts, notifications and archived uploads grow indefinitely | No defined retention/archive process | **Pending:** approved retention policy and bounded archival/cleanup jobs, preserving financial audit records. No business histories were deleted by this review. |

## Verification completed

- `npm run build`: passed; browser guest browse page rendered successfully.
- `npm test`: **21/21 passed** after final changes. Checks cover CSRF, role restrictions, listing privacy, photo workflows, lifecycle activation, billing, provider claims, separate finance confirmation, paginated reports/account search and fetch failure handling.
- PHP syntax checks: all 21 server PHP files passed.
- `php tools/scale-batch-test.php`: passed grouping, empty-parent safety and inspection tree tests.
- Migration executed twice successfully; existing named indexes were skipped on rerun.
- `php tools/scale-jobs-test.php`: verified that a runner skips while another database connection holds its lock and that a second completed run adds no bills/items/rewards/notifications/markers.
- `php tools/scale-db-check.php`: inserted **50,000 accounts, 50,000 invoices, 50,000 items and 50,000 payments** in one transaction, checked a 1,000.00 total / 100.00 paid / 900.00 balance, then rolled everything back. Auto-increment sequence gaps can remain after rollback. Existing regression tests retain marked QA records according to the repository's existing testing workflow.

### Measurements, not guarantees

Database smoke benchmark on this computer:

| Query | Elapsed | Result rows |
|---|---:|---:|
| First account page | 0.39 ms | 100 |
| Deep account page | 8.68 ms | 100 |
| Substring account search | 20.24 ms | 100 |
| Invoice page with balances | 2.95 ms | 100 |
| Existing user's notification page | 0.36 ms | 24 |

See `scale-db-results.json` for raw measurements and query plans. These are single-query timings with warm local data. Account benchmarks do not include the full API role aggregation/count or browser rendering. No 50,000-property/provider/notification dataset was constructed.

Read-only HTTP smoke: **200 requests, 20 in flight, 0 failures, 53 requests/second, p95 462 ms, p99 475 ms** on the local preview. Raw results: `scale-http-results.json`. This tests guest property/catalog reads only. It does not test 50,000 simultaneous users, authenticated write races, production workers or long-running stability.

## Reproduce and finish production readiness

```powershell
npm.cmd run build
npm.cmd test
& E:\Xamppp\php\php.exe server/migrate.php
& E:\Xamppp\php\php.exe tools/scale-batch-test.php
& E:\Xamppp\php\php.exe tools/scale-db-check.php
& E:\Xamppp\php\php.exe tools/scale-jobs-test.php
node tools/scale-load.mjs
```

`scale-load.mjs` accepts `RG_LOAD_BASE`, `RG_LOAD_REQUESTS` and `RG_LOAD_CONCURRENCY`; its default is the local application. Only point it at an authorized staging host. The database benchmark is CLI-only and intentionally rolls back fixtures; run it on a development or isolated staging database because fixture writes consume resources.

Production acceptance work should use an isolated copy with representative tenancy, provider, photo and financial histories. Establish expected daily activity and peak traffic, then run a sustained mixed workload of login, browsing, dashboards, uploads, orders and payments. Ramp traffic until latency/error targets fail, inspect database plans and worker/connection limits, and test billing-day bursts and overlapping writes. Choose worker counts and database capacity from measurements, leaving headroom; account count alone cannot determine them. Run recovery and service-restart drills before admitting customers.

Sources supporting the specific mechanisms: [PHP built-in server limitations](https://www.php.net/manual/en/features.commandline.webserver.php), [PHP session locking and close](https://www.php.net/manual/en/function.session-write-close.php), [MariaDB derived-table optimization](https://mariadb.com/docs/server/ha-and-performance/optimization-and-tuning/query-optimizations/optimizations-for-derived-tables/condition-pushdown-into-derived-table-optimization). Actual app findings come from the local source and tests above.
