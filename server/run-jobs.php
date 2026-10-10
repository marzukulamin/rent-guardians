<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
$lock='rg:jobs:'.$config['database'];
if(!(int)one('SELECT GET_LOCK(?,0) acquired',[$lock])['acquired']){echo "Another job runner is active; skipped.\n";exit;}
function once(string $key,callable $fn):void {
 global $db;$db->beginTransaction();
 try{
  // Unique marker and side effects commit together; failures can retry.
  try{insert('job_runs',['job_key'=>$key]);}catch(PDOException $e){if(($e->errorInfo[1]??null)===1062){$db->rollBack();return;}throw $e;}
  $fn();$db->commit();
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function job_pages(string $sql,string $idColumn):Generator {
 $last=0;
 do{$batch=rows($sql." AND $idColumn>? ORDER BY $idColumn LIMIT 250",[$last]);foreach($batch as $record){$last=(int)$record['id'];yield $record;}}while(count($batch)===250);
}
try{
$month=date('Y-m-01');
foreach(job_pages("SELECT t.*,p.owner_id,p.title,n.monthly_checkin FROM tenancies t JOIN properties p ON p.id=t.property_id LEFT JOIN notification_preferences n ON n.user_id=t.head_tenant_id WHERE t.status IN('active','notice') AND t.start_date<=CURDATE()",'t.id') as $t){
 once('rent:'.$t['id'].':'.$month,function()use($t,$month){$invoice=invoice_for((int)$t['id'],(int)$t['head_tenant_id'],$month);if(!one("SELECT id FROM invoice_items WHERE invoice_id=? AND kind='rent'",[$invoice])){insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'rent','description'=>'Monthly rent','amount'=>$t['rent']]);insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'utility','description'=>'Monthly utilities','amount'=>$t['utility_charge']]);}notify((int)$t['head_tenant_id'],'Your monthly bill is ready','Rent and utilities for '.$t['title'].' are ready to review.','payments');});
 if($t['monthly_checkin'])once('checkin:'.$t['id'].':'.$month,fn()=>notify((int)$t['head_tenant_id'],'How is everything at home?','Anything need a little care? Your guardian is here if you need us.','maintenance'));
}
foreach(job_pages("SELECT a.* FROM appointments a JOIN notification_preferences n ON n.user_id=a.requested_by WHERE a.status='confirmed' AND n.appointment_reminders=1 AND a.starts_at BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 24 HOUR)",'a.id') as $a)once('reminder:'.$a['id'].':'.date('Y-m-d',strtotime($a['starts_at'])),function()use($a){notify((int)$a['requested_by'],'Appointment reminder','Your '.$a['kind'].' appointment is at '.$a['starts_at'].'.','appointments');if($a['assigned_to'])notify((int)$a['assigned_to'],'Upcoming appointment','Your visit is at '.$a['starts_at'].'.','appointments');});
foreach(job_pages("SELECT r.* FROM referrals r JOIN users u ON u.id=r.referred_user_id WHERE r.status='registered' AND u.verified_at IS NOT NULL AND EXISTS(SELECT 1 FROM tenancies t WHERE t.head_tenant_id=r.referred_user_id AND t.status='active')",'r.id') as $r)once('referral:'.$r['id'],function()use($r){$points=(int)(one("SELECT setting_value FROM system_settings WHERE setting_key='referral_reward_points'")['setting_value']??200);insert('reward_ledger',['user_id'=>$r['referrer_id'],'points'=>$points,'reason'=>'Verified friend referral #'.$r['id']]);run("UPDATE referrals SET status='rewarded' WHERE id=?",[$r['id']]);notify((int)$r['referrer_id'],'Your referral earned a reward',"$points Guardian points have been added to your account.",'account');});
echo "Monthly bills, check-ins, appointment reminders and referral rewards are up to date.\n";

}finally{one('SELECT RELEASE_LOCK(?) released',[$lock]);}
