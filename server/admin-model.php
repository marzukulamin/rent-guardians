<?php
function admin_qi(string $s):string{return '`'.str_replace('`','``',$s).'`';}
function admin_resources():array{
 $groups=[
 'People'=>['users','user_roles','bank_accounts','notification_preferences','referrals','reward_ledger'],
 'Homes & residents'=>['properties','addresses','locations','listing_drafts','property_photos','property_utilities','utility_types','saved_properties','property_visits','tenancies','tenancy_members','tenancy_transfers','contracts','contract_signatures','complaints'],
 'Work & services'=>['appointments','maintenance_requests','request_events','provider_profiles','provider_categories','service_categories','catalog_products','orders','order_items','meal_plans','meal_subscriptions','reviews','support_tickets'],
 'Documents & inspections'=>['documents','inspections','inspection_rooms','inspection_items'],
 'Money'=>['invoices','invoice_items','payments'],
 'Outreach'=>['campaigns','campaign_assignments','campaign_checklists','training_materials'],
 'System'=>['notifications','roles','system_settings','audit_logs','auth_attempts','request_budgets','job_runs','user_stories']];
 $readonly=['audit_logs','auth_attempts','request_budgets','job_runs','contract_signatures','request_events','reward_ledger','user_roles','roles'];
 $create=['addresses','bank_accounts','locations','property_utilities','utility_types','tenancy_members','provider_categories','service_categories','catalog_products','meal_plans','support_tickets','inspection_rooms','inspection_items','referrals','notification_preferences','notifications','campaigns','campaign_assignments','campaign_checklists','training_materials'];
 $secret=['password_hash','otp_hash','identity_hash','storage_path'];
 $protected=['public_id','created_at','updated_at','signed_at','inspected_at','confirmed_at','verified_at','approved_by','reviewed_by','confirmed_by','otp_attempts','otp_expires_at','otp_verified_at','cost_confirmed_at','content_hash','version','deleted_at','advance_paid'];
 $workflow=['properties','tenancies','appointments','maintenance_requests','orders','payments','invoices','documents','inspections','tenancy_transfers'];
 $fks=[];foreach(rows('SELECT TABLE_NAME t,COLUMN_NAME c,REFERENCED_TABLE_NAME p,REFERENCED_COLUMN_NAME r FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL') as $fk)$fks[$fk['t']][$fk['c']]=['table'=>$fk['p'],'column'=>$fk['r']];
 $columns=rows('SELECT TABLE_NAME t,COLUMN_NAME name,DATA_TYPE type,COLUMN_TYPE definition,IS_NULLABLE nullable,COLUMN_DEFAULT default_value,COLUMN_KEY key_type,EXTRA extra,CHARACTER_MAXIMUM_LENGTH max_length FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION');$by=[];foreach($columns as $c)$by[$c['t']][]=$c;
 $result=[];foreach(rows("SELECT TABLE_NAME name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME") as $t){$name=$t['name'];$group='System';foreach($groups as $label=>$tables)if(in_array($name,$tables,true))$group=$label;
  $r=['table'=>$name,'label'=>ucwords(str_replace('_',' ',$name)),'group'=>$group,'readonly'=>in_array($name,$readonly,true),'create'=>in_array($name,$create,true),'primary'=>[],'fields'=>[]];
  foreach($by[$name]??[] as $c){if($c['key_type']==='PRI')$r['primary'][]=$c['name'];if(in_array($c['name'],$secret,true))continue;$c['nullable']=$c['nullable']==='YES';$c['label']=ucwords(str_replace('_',' ',$c['name']));$c['foreign']=$fks[$name][$c['name']]??null;$c['editable']=!$r['readonly']&&!in_array($c['name'],$protected,true)&&$c['key_type']!=='PRI'&&!(in_array($name,$workflow,true)&&$c['name']==='status');
   if($name==='users')$c['editable']=in_array($c['name'],['name','email','phone'],true);
   if($name==='documents')$c['editable']=in_array($c['name'],['original_name','user_id','tenancy_id','property_id','kind'],true);
   if(in_array($name,['orders','order_items','meal_subscriptions','invoices','invoice_items','payments'],true)&&in_array($c['name'],['user_id','tenancy_id','provider_id','product_id','order_id','request_id','subscription_id','invoice_id','address_id','meal_plan_id','unit_price','installation_cost','quantity'],true))$c['editable']=false;
   if($name==='tenancies'&&in_array($c['name'],['property_id','head_tenant_id','end_date','move_out_date'],true))$c['editable']=false;
   if($name==='properties'&&in_array($c['name'],['owner_id','address_id'],true))$c['editable']=false;
   if($name==='maintenance_requests'&&in_array($c['name'],['tenancy_id','requested_by','provider_id','owner_decision','owner_involved','tenant_cost','owner_cost','provider_cost'],true))$c['editable']=false;
   if($name==='tenancy_transfers')$c['editable']=false;
   if($name==='appointments'&&in_array($c['name'],['assigned_to','requested_by','property_id'],true))$c['editable']=false;
   if($name==='inspections'&&in_array($c['name'],['property_id','appointment_id','agent_id'],true))$c['editable']=false;
   if($name==='contracts'&&$c['name']==='tenancy_id')$c['editable']=false;
   if($name==='property_visits'&&in_array($c['name'],['user_id','property_id','appointment_id'],true))$c['editable']=false;
   if($name==='reviews'&&in_array($c['name'],['author_id','subject_id','tenancy_id','order_id','request_id'],true))$c['editable']=false;
   if($name==='property_photos'&&in_array($c['name'],['path','sort_order','property_id'],true))$c['editable']=false;
   if($c['type']==='enum'){preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/",$c['definition'],$m);$c['options']=array_map('stripslashes',$m[1]);}
   $r['fields'][]=$c;
  }$result[$name]=$r;
 }return $result;
}
function admin_resource(string $table):array{$all=admin_resources();if(!isset($all[$table]))fail('Choose an available record type.');return $all[$table];}
function admin_redact(array $row):array{foreach(['password_hash','otp_hash','identity_hash','storage_path'] as $key)unset($row[$key]);return $row;}
function admin_revision(array $row):string{return hash('sha256',json_encode($row,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));}
function admin_key(array $resource,array $key):array{$values=[];foreach($resource['primary'] as $col){if(!isset($key[$col])||!is_scalar($key[$col]))fail('A complete record reference is required.');$values[]=$key[$col];}return [implode(' AND ',array_map(fn($c)=>admin_qi($c).'=?',$resource['primary'])),$values];}
function admin_account_scope(string $table,int $uid):string{
 $uid=(int)$uid;$props="SELECT property_id FROM tenancies WHERE head_tenant_id=$uid OR id IN(SELECT tenancy_id FROM tenancy_members WHERE user_id=$uid) UNION SELECT id FROM properties WHERE owner_id=$uid UNION SELECT property_id FROM appointments WHERE assigned_to=$uid OR requested_by=$uid";
 $ten="SELECT t.id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.head_tenant_id=$uid OR p.owner_id=$uid OR EXISTS(SELECT 1 FROM tenancy_members m WHERE m.tenancy_id=t.id AND m.user_id=$uid)";
 $inv="SELECT id FROM invoices WHERE user_id=$uid OR tenancy_id IN($ten)";
 $req="SELECT id FROM maintenance_requests WHERE requested_by=$uid OR provider_id=$uid OR preferred_provider_id=$uid OR tenancy_id IN($ten)";
 $orders="SELECT id FROM orders WHERE user_id=$uid OR provider_id=$uid OR tenancy_id IN($ten)";
 $insp="SELECT id FROM inspections WHERE agent_id=$uid OR property_id IN($props)";
 return match($table){
 'users'=>"id=$uid",'properties'=>"id IN($props)",'addresses'=>"id IN(SELECT address_id FROM properties WHERE id IN($props))",'tenancies'=>"id IN($ten)",'tenancy_members','contracts','complaints'=>"tenancy_id IN($ten)",'contract_signatures'=>"user_id=$uid OR contract_id IN(SELECT id FROM contracts WHERE tenancy_id IN($ten))",'tenancy_transfers'=>"from_user_id=$uid OR to_user_id=$uid OR tenancy_id IN($ten)",
 'appointments'=>"requested_by=$uid OR assigned_to=$uid OR property_id IN(SELECT id FROM properties WHERE owner_id=$uid)",'maintenance_requests'=>"id IN($req)",'request_events'=>"request_id IN($req)",'orders'=>"id IN($orders)",'order_items'=>"order_id IN($orders)",'meal_subscriptions'=>"user_id=$uid OR tenancy_id IN($ten) OR meal_plan_id IN(SELECT id FROM meal_plans WHERE provider_id=$uid)",
 'invoices'=>"id IN($inv)",'invoice_items'=>"invoice_id IN($inv)",'payments'=>"invoice_id IN($inv) OR reviewed_by=$uid OR confirmed_by=$uid",'documents'=>"user_id=$uid OR tenancy_id IN($ten) OR property_id IN($props)",'inspections'=>"id IN($insp)",'inspection_rooms'=>"inspection_id IN($insp)",'inspection_items'=>"room_id IN(SELECT id FROM inspection_rooms WHERE inspection_id IN($insp))",
 'property_photos','property_utilities'=>"property_id IN($props)",'catalog_products','meal_plans','provider_categories'=>"provider_id=$uid",'provider_profiles','bank_accounts','saved_properties','property_visits','notification_preferences','notifications','listing_drafts','user_roles','reward_ledger'=>"user_id=$uid",'referrals'=>"referrer_id=$uid OR referred_user_id=$uid",'reviews'=>"author_id=$uid OR subject_id=$uid OR tenancy_id IN($ten)",'support_tickets'=>"user_id=$uid OR assigned_to=$uid",'campaigns'=>"id IN(SELECT campaign_id FROM campaign_assignments WHERE user_id=$uid)",'campaign_assignments','campaign_checklists'=>"user_id=$uid",'audit_logs'=>"actor_id=$uid OR (entity='users' AND entity_id=$uid)",'system_settings'=>"updated_by=$uid",'training_materials'=>"audience IN(SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=$uid)",'locations','roles','service_categories','utility_types','user_stories'=>'1=1',default=>'0=1'};
}
function admin_record_locked(string $table,array $row):?string{
 if($table==='contracts'&&one('SELECT contract_id FROM contract_signatures WHERE contract_id=? LIMIT 1',[$row['id']]))return 'Signed agreements are preserved. Use tenancy management for changes to the resident relationship.';
 if($table==='payments'&&$row['status']==='confirmed')return 'Confirmed payment history is preserved. Use an audited invoice adjustment.';
 if($table==='invoices'&&$row['status']!=='open')return 'This invoice is closed. Its financial history is preserved.';
 if($table==='invoice_items'&&(one("SELECT id FROM payments WHERE invoice_id=? AND status IN('reviewed','confirmed') LIMIT 1",[$row['invoice_id']])||$row['order_id']||$row['request_id']||$row['subscription_id']))return 'This charge belongs to a saved service or reviewed payment. Use an invoice adjustment.';
 if($table==='orders'&&in_array($row['status'],['completed','cancelled']))return 'Completed or cancelled order history is preserved.';
 return null;
}
function admin_validate(string $table,array &$r,?array $old):void{
 $role=function($id,$role){if(!one("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles ro ON ro.id=ur.role_id WHERE u.id=? AND ro.name=? AND u.status='active'",[$id,$role]))fail('Choose an active '.$role.' account.');};
 if($table==='users'){if(!filter_var($r['email'],FILTER_VALIDATE_EMAIL))fail('Enter a valid email address.');$r['email']=strtolower($r['email']);if(!trim($r['name']))fail('Enter the account name.');}
 if($table==='tenancy_members'&&$old){$oldTenancy=one('SELECT head_tenant_id FROM tenancies WHERE id=?',[$old['tenancy_id']]);if($old['user_id']==$oldTenancy['head_tenant_id']&&($r['user_id']!=$old['user_id']||$r['tenancy_id']!=$old['tenancy_id']))fail('Transfer or end the head tenancy before changing its household account.');}
 if($table==='tenancy_members'){$t=one('SELECT t.*,p.owner_id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.id=?',[$r['tenancy_id']]);if(!$t)fail('Choose a tenancy.');if(!empty($r['user_id'])){$u=one('SELECT id,name,phone FROM users WHERE id=?',[$r['user_id']]);if($u['id']==$t['owner_id'])fail('The property owner cannot be a household member.');if(one('SELECT id FROM tenancy_members WHERE tenancy_id=? AND user_id=? AND id<>?',[$t['id'],$u['id'],$old['id']??0]))fail('This account is already a household member.');$r['name']=$u['name'];$r['phone']=$u['phone'];}$head=($r['user_id']??null)==$t['head_tenant_id'];if(!$head&&strcasecmp($r['relationship']??'','Head tenant')===0)fail('Use the tenancy transfer workflow for the head tenant.');if($head)$r['relationship']='Head tenant';}
 if(in_array($table,['catalog_products','meal_plans','provider_categories'],true))$role($r['provider_id'],'provider');
 if($table==='catalog_products'&&$old){$used=one('SELECT id FROM order_items WHERE product_id=? LIMIT 1',[$old['id']]);if($used&&$r['provider_id']!=$old['provider_id'])fail('A product used in orders cannot change its provider.');if($used&&$r['category_id']!=$old['category_id']){$from=one('SELECT kind FROM service_categories WHERE id=?',[$old['category_id']]);$to=one('SELECT kind FROM service_categories WHERE id=?',[$r['category_id']]);if($from['kind']!==$to['kind'])fail('A product used in orders cannot change its service type.');}}
 if($table==='meal_plans'&&$old&&$r['provider_id']!=$old['provider_id']&&one('SELECT id FROM meal_subscriptions WHERE meal_plan_id=? LIMIT 1',[$old['id']]))fail('A subscribed meal plan cannot change its provider.');
 if($table==='provider_profiles'){$role($r['user_id'],'provider');if(!one("SELECT id FROM locations WHERE id=? AND kind='sub_area'",[$r['location_id']]))fail('Choose an operating neighbourhood.');}
 if($table==='documents'&&!empty($r['tenancy_id'])){$t=one('SELECT property_id FROM tenancies WHERE id=?',[$r['tenancy_id']]);if(!$t||(!empty($r['property_id'])&&$r['property_id']!=$t['property_id']))fail('The document property must match its tenancy.');$r['property_id']=$t['property_id'];}
 if($table==='documents'&&$old&&$old['kind']==='property_photo'&&($r['user_id']!=$old['user_id']||$r['property_id']!=$old['property_id']||$r['tenancy_id']!=$old['tenancy_id']))fail('Manage a photo’s property and owner through the property gallery.');
 if($table==='appointments'&&$old&&$old['status']!=='requested'&&($r['starts_at']!==$old['starts_at']||$r['kind']!==$old['kind']))fail('Cancel this appointment and book its replacement to keep entry verification and visit history consistent.');
 if($table==='documents'&&$old&&$r['kind']!==$old['kind']){if(in_array('property_photo',[$r['kind'],$old['kind']],true))fail('Manage property photos through the property gallery.');}
 if($table==='property_photos'&&$old&&!in_array($r['category'],['Living room','Bedroom','Kitchen','Washroom','Balcony','Building exterior','Parking','Other']))fail('Choose a photo category.');
 if($table==='tenancies'&&$old&&in_array($old['status'],['active','notice','ended'])&&($r['rent']!=$old['rent']||$r['utility_charge']!=$old['utility_charge']||$r['start_date']!==$old['start_date']))fail('Occupied tenancy terms are preserved with the signed agreement.');
 if($table==='properties'&&$old&&$old['status']==='rented')fail('Occupied listing terms are preserved. End the tenancy before changing the listing.');
 if($table==='payments'){$i=one('SELECT * FROM v_invoice_balances WHERE id=?',[$r['invoice_id']]);if(!$i||$r['user_id']!=$i['user_id'])fail('Payment must belong to the invoice account.');if($r['amount']>$i['balance'])fail('Payment exceeds the remaining invoice balance.');if($old&&$old['status']!=='submitted')fail('Only a submitted payment can be corrected before review.');}
 if($table==='invoice_items'){if($old&&$r['invoice_id']!=$old['invoice_id'])fail('A charge cannot move between invoices.');if(!one("SELECT id FROM invoices WHERE id=? AND status='open'",[$r['invoice_id']]))fail('Only open invoice charges can be corrected.');}
 if($table==='meal_subscriptions'&&$old&&($r['people']!=$old['people']||$r['starts_on']!==$old['starts_on']||$r['ends_on']!==$old['ends_on'])&&one('SELECT id FROM invoice_items WHERE subscription_id=? LIMIT 1',[$old['id']]))fail('A billed subscription schedule is preserved. Cancel it and create a corrected subscription, then use an audited invoice adjustment.');
 if($table==='meal_subscriptions'&&($r['ends_on']<$r['starts_on']||(strtotime($r['ends_on'])-strtotime($r['starts_on']))/86400>30))fail('Subscription dates must cover between 1 and 31 days.');
 if($table==='locations'){$parent=$r['parent_id']?one('SELECT kind FROM locations WHERE id=?',[$r['parent_id']]):null;$expected=['division'=>null,'district'=>'division','area'=>'district','sub_area'=>'area'][$r['kind']];if(($parent['kind']??null)!==$expected)fail('Choose the correct parent location level.');if($old&&($old['kind']!==$r['kind']||$old['parent_id']!=$r['parent_id'])&&one('SELECT id FROM locations WHERE parent_id=? LIMIT 1',[$old['id']]))fail('A location with child areas cannot change its hierarchy.');}
 if($table==='locations'&&$old&&$old['kind']!==$r['kind']&&(one('SELECT id FROM addresses WHERE location_id=? LIMIT 1',[$old['id']])||one('SELECT user_id FROM provider_profiles WHERE location_id=? LIMIT 1',[$old['id']])||one('SELECT user_id FROM notification_preferences WHERE location_id=? LIMIT 1',[$old['id']])))fail('A location used by accounts or properties must keep its location level.');
 if($table==='addresses'&&!one("SELECT id FROM locations WHERE id=? AND kind='sub_area'",[$r['location_id']]))fail('An address needs a valid neighbourhood.');
 if($table==='campaign_assignments')$role($r['user_id'],'field_agent');
 if($table==='inspection_rooms'&&$old&&$r['inspection_id']!=$old['inspection_id'])fail('A room cannot move between inspection reports.');
 if($table==='inspection_items'&&$old&&$r['room_id']!=$old['room_id'])fail('A fixture cannot move between rooms.');
 if($table==='campaign_checklists'&&!one('SELECT campaign_id FROM campaign_assignments WHERE campaign_id=? AND user_id=?',[$r['campaign_id'],$r['user_id']]))fail('Assign the field agent to this campaign first.');
 if($table==='campaigns'&&$r['ends_on']<$r['starts_on'])fail('The campaign must end after it starts.');
 if($table==='contracts'){$r['content_hash']=hash('sha256',$r['body']);if($old)$r['version']=$old['version']+1;}
 if($table==='referrals'&&!empty($r['referred_user_id'])){$u=one('SELECT email FROM users WHERE id=?',[$r['referred_user_id']]);if($u['email']!==$r['referred_email'])fail('The referral email must match its registered account.');}
 if($table==='service_categories'&&$old&&$r['kind']!==$old['kind']&&one('SELECT id FROM catalog_products WHERE category_id=? LIMIT 1',[$old['id']]))fail('A category used by products cannot change its service type.');
 foreach(['rent','price_per_person','people','quantity','amount'] as $key)if(isset($r[$key])&&(float)$r[$key]<=0&&!($table==='invoice_items'&&$key==='amount'))fail(ucwords(str_replace('_',' ',$key)).' must be positive.');
 foreach(['price','installation_cost','recurring_cost','utility_charge','estimate','size_sqft','radius_km'] as $key)if(isset($r[$key])&&(float)$r[$key]<0)fail(ucwords(str_replace('_',' ',$key)).' cannot be negative.');
 foreach(['latitude'=>[-90,90],'longitude'=>[-180,180]] as $key=>$limits)if(isset($r[$key])&&($r[$key]<$limits[0]||$r[$key]>$limits[1]))fail('Enter valid '.$key.'.');
}
