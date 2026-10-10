<?php
// Batch only children of already authorized parent records. Never widen a scope here.
function attach_children(array &$parents,string $key,string $foreignKey,string $sql):void {
 $ids=array_values(array_unique(array_map('intval',array_column($parents,'id'))));$groups=[];
 if($ids){foreach(rows(str_replace(':ids',implode(',',$ids),$sql)) as $child)$groups[(int)$child[$foreignKey]][]=$child;}
 foreach($parents as &$parent)$parent[$key]=$groups[(int)$parent['id']]??[];
}
function attach_inspection_rooms(array &$inspections):void {
 attach_children($inspections,'rooms','inspection_id','SELECT * FROM inspection_rooms WHERE inspection_id IN(:ids) ORDER BY id');
 $rooms=[];foreach($inspections as $inspection)foreach($inspection['rooms'] as $room)$rooms[]=$room;
 attach_children($rooms,'items','room_id','SELECT id,room_id,component,condition_status,permanent_damage,description,estimate,repair_decision FROM inspection_items WHERE room_id IN(:ids) ORDER BY id');
 $byId=[];foreach($rooms as $room)$byId[(int)$room['id']]=$room;
 foreach($inspections as &$inspection)foreach($inspection['rooms'] as &$room)$room=$byId[(int)$room['id']];
}
function attach_one(array &$parents,string $key,string $sql):void {
 $ids=array_values(array_unique(array_map('intval',array_column($parents,'id'))));$children=[];
 if($ids)foreach(rows(str_replace(':ids',implode(',',$ids),$sql)) as $child){$id=(int)$child['parent_id'];unset($child['parent_id']);$children[$id]=$child;}
 foreach($parents as &$parent)if(isset($children[(int)$parent['id']]))$parent[$key]=$children[(int)$parent['id']];
}
function public_properties(array $properties,?array $viewer):array {
 $authorized=[];$ids=implode(',',array_map('intval',array_column($properties,'id')));
 if($viewer&&$ids&&!has($viewer,['admin','supervisor'])){
  $uid=$viewer['id'];$authorized=array_fill_keys(array_column(rows("SELECT property_id FROM appointments WHERE property_id IN($ids) AND status IN('confirmed','completed') AND (requested_by=? OR assigned_to=?) UNION SELECT t.property_id FROM tenancies t WHERE t.property_id IN($ids) AND t.status IN('active','notice') AND (t.head_tenant_id=? OR EXISTS(SELECT 1 FROM tenancy_members tm WHERE tm.tenancy_id=t.id AND tm.user_id=?))",[$uid,$uid,$uid,$uid]),'property_id'),true);
 }
 return array_map(fn($p)=>public_property($p,$viewer,(bool)($viewer&&($p['owner_id']==$viewer['id']||has($viewer,['admin','supervisor'])||isset($authorized[$p['id']])))),$properties);
}
