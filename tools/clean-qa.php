<?php
// Compatibility entry point: complete backed-up, dependency-aware QA cleanup.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(!in_array('--apply',$argv,true))$argv[]='--apply';
require __DIR__.'/../server/data-audit.php';