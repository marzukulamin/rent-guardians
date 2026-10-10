CREATE TABLE IF NOT EXISTS request_budgets (
 bucket_key CHAR(64) CHARACTER SET ascii NOT NULL,
 window_start BIGINT UNSIGNED NOT NULL,
 hits INT UNSIGNED NOT NULL DEFAULT 1,
 expires_at BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY(bucket_key,window_start),
 INDEX budget_expiry(expires_at)
) ENGINE=InnoDB;
