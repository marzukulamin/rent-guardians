<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
if(($config['environment']??'')!=='local')throw new RuntimeException('Local database required.');
function qi(string $name):string{return '`'.str_replace('`','``',$name).'`';}
$tables=array_column(rows("SELECT TABLE_NAME name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME"),'name');
$columns=rows("SELECT TABLE_NAME t,COLUMN_NAME c,DATA_TYPE type,COLUMN_KEY k FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY ORDINAL_POSITION");
$pk=[];$textCols=[];foreach($columns as $c){if($c['k']==='PRI')$pk[$c['t']][]=$c['c'];if(in_array($c['type'],['char','varchar','text','mediumtext','longtext','tinytext','json']))$textCols[$c['t']][]=$c['c'];}
$fks=rows("SELECT TABLE_NAME child,COLUMN_NAME col,REFERENCED_TABLE_NAME parent,REFERENCED_COLUMN_NAME ref FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
$marked=[];$counts=[];$roots=[];
$key=fn($table,$row)=>json_encode(array_map(fn($c)=>$row[$c],$pk[$table]??[]));
$mark=function($table,$row)use(&$marked,$key,$pk){if(empty($pk[$table]))throw new RuntimeException('Primary key required: '.$table);$k=$key($table,$row);if(isset($marked[$table][$k]))return false;$marked[$table][$k]=$row;return true;};
foreach($tables as $table){
 $counts[$table]=(int)one('SELECT COUNT(*) n FROM '.qi($table))['n'];
 $cols=$textCols[$table]??[];if(!$cols)continue;
 $match=implode(' OR ',array_map(fn($c)=>qi($c).' REGEXP ?',$cols));
 $found=rows('SELECT * FROM '.qi($table).' WHERE '.$match,array_fill(0,count($cols),'QA[_ ]TEST'));
 $roots[$table]=count($found);foreach($found as $row)$mark($table,$row);
}
do{
 $changed=false;
 foreach($fks as $fk){
  $values=array_unique(array_column($marked[$fk['parent']]??[],$fk['ref']));
  foreach(array_chunk($values,500) as $chunk){foreach(rows('SELECT * FROM '.qi($fk['child']).' WHERE '.qi($fk['col']).' IN ('.implode(',',array_fill(0,count($chunk),'?')).')',$chunk) as $row)$changed=$mark($fk['child'],$row)||$changed;}
 }
 // Photo paths and audit entities are application relationships, not SQL FKs.
 foreach($marked['documents']??[] as $doc)foreach(rows('SELECT * FROM property_photos WHERE path=?',['/files/photo/'.$doc['id']]) as $row)$changed=$mark('property_photos',$row)||$changed;
 foreach($marked as $table=>$records){if($table==='audit_logs'||!isset($pk[$table])||$pk[$table]!==['id'])continue;$ids=array_column($records,'id');foreach(array_chunk($ids,500) as $chunk)foreach(rows('SELECT * FROM audit_logs WHERE entity=? AND entity_id IN ('.implode(',',array_fill(0,count($chunk),'?')).')',[$table,...$chunk]) as $row)$changed=$mark('audit_logs',$row)||$changed;}
 // Remove only invoices emptied entirely by fixture cleanup.
 foreach(array_unique(array_column($marked['invoice_items']??[],'invoice_id')) as $id){
  $items=rows('SELECT * FROM invoice_items WHERE invoice_id=?',[$id]);
  if($items&&!array_filter($items,fn($r)=>!isset($marked['invoice_items'][$key('invoice_items',$r)]))){$invoice=one('SELECT * FROM invoices WHERE id=?',[$id]);if($invoice)$changed=$mark('invoices',$invoice)||$changed;}
 }
}while($changed);
$plan=array_map('count',$marked);
$report=['database'=>$config['database'],'tables_scanned'=>count($tables),'before_counts'=>$counts,'qa_roots'=>array_filter($roots),'removal_plan'=>$plan];
$folder=__DIR__.'/../storage/data-audit';if(!is_dir($folder))mkdir($folder,0700,true);
if(in_array('--apply',$argv,true)&&$marked){
 $backup=$folder.'/qa-backup-'.date('Ymd-His').'.json';
 if(file_put_contents($backup,json_encode(['created_at'=>date(DATE_ATOM),'database'=>$config['database'],'records'=>$marked],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),LOCK_EX)===false)throw new RuntimeException('Backup failed; no cleanup performed.');
 $order=[];$visited=[];$visit=function($table)use(&$visit,&$order,&$visited,$fks){if(isset($visited[$table]))return;$visited[$table]=true;foreach($fks as $fk)if($fk['parent']===$table)$visit($fk['child']);$order[]=$table;};foreach($tables as $table)$visit($table);
 $db->beginTransaction();try{
  foreach($order as $table)foreach($marked[$table]??[] as $row){$where=implode(' AND ',array_map(fn($c)=>qi($c).'=?',$pk[$table]));run('DELETE FROM '.qi($table).' WHERE '.$where,array_map(fn($c)=>$row[$c],$pk[$table]));}
  $db->commit();$report['applied']=true;$report['backup']=basename($backup);
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
$orphans=[];
foreach($fks as $fk){$n=(int)one('SELECT COUNT(*) n FROM '.qi($fk['child']).' c LEFT JOIN '.qi($fk['parent']).' p ON c.'.qi($fk['col']).'=p.'.qi($fk['ref']).' WHERE c.'.qi($fk['col']).' IS NOT NULL AND p.'.qi($fk['ref']).' IS NULL')['n'];if($n)$orphans[]=$fk+['missing'=>$n];}
$report['foreign_keys_checked']=count($fks);$report['orphans']=$orphans;
file_put_contents($folder.'/latest.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
echo json_encode(['tables_scanned'=>count($tables),'qa_roots'=>$report['qa_roots'],'removal_plan'=>$plan,'applied'=>$report['applied']??false,'foreign_keys_checked'=>count($fks),'orphans'=>$orphans],JSON_PRETTY_PRINT)."\n";
