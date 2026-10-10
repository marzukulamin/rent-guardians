<?php
declare(strict_types=1);
$queries=[];
function rows(string $sql):array {
 global $queries;$queries[]=$sql;
 if(str_contains($sql,'inspection_rooms'))return [['id'=>7,'inspection_id'=>1],['id'=>8,'inspection_id'=>2]];
 if(str_contains($sql,'inspection_items'))return [['id'=>11,'room_id'=>7],['id'=>12,'room_id'=>8]];
 return [['id'=>1,'invoice_id'=>1],['id'=>2,'invoice_id'=>2],['id'=>3,'invoice_id'=>2]];
}
require __DIR__.'/../server/batch.php';
function check(bool $ok,string $message):void {if(!$ok)throw new RuntimeException($message);}
$parents=[['id'=>1],['id'=>2],['id'=>3]];
attach_children($parents,'items','invoice_id','SELECT * FROM invoice_items WHERE invoice_id IN(:ids)');
check(count($queries)===1,'Children must require one query.');
check(count($parents[0]['items'])===1&&count($parents[1]['items'])===2&&$parents[2]['items']===[],'Grouping and empty parents must be correct.');
$queries=[];$parents=[];attach_children($parents,'items','invoice_id','SELECT * FROM invoice_items WHERE invoice_id IN(:ids)');check(!$queries,'Empty parents must not query all children.');
$parents=[['id'=>1],['id'=>2]];attach_inspection_rooms($parents);
check(count($queries)===2,'Inspection trees must require two queries.');
check($parents[0]['rooms'][0]['items'][0]['id']===11&&$parents[1]['rooms'][0]['items'][0]['id']===12,'Inspection items must stay with their own authorized parent.');
echo "Batch grouping, empty scopes and inspection trees passed.\n";
