# Updated cursor-guided workflow tours

Updated on 10 October 2026 for the role-specific portals and rebuilt administrator console.

Visitors choose one task rather than taking a compulsory full-product tour. The first six choices follow each role’s work; Show all reveals the rest. Back, Pause, Resume and Skip remain available. The cursor is a visual pointer: visitors operate controls themselves.

## Database connection

Tours use the portal’s actual controls and APIs. Eligibility hints come from the signed-in database workspace. Mutation steps wait for a successful save, the expected action/status/entity and, where available, the highlighted record ID. A saved viewing points to its exact appointment card. Session storage contains tour progress and record references only. No business/demo fixtures were added to the frontend. Existing portal permissions and independent payment approvals remain enforced.

## Workflow inventory

### Tenant — 18 tasks

- Find a home & book a viewing (7 steps)
- Save & compare homes (3 steps)
- Apply to rent (4 steps)
- Tenancy & agreements (1 steps)
- Sign an agreement (3 steps)
- Add a household member (3 steps)
- Upload verification documents (3 steps)
- Bills & payment receipts (1 steps)
- Record a payment (3 steps)
- Report a repair (3 steps)
- Place & follow a grocery order (6 steps)
- Move-in essentials (4 steps)
- Home help (4 steps)
- Moving support (4 steps)
- Daily meals (4 steps)
- Plan a move-out (3 steps)
- Refer a friend (3 steps)
- Help & support (3 steps)

### Owner — 15 tasks

- List a property (3 steps)
- Manage property photos (3 steps)
- Edit listing details (3 steps)
- Request a property inspection (3 steps)
- Visits & appointments (1 steps)
- Tenancy & agreements (1 steps)
- Sign an agreement (3 steps)
- Upload verification documents (3 steps)
- Approve a repair cost (3 steps)
- Record a repair expense (3 steps)
- Bills & payment receipts (1 steps)
- Bank details (3 steps)
- Help & support (3 steps)
- Understand your portfolio (4 steps)
- Raise a tenant complaint (3 steps)

### Service provider — 9 tasks

- Service work & entry codes (8 steps)
- Fulfil delivery orders (5 steps)
- Add a catalog item (4 steps)
- Create a meal plan (4 steps)
- Provider profile (3 steps)
- Help & support (3 steps)
- Edit a catalog item (4 steps)
- Edit a meal plan (4 steps)
- Work bills & documents (4 steps)

### Agent — 17 tasks

- Visits & appointments (1 steps)
- Verify visit entry (3 steps)
- Complete a verified visit (3 steps)
- Create an inspection (3 steps)
- Upload verification documents (3 steps)
- Training & identity (1 steps)
- Help & support (3 steps)
- Assigned property details (1 steps)
- Services & move-out handoffs (2 steps)
- Record a repair follow-up (3 steps)
- Start with your assignments (3 steps)
- Save a handoff note (3 steps)
- Take a support request (3 steps)
- Resolve a support request (3 steps)
- Escalate to a supervisor (3 steps)
- Follow up on a complaint (3 steps)
- Update your work identity (3 steps)

### Supervisor — 23 tasks

- Assign appointments (3 steps)
- Review & publish listings (3 steps)
- Approve verification documents (3 steps)
- Approve inspection reports (3 steps)
- Activate a ready tenancy (3 steps)
- Repair decisions & costs (1 steps)
- Approve a repair request (3 steps)
- Assign a service provider (3 steps)
- Reconcile repair costs (3 steps)
- Review submitted payments (3 steps)
- Find agents & providers (2 steps)
- Help & support (3 steps)
- List a property (3 steps)
- Start with review & dispatch (3 steps)
- Services & move-out handoffs (2 steps)
- Follow up with the team (3 steps)
- Assign a support request (3 steps)
- Save a handoff note (3 steps)
- Take a support request (3 steps)
- Resolve a support request (3 steps)
- Update your work identity (3 steps)
- Settle & close a tenancy (3 steps)
- Approve a tenant transfer (3 steps)

### Finance — 5 tasks

- Confirm reviewed payments (3 steps)
- Inspect receipt evidence & balances (3 steps)
- Explore financial reports (2 steps)
- Help & support (3 steps)
- Update your work identity (3 steps)

### Field agent — 7 tasks

- Prepare for campaign outreach (3 steps)
- Daily outreach checklist (3 steps)
- Request an appointment (3 steps)
- Help & support (3 steps)
- Review your outreach history (1 steps)
- Campaign scripts & training (1 steps)
- Update your work identity (3 steps)

### Administrator — 12 tasks

- Explore an account & its portal (5 steps)
- Inspect & edit connected records (4 steps)
- Reports & exports (3 steps)
- Audit history (2 steps)
- Operational settings (2 steps)
- Help & support (3 steps)
- Manage demo account permissions (4 steps)
- Update an operational setting (4 steps)
- Guide pending & overdue work (4 steps)
- User reports & documents (4 steps)
- Edit a stored record (5 steps)
- Follow up on missed work (4 steps)

## Verification

Production build succeeds. Application regression suite: 27 tests passed, including four new tour regression checks. Browser checks sampled all eight role portals: tenant discovery and database save, owner portfolio, agent assignments and inspection fields, supervisor dispatch, finance receipt/report pages, field agent campaign/training, provider profile and administrator account previews/work queue. This is not a claim that every optional mutation was submitted in browser testing.

The guest home-search guide continued through Demo Tenant sign-in into the viewing form. Date input now handles input events as well as change events, so changing another field preserves the selected time. Fictional viewing requests #228 and #229 were saved in the local demo database; #229 verified the exact saved-record highlight. No signatures, permission grants, account deletions, financial confirmations or outbound follow-up messages were submitted during browser checks.

Desktop inspection and administrator preview screenshots, and a 390 × 844 viewing-form screenshot, are saved in storage/ui-review. Phone layout keeps the active field available below or above the guide. Reduced-motion settings disable pointer transitions and smooth scrolling.

## Eligibility

Some actions require a record in the appropriate state, such as a confirmed assigned visit, a reviewed payment, an active campaign or an available job. The guide explains this and allows choosing another task. It does not fabricate an eligible record or bypass the workflow’s prerequisites.
