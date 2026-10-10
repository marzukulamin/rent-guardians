<?php
// Schedule every 5 minutes. Delete only expired traffic counters, never business data.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
session_write_close();
$removed=0;
for($batch=0;$batch<20;$batch++){
 $count=run('DELETE FROM request_budgets WHERE expires_at<? ORDER BY expires_at LIMIT 5000',[time()]);
 $removed+=$count;
 if($count<5000)break;
}
echo "Expired request counters removed: $removed\n";
