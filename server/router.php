<?php
$path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if($path==='/health/ready'){require __DIR__.'/health.php';return true;}
if(str_starts_with($path, '/api/')) { require __DIR__.'/api.php'; return true; }
if(str_starts_with($path, '/files/')) { require __DIR__.'/files.php'; return true; }
$root=realpath(__DIR__.'/../dist');
$file=$root ? realpath($root.$path) : false;
if($file && str_starts_with($file,$root.DIRECTORY_SEPARATOR) && is_file($file)) {
 $types=['js'=>'application/javascript','css'=>'text/css','png'=>'image/png','jpg'=>'image/jpeg','webp'=>'image/webp','svg'=>'image/svg+xml','woff2'=>'font/woff2','pdf'=>'application/pdf'];
 header('X-Content-Type-Options: nosniff');
 header('Cache-Control: '.(str_starts_with($path,'/assets/')?'public, max-age=31536000, immutable':'public, max-age=300'));
 header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)]??'application/octet-stream')); readfile($file); return true;
}
if($root && is_file($root.'/index.html')) {header('Cache-Control: no-cache');header('Content-Type: text/html; charset=utf-8');readfile($root.'/index.html');return true;}
http_response_code(503);echo 'Run npm run build before starting the production preview.';
