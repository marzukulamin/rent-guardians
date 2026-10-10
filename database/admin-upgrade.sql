-- Idempotence is managed by server/migrate-admin.php.
ALTER TABLE users ADD deleted_at DATETIME NULL;
CREATE INDEX admin_accounts_access ON users(deleted_at,status,id);
