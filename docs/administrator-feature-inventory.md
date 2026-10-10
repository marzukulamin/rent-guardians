# Administrator access and complete portal inventory

Generated from the database and portal capability inventory on 2026-10-10.

## All users

The database contains 810 accounts. The complete account list, including IDs, names, email, phone, roles and access, is in `storage/admin-account-inventory.csv`. The administrator account directory reads the current database, supports search by name/email/phone/membership ID, role and access filters, pagination, and Export all. This CSV is a dated local snapshot; the dashboard remains live.

| Account type | Accounts |
|---|---:|
| Admin | 102 |
| Tenant | 102 |
| Owner | 101 |
| Provider | 101 |
| Agent | 101 |
| Supervisor | 101 |
| Finance | 101 |
| Field Agent | 101 |

## All portal features

Shared features apply to all account roles. Visitor features describe the public landing-page and home-discovery experience. The administrator can inspect every underlying database record and the appropriate role portal; changes use validated record editing or the original workflow endpoints.

### Shared

- Sign in and sign out
- Profile name, email, phone and verification
- Role permissions and account access
- Membership ID card
- Notifications and read status
- Listing, appointment and monthly check-in preferences
- Help requests, callbacks and request status
- Demo role sign-in and guided workflow tours

### Visitor

- Neighbourhood, division, district and sector search
- Budget, bedrooms, washrooms, balconies, parking, size and move-in filters
- Family, bachelor and everyone-welcome tabs
- Price, newest and recommended sorting
- Paginated home results
- Photos, captions, categories and fullscreen gallery
- Rent, utilities, advance and recurring costs
- House rules and availability
- Inspection rooms, fixtures, damage and estimates
- Virtual tour link
- Save homes after sign-in
- Book a viewing after sign-in
- Apply to rent after sign-in

### Tenant

- Home summary, invoices, appointment counts and repair status
- Find, save and compare homes
- Viewing requests, appointment details, agent contact and entry codes
- Rental application and reserved-property status
- Tenancy rent, utilities, advance and move-in/move-out dates
- Head tenant and household members
- Add registered or unregistered household members
- Agreement body, signatures and signed download
- Police verification template, upload and review status
- Maintenance categories, preferred provider, urgency and appointment
- Owner payment decision and tenant self-funding
- Repair entry codes, event history, progress, expense confirmation and reconciliation
- Service provider profile and reviews
- Grocery basket, quantity and customization
- Installation, moving and housemaid orders
- Delivery location, schedule, payment timing, cancellation and order tracking
- Catering plans, people, dates and subscription status
- Invoice line items, balances and payment receipts
- Submit payment method, reference, channel and receipt
- Provider and owner reviews
- Move-out notice and inspection
- Head tenancy transfer and acceptance
- Complaints and resolutions
- Reward balance, points redemption and referrals
- List a property, save draft and review submission
- Property details, photo upload, captions, categories, order, cover and archive
- Reports, room/fixture details and document downloads

### Owner

- Owned properties and status
- Create property, address hierarchy, coordinates and terms
- Save and resume listing drafts
- Edit vacant listing details and included utilities
- Upload, caption, categorize, reorder, set cover and archive photos
- Pause or remove eligible listings
- Inspection and viewing appointment requests
- Agent identity, phone and appointment status
- Resident and household details
- Rental agreement, owner signature and signed download
- Verification documents and inspection reports
- Room and fixture condition, permanent damage, estimates and repair responsibility
- Maintenance owner approval/decline, provider details and event history
- Record owner-paid repair expense
- Tenancy invoices, payments and balances
- Tenant complaints and resolutions
- Move-out and head-tenant transfer history
- Bank payout details
- Reviews, rewards and referrals

### Provider

- Business profile, operating area, coordinates, expertise and availability
- Verified identity and membership card
- Matching nearby jobs and distance
- Claim eligible urgent jobs
- Assigned scheduled work and customer contact/address
- Entry code verification
- Start work, complete work and final price
- Customer price confirmation and reconciliation state
- Repair event history
- Incoming grocery, installation, moving and maid orders
- Order items, quantity, installation charge and instructions
- Customer phone and exact delivery address
- Accept, start delivery and complete order
- Product/service catalog, descriptions, units, prices, installation and recurring cost
- Meal-plan name, meal, description and daily price
- Catering customer roster, people, dates, address, phone and payment timing
- Provider ratings and customer reviews

### Agent

