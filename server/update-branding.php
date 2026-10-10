<?php
// Update stored service copy without touching account identifiers or signed contracts.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local environment required.');
$counts=[];$db->beginTransaction();
try {
 foreach(['training_materials'=>['title','body'],'notifications'=>['title','message']] as $table=>$fields){
  $counts[$table]=0;
  foreach(rows('SELECT id,'.implode(',',$fields).' FROM '.$table.' WHERE '.implode(' OR ',array_map(fn($field)=>$field." LIKE '%Rent Guardian%'",$fields))) as $record){
   $updates=[];$args=[];
   foreach($fields as $field){$value=preg_replace('/\bRent Guardian\b/i','Rent Guardians',(string)$record[$field]);if($value!==$record[$field]){$updates[]=$field.'=?';$args[]=$value;}}
   if($updates){$args[]=$record['id'];run('UPDATE '.$table.' SET '.implode(',',$updates).' WHERE id=?',$args);$counts[$table]++;}
  }
 }
 $db->commit();echo json_encode($counts,JSON_PRETTY_PRINT).PHP_EOL;
}catch(Throwable $e){$db->rollBack();throw $e;}
