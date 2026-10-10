# Database design

Identity, location, rentals, inspections, commerce and billing are separate relational domains. Each record uses a primary key; cross-domain records use foreign keys rather than copied names. Status changes and audit events preserve operational history.

```mermaid
erDiagram
    users ||--o{ user_roles : has
    roles ||--o{ user_roles : grants
    locations ||--o{ locations : contains
    locations ||--o{ addresses : locates
    addresses ||--o{ properties : identifies
    users ||--o{ properties : lists
    properties ||--o{ appointments : schedules
    properties ||--o{ inspections : documents
    inspections ||--o{ inspection_rooms : contains
    inspection_rooms ||--o{ inspection_items : checks
    properties ||--o{ tenancies : rents
    tenancies ||--o{ tenancy_members : houses
    tenancies ||--|| contracts : agrees
    contracts ||--o{ contract_signatures : records
    tenancies ||--o{ documents : verifies
    tenancies ||--o{ maintenance_requests : requests
    maintenance_requests ||--o{ request_events : tracks
    provider_profiles ||--o{ maintenance_requests : fulfils
    provider_profiles ||--o{ catalog_products : offers
    tenancies ||--o{ orders : receives
    orders ||--o{ order_items : contains
    catalog_products ||--o{ order_items : prices
    meal_plans ||--o{ meal_subscriptions : schedules
    tenancies ||--o{ invoices : bills
    invoices ||--o{ invoice_items : itemizes
    invoices ||--o{ payments : reconciles
    users ||--o{ audit_logs : acts
```

`v_property_locations` reconstructs division → district → area → sub-area and the exact road, house, floor and flat. Admin location management can extend this hierarchy beyond the seeded Dhaka pilot areas.

`v_invoice_balances` first aggregates invoice lines and confirmed payments independently. This avoids double-counting when one invoice has multiple charge lines and multiple payments. `DECIMAL(12,2)` stores money; final totals are computed in SQL.

`v_cost_reconciliation` compares provider price against the responsible customer's price. Maintenance cannot be added to an invoice twice because `invoice_items.request_id` is unique. Orders likewise use a unique invoice reference.

Monthly invoices are unique per tenancy, user, month and billing group. Immediate installation/grocery/service orders receive an order-specific group; deferred groceries, meals and repairs join the monthly group. Separate owner and tenant payer records keep responsibility explicit.

Payment records have separate reviewer and confirmer foreign keys. The API locks payment and invoice records, rejects excess payments, and prevents the same user from performing both approval steps. Only confirmed payments affect outstanding balances.

Private document metadata links to its uploading user and optionally its property and tenancy. Random storage paths stay outside `dist`, and downloads require an authorized session. Actual property photographs may be served publicly only for published listings.

Instance data stays in `storage/mysql`. The normal app account has business-database DML permissions; root is used only for setup/migration and local phpMyAdmin administration. `schema.sql` and `queries.sql` are importable in phpMyAdmin and can be used with a separately hosted MySQL instance by updating the private config.
