-- Non-destructive additions. server/migrate.php skips indexes that already exist.
CREATE INDEX invoice_payment_status ON payments(invoice_id,status);
CREATE INDEX invoice_user_history ON invoices(user_id,id);
CREATE INDEX invoice_status_history ON invoices(status,id);
CREATE INDEX notification_user_history ON notifications(user_id,id);
CREATE INDEX notification_unread ON notifications(user_id,read_at);
CREATE INDEX request_event_history ON request_events(request_id,id);
CREATE INDEX tenancy_member_access ON tenancy_members(user_id,tenancy_id);
CREATE INDEX appointment_property_access ON appointments(property_id,status,requested_by,assigned_to);
CREATE INDEX appointment_reminders ON appointments(status,starts_at);
CREATE INDEX property_newest ON properties(status,created_at,id);
CREATE INDEX property_owner_history ON properties(owner_id,id);
CREATE INDEX review_subject ON reviews(subject_id,id);
CREATE INDEX tenancy_jobs ON tenancies(status,id,start_date);
CREATE INDEX referral_jobs ON referrals(status,id);
CREATE INDEX auth_identity_window ON auth_attempts(identity_hash,attempted_at);
