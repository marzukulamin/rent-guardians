<?php
$table=$path==='provider/products'?'catalog_products':'meal_plans';$id=(int)number($b,'id',1);$old=one('SELECT * FROM '.$table.' WHERE id=? AND provider_id=? FOR UPDATE',[$id,$u['id']]);if(!$old)fail('This item is not in your catalog.',403);
$data=['name'=>textfield($b,'name',150),'description'=>textfield($b,'description',3000,false),'active'=>(int)!empty($b['active'])];
if($table==='catalog_products'){$data+=['category_id'=>(int)number($b,'category_id',1),'unit'=>textfield($b,'unit',40),'price'=>number($b,'price'),'installation_cost'=>number($b,'installation_cost'),'recurring_cost'=>number($b,'recurring_cost')];}
else $data+=['meal'=>choice($b,'meal',['breakfast','lunch','dinner']),'price_per_person'=>number($b,'price_per_person',0.01)];
run('UPDATE '.$table.' SET '.implode(',',array_map(fn($k)=>'`'.$k.'`=?',array_keys($data))).' WHERE id=?',[...array_values($data),$id]);audit($u,'provider_catalog_updated',$table,$id,$data);$result=['id'=>$id];
