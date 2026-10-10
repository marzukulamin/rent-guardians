# Demo testing

Open http://127.0.0.1:8000. Sign out between roles, or use separate browser profiles.

The sign-in popup includes one-click demo access for Tenant, Owner, Service provider, Agent, Supervisor, Finance, Field agent and Administrator when the server environment is `local`. With the connected population installed, each button opens `demo.<role>.001@rentguardian.local`; otherwise it uses the original demo account. Normal email/password sign-in remains available. Demo access is disabled by the server in other environments.

## Connected population

Run `server/seed-demo-population.php` after the normal schema and base demo setup. It adds 100 users for each of the eight roles (800 new accounts), 100 properties with two stored photos each, and linked inspections, appointments, 40 active tenancies, agreements, verification documents, invoices, payments, repairs, orders, meal subscriptions, referrals, support and field campaigns. Existing records are preserved. The seed runs once and does not reset later manual changes.

Emails follow `demo.tenant.001@rentguardian.local` through `demo.tenant.100@rentguardian.local`, replacing `tenant` with `owner`, `provider`, `agent`, `supervisor`, `finance`, `field_agent` or `admin`. Password: `Guardian@2026!`. These are fictional local accounts. Of the new homes, 60 are published and available for discovery; 40 are rented and visible in the appropriate workspaces.

Run `server/verify-demo-population.php` for database checks and `npm test` for portal/API checks. `server/data-audit.php` scans every base table and foreign key; `--apply` backs up QA fixtures before removing them and their dependent records. Reports and recoverable backups are in `storage/data-audit/`.

Mutation tests run separately with `npm run test:integration`. Set `RG_TEST_URL` to a separate server whose environment is `test` and whose database is disposable. They refuse the interactive local demo database, preventing QA fixtures from returning to the portals.

Run `server/seed-demo-catalog.php` to add six published demo homes in Dhanmondi, Bashundhara, Uttara and Mirpur, with two local illustrative interior photos each. It also ensures all eight demo roles exist without changing existing passwords. Rerunning it does not duplicate homes. In local demo mode, the portal displays fictional scenarios and illustrative photos.

All records labelled DEMO are fictional. Uploaded PDFs are copies of the sample verification template; photos are illustrative interiors. They do not represent real identity documents, payments or inspections.

## Accounts

For each of these accounts, the password is `Guardian@2026!`:

| Role | Email | What to test |
| --- | --- | --- |
| Tenant | tenant@rentguardian.local | Saved homes, viewings, applications, documents, signing, household, bills, payment receipts, repairs, groceries, installations, moving, home help, meal subscriptions, reviews, points, referrals, support and move-out |
| Owner | owner@rentguardian.local | Draft/pending/published/paused/removed listings, repair approval and decline, agreements, tenants, expenses, bank details, complaints and support |
| Provider | provider@rentguardian.local | Claim the approved instant electrical job, verify service entry, start/complete repairs, submit costs, reconcile mismatches, fulfil each order type and manage products/meals |
| Agent | agent@rentguardian.local | Assigned inspections, entry verification, room reports, fixture damages, illustrative photo downloads and ID card |
| Supervisor | supervisor@rentguardian.local | Approve documents/reports, publish pending listings, activate the ready tenancy, assign services, reconcile matched owner costs, review payments, handle transfers and support |
| Finance | finance@rentguardian.local | Confirm reviewed payments, inspect paid/open/void invoices and balances; submitted payments require supervisor review first |
| Field agent | field_agent@rentguardian.local | Active outreach campaign, training and sample daily checklist; financial and tenant data remain inaccessible |
| Flatmate | flatmate@rentguardian.local | Shared household and acceptance of the head tenant transfer |

Admin: `admin@rentguardian.local`. Its existing private password is in `storage/demo-credentials.md` or `storage/credentials.md`. Test user management, all datasets, audit history, reports, settings and CSV export.

## Suggested workflow

1. Browse as a guest. Use the published demo listing to test a fresh rental application.
2. As tenant and owner, sign the unsigned agreement for **DEMO: Tenancy pending_verification**. The supervisor can review its pending verification document. Submit an advance payment, review it as supervisor, then confirm it as finance before activation.
3. **DEMO: Tenancy ready** already has both signatures, approved verification and a confirmed advance. The supervisor can activate it immediately.
4. Keep the original active Lakeview tenancy for groceries, catering, repairs and household tests. Accept its transfer as flatmate, then approve it as supervisor when ready to change the head tenant. Approval changes which account controls that tenancy.
5. **DEMO: Tenancy notice** has a submitted move-out report and a settled advance invoice. Approve its report, then end the tenancy. **DEMO: Tenancy ended** shows historical records and an available home.
6. For maintenance, use the labelled owner-decision, supervisor-approval, instant-claim, entry-verification, in-progress, price-mismatch and matched-owner-expense scenarios. Completed unreconciled jobs need matching customer/provider prices before billing.
7. Grocery, installation, moving and maid orders each have placed, accepted, delivering, completed and cancelled examples. Cancelled demo orders have void invoices. Installation, moving and maid invoices are separate; deferred meals are on the monthly bill.
8. Breakfast, lunch and dinner subscriptions have active, cancelled and ended examples. Review sample customer feedback, points redemption history, registered referral, complaints and support tickets.

## Entry codes

New functional demo service entry: **135790**. Inspection entry: **246810**. These expire 30 days after seeding. Each assigned record can verify its code once. The tenant's notification records the expiry. Older basic demo records use the same codes with their original seven-day expiry.

## Add the demo to a local setup

From the project directory:

```powershell
& 'E:\Xamppp\php\php.exe' server/seed-demo.php
& 'E:\Xamppp\php\php.exe' server/seed-functional-demo.php
& 'E:\Xamppp\php\php.exe' server/seed-demo-catalog.php
```

Both seeders are additive and keep existing account passwords. Their database markers prevent duplicate scenarios on rerun. They do not reset scenarios after you test them. Only run against the local database.

The demo supports local manual workflows. External SMS, calls, payment processing and real service dispatch still require integrations described in README.md.

## Guided tours
Every fictional demo role has an optional **Test [role] workflow** button. Its feature picker opens short cursor tours with one instruction at a time, field guidance, Back, Pause, Resume and Skip. Successful saves advance the workflow; failed forms keep their context. The tenant grocery tour captures the new order ID and offers the exact receiving provider and admin dashboards. The provider can accept, deliver and complete that same order. The landing page also guides visitors through search and viewing booking, retaining the selected booking through sign-in. See [the feature inventory and tour guide](guided-demo-tours.md).

