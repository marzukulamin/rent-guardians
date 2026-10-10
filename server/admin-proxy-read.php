<?php
// Read a portal as its account without replacing the administrator session.
$adminActor=need(['admin']);$targetId=(int)$proxyReadMatch[1];
$requested=(string)($_GET['path']??'');$proxyPath=parse_url($requested,PHP_URL_PATH);
if(!is_string($proxyPath)||!preg_match('#^(properties(?:/\d+)?|listing-draft|finance/report)$#',$proxyPath))fail('Choose an available portal read.');
$actingUser=one('SELECT id,public_id,name,email,phone,status,verified_at FROM users WHERE id=?',[$targetId]);if(!$actingUser)fail('Account not found.',404);
$actingUser['roles']=array_column(rows('SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',[$targetId]),'name');
parse_str(parse_url($requested,PHP_URL_QUERY)??'',$_GET);$path=$proxyPath;
