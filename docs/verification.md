# Verification performed on 9 October 2026 (Asia/Dhaka)

Current final checks supersede the historical notes below. The complete master request remains PARTIALLY COMPLETED; see [the implementation report](implementation-report.md) and [all 161 audited stories](story-coverage.md).

| Command/check | Current result |
|---|---|
| `npm.cmd run build` | Passed |
| PHP `-l` on all 20 server files | Passed |
| `E:\Xamppp\php\php.exe server/migrate.php` | Passed repeatedly; additive, idempotent migration |
| `npm.cmd test` | 19 passed, 0 failed: 4 unit tests and 15 API tests |
| `node tools/ui-qa.cjs` | Passed; all existing portal checks and five responsive widths |
| `node tools/priority-ui-qa.cjs` | Passed; draft recovery, review/submission, absent-photo state, optimized upload/removal, readable text and five widths |
| `node tools/mobile-portals-qa.cjs` | Passed; 26 routes across eight roles at 390 px |
| Original DOCX versus `database/user-stories.json` | All 161 requirements matched after whitespace normalization |

Browser coverage uses installed Chrome through Playwright at 375, 390, 768, 1024 and 1440 pixels. No JavaScript errors or document overflow were found in the final suites. Other browser engines, production load and live integrations were not tested.

The new API journey verifies owner creation → inspection appointment/agent OTP → report approval/publication → tenant viewing/visit history → application → private police document/review → both signatures/gated download → advance invoice due on move-in → separate supervisor review and finance confirmation → active tenancy → move-out notice/inspection scheduling. Final damage settlement and deposit refunds remain incomplete.

Fixed defects include small controls, absent-photo fallback, empty-draft parsing, stale search responses, included utilities lost on edit, reserved-property verification upload denial, exact-address exposure, mixed advance billing, repeated activation, occupied-property publication, and tablet caption/header overflow. Unit tests cover fractional BDT display, demo-media classification, invalid uploads and Bangladesh date boundaries.

Marked QA property fixtures remain for review, with test photos archived and UI listings removed. Successful lifecycle fixtures retain their test tenancy/payment history. The existing `tools/clean-qa.php` cleans only older marked service/financial fixtures; it does not delete new property histories. Business records and uploaded files were preserved.

Remaining implementation includes catering fulfillment, grocery stock/customer confirmation, final settlements/refunds, inspection repair dispatch, listing authority approval and head-tenant financial reassignment. Further boundary tests are needed for scheduling, OTP lockouts/expiry, referrals and background jobs. Real payments, SMS, phone, push, dispatch, reviewed signing and authentic property media are unavailable.

## Historical checks from 8 October 2026

The app was built and tested against its running PHP API and isolated MariaDB database on this computer.

- Production Vite build: passed. React and Three.js are separated into cached bundles.
- PHP syntax checks: all server files passed.
- API integration suite: 10 tests passed. These cover guest location search, CSRF checks, role restrictions, saved-home persistence, grocery line items and chicken preparation, private delivery addresses, separate installation invoices, radius-limited job claiming, entry-code verification, maintenance reconciliation, independent payment approval, admin reports, story coverage and masked bank details.
- Browser QA in installed Chrome: passed. Guest filters, property reports, full 3D controls, tenant screens, owner listing form, provider tabs, supervisor workspace, finance ledger, outreach workspace and every administrator tab were checked.
- Viewports checked: 375, 390, 768, 1024 and 1440 CSS pixels. No horizontal document overflow or browser JavaScript errors were found.
- Reduced-motion mobile mode was included. The 3D view supports pause, reset, rotate and zoom buttons.
- phpMyAdmin returned HTTP 200 and connected to the local database.
- All 161 original story rows are stored in `user_stories`; audited exceptions are explicitly flagged for integration work. The schema has 51 tables and 101 foreign-key links.

During browser QA, an initial request race was found: parallel anonymous requests could establish different session cookies. Initialization was made sequential, and the sign-in and role checks were rerun successfully. Separate immediate invoices and the refreshed invoice view were also verified after their migration.

Sample homes and photographs are visibly labelled. These checks verify the local application, not real bank transfers, real property inspections, SMS delivery, phone calls, public push delivery, scanned tour media or production load. See the README for those external connections.
