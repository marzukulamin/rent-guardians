<?php
// Staff workflows share persisted operational records, not front-end demo fixtures.
$u=auth();$uid=(int)$u['id'];
if($path==='finance/report'&&$method==='GET'){
 need(['finance','admin']);$key=(string)($_GET['key']??'monthly_dues');$queries=require __DIR__.'/queries.php';
 $queries['commissions']=['title'=>'Recorded maintenance margins','sql'=>"SELECT m.id request_id,m.title,m.provider_cost,m.tenant_cost,m.owner_cost,m.cost_confirmed_at,COALESCE((SELECT SUM(ii.amount) FROM invoice_items ii WHERE ii.request_id=m.id AND ii.kind='maintenance'),0) billed_amount,COALESCE((SELECT SUM(ii.amount) FROM invoice_items ii WHERE ii.request_id=m.id AND ii.kind='maintenance'),0)-m.provider_cost recorded_margin FROM maintenance_requests m WHERE m.cost_confirmed_at IS NOT NULL ORDER BY m.id DESC"];
 if(!in_array($key,['monthly_dues','rent_roll','payment_trail','cost_reconciliation','owner_income','commissions'],true))fail('Choose an available financial report.',403);
 $page=max(1,min(100000,(int)($_GET['page']??1)));$offset=($page-1)*50;$sql=$queries[$key]['sql'];$total=(int)one('SELECT COUNT(*) n FROM ('.$sql.') report_rows')['n'];
 respond(['title'=>$queries[$key]['title'],'rows'=>rows($sql.' LIMIT 50 OFFSET '.$offset),'total'=>$total,'page'=>$page,'has_more'=>$offset+50<$total]);
}
elseif($path==='operations/note'&&$method==='POST'){
 need(['agent','supervisor','admin']);$kind=choice($b,'kind',['maintenance','order','subscription','tenancy','complaint']);$id=(int)number($b,'id',1);$message=textfield($b,'message',4000);
 $tables=['maintenance'=>'maintenance_requests','order'=>'orders','subscription'=>'meal_subscriptions','tenancy'=>'tenancies','complaint'=>'complaints'];$row=one('SELECT * FROM '.$tables[$kind].' WHERE id=?',[$id]);if(!$row)fail('Record not found.',404);$tid=$kind==='tenancy'?$id:(int)$row['tenancy_id'];$t=tenancy_access($tid,$u);
 if(!has($u,['admin','supervisor'])&&!one('SELECT id FROM appointments WHERE property_id=? AND assigned_to=?',[$t['property_id'],$uid]))fail('Use a property assigned to you.',403);
 if($kind==='maintenance')insert('request_events',['request_id'=>$id,'actor_id'=>$uid,'status'=>$row['status'],'message'=>$message]);
 else insert('support_tickets',['user_id'=>$uid,'property_id'=>$t['property_id'],'category'=>'inspection','subject'=>ucfirst($kind).' #'.$id.' handoff','message'=>$message,'contact_channel'=>'app']);
 audit($u,'coordination_note',$tables[$kind],$id,['message'=>$message]);$result=['ok'=>true];
}
elseif($path==='operations/notify'&&$method==='POST'){
 need(['supervisor','admin']);$target=(int)number($b,'user_id',1);if(!one("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE u.id=? AND u.status='active' AND u.deleted_at IS NULL AND r.name IN('agent','provider')",[$target]))fail('Choose an active agent or provider.');$message=textfield($b,'message',2000);notify($target,'Supervisor follow-up',$message,'support');audit($u,'team_follow_up','users',$target,['message'=>$message]);$result=['ok'=>true,'message'=>'Follow-up delivered to in-app notifications.'];
}
elseif($path==='operations/support'&&$method==='POST'){
 need(['agent','supervisor','admin']);$id=(int)number($b,'id',1);$t=one('SELECT * FROM support_tickets WHERE id=? FOR UPDATE',[$id]);if(!$t)fail('Request not found.',404);$super=has($u,['supervisor','admin']);if(!$super&&(int)$t['assigned_to']!==$uid)fail('This request is not assigned to you.',403);if(in_array($t['status'],['resolved','closed'],true))fail('This request is already closed.');
 $action=choice($b,'action',['assign','take','resolve','escalate']);
 if($action==='assign'){if(!$super)fail('Only supervisors assign support.',403);$target=(int)number($b,'assigned_to',1);if(!one("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE u.id=? AND u.status='active' AND u.deleted_at IS NULL AND r.name IN('agent','supervisor')",[$target]))fail('Choose an active support agent or supervisor.');run('UPDATE support_tickets SET assigned_to=? WHERE id=?',[$target,$id]);notify($target,'Support request assigned',$t['subject'],'support');}
 else{$message=textfield($b,'message',4000);$status=match($action){'take'=>'in_progress','resolve'=>'resolved','escalate'=>'open'};$assigned=$action==='escalate'?null:($t['assigned_to']?:$uid);run('UPDATE support_tickets SET status=?,assigned_to=?,message=CONCAT(message,?) WHERE id=?',[$status,$assigned,"\n\n".$u['name'].' · '.date('Y-m-d H:i').' · '.$action.': '.$message,$id]);if($action==='escalate')foreach(rows("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE r.name='supervisor' AND u.status='active' AND u.deleted_at IS NULL") as $person)notify((int)$person['id'],'Support escalation',$t['subject'],'support');else notify((int)$t['user_id'],'Support request updated',$t['subject'].' · '.$status,'support');}
 audit($u,'support_'.$action,'support_tickets',$id,$b);$result=['ok'=>true];
}
else fail('Endpoint not found.',404);
