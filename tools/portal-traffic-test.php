<?php
declare(strict_types=1);
require __DIR__.'/../server/portal.php';
function check(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
$result=portal_workspace([
 'invoices'=>[['id'=>1,'items'=>[['description'=>'QA_TEST plumbing']]],['id'=>2,'items'=>[['description'=>'Monthly rent']]]],
 'payments'=>[['id'=>3,'invoice_id'=>1],['id'=>4,'invoice_id'=>2]],
 'properties'=>[['id'=>1,'title'=>'DEMO: home'],['id'=>2,'title'=>'Garden apartment']],
 'notifications'=>[['id'=>1,'message'=>'QA_TEST created'],['id'=>2,'message'=>'Appointment confirmed']],
 'user'=>['id'=>9,'name'=>'Customer'],
]);
check(array_column($result['invoices'],'id')===[2],'Fixture invoice leaked.');
check(array_column($result['payments'],'id')===[4],'Linked fixture payment leaked.');
check(array_column($result['properties'],'id')===[2],'Fixture property leaked.');
check(count($result['notifications'])===1,'Fixture notification leaked.');
check($result['user']['id']===9,'Current user changed.');
require __DIR__.'/../server/bootstrap.php';
$identity='test-'.bin2hex(random_bytes(12));$scope='qa-budget';
try{
 traffic_limit($scope,$identity,2,60);
 traffic_limit($scope,$identity,2,60);
 try{traffic_limit($scope,$identity,2,60);throw new LogicException('Budget did not reject.');}catch(RuntimeException $e){check($e->getCode()===429,'Wrong rejection status.');}
 $other=new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",$config['user'],$config['password']);
 $query=$other->prepare('SELECT hits FROM request_budgets WHERE bucket_key=?');$query->execute([hash('sha256',$scope.'|'.$identity)]);
 check((int)$query->fetchColumn()===3,'Counter not visible across connections.');
}finally{run('DELETE FROM request_budgets WHERE bucket_key=?',[hash('sha256',$scope.'|'.$identity)]);}
echo "Portal isolation and shared traffic budgets passed.\n";
