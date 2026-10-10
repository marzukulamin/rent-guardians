USE rent_guardian;
-- Exact location and all listing features (normalized address hierarchy).
SELECT * FROM v_property_locations WHERE area='Dhanmondi' AND bedrooms=3 AND rent<=35000 AND status='published';
-- Rent + utilities + groceries + catering + maintenance, minus confirmed payments.
SELECT u.public_id,u.name,b.period,b.total,b.paid,b.balance FROM v_invoice_balances b JOIN users u ON u.id=b.user_id ORDER BY b.period DESC;
-- All tenants in an owner's properties.
SELECT p.id property_id,p.title,t.id tenancy_id,u.public_id,u.name,u.phone,tm.name member,tm.relationship FROM properties p JOIN tenancies t ON t.property_id=p.id JOIN users u ON u.id=t.head_tenant_id LEFT JOIN tenancy_members tm ON tm.tenancy_id=t.id WHERE p.owner_id=2;
-- Every job and its reconciled customer/provider price.
SELECT m.title,m.status,m.owner_decision,c.* FROM maintenance_requests m JOIN v_cost_reconciliation c ON c.request_id=m.id;
-- Which person reviewed and which separate person confirmed each payment.
SELECT p.id,p.amount,p.method,p.reference,p.status,r.name supervisor,f.name finance FROM payments p LEFT JOIN users r ON r.id=p.reviewed_by LEFT JOIN users f ON f.id=p.confirmed_by;
-- Room-specific damage and owner repair decisions.
SELECT p.title,i.kind,r.name room,it.component,it.condition_status,it.permanent_damage,it.estimate,it.repair_decision FROM inspection_items it JOIN inspection_rooms r ON r.id=it.room_id JOIN inspections i ON i.id=r.inspection_id JOIN properties p ON p.id=i.property_id;
-- Groceries including chicken cutting preferences, payment choice, and exact delivery address.
SELECT o.id,u.name,u.phone,c.name product,oi.quantity,oi.customization,o.pay_later,a.house_no,a.road_no,a.floor_no,a.flat_no FROM orders o JOIN order_items oi ON oi.order_id=o.id JOIN catalog_products c ON c.id=oi.product_id JOIN users u ON u.id=o.user_id JOIN addresses a ON a.id=o.address_id WHERE o.kind='grocery';
-- Catering delivery roster.
SELECT u.name,m.meal,s.people,s.starts_on,s.ends_on,s.pay_later,a.house_no,a.road_no,a.floor_no,a.flat_no FROM meal_subscriptions s JOIN meal_plans m ON m.id=s.meal_plan_id JOIN users u ON u.id=s.user_id JOIN tenancies t ON t.id=s.tenancy_id JOIN properties p ON p.id=t.property_id JOIN addresses a ON a.id=p.address_id;
-- Move-out inspection exactly three days before departure.
SELECT t.id,t.move_out_date,DATE_SUB(t.move_out_date,INTERVAL 3 DAY) inspect_on,p.title FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.status='notice';
-- All user-story requirements and the tables that store their data.
SELECT * FROM user_stories ORDER BY id;
-- Foreign-key graph.
SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL;
