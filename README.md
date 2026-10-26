# Rent Guardian

## Public portfolio demo

The `portfolio-demo/` folder is a standalone interactive preview built from fictional sample listings and workflows. Visitors can search homes, save favourites, inspect details, request a sample viewing, try sample services, and switch between eight role workspaces. Demo actions stay in each visitor's browser and do not reach the PHP API or a database. It is a product preview, not a place to submit real rental requests or payments.

Run `npm run build:demo` to prepare `demo-dist/` for Netlify. The root `netlify.toml` sets that build and publish directory. The full application still requires the PHP API and MariaDB described below.

A local rental operations web app built in `E:\RENT GUARDIANS APP`, based on all 161 rows extracted from **User Story Rent Guardian .docx**, plus your PRD, role permissions, house inspection specification, business model and brand guide.

The master implementation request is **partially completed**. See [the measured implementation report](docs/implementation-report.md), [all 161 audited stories](docs/story-coverage.md), and [current test results](docs/verification.md) for exact scope and unfinished work. The latest update adds readable portal typography, private listing drafts and review, property-photo management, address privacy, paginated search, and stricter tenancy/advance-payment checks.

## Open the app

- App: http://127.0.0.1:8000
- phpMyAdmin: http://127.0.0.1:8081
- Database: `rent_guardian`
- Private administrator and database credentials: `storage/credentials.md`
- Sample login: `tenant@rentguardian.local`, password `Guardian@2026!`
- Other sample accounts: `owner`, `provider`, `agent`, `supervisor`, `finance`, `field_agent` at `@rentguardian.local`, using the same sample password.

The administrator password is randomly generated at setup and is not committed to source code. The local demo contains fictional accounts and property records. Replace them with verified real data before use with customers.

For comprehensive demo scenarios across every role, run `server/seed-demo.php` followed by `server/seed-functional-demo.php` with the PHP executable. See [the demo testing guide](docs/demo-testing.md) for account emails, entry codes and workflows. Both seeders add records once without resetting existing users or passwords.

## Start again after restarting Windows

Run `powershell -ExecutionPolicy Bypass -File tools/start.ps1`. This starts the isolated MySQL instance, PHP app and phpMyAdmin on loopback addresses. It keeps all Rent Guardian database files in `storage/mysql`; it does not use or overwrite the existing XAMPP application databases.

## Stack and development

- React, Vite, Lucide and Three.js frontend, with local Poppins fonts.
- PHP 8.2 API with PDO prepared queries, server-side sessions, CSRF checks and role permissions.
- MySQL-compatible MariaDB 10.4, accessible in phpMyAdmin.
- All account, property, form, request, order, invoice, notification, signature and audit records persist in MySQL. Uploaded file bytes are stored outside the public web root, with metadata and foreign-key relationships in MySQL. No business data is stored in browser localStorage.

Run `npm install`, then `npm run build`. The PHP server serves the build and API together. For frontend development, run `npm run dev` while PHP runs on port 8000.

On a fresh MySQL instance, run `E:\Xamppp\php\php.exe server/setup.php` once. It refuses to overwrite an existing `rent_guardian` database. Optional environment variables are `RG_DB_HOST`, `RG_DB_PORT`, `RG_DB_ROOT_USER`, `RG_DB_ROOT_PASSWORD`. The setup creates a separate application database account. Connection details live in the ignored `server/config.local.php`.

## Workflows

- **Guest:** browse, filter, sort, view full-screen photography, read approved room-by-room reports and inspect the conceptual 3D apartment without a questionnaire.
- **Property photos:** absent images show “Photos not yet available”; sample photographs are explicitly labelled. Owners can select several images, preview them, categorize/caption them, choose a cover, reorder, replace by uploading and archiving, and remove photographs. Browser preparation bounds dimensions to 2400 px and converts images to WebP before server validation. Exact residential addresses appear only for authorized participants.
- **Tenant:** save homes, book viewings, apply to rent, submit verification, sign and download agreements, manage household members, record payments, see advances, report repairs, review providers and owners, order groceries with chicken cutting instructions, subscribe to breakfast/lunch/dinner plans, arrange installations, moving support and home help, redeem points, refer friends, give move-out notice and request a head tenant transfer.
- **Owner:** list and update a property using exact addresses, utility inclusions and terms; request inspections; decide repair responsibility; sign agreements; view tenants and verification documents; track rent and property expenses; file complaints; request callbacks; store bank details; pause or remove available listings.
- **Provider:** maintain expertise and location, claim eligible approved instant jobs within 4 km, verify customer entry codes, track assigned work, enter prices, view exact delivery addresses and grocery instructions, fulfil orders, and create catering and service catalogs.
- **Agent:** handle assigned appointments, verify entry, record detailed room/fixture inspections and upload property photos. Agents cannot publish listings or confirm payments.
- **Supervisor:** assign agents, approve reports and verification, publish listings, decide and coordinate maintenance, review payment records and resolve support.
- **Finance:** inspect ledgers and confirm separately reviewed payments. The same person cannot review and confirm a payment.
- **Field agent:** assigned outreach campaigns, daily checklists, owner contacts and training. Tenant and financial datasets are hidden.
- **Admin:** inspect every business dataset and original story, manage roles/suspension/verification, view audit history and analytics, run read-only SQL reports, export CSV, and change stored operational settings. Password hashes and entry-code hashes are concealed in the UI.

