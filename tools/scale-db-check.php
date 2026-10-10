<?php
// Local-only database smoke benchmark. All 50,000 fixture accounts roll back.
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$c=require __DIR__.'/../server/config.local.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->beginTransaction();
try{
 $token=bin2hex(random_bytes(4));$insert=$db->prepare('INSERT INTO users(public_id,name,email,password_hash) VALUES '.implode(',',array_fill(0,250,'(?,?,?,?)')));
 for($batch=0;$batch<200;$batch++){$values=[];for($i=0;$i<250;$i++){$n=$batch*250+$i;$values[]='SCALE-'.$token.'-'.$n;$values[]='SCALE_TEST '.$n;$values[]='scale-'.$token.'-'.$n.'@example.invalid';$values[]='disabled-test-account';}$insert->execute($values);}
 $group='scale-'.$token;
 $s=$db->prepare("INSERT INTO invoices(user_id,period,due_date,billing_group) SELECT id,CURDATE(),CURDATE(),? FROM users WHERE public_id LIKE ?");$s->execute([$group,'SCALE-'.$token.'-%']);
 $s=$db->prepare("INSERT INTO invoice_items(invoice_id,kind,description,amount) SELECT id,'rent','SCALE_TEST',1000 FROM invoices WHERE billing_group=?");$s->execute([$group]);
 $s=$db->prepare("INSERT INTO payments(invoice_id,user_id,amount,method,reference,status) SELECT id,user_id,100,'bank_transfer',CONCAT(?,id),'confirmed' FROM invoices WHERE billing_group=?");$s->execute([$group.'-',$group]);
 $s=$db->prepare('SELECT id,user_id FROM invoices WHERE billing_group=? ORDER BY id LIMIT 1');$s->execute([$group]);$fixture=$s->fetch(PDO::FETCH_ASSOC);
 $s=$db->prepare('SELECT total,paid,balance FROM v_invoice_balances WHERE id=?');$s->execute([$fixture['id']]);$balance=$s->fetch(PDO::FETCH_ASSOC);if((float)$balance['total']!==1000.0||(float)$balance['paid']!==100.0||(float)$balance['balance']!==900.0)throw new RuntimeException('Invoice balance regression.');
 $results=['fixture_accounts'=>50000,'fixture_invoices'=>50000,'fixture_invoice_items'=>50000,'fixture_payments'=>50000,'balance_check'=>$balance,'queries'=>[]];
 foreach([
 'account_first_page'=>'SELECT id,public_id,name,email FROM users ORDER BY id LIMIT 100',
 'account_last_page'=>'SELECT id,public_id,name,email FROM users ORDER BY id LIMIT 100 OFFSET 49900',
 'account_search'=>"SELECT id,public_id,name,email FROM users WHERE name LIKE '%SCALE_TEST 499%' ORDER BY id LIMIT 100",
 'invoice_page'=>'SELECT * FROM v_invoice_balances ORDER BY id DESC LIMIT 100',
 'notification_page'=>'SELECT * FROM notifications WHERE user_id=2 ORDER BY id DESC LIMIT 100'
 ] as $name=>$sql){$start=hrtime(true);$rows=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);$results['queries'][$name]=['ms'=>round((hrtime(true)-$start)/1e6,2),'rows'=>count($rows)];}
 // Explain equivalent base-table SQL; the application account has no SHOW VIEW privilege.
 $results['invoice_explain']=$db->query("EXPLAIN SELECT i.id,(SELECT SUM(amount) FROM invoice_items WHERE invoice_id=i.id) total,(SELECT SUM(amount) FROM payments WHERE invoice_id=i.id AND status='confirmed') paid FROM invoices i WHERE i.id=".(int)$fixture['id'])->fetchAll(PDO::FETCH_ASSOC);
 $results['notification_explain']=$db->query('EXPLAIN SELECT * FROM notifications WHERE user_id=2 ORDER BY id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
 echo json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
}finally{$db->rollBack();}
