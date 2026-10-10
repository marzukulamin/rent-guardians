<?php
/** Additive, repeatable local demo catalog. Never resets existing accounts. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
$db->beginTransaction();
try{
 $accounts=['owner'=>'Farhana Rahman','tenant'=>'Ariyan Ahmed','provider'=>'Dhaka Home Care','agent'=>'Tanvir Hasan','supervisor'=>'Nadia Islam','finance'=>'Sadia Khan','field_agent'=>'Imran Hossain','admin'=>'Demo Administrator'];
 $uids=[];
 foreach($accounts as $role=>$name){
  $email=$role==='admin'?'demo-admin@rentguardian.local':$role.'@rentguardian.local';
  $u=one('SELECT id FROM users WHERE email=?',[$email]);
  $uid=$u?(int)$u['id']:insert('users',['public_id'=>'RG-DEMO-'.strtoupper($role),'name'=>$name,'email'=>$email,'password_hash'=>password_hash('Guardian@2026!',PASSWORD_DEFAULT),'verified_at'=>date('Y-m-d H:i:s')]);
  $rid=one('SELECT id FROM roles WHERE name=?',[$role]);
  if(!$rid)throw new RuntimeException('Missing role: '.$role);
  if(!one('SELECT user_id FROM user_roles WHERE user_id=? AND role_id=?',[$uid,$rid['id']]))insert('user_roles',['user_id'=>$uid,'role_id'=>$rid['id']]);
  if(!one('SELECT user_id FROM notification_preferences WHERE user_id=?',[$uid]))insert('notification_preferences',['user_id'=>$uid]);
  $uids[$role]=$uid;
 }
 $homes=[['Garden apartment in Dhanmondi','Dhanmondi',32000,3,3,1650,'family'],['Bright balcony home in Bashundhara','Bashundhara',28000,3,2,1450,'any'],['Quiet family home in Uttara','Uttara',22000,2,2,1100,'family'],['Budget apartment in Mirpur','Mirpur',16000,2,1,850,'bachelor'],['Spacious residence in Dhanmondi','Dhanmondi',38000,4,3,1900,'family'],['Compact city home in Uttara','Uttara',18000,2,1,900,'any']];
 $created=0;
 foreach($homes as $i=>[$title,$area,$rent,$beds,$baths,$size,$pref]){
  $title='DEMO: '.$title;
  if(one('SELECT id FROM properties WHERE owner_id=? AND title=?',[$uids['owner'],$title]))continue;
  $location=one("SELECT sub.* FROM locations sub JOIN locations area ON area.id=sub.parent_id WHERE area.name=? AND sub.kind='sub_area' ORDER BY sub.id LIMIT 1",[$area]);
  if(!$location)throw new RuntimeException('Missing demo location: '.$area);
  $aid=insert('addresses',['location_id'=>$location['id'],'road_no'=>(string)(10+$i),'house_no'=>(string)(20+$i),'house_name'=>'Demo Guardian Residence '.($i+1),'floor_no'=>$i+1,'flat_no'=>($i+1).'A','latitude'=>$location['latitude'],'longitude'=>$location['longitude']]);
  $pid=insert('properties',['owner_id'=>$uids['owner'],'address_id'=>$aid,'title'=>$title,'kind'=>'apartment','description'=>'Fictional demo home with bright rooms, a practical layout and easy access to neighbourhood essentials. Photos are illustrative interiors.','bedrooms'=>$beds,'bathrooms'=>$baths,'balconies'=>1,'size_sqft'=>$size,'garage'=>$size>=1100?1:0,'rent'=>$rent,'utility_charge'=>2500,'advance_months'=>2,'tenant_preference'=>$pref,'available_from'=>date('Y-m-d'),'house_rules'=>'Quiet hours after 10 PM. Keep shared areas clean.','status'=>'published','approved_by'=>$uids['supervisor']]);
  foreach(['/home-1.jpg','/home-2.jpg'] as $j=>$path)insert('property_photos',['property_id'=>$pid,'path'=>$i%2?($j?'/home-1.jpg':'/home-2.jpg'):$path,'caption'=>'Demo photo: illustrative interior','category'=>'Living room','sort_order'=>$j]);
  foreach(rows('SELECT id FROM utility_types LIMIT 5') as $utility)insert('property_utilities',['property_id'=>$pid,'utility_type_id'=>$utility['id']]);
  insert('inspections',['property_id'=>$pid,'agent_id'=>$uids['agent'],'summary'=>'DEMO: illustrative approved condition report.','status'=>'approved','approved_by'=>$uids['supervisor']]);
  $created++;
 }
 $db->commit();
 echo "Demo accounts ready for all eight roles; $created published homes added with two photos each.\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
