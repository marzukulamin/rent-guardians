<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$c=require __DIR__.'/../server/config.local.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
function invoke_jobs():string {
 $process=proc_open([PHP_BINARY,__DIR__.'/../server/run-jobs.php'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
 if(!is_resource($process))throw new RuntimeException('Could not run jobs.');$out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($process)!==0)throw new RuntimeException($error);return $out;
}
$lock='rg:jobs:'.$c['database'];$s=$db->prepare('SELECT GET_LOCK(?,0)');$s->execute([$lock]);if(!(int)$s->fetchColumn())throw new RuntimeException('Another runner is active. Retry later.');
try{if(!str_contains(invoke_jobs(),'Another job runner is active; skipped.'))throw new RuntimeException('Runner did not respect database lock.');}finally{$s=$db->prepare('SELECT RELEASE_LOCK(?)');$s->execute([$lock]);}
invoke_jobs();$sql='SELECT (SELECT COUNT(*) FROM job_runs) jobs,(SELECT COUNT(*) FROM invoices) invoices,(SELECT COUNT(*) FROM invoice_items) items,(SELECT COUNT(*) FROM reward_ledger) rewards,(SELECT COUNT(*) FROM notifications) notifications';$before=$db->query($sql)->fetch(PDO::FETCH_ASSOC);invoke_jobs();$after=$db->query($sql)->fetch(PDO::FETCH_ASSOC);if($before!==$after)throw new RuntimeException('Repeated jobs changed record counts.');
echo "Scheduler overlap lock and repeated-run idempotence passed.\n";