## Relational database

`database/schema.sql` contains the complete schema, primary keys, foreign keys, unique constraints, monetary checks, search indexes and reporting views. `database/queries.sql` contains SQL examples for exact addresses, household membership, inspection damages, owner rent roll, invoice balances, provider price reconciliation, food delivery, catering and move-out scheduling.

`user_stories` stores every original requirement with persona, benefit, module, relevant tables and an integration status. `docs/story-coverage.md` is a readable coverage register. The admin SQL report exposes the foreign-key graph.

Money uses `DECIMAL`, not floating-point storage. Account and routing numbers are text so leading zeros are preserved. Deferred groceries, catering and maintenance join the monthly invoice. Immediate services receive their own invoice. Pre-installation has no pay-later path. Only finance-confirmed payments reduce balances. New approved charges reopen a settled monthly bill when needed.

## Background jobs

Run `E:\Xamppp\php\php.exe server/run-jobs.php` daily through your scheduler. It generates monthly rent/utility bills, monthly in-app home check-ins, appointment reminders and rewards for verified successful referrals. Each job has a unique key so repeated runs do not duplicate bills or rewards. The local launcher runs it once at startup; a permanent daily scheduler has not been installed.

## Verification

`npm test` checks the populated local demo API, all eight role workspaces, catalog pagination, photos, account totals, financial summaries and request protections. Install `server/seed-demo-population.php` first. `npm start` launches the Vite frontend.

Mutation suites run with `npm run test:integration` and require `RG_TEST_URL` pointing to a separate disposable database server with environment `test`. They refuse the interactive local demo server. `server/data-audit.php` checks all application tables and foreign keys; `--apply` backs up and removes QA fixtures and their linked records.

The expanded suite has 19 unit/API checks. `tools/priority-ui-qa.cjs` tests persistent drafts, review and submission, optimized photograph uploads and archival at five widths. `tools/mobile-portals-qa.cjs` checks 26 routes across eight roles. New marked property/lifecycle histories are retained for review; the older cleanup script does not remove those histories. Run `server/migrate.php` before using these additions on an existing database.

## External connections still needed

The [50,000-user readiness review](docs/scale-readiness.md) lists scaling risks, applied code/database fixes, measured tests and remaining production work. The local build passes 21 regression checks and a rollback-only benchmark with 50,000 accounts/invoices/items/payments; this is not certification for 50,000 concurrent users. Admin account search and SQL reports now use 100-record pages and export the displayed page.

This is a functioning local web app with manual financial reconciliation. It does not yet send SMS, make phone calls, deliver browser push messages, charge bKash/cards, transfer owner/vendor payouts, dispatch real people or guarantee one-hour arrival. It stores callback requests and uses in-app notifications. Payment references and receipts are reviewed manually. Scanned tours need real property media. Agreement signatures record typed consent and an exact version hash; the sample agreement should be replaced with your reviewed real agreement.

Before public deployment, replace sample data, rotate credentials, configure a production web server with HTTPS, backups and scheduling, connect your chosen providers, and review the agreement and personal-data retention process. No public deployment was made.

## Customer portal and ten-million-user review

The local portal includes one-click demo sign-in for all eight roles and displays demo homes with illustrative photos. Run `server/seed-demo-catalog.php` to add six published homes without duplicating existing demo listings or resetting passwords. Other server environments retain normal account sign-in and hide marked demo activity and stock photos. Read the [10-million-user review](docs/ten-million-readiness.md) for applied traffic safeguards, measured checks, remaining bottlenecks and the production deployment sequence. A production Nginx/PHP-FPM template is in `deploy/nginx.conf.example`; it has not been deployed. Schedule `server/clean-request-budgets.php` every five minutes after migrating. Current local data still includes fictional seed accounts; customer deployment requires a separate verified dataset.
