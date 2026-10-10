<?php
// Only fictional allowlisted accounts can be accessed through the local demo.
if (($config['environment'] ?? '') !== 'local') fail('Demo sign-in is unavailable.',404);
$role = (string)($b['role'] ?? '');
if (!in_array($role,['tenant','owner','provider','agent','supervisor','finance','field_agent','admin'],true)) fail('Choose a demo role.');
$email = $role==='admin' ? 'demo-admin@rentguardian.local' : $role.'@rentguardian.local';
$u = one('SELECT * FROM users WHERE email=?',[sprintf('demo.%s.001@rentguardian.local',$role)]) ?? one('SELECT * FROM users WHERE email=?',[$email]);
if (isset($b['account_id'])) {
 $u=one('SELECT * FROM users WHERE id=?',[(int)number($b,'account_id',1)]);
 if (!$u || !($u['email']===$email || preg_match('/^demo\.'.preg_quote($role,'/').'\.\d{3}@rentguardian\.local$/D',$u['email']))) fail('Only fictional demo accounts can be selected.',403);
}
if (!$u || $u['status']!=='active') fail('This demo account is unavailable. Run the demo catalog seeder.',503);
if (!one('SELECT ur.user_id FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? AND r.name=?',[$u['id'],$role])) fail('Demo role is unavailable.',503);
session_regenerate_id(true);
$_SESSION['user_id']=$u['id'];
$_SESSION['csrf']=bin2hex(random_bytes(32));
audit($u,'demo.login','users',(int)$u['id'],['role'=>$role]);
$result=['user'=>user(),'csrf'=>$_SESSION['csrf']];
