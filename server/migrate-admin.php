<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$c=require __DIR__.'/config.local.php';
if(($c['environment']??'')!=='local')throw new RuntimeException('Run against the intended local database.');
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",getenv('RG_DB_ROOT_USER')?:'root',getenv('RG_DB_ROOT_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if(!$db->query("SHOW COLUMNS FROM users LIKE 'deleted_at'")->fetch())$db->exec('ALTER TABLE users ADD deleted_at DATETIME NULL');
if(!$db->query("SHOW INDEX FROM users WHERE Key_name='admin_accounts_access'")->fetch())$db->exec('CREATE INDEX admin_accounts_access ON users(deleted_at,status,id)');
echo "Administrator account archive migration complete.\n";