- Assigned viewing, inspection and move-out appointments
- Appointment time, property, entry code and completion
- Agent phone, verified identity and ID card
- Verified property access and property photos
- Create move-in/move-out inspection
- Room type, size, electrical ports, light holders and amenities
- Fixture condition, description, estimate and permanent damage
- Add/remove rooms and fixtures before submission
- Submit inspection for supervisor review
- Inspection photo/document upload and downloads
- Property and inspection review status
- Training materials

### Supervisor

- Operations overview and all properties/tenancies
- Review pending listings and approved inspections
- Publish eligible homes
- Assign verified agents and issue appointment entry codes
- Review submitted inspections and fixture repair decisions
- Review identity/police documents
- Activate verified, signed and advance-paid tenancies
- Review maintenance responsibility and approve requests
- Assign matching verified providers and issue service codes
- Review costs, mismatches and reconcile completed work
- Review submitted payments and receipts
- Handle support, callbacks and tenant complaints
- Approve verified head-tenant transfers
- Manage move-out settlement and tenancy closure
- All operational documents and reports

### Finance

- All invoices and line items
- Balances, paid totals and outstanding amounts
- Payment method, reference, receipt, channel and timestamps
- Supervisor review and independent confirmation
- Confirm reviewed payment within remaining balance
- Reject unconfirmed payment
- Financial approval trail and collection reports

### Field Agent

- Assigned campaigns, locations and dates
- Daily outreach checklist
- Owner name, phone and follow-up notes
- Request owner appointment through support
- Assigned campaign and checklist history
- Training materials

### Admin

- All accounts, profile/contact details, verification and roles
- Create, suspend, restore and archive accounts
- Every role portal and linked record
- Edit supported records with database validation
- Create requests and services on behalf of active accounts
- Pending and overdue work queue
- Assign staff/providers and guide request transitions
- Send targeted in-app follow-ups with linked work
- Agent/provider names, phones and assignments
- Remove household members or end a tenancy with an audit reason
- All documents, receipt files, ID cards and inspection reports
- Full database record explorer with search, status and account filters
- Paginated financial and operational reports and CSV exports
- All portal-feature inventory
- Append-only actor history
- Operational settings, address hierarchy, campaigns and training

## Administrator workspace

- **Accounts:** every account first; profile/contact editor, role verification/access, recoverable Delete account and Restore account, and role portal preview. Portal actions record the administrator actor and the account they act for without changing the signed-in session.
- **Work queue:** requested/confirmed appointments, maintenance and unreconciled costs, active orders, pending documents and inspections, pending listings, submitted/reviewed payments, support/callbacks, complaints, transfers and pending/notice tenancies. Overdue work uses stored dates; assigned person contact and a targeted in-app follow-up are shown.
- **Records:** all schema tables with human-readable groups, search, status/account/relationship filters, complete record details, linked contacts, database-backed FK choices, typed validation, conflict detection, create/edit and workflow controls.
- **Documents:** every user file, receipt, verification document, photo and inspection report, with private download access, review and upload on behalf of an account.
- **Reports:** twenty SQL reports with paginated exports, including account, staff contact, appointment, order, catering, document, inspection, support, campaign and financial reports.
- **Overview:** live pending/overdue counts, balances, collections, reconciliation mismatches and recent audit activity.
- **System:** live settings, location management, all portal features and searchable paginated audit history.

## Editing, removal and historical records

Account deletion is recoverable and disables sign-in. It preserves foreign keys and history. A current head tenant must first be transferred or removed from the active property. Ending a tenancy records a reason, preserves outstanding invoices and signed documents, notifies both parties and returns the property to inspection review. Removing a non-head household member revokes their household access and rejects any pending transfer to that member.

Editable records require a reason and optimistic revision check. Workflow-managed states use their existing transition endpoints. Verified signatures cannot be forged by the administrator; genuine signed contracts and confirmed payment history are preserved. Invoice corrections are explicit database line-item adjustments, and reviewed/confirmed payments still require different actual approving accounts. OTP secrets, password hashes, private file paths and authentication internals are not exposed as editable business fields.

Staff reminders are delivered through the app's database-backed notifications. Phone numbers are available for contact; SMS/email/phone delivery is not integrated into this app.

## Validation

Run `npm test`, `E:\Xamppp\php\php.exe tests/admin-mutations.php`, and `E:\Xamppp\php\php.exe server/consistency-audit.php`. The administrator tests cover every schema resource, all eight role workspaces, reports/downloads, permissions, delegation, editing conflicts, recoverable account deletion, tenant removal and audited adjustments. Mutation fixtures are held in one transaction and rolled back.
