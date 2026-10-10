<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
$summary=json_decode(file_get_contents(__DIR__.'/../storage/demo-population.json'),true,512,JSON_THROW_ON_ERROR);
$checks=[];
function check(string $label,bool $ok):void{global $checks;$checks[$label]=$ok;if(!$ok)throw new RuntimeException('Failed: '.$label);}
foreach($summary['account_ids'] as $role=>$ids){$found=one("SELECT COUNT(*) n FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE r.name=? AND u.email LIKE ?",[$role,'demo.'.$role.'.%@rentguardian.local']);check('100 '.$role.' accounts',(int)$found['n']===100);}
$in=implode(',',array_map('intval',$summary['property_ids']));$tin=implode(',',array_map('intval',$summary['tenancy_ids']));
check('100 connected properties',(int)one("SELECT COUNT(*) n FROM v_property_locations WHERE id IN($in)")['n']===100);
check('60 published / 40 rented',(int)one("SELECT COUNT(*) n FROM properties WHERE id IN($in) AND status='published'")['n']===60&&(int)one("SELECT COUNT(*) n FROM properties WHERE id IN($in) AND status='rented'")['n']===40);
check('every property has two photos',(int)one("SELECT COUNT(*) n FROM (SELECT p.id FROM properties p LEFT JOIN property_photos f ON f.property_id=p.id AND f.deleted_at IS NULL WHERE p.id IN($in) GROUP BY p.id HAVING COUNT(f.id)<>2) x")['n']===0);
foreach(rows("SELECT f.property_id,f.path,d.property_id document_property,d.storage_path,d.size_bytes FROM property_photos f LEFT JOIN documents d ON f.path=CONCAT('/files/photo/',d.id) WHERE f.property_id IN($in)") as $photo){$path=__DIR__.'/../storage/uploads/'.$photo['storage_path'];check('photo '.$photo['path'],$photo['property_id']===$photo['document_property']&&is_file($path)&&filesize($path)===$photo['size_bytes']);}
check('all homes approved after inspection',(int)one("SELECT COUNT(*) n FROM properties p WHERE p.id IN($in) AND NOT EXISTS(SELECT 1 FROM inspections i WHERE i.property_id=p.id AND i.status='approved')")['n']===0);
check('40 active tenancies',(int)one("SELECT COUNT(*) n FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.id IN($tin) AND t.status='active' AND p.status='rented'")['n']===40);
check('matching signed contracts',(int)one("SELECT COUNT(*) n FROM tenancies t WHERE t.id IN($tin) AND NOT EXISTS(SELECT 1 FROM contracts c WHERE c.tenancy_id=t.id AND (SELECT COUNT(*) FROM contract_signatures s WHERE s.contract_id=c.id AND s.content_hash=c.content_hash)=2)")['n']===0);
check('verified tenancy documents',(int)one("SELECT COUNT(*) n FROM tenancies t WHERE t.id IN($tin) AND NOT EXISTS(SELECT 1 FROM documents d WHERE d.tenancy_id=t.id AND d.kind='police_verification' AND d.status='approved')")['n']===0);
check('paid invoices match confirmed payments',(int)one("SELECT COUNT(*) n FROM v_invoice_balances WHERE tenancy_id IN($tin) AND ((status='paid' AND ABS(balance)>0.01) OR balance< -0.01)")['n']===0);
check('separate payment reviewer / confirmer',(int)one("SELECT COUNT(*) n FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.tenancy_id IN($tin) AND p.status='confirmed' AND (p.reviewed_by IS NULL OR p.confirmed_by IS NULL OR p.reviewed_by=p.confirmed_by)")['n']===0);
check('service billing matches stored catalog',(int)one("SELECT COUNT(*) n FROM orders o JOIN order_items oi ON oi.order_id=o.id JOIN catalog_products c ON c.id=oi.product_id WHERE o.tenancy_id IN($tin) AND (c.provider_id<>o.provider_id OR oi.unit_price<>c.price OR oi.installation_cost<>c.installation_cost OR NOT EXISTS(SELECT 1 FROM invoice_items ii WHERE ii.order_id=o.id AND ii.amount=oi.quantity*oi.unit_price+oi.installation_cost))")['n']===0);
check('completed repairs reconciled and billed',(int)one("SELECT COUNT(*) n FROM maintenance_requests m WHERE m.tenancy_id IN($tin) AND m.status='completed' AND (m.tenant_cost<>m.provider_cost OR m.cost_confirmed_at IS NULL OR NOT EXISTS(SELECT 1 FROM invoice_items ii WHERE ii.request_id=m.id AND ii.amount=m.provider_cost))")['n']===0);
$report=['passed'=>count($checks),'checks'=>$checks,'properties'=>100,'new_users'=>800,'active_tenancies'=>40];
file_put_contents(__DIR__.'/../storage/data-audit/population-verification.json',json_encode($report,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo json_encode(['passed'=>count($checks),'new_users'=>800,'properties'=>100,'active_tenancies'=>40],JSON_PRETTY_PRINT)."\n";
