# Guided demo tours

The sign-in popup retains all eight demo roles. Sign in, select **Test [role] workflow**, then pick a short task. Six tasks are shown initially; **Show all features** reveals the rest. Tours never start on first sign-in. The landing page has **Show me how** for house search and viewing bookings, including continuing the chosen viewing after sign-in.

## Important features by portal

| Portal | Guided features |
| --- | --- |
| Tenant | Search and viewing; saved homes; rental applications; tenancy and agreement signing; household; verification uploads; bills and payment records; repairs; groceries; installation, home help and moving orders; meals; move-out; referrals; support |
| Owner | New listings and drafts; photos; listing edits; inspection bookings and appointments; tenancy and signing; verification; repair approval and expense records; bills; bank details; support |
| Provider | Nearby jobs, entry verification, start and completion; delivery acceptance, dispatch and completion; products; meal plans; profile; support |
| Agent | Assigned appointments; entry verification and visit completion; inspection creation; document upload; training; support |
| Supervisor | Appointment assignment; listing publication; document and report approval; tenancy activation; repair approval, provider assignment and cost reconciliation; payment review; support |
| Finance | Reviewed payment confirmation; bills and ledger; support |
| Field agent | Campaign and training; outreach checklist; appointment request; support |
| Administrator | Accounts and permissions; datasets; reports and exports; audit history; operational settings; payment review; repair costs; support |

## Interaction

- One instruction at a time with an SVG cursor and a highlighted live control.
- Forms guide each visible field separately, preserving native validation. Optional fields can be left empty. Multi-section listing forms stay in the tour through review.
- Clicks that open another view advance before the old control unmounts. Mutations advance only after a matching successful save; failed requests stay in the form.
- Back, Pause, Resume, Skip and Escape keep visitors in control. Missing or already-used scenarios explain how to skip instead of blocking the portal. Skipped steps are distinguished from completed testing.
- Instructions join the open dialog’s focus trap. Touch controls are at least 44px; focus indicators remain visible; scrolling respects reduced motion.
- Tour progress is stored per account in session storage. It contains guidance state, not business records. Storage failure does not prevent a tour.

## Connected order demonstration

The grocery tour captures the ID returned by the new order save; it never guesses from old demo notes. The customer can open the exact receiving fictional provider. That provider sees the delivery address and follows acceptance, delivery and completion for the same ID. The customer and administrator see the saved status, and the admin control centre includes the exact order and its items above the normal datasets.

Role switching uses the existing server allowlist and local-demo restriction. It never grants access to a real account. Demo mutations use normal server validation and persist; tours do not reset seeded scenarios. A used scenario may require another eligible record or a different demo account. External dispatch and payments remain subject to the app’s existing integrations.

## Verification

`npm run build` builds the shipped frontend. `node --test tests/tour-workflows.test.mjs` covers every role, demo identity checks, successful-save matching, exact-order delivery and finance confirmation. `npm test` includes these checks plus the existing API tests. The existing population test assumes pristine seeded counts and can fail after manual demo activity; preserve that activity rather than deleting records to satisfy the assertion.

Design guidance: local [UI UX Pro Max skill](../tools/ui-ux-pro-max-SKILL.md), with verified searches for onboarding freedom and visible keyboard focus in dialogs.
