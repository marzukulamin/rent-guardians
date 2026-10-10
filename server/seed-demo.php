<?php
/** Additive, one-time local demo scenarios. Never resets existing users or passwords. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/bootstrap.php';
if (($config['environment'] ?? '') !== 'local') throw new RuntimeException('Local environment required.');
$roles=['admin','owner','tenant','provider','agent','supervisor','finance','field_agent'];
$accounts=[];
foreach($roles as $role){$accounts[$role]=one('SELECT id,name,email,password_hash FROM users WHERE email=?',[$role.'@rentguardian.local']);if(!$accounts[$role])throw new RuntimeException('Run setup first: missing '.$role);}
$id=fn($role)=>(int)$accounts[$role]['id'];
$t=one("SELECT t.*,p.address_id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.head_tenant_id=? AND t.status='active' ORDER BY t.id LIMIT 1",[$id('tenant')]);
if(!$t)throw new RuntimeException('Active demo tenancy required.');
$db->beginTransaction();
try{
if(!one('SELECT id FROM job_runs WHERE job_key=?',['demo-scenarios-v1'])){
 $tomorrow=date('Y-m-d H:i:s',strtotime('tomorrow 11:00'));
 $appointment=insert('appointments',['property_id'=>$t['property_id'],'requested_by'=>$id('tenant'),'assigned_to'=>$id('agent'),'kind'=>'inspection','starts_at'=>$tomorrow,'status'=>'confirmed','notes'=>'DEMO: follow-up condition inspection; arrange entry with the resident.','otp_hash'=>password_hash('246810',PASSWORD_DEFAULT),'otp_expires_at'=>date('Y-m-d H:i:s',strtotime('+7 days'))]);
 $inspection=insert('inspections',['property_id'=>$t['property_id'],'agent_id'=>$id('agent'),'appointment_id'=>$appointment,'status'=>'submitted','summary'=>'DEMO: routine condition report awaiting supervisor approval. Kitchen tap needs a washer; bedroom fittings are working.']);
 foreach([['Kitchen','kitchen',120,'Tap','broken',450],['Main bedroom','bedroom',210,'Light switch','working',0]] as $r){$room=insert('inspection_rooms',['inspection_id'=>$inspection,'name'=>$r[0],'room_type'=>$r[1],'size_sqft'=>$r[2],'electric_ports'=>4,'light_holders'=>2,'notes'=>'DEMO sample observations']);insert('inspection_items',['room_id'=>$room,'component'=>$r[3],'condition_status'=>$r[4],'estimate'=>$r[5],'description'=>'DEMO: illustrative condition record.']);}
 $requests=[];
 foreach([['Electrical safety check','approved',2,null],['Bathroom tap replacement','assigned',1,$id('provider')],['AC filter cleaning','in_progress',3,$id('provider')],['Completed plumbing service','completed',1,$id('provider')]] as $r){
  $data=['tenancy_id'=>$t['id'],'category_id'=>$r[2],'requested_by'=>$id('tenant'),'provider_id'=>$r[3],'preferred_provider_id'=>$id('provider'),'title'=>'DEMO: '.$r[0],'description'=>'Illustrative service request for exploring the '.$r[1].' workflow.','urgency'=>'scheduled','appointment_at'=>$tomorrow,'owner_involved'=>0,'owner_decision'=>'not_needed','pay_later'=>1,'status'=>$r[1],'approved_by'=>$id('supervisor')];
  if($r[1]==='assigned'){$data['otp_hash']=password_hash('135790',PASSWORD_DEFAULT);$data['otp_expires_at']=date('Y-m-d H:i:s',strtotime('+7 days'));}
  if(in_array($r[1],['in_progress','completed']))$data['otp_verified_at']=date('Y-m-d H:i:s');
  if($r[1]==='completed'){$data['tenant_cost']=900;$data['provider_cost']=900;$data['cost_confirmed_at']=date('Y-m-d H:i:s');}
  $request=insert('maintenance_requests',$data);$requests[]=$request;
  insert('request_events',['request_id'=>$request,'actor_id'=>$id('supervisor'),'status'=>$r[1],'message'=>'DEMO: seeded scenario at this stage; no real service was performed.']);
 }
 $invoice=invoice_for((int)$t['id'],$id('tenant'));
 insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'maintenance','description'=>'DEMO: completed plumbing service','amount'=>900,'request_id'=>$requests[3]]);
 insert('reward_ledger',['user_id'=>$id('tenant'),'request_id'=>$requests[3],'points'=>100,'reason'=>'DEMO: completed service reward']);
 insert('reviews',['author_id'=>$id('tenant'),'subject_id'=>$id('provider'),'tenancy_id'=>$t['id'],'request_id'=>$requests[3],'rating'=>5,'comment'=>'DEMO: punctual service and clear communication.']);
 $order=insert('orders',['tenancy_id'=>$t['id'],'user_id'=>$id('tenant'),'provider_id'=>$id('provider'),'address_id'=>$t['address_id'],'kind'=>'grocery','status'=>'placed','pay_later'=>1,'scheduled_at'=>$tomorrow,'notes'=>'DEMO: call before delivery; use the main entrance.']);
 $total=0;
 foreach(['Fresh chicken'=>2,'Farm-fresh eggs'=>1] as $name=>$qty){$p=one('SELECT * FROM catalog_products WHERE provider_id=? AND name=?',[$id('provider'),$name]);if(!$p)throw new RuntimeException('Missing product '.$name);insert('order_items',['order_id'=>$order,'product_id'=>$p['id'],'quantity'=>$qty,'unit_price'=>$p['price'],'customization'=>$name==='Fresh chicken'?'Cut into 12 pieces; pack separately.':'Check eggs before packing.']);$total+=(float)$p['price']*$qty;}
 insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'grocery','description'=>'DEMO: chicken and eggs delivery','amount'=>$total,'order_id'=>$order]);
 $meal=one("SELECT * FROM meal_plans WHERE provider_id=? AND meal='lunch' LIMIT 1",[$id('provider')]);
 $subscription=insert('meal_subscriptions',['tenancy_id'=>$t['id'],'user_id'=>$id('tenant'),'meal_plan_id'=>$meal['id'],'people'=>2,'starts_on'=>date('Y-m-d'),'ends_on'=>date('Y-m-d',strtotime('+6 days')),'pay_later'=>1]);
 insert('invoice_items',['invoice_id'=>$invoice,'kind'=>'catering','description'=>'DEMO: lunch for two people for seven days','amount'=>(float)$meal['price_per_person']*14,'subscription_id'=>$subscription]);
 foreach([['submitted',3000,null],['reviewed',5000,$id('supervisor')]] as $p)insert('payments',['invoice_id'=>$invoice,'user_id'=>$id('tenant'),'amount'=>$p[1],'method'=>'bank_transfer','channel'=>'app','reference'=>'DEMO-SAMPLE-'.$p[0], 'status'=>$p[0],'reviewed_by'=>$p[2]]);
 $historic=insert('invoices',['user_id'=>$id('tenant'),'tenancy_id'=>$t['id'],'period'=>date('Y-m-01',strtotime('first day of last month')),'due_date'=>date('Y-m-10',strtotime('first day of last month')),'status'=>'paid','billing_group'=>'demo-history']);
 insert('invoice_items',['invoice_id'=>$historic,'kind'=>'rent','description'=>'DEMO: previous month rent','amount'=>$t['rent']]);
 insert('invoice_items',['invoice_id'=>$historic,'kind'=>'utility','description'=>'DEMO: previous month utilities','amount'=>$t['utility_charge']]);
 insert('payments',['invoice_id'=>$historic,'user_id'=>$id('tenant'),'amount'=>(float)$t['rent']+(float)$t['utility_charge'],'method'=>'bank_transfer','reference'=>'DEMO-SAMPLE-confirmed','status'=>'confirmed','reviewed_by'=>$id('supervisor'),'confirmed_by'=>$id('finance'),'confirmed_at'=>date('Y-m-d H:i:s')]);
 insert('bank_accounts',['user_id'=>$id('owner'),'bank_name'=>'DEMO Bank (fictional)','account_name'=>$accounts['owner']['name'],'account_number'=>'000000001234','routing_code'=>'000000000']);
 $campaign=one('SELECT campaign_id FROM campaign_assignments WHERE user_id=? LIMIT 1',[$id('field_agent')]);
 if($campaign)insert('campaign_checklists',['campaign_id'=>$campaign['campaign_id'],'user_id'=>$id('field_agent'),'completed_on'=>date('Y-m-d'),'owner_name'=>'DEMO: prospective property owner','owner_phone'=>'00000000000','checklist'=>'DEMO: introduction completed; rental needs discussed; follow-up agreed; no personal documents collected.']);
 insert('support_tickets',['user_id'=>$id('tenant'),'property_id'=>$t['property_id'],'assigned_to'=>$id('supervisor'),'category'=>'callback','subject'=>'DEMO: help understanding the monthly bill','message'=>'Please explain how groceries and catering are added to the rent statement.','contact_channel'=>'call']);
 insert('complaints',['tenancy_id'=>$t['id'],'filed_by'=>$id('tenant'),'about_user'=>$id('owner'),'description'=>'DEMO: clarify shared corridor cleaning responsibilities.','status'=>'reviewing']);
 insert('referrals',['referrer_id'=>$id('tenant'),'referred_email'=>'demo-friend@example.invalid','status'=>'invited']);
 foreach($roles as $role)notify($id($role),'Demo workspace ready','Illustrative records have been added for your '.$role.' role. No real payments or services took place.','workspace');
 notify($id('tenant'),'Demo service entry code','DEMO bathroom tap service entry code: 135790. Expires seven days after seeding.','workspace');
 notify($id('tenant'),'Demo inspection entry code','DEMO inspection entry code: 246810. Expires seven days after seeding.','workspace');
 audit(['id'=>$id('admin')],'demo.seed','tenancies',(int)$t['id'],['marker'=>'demo-scenarios-v1','roles'=>$roles]);
 insert('job_runs',['job_key'=>'demo-scenarios-v1']);
}
$db->commit();
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
$existing=file_get_contents(__DIR__.'/../storage/credentials.md');
if(!preg_match('/Admin password: (.+)/',$existing,$match))throw new RuntimeException('Admin credential record missing.');
$adminPassword=trim($match[1]);
if(!password_verify($adminPassword,$accounts['admin']['password_hash']))throw new RuntimeException('Recorded admin credential has changed.');
$out="# Rent Guardians local demo accounts\n\nWebsite: http://127.0.0.1:8000\n\n| Role | Name | Email | Password |\n| --- | --- | --- | --- |\n";
foreach($roles as $role){$a=$accounts[$role];$password=$role==='admin'?$adminPassword:'Guardian@2026!';if(!password_verify($password,$a['password_hash']))throw new RuntimeException('Demo credential changed: '.$role);$out.="| $role | {$a['name']} | {$a['email']} | $password |\n";}
$out.="\n## What to explore\n\n- Tenant: active home, signed contract, bills, grocery order, lunch subscription, service history, referral and support request.\n- Owner: six listings, rental household, repair decisions, condition reports and fictional bank account.\n- Provider: available electrical job, assigned plumbing job, AC job in progress, completed job, grocery delivery and catering customers.\n- Agent: assigned inspection appointment, resident details and submitted room report.\n- Supervisor: inspection approval, payment review, requests, complaint and callback ticket.\n- Finance: reviewed payment ready for confirmation, submitted payment awaiting review and paid historical statement.\n- Field agent: assigned outreach campaign, training and sample checklist.\n- Admin: all users, datasets, linked records, SQL reports and seed audit entry.\n\n## Demo entry codes\n\nAssigned bathroom tap service: **135790**. Inspection appointment: **246810**. Each expires seven days after the demo seed and can be verified only once.\n\nAll DEMO records are fictional. Sample payments do not represent real bank transfers. The seeder is additive and will not duplicate scenarios when rerun.\n";
file_put_contents(__DIR__.'/../storage/demo-credentials.md',$out);
echo "Demo scenarios ready for all eight roles. Credentials: storage/demo-credentials.md\n";
