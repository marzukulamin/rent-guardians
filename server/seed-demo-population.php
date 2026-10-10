<?php
/** Connected fictional local dataset: 100 NEW accounts per role and 100 homes. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
require __DIR__.'/demo-identities.php';
$marker='connected-demo-population-v1';
$db->beginTransaction();
try{
 if(one('SELECT id FROM job_runs WHERE job_key=? FOR UPDATE',[$marker])){$db->commit();echo "Population already exists; no duplicates added.\n";exit;}
 $roles=array_column(rows('SELECT id,name FROM roles'),'id','name');
 $areas=rows("SELECT sub.*,area.name area FROM locations sub JOIN locations area ON area.id=sub.parent_id WHERE sub.kind='sub_area' ORDER BY sub.id");
 if(count($areas)<4)throw new RuntimeException('Expected local neighbourhood hierarchy.');
 $cats=array_column(rows('SELECT id,name FROM service_categories'),'id','name');
 $first=['Ayesha','Sadia','Farhana','Nadia','Samira','Nusrat','Maliha','Shaila','Rafia','Tania','Arif','Fahim','Tanvir','Imran','Rafi','Hasan','Nabil','Sajid','Rakib','Omar'];
 $last=['Rahman','Ahmed','Islam','Hasan','Chowdhury'];
 $hash=password_hash('Guardian@2026!',PASSWORD_DEFAULT);$uids=[];$names=[];$profiles=[];$campaigns=[];
 foreach(['tenant','owner','provider','agent','supervisor','finance','field_agent','admin'] as $roleIndex=>$role){
  if(!isset($roles[$role]))throw new RuntimeException('Missing role: '.$role);
  for($n=1;$n<=100;$n++){
   $name=demo_identity($role,$n);$area=$areas[($n-1)%count($areas)];
   $uid=insert('users',['public_id'=>sprintf('RGD-%s-%03d',strtoupper(str_replace('_','',$role)),$n),'name'=>$name,'email'=>sprintf('demo.%s.%03d@rentguardian.local',$role,$n),'phone'=>'000'.str_pad((string)($roleIndex*100+$n),8,'0',STR_PAD_LEFT),'password_hash'=>$hash,'verified_at'=>date('Y-m-d H:i:s')]);
   $uids[$role][$n]=$uid;$names[$role][$n]=$name;insert('user_roles',['user_id'=>$uid,'role_id'=>$roles[$role]]);
   insert('notification_preferences',['user_id'=>$uid,'location_id'=>$area['id']]);
   if($role==='provider'){
    insert('provider_profiles',['user_id'=>$uid,'business_name'=>$area['area'].' Home Services '.sprintf('%03d',$n),'location_id'=>$area['id'],'latitude'=>$area['latitude'],'longitude'=>$area['longitude'],'bio'=>'Fictional local demo provider for household services.']);
    foreach($cats as $category)insert('provider_categories',['provider_id'=>$uid,'category_id'=>$category]);
    foreach([['Fresh chicken','Groceries','kg',320,0],['WiFi setup','WiFi','connection',1200,500],['Home cleaning visit','Housemaid','visit',800,0],['Moving assistance','Moving help','trip',3500,0]] as [$product,$category,$unit,$price,$installation])$profiles[$n]['products'][$category]=insert('catalog_products',['provider_id'=>$uid,'category_id'=>$cats[$category],'name'=>$product,'description'=>'Sample catalog item; price is stored in the database.','unit'=>$unit,'price'=>$price,'installation_cost'=>$installation]);
    foreach(['breakfast'=>80,'lunch'=>150,'dinner'=>130] as $meal=>$price)$profiles[$n]['meals'][$meal]=insert('meal_plans',['provider_id'=>$uid,'name'=>ucfirst($meal).' home meal plan','meal'=>$meal,'description'=>'Rice or bread with seasonal vegetables and a protein option. Fictional sample menu.','price_per_person'=>$price]);
   }
   if($role==='owner')insert('bank_accounts',['user_id'=>$uid,'bank_name'=>'Sample Bank','account_name'=>$name,'account_number'=>'000000'.str_pad((string)$n,6,'0',STR_PAD_LEFT),'routing_code'=>'000000000']);
   notify($uid,'Your workspace is ready','Explore your connected sample homes, appointments and role workspace.','home');
  }
 }
 foreach($areas as $area){$campaigns[$area['id']]=insert('campaigns',['name'=>$area['area'].' property owner outreach','location_id'=>$area['id'],'starts_on'=>date('Y-m-d'),'ends_on'=>date('Y-m-d',strtotime('+30 days'))]);}
 for($n=1;$n<=100;$n++){
  $area=$areas[($n-1)%count($areas)];$campaign=$campaigns[$area['id']];$field=$uids['field_agent'][$n];
  insert('campaign_assignments',['campaign_id'=>$campaign,'user_id'=>$field]);
  insert('campaign_checklists',['campaign_id'=>$campaign,'user_id'=>$field,'completed_on'=>date('Y-m-d'),'owner_name'=>$names['owner'][$n],'owner_phone'=>one('SELECT phone FROM users WHERE id=?',[$uids['owner'][$n]])['phone'],'checklist'=>'Introduced the service, explained inspections and arranged an owner follow-up. Sample outreach activity.']);
 }
 $root=dirname(__DIR__);$folder=$root.'/storage/uploads';if(!is_dir($folder))mkdir($folder,0700,true);
 $file=function($uid,$pid,$tid,$kind,$source,$name,$status,$reviewer)use($folder){
  $dest='population-v1-'.$name;
  if(!copy($source,$folder.'/'.$dest))throw new RuntimeException('Could not store sample file.');
  return insert('documents',['user_id'=>$uid,'property_id'=>$pid,'tenancy_id'=>$tid,'kind'=>$kind,'original_name'=>'Sample '.str_replace('-',' ',$name),'storage_path'=>$dest,'mime_type'=>str_ends_with($name,'.pdf')?'application/pdf':'image/jpeg','size_bytes'=>filesize($folder.'/'.$dest),'status'=>$status,'reviewed_by'=>$reviewer]);
 };
 $homes=[];$tenancies=[];
 for($n=1;$n<=100;$n++){
  $area=$areas[($n-1)%count($areas)];$owner=$uids['owner'][$n];$tenant=$uids['tenant'][$n];$agent=$uids['agent'][$n];$super=$uids['supervisor'][$n];$finance=$uids['finance'][$n];$provider=$uids['provider'][$n];
  $beds=1+(($n-1)%4);$rent=12000+$beds*4500+($n%5)*1000;$size=500+$beds*300;
  $aid=insert('addresses',['location_id'=>$area['id'],'road_no'=>(string)(1+$n%20),'house_no'=>(string)(100+$n),'house_name'=>'Guardian Residence '.sprintf('%03d',$n),'floor_no'=>1+$n%8,'flat_no'=>(1+$n%8).'B','latitude'=>$area['latitude'],'longitude'=>$area['longitude']]);
  $label=['Sunlit apartment','Garden-view family home','Modern balcony residence','Quiet neighbourhood flat'][($n-1)%4];
  $pid=insert('properties',['owner_id'=>$owner,'address_id'=>$aid,'title'=>$label.' in '.$area['area'].' '.sprintf('%03d',$n),'description'=>'Fictional demo property with comfortable rooms, natural light and nearby everyday essentials. Interior photos are illustrative.','bedrooms'=>$beds,'bathrooms'=>max(1,$beds-1),'balconies'=>1+$n%2,'size_sqft'=>$size,'garage'=>$n%3?1:0,'rent'=>$rent,'utility_charge'=>2000,'advance_months'=>2,'tenant_preference'=>['family','bachelor','any'][$n%3],'available_from'=>date('Y-m-d',strtotime('+'.($n%14).' days')),'house_rules'=>'Quiet hours after 10 PM. Keep shared spaces clean.','status'=>$n<=40?'rented':'published','approved_by'=>$super]);$homes[]=$pid;
  foreach(['living','bedroom'] as $j=>$room){$doc=$file($owner,$pid,null,'property_photo',$root.'/public/home-'.(1+(($n+$j)%2)).'.jpg',sprintf('home-%03d-%s.jpg',$n,$room),'approved',$super);insert('property_photos',['property_id'=>$pid,'path'=>'/files/photo/'.$doc,'caption'=>'Illustrative '.str_replace('living','living room',$room).' photo for this sample home','category'=>$j?'Bedroom':'Living room','sort_order'=>$j]);}
  foreach(rows('SELECT id FROM utility_types ORDER BY id LIMIT 5') as $utility)insert('property_utilities',['property_id'=>$pid,'utility_type_id'=>$utility['id']]);
  $appointment=insert('appointments',['property_id'=>$pid,'requested_by'=>$owner,'assigned_to'=>$agent,'kind'=>'inspection','starts_at'=>date('Y-m-d H:i:s',strtotime('-2 days 11:00')),'status'=>'completed','otp_verified_at'=>date('Y-m-d H:i:s',strtotime('-2 days 11:00')),'notes'=>'Initial sample property inspection completed.']);
  $inspection=insert('inspections',['property_id'=>$pid,'agent_id'=>$agent,'appointment_id'=>$appointment,'status'=>'approved','approved_by'=>$super,'summary'=>'Sample inspection: rooms and fixtures checked before publication.']);
  foreach(['Living room'=>'living','Main bedroom'=>'bedroom','Kitchen'=>'kitchen','Washroom'=>'washroom'] as $roomName=>$roomType){$room=insert('inspection_rooms',['inspection_id'=>$inspection,'name'=>$roomName,'room_type'=>$roomType,'size_sqft'=>$size/4,'electric_ports'=>4,'light_holders'=>2]);foreach(['Door lock','Light switch','Electrical outlet','Wall finish'] as $component)insert('inspection_items',['room_id'=>$room,'component'=>$component,'condition_status'=>'working','repair_decision'=>'fixed']);}
  insert('saved_properties',['user_id'=>$tenant,'property_id'=>$pid]);
  $view=insert('appointments',['property_id'=>$pid,'requested_by'=>$tenant,'assigned_to'=>$agent,'kind'=>'viewing','starts_at'=>date('Y-m-d H:i:s',strtotime($n<=40?'-1 day 14:00':'+3 days 14:00')),'status'=>$n<=40?'completed':'confirmed','notes'=>'Viewing arranged with the resident and agent.','otp_hash'=>password_hash('246810',PASSWORD_DEFAULT),'otp_expires_at'=>date('Y-m-d H:i:s',strtotime('+30 days'))]);
  if($n<=40){
   insert('property_visits',['user_id'=>$tenant,'property_id'=>$pid,'appointment_id'=>$view,'visited_at'=>date('Y-m-d H:i:s',strtotime('-1 day 14:00'))]);run('UPDATE appointments SET otp_verified_at=starts_at WHERE id=?',[$view]);
   $tid=insert('tenancies',['property_id'=>$pid,'head_tenant_id'=>$tenant,'start_date'=>date('Y-m-01'),'rent'=>$rent,'utility_charge'=>2000,'advance_paid'=>$rent*2,'status'=>'active']);$tenancies[]=$tid;
   insert('tenancy_members',['tenancy_id'=>$tid,'user_id'=>$tenant,'name'=>$names['tenant'][$n],'relationship'=>'Head tenant','phone'=>one('SELECT phone FROM users WHERE id=?',[$tenant])['phone']]);
   $body='Sample rental agreement for '.$label.'. Monthly rent BDT '.$rent.'; utilities BDT 2,000; refundable advance BDT '.($rent*2).'. Fictional demonstration agreement.';$digest=hash('sha256',$body);$contract=insert('contracts',['tenancy_id'=>$tid,'body'=>$body,'content_hash'=>$digest]);
   foreach(['owner'=>$owner,'tenant'=>$tenant] as $role=>$uid)insert('contract_signatures',['contract_id'=>$contract,'user_id'=>$uid,'signer_role'=>$role,'signed_name'=>$names[$role][$n],'content_hash'=>$digest,'ip_address'=>'local-demo']);
   $verification=$file($tenant,$pid,$tid,'police_verification',$root.'/public/police-verification-template.pdf',sprintf('household-verification-%03d.pdf',$n),'approved',$super);
   $advance=insert('invoices',['user_id'=>$tenant,'tenancy_id'=>$tid,'period'=>date('Y-m-01'),'due_date'=>date('Y-m-01'),'status'=>'paid','billing_group'=>'advance-'.$tid]);insert('invoice_items',['invoice_id'=>$advance,'kind'=>'advance','description'=>'Refundable advance: two months','amount'=>$rent*2]);
   insert('payments',['invoice_id'=>$advance,'user_id'=>$tenant,'amount'=>$rent*2,'method'=>'bank_transfer','reference'=>sprintf('SAMPLE-ADVANCE-%03d',$n),'status'=>'confirmed','reviewed_by'=>$super,'confirmed_by'=>$finance,'confirmed_at'=>date('Y-m-d H:i:s')]);
   $invoice=insert('invoices',['user_id'=>$tenant,'tenancy_id'=>$tid,'period'=>date('Y-m-01'),'due_date'=>date('Y-m-10'),'billing_group'=>'monthly']);foreach(['rent'=>$rent,'utility'=>2000] as $kind=>$amount)insert('invoice_items',['invoice_id'=>$invoice,'kind'=>$kind,'description'=>$kind==='rent'?'Monthly rent':'Monthly utilities','amount'=>$amount]);
   $status=['open','approved','assigned','in_progress','completed'][($n-1)%5];$cost=750+($n%4)*150;
   $request=insert('maintenance_requests',['tenancy_id'=>$tid,'category_id'=>$cats['Plumbing'],'requested_by'=>$tenant,'provider_id'=>in_array($status,['assigned','in_progress','completed'])?$provider:null,'preferred_provider_id'=>$provider,'title'=>['Kitchen tap repair','Bathroom drain cleaning','Water line inspection','Basin fixture replacement'][($n-1)%4],'description'=>'A resident reported a household plumbing issue and requested a service visit.','urgency'=>$status==='approved'?'instant':'scheduled','appointment_at'=>date('Y-m-d H:i:s',strtotime('+2 days 10:00')),'owner_involved'=>0,'owner_decision'=>'not_needed','pay_later'=>1,'status'=>$status,'approved_by'=>$status==='open'?null:$super,'otp_hash'=>password_hash('135790',PASSWORD_DEFAULT),'otp_expires_at'=>date('Y-m-d H:i:s',strtotime('+30 days')),'otp_verified_at'=>in_array($status,['in_progress','completed'])?date('Y-m-d H:i:s'):null,'tenant_cost'=>$status==='completed'?$cost:null,'provider_cost'=>$status==='completed'?$cost:null,'cost_confirmed_at'=>$status==='completed'?date('Y-m-d H:i:s'):null]);
   insert('request_events',['request_id'=>$request,'actor_id'=>$tenant,'status'=>'open','message'=>'Resident reported the issue.']);if($status!=='open')insert('request_events',['request_id'=>$request,'actor_id'=>$super,'status'=>$status,'message'=>'Service request is '.$status.'.']);
   if($status==='completed'){insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'maintenance','description'=>'Completed plumbing service','amount'=>$cost,'request_id'=>$request]);insert('reward_ledger',['user_id'=>$tenant,'request_id'=>$request,'points'=>100,'reason'=>'Completed and reconciled service']);insert('reviews',['author_id'=>$tenant,'subject_id'=>$provider,'tenancy_id'=>$tid,'request_id'=>$request,'rating'=>5,'comment'=>'Clear communication and a tidy repair. Sample review.']);}
   $kind=['grocery','installation','maid','moving'][($n-1)%4];$category=['grocery'=>'Groceries','installation'=>'WiFi','maid'=>'Housemaid','moving'=>'Moving help'][$kind];$product=one('SELECT * FROM catalog_products WHERE id=?',[$profiles[$n]['products'][$category]]);$orderStatus=['placed','accepted','delivering','completed'][($n-1)%4];
   $oid=insert('orders',['tenancy_id'=>$tid,'user_id'=>$tenant,'provider_id'=>$provider,'address_id'=>$aid,'kind'=>$kind,'status'=>$orderStatus,'pay_later'=>$kind==='grocery'?1:0,'scheduled_at'=>date('Y-m-d H:i:s',strtotime('+2 days 15:00')),'notes'=>'Please call before arrival. Sample service order.']);insert('order_items',['order_id'=>$oid,'product_id'=>$product['id'],'quantity'=>1,'unit_price'=>$product['price'],'installation_cost'=>$product['installation_cost'],'customization'=>'Use the main entrance.']);
   $bill=$kind==='grocery'?$invoice:insert('invoices',['user_id'=>$tenant,'tenancy_id'=>$tid,'period'=>date('Y-m-01'),'due_date'=>date('Y-m-d',strtotime('+2 days')),'billing_group'=>'order-'.$oid]);insert('invoice_items',['invoice_id'=>$bill,'kind'=>$kind,'description'=>ucfirst($kind).' order','amount'=>(float)$product['price']+(float)$product['installation_cost'],'order_id'=>$oid]);
   $subscription=insert('meal_subscriptions',['tenancy_id'=>$tid,'user_id'=>$tenant,'meal_plan_id'=>$profiles[$n]['meals']['lunch'],'people'=>2,'starts_on'=>date('Y-m-d'),'ends_on'=>date('Y-m-d',strtotime('+6 days')),'pay_later'=>1]);insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'catering','description'=>'Lunch for two, seven days','amount'=>2100,'subscription_id'=>$subscription]);
   if($n%3===0){$total=(float)one('SELECT SUM(amount) total FROM invoice_items WHERE invoice_id=?',[$invoice])['total'];$paymentStatus=$n%2?'reviewed':'confirmed';insert('payments',['invoice_id'=>$invoice,'user_id'=>$tenant,'amount'=>$total,'method'=>'bkash','reference'=>sprintf('SAMPLE-RENT-%03d',$n),'status'=>$paymentStatus,'reviewed_by'=>$super,'confirmed_by'=>$paymentStatus==='confirmed'?$finance:null,'confirmed_at'=>$paymentStatus==='confirmed'?date('Y-m-d H:i:s'):null]);if($paymentStatus==='confirmed')run("UPDATE invoices SET status='paid' WHERE id=?",[$invoice]);}
   insert('complaints',['tenancy_id'=>$tid,'filed_by'=>$tenant,'about_user'=>$owner,'description'=>'Please clarify the shared stairwell cleaning schedule. Sample resident question.','status'=>$n%2?'reviewing':'resolved','resolution'=>$n%2?null:'The shared cleaning schedule has been explained.']);
   insert('referrals',['referrer_id'=>$tenant,'referred_email'=>sprintf('demo.tenant.%03d@rentguardian.local',$n+40),'referred_user_id'=>$uids['tenant'][$n+40],'status'=>'registered']);
  }
  insert('support_tickets',['user_id'=>$tenant,'property_id'=>$pid,'assigned_to'=>$super,'category'=>$n<=40?'payment':'listing','subject'=>$n<=40?'Help understanding the monthly bill':'A question about the viewing','message'=>$n<=40?'Please explain the rent and service charges.':'Please confirm the preferred viewing time.','status'=>$n%3?'open':'resolved']);
  notify($tenant,'Your home journey is connected',$n<=40?'Your agreement, billing and service requests are ready to explore.':'Your saved home and viewing appointment are ready to explore.',$n<=40?'tenancy':'appointments');
 }
 insert('job_runs',['job_key'=>$marker]);
 audit(['id'=>$uids['admin'][1]],'demo.population','properties',$homes[0],['users_per_role'=>100,'properties'=>100,'active_tenancies'=>40]);
 $db->commit();
 $summary=['marker'=>$marker,'accounts_per_role'=>100,'account_ids'=>$uids,'property_ids'=>$homes,'tenancy_ids'=>$tenancies];
 file_put_contents($root.'/storage/demo-population.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
 echo "Created 800 sample users (100 per role), 100 homes, 40 active tenancies and linked photos, inspections, contracts, bills, services and campaigns.\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
