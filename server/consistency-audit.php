<?php
/** Read-only semantic audit. Reports IDs and counts, never credentials or personal documents. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
$checks=[
 'head_missing_member'=>"SELECT t.id FROM tenancies t WHERE NOT EXISTS(SELECT 1 FROM tenancy_members m WHERE m.tenancy_id=t.id AND m.user_id=t.head_tenant_id)",
 'multiple_current_tenancies'=>"SELECT MIN(id) id FROM tenancies WHERE status<>'ended' GROUP BY property_id HAVING COUNT(*)>1",
 'active_property_status'=>"SELECT t.id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.status IN('active','notice') AND p.status<>'rented'",
 'signature_party'=>"SELECT s.contract_id id FROM contract_signatures s JOIN contracts c ON c.id=s.contract_id JOIN tenancies t ON t.id=c.tenancy_id JOIN properties p ON p.id=t.property_id WHERE (s.signer_role='owner' AND s.user_id<>p.owner_id) OR (s.signer_role='tenant' AND s.user_id<>t.head_tenant_id AND NOT EXISTS(SELECT 1 FROM tenancy_transfers tr WHERE tr.tenancy_id=t.id AND tr.from_user_id=s.user_id AND tr.status='approved'))",
 'owner_is_head_tenant'=>'SELECT t.id FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.head_tenant_id=p.owner_id',
 'head_without_tenant_role'=>"SELECT t.id FROM tenancies t WHERE NOT EXISTS(SELECT 1 FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=t.head_tenant_id AND r.name='tenant')",
 'owner_without_owner_role'=>"SELECT p.id FROM properties p WHERE NOT EXISTS(SELECT 1 FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=p.owner_id AND r.name='owner')",
 'linked_member_identity'=>'SELECT m.id FROM tenancy_members m JOIN users u ON u.id=m.user_id WHERE NOT(m.name<=>u.name) OR NOT(m.phone<=>u.phone)',
 'incorrect_head_label'=>"SELECT m.id FROM tenancy_members m JOIN tenancies t ON t.id=m.tenancy_id WHERE m.relationship='Head tenant' AND (m.user_id IS NULL OR m.user_id<>t.head_tenant_id)",
 'duplicate_linked_member'=>'SELECT MIN(id) id FROM tenancy_members WHERE user_id IS NOT NULL GROUP BY tenancy_id,user_id HAVING COUNT(*)>1',
 'inspection_appointment'=>'SELECT i.id FROM inspections i JOIN appointments a ON a.id=i.appointment_id WHERE i.property_id<>a.property_id OR i.agent_id<>a.assigned_to',
 'visit_appointment'=>'SELECT v.id FROM property_visits v JOIN appointments a ON a.id=v.appointment_id WHERE v.property_id<>a.property_id OR v.user_id<>a.requested_by',
 'document_tenancy_property'=>'SELECT d.id FROM documents d JOIN tenancies t ON t.id=d.tenancy_id WHERE d.property_id IS NOT NULL AND d.property_id<>t.property_id',
 'order_product_provider'=>'SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN catalog_products c ON c.id=oi.product_id WHERE c.provider_id<>o.provider_id',
 'order_category'=>"SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN catalog_products c ON c.id=oi.product_id JOIN service_categories s ON s.id=c.category_id WHERE s.kind<>o.kind",
 'order_invoice_identity'=>'SELECT ii.id FROM invoice_items ii JOIN orders o ON o.id=ii.order_id JOIN invoices i ON i.id=ii.invoice_id WHERE i.user_id<>o.user_id OR NOT(i.tenancy_id<=>o.tenancy_id)',
 'order_billing_total'=>'SELECT ii.id FROM invoice_items ii JOIN orders o ON o.id=ii.order_id WHERE ABS(ii.amount-(SELECT SUM(quantity*(unit_price+installation_cost)) FROM order_items WHERE order_id=o.id))>0.01',
 'subscription_invoice_identity'=>'SELECT ii.id FROM invoice_items ii JOIN meal_subscriptions s ON s.id=ii.subscription_id JOIN invoices i ON i.id=ii.invoice_id WHERE i.user_id<>s.user_id OR NOT(i.tenancy_id<=>s.tenancy_id)',
 'maintenance_invoice_tenancy'=>'SELECT ii.id FROM invoice_items ii JOIN maintenance_requests m ON m.id=ii.request_id JOIN invoices i ON i.id=ii.invoice_id WHERE NOT(i.tenancy_id<=>m.tenancy_id)',
 'payment_invoice_user'=>'SELECT p.id FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE p.user_id<>i.user_id',
 'paid_invoice_balance'=>"SELECT id FROM v_invoice_balances WHERE status='paid' AND ABS(balance)>0.01",
 'overpaid_invoice'=>'SELECT id FROM v_invoice_balances WHERE balance< -0.01',
 'payment_separation'=>"SELECT id FROM payments WHERE status='confirmed' AND (reviewed_by IS NULL OR confirmed_by IS NULL OR reviewed_by=confirmed_by)",
 'signature_contract_hash'=>'SELECT s.contract_id id FROM contract_signatures s JOIN contracts c ON c.id=s.contract_id WHERE s.content_hash<>c.content_hash',
 'review_order_relation'=>'SELECT r.id FROM reviews r JOIN orders o ON o.id=r.order_id WHERE r.tenancy_id<>o.tenancy_id OR r.author_id<>o.user_id OR r.subject_id<>o.provider_id',
 'review_request_relation'=>'SELECT r.id FROM reviews r JOIN maintenance_requests m ON m.id=r.request_id WHERE r.tenancy_id<>m.tenancy_id OR r.subject_id<>m.provider_id',
 'reward_request_tenancy'=>'SELECT r.id FROM reward_ledger r JOIN maintenance_requests m ON m.id=r.request_id JOIN tenancies t ON t.id=m.tenancy_id WHERE r.user_id<>m.requested_by AND r.user_id<>t.head_tenant_id',
 'location_hierarchy'=>"SELECT l.id FROM locations l LEFT JOIN locations p ON p.id=l.parent_id WHERE (l.kind='division' AND l.parent_id IS NOT NULL) OR (l.kind='district' AND (p.kind IS NULL OR p.kind<>'division')) OR (l.kind='area' AND (p.kind IS NULL OR p.kind<>'district')) OR (l.kind='sub_area' AND (p.kind IS NULL OR p.kind<>'area'))",
 'provider_without_role'=>"SELECT p.user_id id FROM provider_profiles p WHERE NOT EXISTS(SELECT 1 FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=p.user_id AND r.name='provider')",
 'agent_without_role'=>"SELECT a.id FROM appointments a WHERE a.assigned_to IS NOT NULL AND NOT EXISTS(SELECT 1 FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=a.assigned_to AND r.name='agent')",
 'inspection_photo_document'=>"SELECT i.id FROM inspection_items i LEFT JOIN documents d ON d.id=i.photo_document_id JOIN inspection_rooms r ON r.id=i.room_id JOIN inspections s ON s.id=r.inspection_id WHERE i.photo_document_id IS NOT NULL AND (d.id IS NULL OR d.kind<>'inspection_photo' OR d.property_id<>s.property_id)",
 'campaign_assignment'=> 'SELECT c.id FROM campaign_checklists c WHERE NOT EXISTS(SELECT 1 FROM campaign_assignments a WHERE a.campaign_id=c.campaign_id AND a.user_id=c.user_id)',
 'referral_identity'=>'SELECT r.id FROM referrals r JOIN users u ON u.id=r.referred_user_id WHERE r.referred_email<>u.email',
];
$report=['checked_at'=>date(DATE_ATOM),'tables'=>[],'foreign_keys_checked'=>0,'orphans'=>[],'checks'=>[]];
foreach(rows("SELECT TABLE_NAME name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'") as $table){$name=str_replace('`','``',$table['name']);$report['tables'][$table['name']]=(int)one("SELECT COUNT(*) n FROM `$name`")['n'];}
foreach(rows('SELECT TABLE_NAME child,COLUMN_NAME col,REFERENCED_TABLE_NAME parent,REFERENCED_COLUMN_NAME ref FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL') as $fk){foreach($fk as &$identifier)$identifier=str_replace('`','``',$identifier);unset($identifier);$n=(int)one("SELECT COUNT(*) n FROM `{$fk['child']}` c LEFT JOIN `{$fk['parent']}` p ON c.`{$fk['col']}`=p.`{$fk['ref']}` WHERE c.`{$fk['col']}` IS NOT NULL AND p.`{$fk['ref']}` IS NULL")['n'];$report['foreign_keys_checked']++;if($n)$report['orphans'][]=$fk+['count'=>$n];}
foreach($checks as $key=>$sql){$found=rows($sql);$report['checks'][$key]=['count'=>count($found),'ids'=>array_column($found,'id')];}
$report['same_named_parties']=rows('SELECT t.id,t.head_tenant_id,p.owner_id,u.name tenant_name,o.name owner_name FROM tenancies t JOIN properties p ON p.id=t.property_id JOIN users u ON u.id=t.head_tenant_id JOIN users o ON o.id=p.owner_id WHERE u.name=o.name');
$report['screenshot_tenancy']=one('SELECT t.id,t.head_tenant_id,p.owner_id,u.name tenant_name,o.name owner_name,u.public_id tenant_public_id,o.public_id owner_public_id FROM tenancies t JOIN properties p ON p.id=t.property_id JOIN users u ON u.id=t.head_tenant_id JOIN users o ON o.id=p.owner_id WHERE t.id=13');
// Validate application-only file/photo references and actual stored content.
$files=[];foreach(rows('SELECT id,storage_path,size_bytes FROM documents') as $d){$path=__DIR__.'/../storage/uploads/'.$d['storage_path'];if(!is_file($path)||filesize($path)!=(int)$d['size_bytes'])$files[]=(int)$d['id'];}
$report['checks']['document_files']=['count'=>count($files),'ids'=>$files];
$photos=rows("SELECT p.id FROM property_photos p LEFT JOIN documents d ON p.path=CONCAT('/files/photo/',d.id) WHERE p.deleted_at IS NULL AND p.path LIKE '/files/photo/%' AND (d.id IS NULL OR d.property_id<>p.property_id OR d.kind<>'property_photo')");
$report['checks']['photo_document_relation']=['count'=>count($photos),'ids'=>array_column($photos,'id')];
$report['checks']['contract_body_hash']=['count'=>0,'ids'=>[]];foreach(rows('SELECT id,body,content_hash FROM contracts') as $c)if(hash('sha256',$c['body'])!==$c['content_hash']){$report['checks']['contract_body_hash']['ids'][]=$c['id'];$report['checks']['contract_body_hash']['count']++;}
$folder=__DIR__.'/../storage/data-audit';if(!is_dir($folder))mkdir($folder,0700,true);
$report['failures']=array_sum(array_column($report['checks'],'count'))+count($report['orphans']);
file_put_contents($folder.'/consistency.json',json_encode($report,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo json_encode($report,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
exit($report['failures']?1:0);
