<?php
// A private load-balancer readiness route. No sessions and no connection details.
header('Content-Type: application/json');header('Cache-Control: no-store');
try{
 $c=require __DIR__.'/config.local.php';
 $pdo=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['user'],$c['password'],[PDO::ATTR_TIMEOUT=>2,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $pdo->query('SELECT 1');echo '{"status":"ready"}';
}catch(Throwable $e){http_response_code(503);echo '{"status":"unavailable"}';}
