<?php
// One request on behalf of an account. Never changes the administrator session.
$adminActor=need(['admin']);$targetId=(int)$proxyMatch[1];$proxyPath=textfield($b,'path',150);$proxyBody=$b['data']??[];if(!is_array($proxyBody))fail('Enter valid action details.');
$allowed=['profile','preferences','bank','saved','properties','appointments','tenancies','maintenance','orders','payments','meals','support','reviews','complaints','referrals','redeem','transfers','provider/profile','provider/products','provider/meals','campaigns/checklist','inspections','listing-draft','notifications/read','operations/note','operations/support','operations/notify'];
$valid=in_array($proxyPath,$allowed,true)||preg_match('#^(appointments|tenancies|maintenance|orders|transfers)/\d+/action$#',$proxyPath)||preg_match('#^properties/\d+/photos$#',$proxyPath);
if(!$valid||($proxyBody['action']??'')==='sign')fail('This action must be completed by the account holder.');
$actingUser=one("SELECT id,public_id,name,email,phone,status,verified_at FROM users WHERE id=? AND status='active' AND deleted_at IS NULL",[$targetId]);if(!$actingUser)fail('Use an active account for portal actions.');$actingUser['roles']=array_column(rows('SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',[$targetId]),'name');
$path=$proxyPath;$b=$proxyBody;
