<?php
// Called inside the API transaction. Serialize changes against the parent property.
$u=auth();$pid=(int)$pm[1];property_access($pid,$u,true);
one('SELECT id FROM properties WHERE id=? FOR UPDATE',[$pid]);
$action=choice($b,'action',['cover','move','caption','remove']);
$photo=one('SELECT * FROM property_photos WHERE id=? AND property_id=? AND deleted_at IS NULL FOR UPDATE',[(int)number($b,'photo_id',1),$pid]);
if(!$photo)fail('Photo not found.',404);
$photos=rows('SELECT id FROM property_photos WHERE property_id=? AND deleted_at IS NULL ORDER BY sort_order,id FOR UPDATE',[$pid]);
$ids=array_map('intval',array_column($photos,'id'));$index=array_search((int)$photo['id'],$ids,true);
if($action==='remove')run('UPDATE property_photos SET deleted_at=NOW() WHERE id=?',[$photo['id']]);
elseif($action==='caption')run('UPDATE property_photos SET caption=?,category=? WHERE id=?',[textfield($b,'caption',150),choice($b,'category',['Living room','Bedroom','Kitchen','Washroom','Balcony','Building exterior','Parking','Other'],'Other'),$photo['id']]);
else{
 if($action==='cover'){array_splice($ids,$index,1);array_unshift($ids,(int)$photo['id']);}
 else{$direction=choice($b,'direction',['up','down']);$target=$index+($direction==='up'?-1:1);if(isset($ids[$target]))[$ids[$index],$ids[$target]]=[$ids[$target],$ids[$index]];}
 foreach($ids as $order=>$photoId)run('UPDATE property_photos SET sort_order=? WHERE id=?',[$order,$photoId]);
}
audit($u,'photo_'.$action,'property_photos',(int)$photo['id']);$result=['ok'=>true];
