<?php
$u=need(['owner','tenant','admin','supervisor']);
if($method==='GET')respond(one('SELECT payload,updated_at FROM listing_drafts WHERE user_id=?',[$u['id']]));
if(!empty($b['clear']))run('DELETE FROM listing_drafts WHERE user_id=?',[$u['id']]);
else{
 $values=$b['values']??null;if(!is_array($values))fail('Invalid listing draft.');
 $allowed=['title','kind','description','bedrooms','bathrooms','balconies','size_sqft','garage','location_id','house_name','house_no','road_no','floor_no','flat_no','latitude','longitude','rent','utility_charge','advance_months','tenant_preference','available_from','utilities','gate_closes','lift_closes','house_rules','tour_url'];
 $values=array_intersect_key($values,array_flip($allowed));$payload=json_encode($values,JSON_UNESCAPED_UNICODE);if(strlen($payload)>20000)fail('Draft is too large.');
 run('INSERT INTO listing_drafts(user_id,payload) VALUES(?,?) ON DUPLICATE KEY UPDATE payload=VALUES(payload),updated_at=CURRENT_TIMESTAMP',[$u['id'],$payload]);
 audit($u,'listing_draft_saved','users',(int)$u['id']);
}
$result=['ok'=>true];
