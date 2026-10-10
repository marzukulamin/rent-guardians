<?php
/** Additive local fixtures for all roles. Run seed-demo.php first. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/bootstrap.php';
if (($config['environment'] ?? '') !== 'local') throw new RuntimeException('Local environment required.');
$marker='demo-functional-v2';
$db->beginTransaction();
$createdFiles=[];
try {
 if (one('SELECT id FROM job_runs WHERE job_key=? FOR UPDATE',[$marker])) {
  $db->commit(); echo "Functional demo already exists; no duplicates added.\n"; exit;
 }
 $accounts=[];
 foreach(['admin','owner','tenant','provider','agent','supervisor','finance','field_agent'] as $role) {
  $accounts[$role]=one('SELECT id,name FROM users WHERE email=?',[$role.'@rentguardian.local']);
  if (!$accounts[$role]) throw new RuntimeException('Missing demo account: '.$role);
 }
 $uid=fn($role)=>(int)$accounts[$role]['id'];
 $base=one("SELECT t.*,p.address_id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.head_tenant_id=? AND t.status='active' ORDER BY t.id LIMIT 1",[$uid('tenant')]);
 if (!$base) throw new RuntimeException('Active demo tenancy required.');
 $address=one('SELECT * FROM addresses WHERE id=?',[$base['address_id']]);
 $category=fn($name)=>(int)one('SELECT id FROM service_categories WHERE name=?',[$name])['id'];
 $future=date('Y-m-d H:i:s',strtotime('+2 days 11:00'));
 $expiry=date('Y-m-d H:i:s',strtotime('+30 days'));
 $flatmate=insert('users',['public_id'=>'RG-DEMO-FLATMATE','name'=>'DEMO Rafi Ahmed','email'=>'flatmate@rentguardian.local','phone'=>'00000000000','password_hash'=>password_hash('Guardian@2026!',PASSWORD_DEFAULT),'verified_at'=>date('Y-m-d H:i:s')]);
 insert('user_roles',['user_id'=>$flatmate,'role_id'=>one("SELECT id FROM roles WHERE name='tenant'")['id']]);
 insert('notification_preferences',['user_id'=>$flatmate]);
 insert('tenancy_members',['tenancy_id'=>$base['id'],'user_id'=>$flatmate,'name'=>'DEMO Rafi Ahmed','relationship'=>'Flatmate','phone'=>'00000000000']);
 insert('tenancy_members',['tenancy_id'=>$base['id'],'name'=>'DEMO Samira Ahmed','relationship'=>'Family member','phone'=>'00000000000']);
 $document=function($user,$tenancy,$property,$kind,$status,$label) use (&$createdFiles,$uid) {
  $photo=in_array($kind,['inspection_photo','property_photo','maintenance_evidence']);
  $source=__DIR__.'/../public/'.($photo?'home-1.jpg':'police-verification-template.pdf');
  $path='demo-v2-'.bin2hex(random_bytes(12)).($photo?'.jpg':'.pdf');
  $folder=__DIR__.'/../storage/uploads';
  if(!is_dir($folder))mkdir($folder,0700,true);
  $dest=$folder.'/'.$path;
  if(!copy($source,$dest))throw new RuntimeException('Could not copy demo document.');
  $createdFiles[]=$dest;
  return insert('documents',['user_id'=>$user,'tenancy_id'=>$tenancy,'property_id'=>$property,'kind'=>$kind,'original_name'=>'DEMO-'.$label.($photo?'.jpg':'.pdf'),'storage_path'=>$path,'mime_type'=>$photo?'image/jpeg':'application/pdf','size_bytes'=>filesize($dest),'status'=>$status,'reviewed_by'=>$status==='pending'?null:$uid('supervisor')]);
 };
 $document($flatmate,$base['id'],$base['property_id'],'police_verification','approved','flatmate-verification-template');
 $document($uid('tenant'),$base['id'],$base['property_id'],'identity','approved','sample-template-not-real-identity');
 $document($uid('tenant'),$base['id'],$base['property_id'],'maintenance_evidence','pending','illustrative-interior');
 insert('tenancy_transfers',['tenancy_id'=>$base['id'],'from_user_id'=>$uid('tenant'),'to_user_id'=>$flatmate,'status'=>'requested']);
 notify($flatmate,'Demo head tenant transfer','Accept the transfer from the tenancy screen; supervisor can then approve it.','tenancy');
 $property=function($label,$status) use ($address,$uid) {
  $a=$address;unset($a['id']);$a['house_name']='DEMO Functional Residence';$a['flat_no']=$label;
  $aid=insert('addresses',$a);
  $p=insert('properties',['owner_id'=>$uid('owner'),'address_id'=>$aid,'title'=>'DEMO: '.$label,'description'=>'Fictional home for testing rental workflows. Photos are illustrative.','bedrooms'=>2,'bathrooms'=>2,'balconies'=>1,'size_sqft'=>1100,'garage'=>1,'rent'=>20000,'utility_charge'=>2000,'advance_months'=>2,'tenant_preference'=>'any','available_from'=>date('Y-m-d'),'house_rules'=>'DEMO quiet hours after 10 PM.','status'=>$status,'approved_by'=>in_array($status,['published','rented','paused'])?$uid('supervisor'):null]);
  foreach(['/home-1.jpg','/home-2.jpg'] as $i=>$photo)insert('property_photos',['property_id'=>$p,'path'=>$photo,'caption'=>'DEMO illustrative photograph','sort_order'=>$i]);
  foreach(rows('SELECT id FROM utility_types LIMIT 5') as $utility)insert('property_utilities',['property_id'=>$p,'utility_type_id'=>$utility['id']]);
  return $p;
 };
 $inspection=function($p,$status,$kind='move_in',$appointment=null) use ($uid,$document) {
  $i=insert('inspections',['property_id'=>$p,'agent_id'=>$uid('agent'),'appointment_id'=>$appointment,'kind'=>$kind,'status'=>$status,'summary'=>'DEMO: room condition and repair responsibility exercise.','approved_by'=>$status==='approved'?$uid('supervisor'):null]);
  foreach(['Living room','Bedroom','Kitchen','Washroom'] as $name){
   $r=insert('inspection_rooms',['inspection_id'=>$i,'name'=>$name,'room_type'=>strtolower(str_replace(' ','_',$name)),'size_sqft'=>150,'electric_ports'=>4,'light_holders'=>2,'cabinet'=>(int)($name==='Kitchen'),'notes'=>'DEMO fixture observations.']);
   insert('inspection_items',['room_id'=>$r,'component'=>'Door lock','condition_status'=>'working','repair_decision'=>'fixed']);
   $d=$document($uid('agent'),null,$p,'inspection_photo','approved','illustrative-room');
   insert('inspection_items',['room_id'=>$r,'component'=>'Wall and tiles','condition_status'=>'cracked','permanent_damage'=>1,'description'=>'DEMO hairline crack; determine repair responsibility.','photo_document_id'=>$d,'estimate'=>750,'repair_decision'=>'pending']);
  }
  return $i;
 };
 foreach(['draft','pending','paused','removed','published'] as $status){
  $p=$property('Listing '.$status,$status);
  if(in_array($status,['pending','published']))$inspection($p,$status==='published'?'approved':'submitted');
  if($status==='pending')insert('appointments',['property_id'=>$p,'requested_by'=>$uid('owner'),'assigned_to'=>$uid('agent'),'kind'=>'inspection','starts_at'=>$future,'status'=>'confirmed','otp_hash'=>password_hash('246810',PASSWORD_DEFAULT),'otp_expires_at'=>$expiry,'notes'=>'DEMO entry code 246810; submit an inspection.']);
 }
 foreach(['pending_verification','ready','notice','ended'] as $status){
  $p=$property('Tenancy '.$status,in_array($status,['notice'])?'rented':($status==='ended'?'published':'paused'));
  $t=insert('tenancies',['property_id'=>$p,'head_tenant_id'=>$uid('tenant'),'start_date'=>date('Y-m-d',strtotime(in_array($status,['notice','ended'])?'-2 months':'+7 days')),'end_date'=>$status==='ended'?date('Y-m-d',strtotime('-2 days')):null,'move_out_date'=>$status==='notice'?date('Y-m-d',strtotime('+14 days')):null,'rent'=>20000,'utility_charge'=>2000,'advance_paid'=>$status==='pending_verification'?0:40000,'status'=>$status]);
  insert('tenancy_members',['tenancy_id'=>$t,'user_id'=>$uid('tenant'),'name'=>$accounts['tenant']['name'],'relationship'=>'Head tenant']);
  $body="DEMO ONLY: Fictional rental agreement for $status scenario. Rent BDT 20,000, utilities BDT 2,000, refundable advance BDT 40,000. No actual lease is created.";
  $hash=hash('sha256',$body);$c=insert('contracts',['tenancy_id'=>$t,'body'=>$body,'content_hash'=>$hash]);
  if($status!=='pending_verification')foreach(['owner','tenant'] as $role)insert('contract_signatures',['contract_id'=>$c,'user_id'=>$uid($role),'signer_role'=>$role,'signed_name'=>$accounts[$role]['name'],'content_hash'=>$hash,'ip_address'=>'local-demo']);
  $document($uid('tenant'),$t,$p,'police_verification',$status==='pending_verification'?'pending':'approved','verification-template-'.$status);
  if($status==='pending_verification')$document($uid('tenant'),$t,$p,'identity','rejected','rejected-sample-template');
  $i=insert('invoices',['user_id'=>$uid('tenant'),'tenancy_id'=>$t,'period'=>date('Y-m-01'),'due_date'=>date('Y-m-d',strtotime('+7 days')),'status'=>$status==='pending_verification'?'open':'paid','billing_group'=>'demo-v2-advance']);
  insert('invoice_items',['invoice_id'=>$i,'kind'=>'advance','description'=>'DEMO refundable advance','amount'=>40000]);
  if($status!=='pending_verification')insert('payments',['invoice_id'=>$i,'user_id'=>$uid('tenant'),'amount'=>40000,'method'=>'bank_transfer','reference'=>'DEMO-V2-ADVANCE-'.$t,'status'=>'confirmed','reviewed_by'=>$uid('supervisor'),'confirmed_by'=>$uid('finance'),'confirmed_at'=>date('Y-m-d H:i:s')]);
  $appt=insert('appointments',['property_id'=>$p,'requested_by'=>$uid('tenant'),'assigned_to'=>$uid('agent'),'kind'=>$status==='notice'?'move_out':'inspection','starts_at'=>$future,'status'=>$status==='ended'?'completed':'confirmed','otp_hash'=>password_hash('246810',PASSWORD_DEFAULT),'otp_expires_at'=>$expiry,'otp_verified_at'=>$status==='ended'?date('Y-m-d H:i:s'):null,'notes'=>'DEMO entry code 246810']);
  $inspection($p,$status==='notice'?'submitted':'approved',$status==='notice'||$status==='ended'?'move_out':'move_in',$appt);
 }
 $monthly=invoice_for((int)$base['id'],$uid('tenant'));
 foreach([
  ['Owner approval required','open','pending',true,null,null,null],
  ['Owner declined; tenant can self-fund','open','declined',true,null,null,null],
  ['Supervisor approval required','open','approved',true,null,null,null],
  ['Instant electrical job to claim','approved','not_needed',false,null,null,null],
  ['Service entry verification','assigned','not_needed',false,$uid('provider'),null,null],
  ['Finish work and submit cost','in_progress','not_needed',false,$uid('provider'),null,null],
  ['Price mismatch to resolve','completed','not_needed',false,$uid('provider'),800,900],
  ['Matched owner expense to reconcile','completed','approved',true,$uid('provider'),null,1200],
  ['Cancelled request history','cancelled','not_needed',false,null,null,null]
 ] as [$label,$status,$decision,$owner,$provider,$tenantCost,$providerCost]){
  $r=insert('maintenance_requests',['tenancy_id'=>$base['id'],'category_id'=>$category($status==='approved'?'Electrical':'Plumbing'),'requested_by'=>$uid('tenant'),'provider_id'=>$provider,'preferred_provider_id'=>$uid('provider'),'title'=>'DEMO: '.$label,'description'=>'Fictional workflow scenario; no actual work performed.','urgency'=>$status==='approved'?'instant':'scheduled','appointment_at'=>$future,'owner_involved'=>(int)$owner,'owner_decision'=>$decision,'pay_later'=>1,'status'=>$status,'approved_by'=>in_array($status,['approved','assigned','in_progress','completed'])?$uid('supervisor'):null,'otp_hash'=>$status==='assigned'?password_hash('135790',PASSWORD_DEFAULT):null,'otp_expires_at'=>$status==='assigned'?$expiry:null,'otp_verified_at'=>in_array($status,['in_progress','completed'])?date('Y-m-d H:i:s'):null,'tenant_cost'=>$tenantCost,'owner_cost'=>$label==='Matched owner expense to reconcile'?1200:null,'provider_cost'=>$providerCost]);
  insert('request_events',['request_id'=>$r,'actor_id'=>$uid('supervisor'),'status'=>$status,'message'=>'DEMO fixture at '.$status.' stage.']);
 }
 foreach(['grocery'=>'Fresh chicken','installation'=>'Fiber internet · 30 Mbps','moving'=>'Move-in / move-out assistance','maid'=>'Verified housemaid visit'] as $kind=>$name){
  $product=one('SELECT * FROM catalog_products WHERE provider_id=? AND name=?',[$uid('provider'),$name]);
  if(!$product)throw new RuntimeException('Missing catalog product: '.$name);
  foreach(['placed','accepted','delivering','completed','cancelled'] as $status){
   $o=insert('orders',['tenancy_id'=>$base['id'],'user_id'=>$uid('tenant'),'provider_id'=>$uid('provider'),'address_id'=>$base['address_id'],'kind'=>$kind,'status'=>$status,'pay_later'=>(int)($kind==='grocery'),'scheduled_at'=>$future,'notes'=>'DEMO: '.$kind.' '.$status.'; call before arrival.']);
   insert('order_items',['order_id'=>$o,'product_id'=>$product['id'],'quantity'=>1,'unit_price'=>$product['price'],'installation_cost'=>$product['installation_cost'],'customization'=>$kind==='grocery'?'DEMO cut into 12 pieces, pack separately.':'DEMO arrange entry with resident.']);
   $i=insert('invoices',['user_id'=>$uid('tenant'),'tenancy_id'=>$base['id'],'period'=>date('Y-m-01'),'due_date'=>date('Y-m-d'),'status'=>$status==='cancelled'?'void':'open','billing_group'=>'demo-v2-order-'.$o]);
   insert('invoice_items',['invoice_id'=>$i,'kind'=>$kind,'description'=>'DEMO '.$kind.' '.$status,'amount'=>$product['price']+$product['installation_cost'],'order_id'=>$o]);
   if($status==='completed')insert('reviews',['author_id'=>$uid('tenant'),'subject_id'=>$uid('provider'),'tenancy_id'=>$base['id'],'order_id'=>$o,'rating'=>4,'comment'=>'DEMO sample customer review.']);
  }
 }
 foreach(rows('SELECT * FROM meal_plans WHERE provider_id=?',[$uid('provider')]) as $plan)foreach(['active','cancelled','ended'] as $status){
  $s=insert('meal_subscriptions',['tenancy_id'=>$base['id'],'user_id'=>$uid('tenant'),'meal_plan_id'=>$plan['id'],'people'=>2,'starts_on'=>date('Y-m-d',strtotime($status==='ended'?'-10 days':'today')),'ends_on'=>date('Y-m-d',strtotime($status==='ended'?'-4 days':'+6 days')),'pay_later'=>1,'status'=>$status]);
  if($status!=='cancelled')insert('invoice_items',['invoice_id'=>$monthly,'kind'=>'catering','description'=>'DEMO '.$plan['meal'].' '.$status.' for 2 people / 7 days','amount'=>$plan['price_per_person']*14,'subscription_id'=>$s]);
 }
 $receipt=$document($uid('tenant'),$base['id'],$base['property_id'],'receipt','pending','sample-template-not-real-payment');
 foreach(['submitted','reviewed','rejected'] as $status)insert('payments',['invoice_id'=>$monthly,'user_id'=>$uid('tenant'),'amount'=>500,'method'=>'bkash','reference'=>'DEMO-V2-'.$status,'receipt_document_id'=>$receipt,'status'=>$status,'reviewed_by'=>$status==='submitted'?null:$uid('supervisor')]);
 insert('reward_ledger',['user_id'=>$uid('tenant'),'points'=>1000,'reason'=>'DEMO points available to redeem']);
 insert('reward_ledger',['user_id'=>$uid('tenant'),'points'=>-100,'reason'=>'DEMO historical points redemption']);
 insert('invoice_items',['invoice_id'=>$monthly,'kind'=>'discount','description'=>'DEMO points discount','amount'=>-100]);
 foreach(['open','in_progress','resolved','closed'] as $status)insert('support_tickets',['user_id'=>$uid('owner'),'property_id'=>$base['property_id'],'assigned_to'=>$uid('supervisor'),'category'=>'listing','subject'=>'DEMO: owner support '.$status,'message'=>'Fictional listing assistance request.','status'=>$status]);
 foreach(['open','resolved'] as $status)insert('complaints',['tenancy_id'=>$base['id'],'filed_by'=>$uid('owner'),'about_user'=>$uid('tenant'),'description'=>'DEMO: shared hallway cleaning '.$status,'status'=>$status,'resolution'=>$status==='resolved'?'DEMO cleaning schedule agreed.':null]);
 insert('referrals',['referrer_id'=>$uid('tenant'),'referred_email'=>'flatmate@rentguardian.local','referred_user_id'=>$flatmate,'status'=>'registered']);
 $campaign=insert('campaigns',['name'=>'DEMO: active Dhanmondi outreach','location_id'=>one('SELECT parent_id FROM locations WHERE id=?',[$address['location_id']])['parent_id'],'starts_on'=>date('Y-m-d'),'ends_on'=>date('Y-m-d',strtotime('+30 days'))]);
 insert('campaign_assignments',['campaign_id'=>$campaign,'user_id'=>$uid('field_agent')]);
 insert('campaign_checklists',['campaign_id'=>$campaign,'user_id'=>$uid('field_agent'),'completed_on'=>date('Y-m-d'),'owner_name'=>'DEMO Nusrat Rahman','owner_phone'=>'00000000000','checklist'=>'DEMO introduction, property overview, app walkthrough, follow-up inspection requested.']);
 foreach($accounts as $role=>$account){
  notify((int)$account['id'],'Functional demo scenarios ready','DEMO records added for '.$role.'. Use storage/demo-test-guide.md for scenarios and entry codes.','workspace');
 }
 notify($uid('tenant'),'Functional demo entry codes','DEMO service: 135790. DEMO inspection: 246810. Valid until '.$expiry.'.','workspace');
 audit(['id'=>$uid('admin')],'demo.seed','tenancies',(int)$base['id'],['marker'=>$marker,'roles'=>array_keys($accounts)]);
 insert('job_runs',['job_key'=>$marker]);
 $db->commit();
 echo "Functional demo created for eight roles plus a flatmate transfer account. Entry codes expire $expiry.\n";
} catch(Throwable $e) {
 if($db->inTransaction())$db->rollBack();
 foreach($createdFiles as $path)if(is_file($path))unlink($path);
 throw $e;
}
