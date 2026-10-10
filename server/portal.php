<?php
// Presentation isolation: keep marked fixtures available to developer tests and
// the authorized admin explorer, but never relabel them as customer activity.
function fixture_record(array $record,bool $qaOnly=false):bool {
 foreach($record as $key=>$value){
  if(is_string($value)&&preg_match($qaOnly?'/QA[_ ]TEST/i':'/QA[_ ]TEST|\bDEMO\b|functional demo|seeded scenario/i',$value))return true;
  if(is_array($value)&&fixture_record($value,$qaOnly))return true;
 }
 return false;
}
function portal_workspace(array $data,bool $qaOnly=false):array {
 if(array_is_list($data))return array_values(array_filter($data,fn($record)=>!is_array($record)||!fixture_record($record,$qaOnly)));
 $hiddenTenancies=[];
 foreach($data['tenancies']??[] as $tenancy)if(fixture_record($tenancy,$qaOnly))$hiddenTenancies[(int)$tenancy['id']]=true;
 $hiddenInvoices=[];
 foreach($data['invoices']??[] as $invoice)if(fixture_record($invoice,$qaOnly)||isset($hiddenTenancies[(int)($invoice['tenancy_id']??0)]))$hiddenInvoices[(int)$invoice['id']]=true;
 foreach($data as $key=>$records){
  if(!is_array($records)||!array_is_list($records))continue;
  $data[$key]=array_values(array_filter($records,fn($record)=>!is_array($record)||(!fixture_record($record,$qaOnly)&&!isset($hiddenTenancies[(int)($record['tenancy_id']??0)])&&!isset($hiddenInvoices[(int)($record['invoice_id']??0)]))));
 }
 return $data;
}
