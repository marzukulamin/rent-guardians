<?php
// Atomic fixed-window budgets shared by all application workers using this DB.
// Edge limits should absorb floods before they reach PHP/MySQL.
function traffic_limit(string $scope,string $identity,int $limit,int $seconds):void {
 global $db;
 if($db->inTransaction())throw new LogicException('Apply traffic budgets before business transactions.');
 $bucket=intdiv(time(),$seconds)*$seconds;
 $key=hash('sha256',$scope.'|'.$identity);
 run('INSERT INTO request_budgets (bucket_key,window_start,hits,expires_at) VALUES (?,?,1,?) ON DUPLICATE KEY UPDATE hits=hits+1',[$key,$bucket,$bucket+$seconds*2]);
 $hits=(int)one('SELECT hits FROM request_budgets WHERE bucket_key=? AND window_start=?',[$key,$bucket])['hits'];
 if($hits>$limit){
  header('Retry-After: '.max(1,$bucket+$seconds-time()));
  fail('Too many requests. Please wait before trying again.',429);
 }
}
