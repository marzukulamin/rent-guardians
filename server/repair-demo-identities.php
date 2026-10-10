<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';require __DIR__.'/demo-identities.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
$marker='demo-identity-consistency-v3';$apply=in_array('--apply',$argv,true);
if(one('SELECT id FROM job_runs WHERE job_key=?',[$marker])){echo "Identity repair already applied.\n";exit;}
$changes=[];$members=rows('SELECT m.id,m.name,m.phone,u.name current_name,u.phone current_phone FROM tenancy_members m JOIN users u ON u.id=m.user_id WHERE NOT(m.name<=>u.name) OR NOT(m.phone<=>u.phone)');
foreach(rows("SELECT id,name,email FROM users WHERE email REGEXP '^demo\\.[a-z_]+\\.[0-9]{3}@rentguardian\\.local$'") as $u){
 preg_match('/^demo\.([a-z_]+)\.(\d{3})@/',$u['email'],$match);$role=$match[1];$n=(int)$match[2];
 // Preserve edited profiles: only replace the original generated name.
 if($n<1||$n>100||$u['name']!==demo_identity('tenant',$n))continue;
 $name=demo_identity($role,$n);if($name!==$u['name'])$changes[]=['id'=>$u['id'],'before'=>$u['name'],'after'=>$name,'role'=>$role,'number'=>$n];
}
$signatures=[];foreach(rows("SELECT s.contract_id,s.signer_role,s.signed_name,u.name current_name,u.email FROM contract_signatures s JOIN users u ON u.id=s.user_id WHERE s.ip_address='local-demo' AND s.signed_name<>u.name") as $s){if(preg_match('/^demo\.([a-z_]+)\.(\d{3})@rentguardian\.local$/',$s['email'],$m)&&$s['signed_name']===demo_identity('tenant',(int)$m[2]))$signatures[]=$s;}
$report=['users'=>count($changes),'linked_members'=>count($members),'generated_signatures'=>count($signatures),'applied'=>$apply];
if($apply){
 $backup=__DIR__.'/../storage/data-audit/identity-backup-'.date('Ymd-His').'.json';
 if(file_put_contents($backup,json_encode(['users'=>$changes,'members'=>$members,'generated_signatures'=>$signatures],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),LOCK_EX)===false)throw new RuntimeException('Backup failed.');
 $db->beginTransaction();try{
  foreach($changes as $c){run('UPDATE users SET name=? WHERE id=? AND name=?',[$c['after'],$c['id'],$c['before']]);
   run('UPDATE bank_accounts SET account_name=? WHERE user_id=? AND account_name=?',[$c['after'],$c['id'],$c['before']]);
   // Outreach records have no owner FK; repair only untouched generated sample rows.
   if($c['role']==='owner')run("UPDATE campaign_checklists c JOIN users f ON f.id=c.user_id JOIN users o ON o.id=? SET c.owner_name=?,c.owner_phone=o.phone WHERE f.email=? AND c.owner_name=? AND c.checklist='Introduced the service, explained inspections and arranged an owner follow-up. Sample outreach activity.'",[$c['id'],$c['after'],sprintf('demo.field_agent.%03d@rentguardian.local',$c['number']),$c['before']]);
  }
  run('UPDATE tenancy_members m JOIN users u ON u.id=m.user_id SET m.name=u.name,m.phone=u.phone WHERE NOT(m.name<=>u.name) OR NOT(m.phone<=>u.phone)');
  // Only machine-generated fictional signatures; never change real signed consent.
  foreach($signatures as $s)run("UPDATE contract_signatures SET signed_name=? WHERE contract_id=? AND signer_role=? AND signed_name=? AND ip_address='local-demo'",[$s['current_name'],$s['contract_id'],$s['signer_role'],$s['signed_name']]);
  // On a fresh v3 migration, names may have changed in this transaction.
  foreach($changes as $c)run("UPDATE contract_signatures SET signed_name=? WHERE user_id=? AND signed_name=? AND ip_address='local-demo'",[$c['after'],$c['id'],$c['before']]);
  insert('job_runs',['job_key'=>$marker]);$db->commit();
 }catch(Throwable $e){$db->rollBack();throw $e;}
 // Real signed names remain immutable historical consent.
}
echo json_encode($report,JSON_PRETTY_PRINT)."\n";
