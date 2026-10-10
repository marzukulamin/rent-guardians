# Database consistency audit — 10 October 2026

The local `rent_guardian` database contains 53 base tables, 102 declared foreign key relationships and three reporting views. Every table was inventoried and every declared FK checked across all its rows. The semantic audit checks all rows, including legacy and populated demo accounts, rather than only account 001.

## Confirmed findings and repair

- Tenancy 13 correctly links tenant account 150016 (`RGD-TENANT-001`) and owner account 150116 (`RGD-OWNER-001`). The old generator reused the same name across all eight roles, including all 40 populated owner/tenant pairs. They were separate accounts throughout.
- Repaired 700 untouched generated role identities, matching sample bank account names and generated outreach contacts. Tenant names remain unchanged. The example owner is now Nadia Ahmed; the head tenant remains Ayesha Rahman.
- Reconciled 44 linked household names/phones with `users`. The seeded head-tenant contacts previously used a generic phone rather than the actual account phone.
- Corrected 40 machine-generated fictional owner signatures (`ip_address='local-demo'`). Real signature records and contract content/hashes were preserved.
- Database changes have backups in `storage/data-audit/identity-backup-*.json`. The repair is local-only and idempotent through `job_runs`; edited demo profile names are preserved.

## Authoritative endpoint behavior

- Workspace tenancy names and public account IDs join `tenancies.head_tenant_id` and `properties.owner_id` to `users`. UI labels identify both parties explicitly.
- Registered household members join their current `users.name`, `phone` and `public_id`; unregistered dependants retain their own database member details. Head-tenant labels follow the current tenancy ID relationship. Profile updates also synchronize linked database member fields.
- Adding a registered member reads the account's identity from the database, rejects the owner and duplicate linked accounts, and prevents assigning “Head tenant” outside the transfer workflow.
- New rental applications require the tenant role and create the linked head household record. Transfer approval rechecks the current parties, active tenant role and membership, and updates household labels in the same transaction.
- Maintenance customer contacts and the provider work report identify the original requester, rather than silently replacing that customer with a later head tenant.
- Tour totals, order status, customer and receiving provider are read from the current workspace order and stored order-item prices. Browser storage identifies the selected demo order and navigation context; it does not supply displayed money or decide which account receives an order.
- Provider job discovery respects the database availability flag.
- Account management prevents removing roles with linked owner, head-tenant, provider or agent records, and requires a database operating neighbourhood when creating a provider profile for a newly assigned provider role.
- Catalog prices are current offers. Order-item prices and invoice charges are transaction snapshots and must not be rewritten when a provider changes a catalog price. Signed names in genuine agreements are likewise historical records.

## Coverage and verification

`server/consistency-audit.php` inventories every schema table, checks every FK, then validates 36 business/file relationships: role ownership, head household membership, duplicate current tenancies, property state, linked contacts, appointment/visit/inspection links, signature parties and hashes, document files and photo references, service provider/category links, order totals and invoice identity, subscription and maintenance billing links, payments and balances, independent payment approval, reviews, rewards, location hierarchy, campaign assignment and referral identity.

`tests/database-consistency.test.mjs` reads every administrative schema dataset and checks its database count, resolves all 800 populated identities, compares all populated tenancy parties, and verifies member, order and financial fields across all eight role portals. It also exercises validation against owner-as-member, fake head labels and self-transfer without creating records.

Run the audit with `E:\Xamppp\php\php.exe server/consistency-audit.php`. It writes `storage/data-audit/consistency.json` and exits nonzero if a checked relationship fails. Use `server/repair-demo-identities.php` for a preview or add `--apply` for the backed-up demo-only repair. `npm test` includes the endpoint regression checks.

This validates the current local database and the documented invariants. It is not a claim that future data or untested production systems cannot develop other inconsistencies.
