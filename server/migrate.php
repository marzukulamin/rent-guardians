<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$c=require __DIR__.'/config.local.php';$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",getenv('RG_DB_ROOT_USER')?:'root',getenv('RG_DB_ROOT_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if(!$db->query("SHOW COLUMNS FROM invoices LIKE 'billing_group'")->fetch()){$db->exec("ALTER TABLE invoices ADD billing_group VARCHAR(80) NOT NULL DEFAULT 'monthly', DROP INDEX tenancy_id, ADD UNIQUE tenancy_period_group(tenancy_id,period,user_id,billing_group)");}
foreach(['appointments','maintenance_requests'] as $table)if(!$db->query("SHOW COLUMNS FROM $table LIKE 'otp_attempts'")->fetch())$db->exec("ALTER TABLE $table ADD otp_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0");
if(!$db->query("SHOW COLUMNS FROM maintenance_requests LIKE 'preferred_provider_id'")->fetch())$db->exec('ALTER TABLE maintenance_requests ADD preferred_provider_id BIGINT UNSIGNED NULL, ADD FOREIGN KEY(preferred_provider_id) REFERENCES provider_profiles(user_id)');
if(!$db->query("SHOW COLUMNS FROM property_photos LIKE 'category'")->fetch())$db->exec("ALTER TABLE property_photos ADD category VARCHAR(40) NOT NULL DEFAULT 'Other', ADD deleted_at DATETIME NULL, ADD INDEX photo_order(property_id,deleted_at,sort_order,id)");
$db->exec("CREATE TABLE IF NOT EXISTS listing_drafts (user_id BIGINT UNSIGNED PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id));");
$storyColumn=$db->query("SHOW COLUMNS FROM user_stories LIKE 'implementation_status'")->fetch();if(!str_contains($storyColumn['Type'],'PARTIALLY COMPLETED')){$db->exec("ALTER TABLE user_stories MODIFY implementation_status VARCHAR(32) NOT NULL DEFAULT 'PARTIALLY COMPLETED'");$db->exec("UPDATE user_stories SET implementation_status=CASE WHEN implementation_status='integration_required' THEN 'BLOCKED' ELSE 'PARTIALLY COMPLETED' END");$db->exec("ALTER TABLE user_stories MODIFY implementation_status ENUM('COMPLETED','PARTIALLY COMPLETED','BLOCKED','FAILED','WAITING FOR INPUT') NOT NULL DEFAULT 'PARTIALLY COMPLETED'");}
// Index DDL can lock large tables: run this CLI migration in a maintenance window.
$indexSql=file_get_contents(__DIR__.'/../database/scale-indexes.sql');
preg_match_all('/CREATE INDEX (\w+) ON (\w+)\(([^;]+)\);/',$indexSql,$indexes,PREG_SET_ORDER);
foreach($indexes as $index){$s=$db->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?');$s->execute([$index[2],$index[1]]);if(!$s->fetch())$db->exec($index[0]);}
$schema=file_get_contents(__DIR__.'/../database/schema.sql');preg_match_all('/CREATE OR REPLACE VIEW[^;]+;/s',$schema,$views);foreach($views[0] as $view)$db->exec($view);
$db->exec(file_get_contents(__DIR__.'/../database/traffic.sql'));
if(!$db->query("SHOW COLUMNS FROM users LIKE 'deleted_at'")->fetch())$db->exec('ALTER TABLE users ADD deleted_at DATETIME NULL');
echo "Schema migrations are up to date.\n";
