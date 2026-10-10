<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Dhaka');
$configFile=__DIR__.'/config.local.php';
if(!file_exists($configFile)) {http_response_code(503);header('Content-Type: application/json');echo json_encode(['error'=>'Database setup required. Run server/setup.php.']);exit;}
$config=require $configFile;
$db=new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",$config['user'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
// Honor a deployment's shared PHP session handler instead of forcing local files.
$sessionHandler=getenv('RG_SESSION_HANDLER')?:ini_get('session.save_handler');
if($sessionHandler==='files'){
 $sessionDir=getenv('RG_SESSION_PATH')?:__DIR__.'/../storage/sessions';
 if(!is_dir($sessionDir)&&!mkdir($sessionDir,0700,true))throw new RuntimeException('Session storage unavailable.',503);
 session_save_path($sessionDir);
}else{
 ini_set('session.save_handler',$sessionHandler);
 if(getenv('RG_SESSION_PATH'))session_save_path(getenv('RG_SESSION_PATH'));
}
session_name('rg_session');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>(($config['environment']??'local')==='production'||(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')),'httponly'=>true,'samesite'=>'Lax']);
ini_set('session.use_strict_mode','1');
$requestPath=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)??'';
$anonymousRead=($_SERVER['REQUEST_METHOD']??'')==='GET'&&!isset($_COOKIE['rg_session'])&&
 (preg_match('#^/api/(properties(?:/\d+)?|providers/\d+|lookups)$#',$requestPath)||preg_match('#^/files/photo/\d+$#',$requestPath));
if($anonymousRead){$_SESSION=[];}else{
 if(!session_start())throw new RuntimeException('Session storage unavailable.',503);
 if(!isset($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));
}
require __DIR__.'/batch.php';
require_once __DIR__.'/portal.php';
require __DIR__.'/traffic.php';
function rows(string $sql,array $params=[]):array {global $db;$s=$db->prepare($sql);$s->execute($params);return $s->fetchAll();}
function one(string $sql,array $params=[]):?array{return rows($sql,$params)[0]??null;}
function run(string $sql,array $params=[]):int {global $db;$s=$db->prepare($sql);$s->execute($params);return $s->rowCount();}
function insert(string $table,array $data):int {global $db;$keys=array_keys($data);run('INSERT INTO `'.$table.'` (`'.implode('`,`',$keys).'`) VALUES ('.implode(',',array_fill(0,count($keys),'?')).')',array_values($data));return (int)$db->lastInsertId();}
function fail(string $message,int $status=422):never {throw new RuntimeException($message,$status);}
function respond(mixed $data,int $status=200):never {http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');if(is_array($data)&&!str_starts_with(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)??'','/api/admin/')){if(($_GET['portal']??'')==='1')$data=portal_workspace($data);elseif(($GLOBALS['config']['environment']??'')!=='test')$data=portal_workspace($data,true);}echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function user():?array {$id=$_SESSION['user_id']??null;if(!$id)return null;$u=one('SELECT id,public_id,name,email,phone,status,verified_at,deleted_at FROM users WHERE id=?',[$id]);if(!$u||$u['status']!=='active'||$u['deleted_at']){unset($_SESSION['user_id']);return null;}$u['roles']=array_column(rows('SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',[$id]),'name');return $u;}
function auth():array {$u=$GLOBALS['actingUser']??user();if(!$u)fail('Please sign in to continue.',401);return $u;}
function has(array $u,array $roles):bool{return (bool)array_intersect($u['roles'],$roles);}
function need(array $roles):array {$u=auth();if(!has($u,$roles))fail('Your role does not have access to this action.',403);return $u;}
function textfield(array $b,string $key,int $max=1000,bool $required=true):string {$v=trim((string)($b[$key]??''));if(($required&&$v==='')||mb_strlen($v)>$max)fail("Please enter a valid $key.");return $v;}
function number(array $b,string $key,float $min=0,float $max=100000000):float {$v=$b[$key]??null;if(!is_numeric($v)||(float)$v<$min||(float)$v>$max)fail("Please enter a valid $key.");return (float)$v;}
function choice(array $b,string $key,array $values,string $default=''):string {$v=(string)($b[$key]??$default);if(!in_array($v,$values,true))fail("Invalid $key.");return $v;}
function datefield(array $b,string $key,bool $future=false):string {$v=textfield($b,$key,30);$t=strtotime($v);if($t===false||($future&&$t<time()-60))fail("Choose a valid future $key.");return date(str_contains($v,'T')||str_contains($v,':')?'Y-m-d H:i:s':'Y-m-d',$t);}
function audit(array $u,string $action,string $entity,int $id,array $data=[]):void {unset($data['password'],$data['otp'],$data['password_hash'],$data['national_id'],$data['account_number']);$actor=$GLOBALS['adminActor']??$u;if(isset($GLOBALS['adminActor']))$data['on_behalf_of']=$u['id'];insert('audit_logs',['actor_id'=>$actor['id'],'action'=>$action,'entity'=>$entity,'entity_id'=>$id,'after_data'=>json_encode($data),'ip_address'=>$_SERVER['REMOTE_ADDR']??'cli']);}
function notify(int $id,string $title,string $message,string $link=''):void{insert('notifications',['user_id'=>$id,'title'=>$title,'message'=>$message,'link'=>$link]);}
function property_access(int $id,array $u,bool $edit=false):array {$p=one('SELECT * FROM properties WHERE id=?',[$id]);if(!$p)fail('Property not found.',404);if($p['owner_id']==$u['id']||has($u,['admin','supervisor']))return $p;if(!$edit&&$p['status']==='published')return $p;if(!$edit&&one('SELECT id FROM appointments WHERE property_id=? AND assigned_to=?',[$id,$u['id']]))return $p;if(!$edit&&one('SELECT t.id FROM tenancies t WHERE t.property_id=? AND (t.head_tenant_id=? OR EXISTS(SELECT 1 FROM tenancy_members tm WHERE tm.tenancy_id=t.id AND tm.user_id=?))',[$id,$u['id'],$u['id']]))return $p;fail('You cannot access this property.',403);}
function public_property(array $p,?array $u,?bool $exactAllowed=null):array {
 $allowed=$exactAllowed??($u&&($p['owner_id']==$u['id']||has($u,['admin','supervisor'])||one("SELECT id FROM appointments WHERE property_id=? AND status IN('confirmed','completed') AND (requested_by=? OR assigned_to=?)",[$p['id'],$u['id'],$u['id']])||one("SELECT t.id FROM tenancies t WHERE t.property_id=? AND t.status IN('active','notice') AND (t.head_tenant_id=? OR EXISTS(SELECT 1 FROM tenancy_members tm WHERE tm.tenancy_id=t.id AND tm.user_id=?))",[$p['id'],$u['id'],$u['id']])));
 if(!$allowed)foreach(['address_id','road_no','house_no','house_name','floor_no','flat_no','latitude','longitude'] as $field)unset($p[$field]);
 $p['exact_address_available']=(bool)$allowed;return $p;
}
function tenancy_access(int $id,array $u):array {$t=one('SELECT t.*,p.owner_id,p.address_id,p.title property_title FROM tenancies t JOIN properties p ON p.id=t.property_id WHERE t.id=?',[$id]);if(!$t)fail('Tenancy not found.',404);if(has($u,['admin','supervisor','finance'])||$t['head_tenant_id']==$u['id']||$t['owner_id']==$u['id']||one('SELECT id FROM tenancy_members WHERE tenancy_id=? AND user_id=?',[$id,$u['id']]))return $t;if(has($u,['agent'])&&one('SELECT id FROM appointments WHERE property_id=? AND assigned_to=?',[$t['property_id'],$u['id']]))return $t;fail('You cannot access this tenancy.',403);}
function tenant_only(array $t,array $u):void{if($t['head_tenant_id']!=$u['id']&&!has($u,['admin']))fail('Only the head tenant can do this.',403);}
function invoice_for(int $tenancyId,int $userId,?string $period=null):int {$period=$period??date('Y-m-01');one('SELECT id FROM tenancies WHERE id=? FOR UPDATE',[$tenancyId]);$i=one("SELECT id,status FROM invoices WHERE tenancy_id=? AND user_id=? AND period=? AND billing_group='monthly' FOR UPDATE",[$tenancyId,$userId,$period]);if($i&&$i['status']==='paid')run("UPDATE invoices SET status='open' WHERE id=?",[$i['id']]);return $i?(int)$i['id']:insert('invoices',['tenancy_id'=>$tenancyId,'user_id'=>$userId,'period'=>$period,'due_date'=>date('Y-m-10',strtotime($period))]);}
function maintenance_access(int $id,array $u):array{$m=one('SELECT * FROM maintenance_requests WHERE id=?',[$id]);if(!$m)fail('Request not found.',404);if($m['provider_id']==$u['id'])return $m;tenancy_access((int)$m['tenancy_id'],$u);return $m;}
function check_otp(string $table,array $record,string $input):void{global $db;if($record['otp_verified_at'])fail('Entry has already been verified.');if((int)$record['otp_attempts']>=5)fail('Entry code locked after five attempts. Ask your supervisor to reassign the visit.',429);if(!$record['otp_hash']||strtotime($record['otp_expires_at']??'2000-01-01')<time()||!password_verify($input,$record['otp_hash'])){run("UPDATE `$table` SET otp_attempts=otp_attempts+1 WHERE id=?",[$record['id']]);if($db->inTransaction())$db->commit();fail('Entry code is invalid or expired.');}}
function provider_matches(array $request,int $providerId):bool{$p=one("SELECT pp.*,l.parent_id area_id FROM provider_profiles pp JOIN users u ON u.id=pp.user_id JOIN locations l ON l.id=pp.location_id WHERE pp.user_id=? AND u.status='active' AND u.verified_at IS NOT NULL",[$providerId]);if(!$p||!one('SELECT provider_id FROM provider_categories WHERE provider_id=? AND category_id=?',[$providerId,$request['category_id']]))return false;$a=one('SELECT a.latitude,a.longitude,l.parent_id area_id FROM tenancies t JOIN properties pr ON pr.id=t.property_id JOIN addresses a ON a.id=pr.address_id JOIN locations l ON l.id=a.location_id WHERE t.id=?',[$request['tenancy_id']]);if(!$a)return false;if($request['urgency']==='scheduled')return $p['area_id']==$a['area_id'];if(!$p['available']||$p['latitude']===null||$a['latitude']===null)return false;$distance=6371*acos(min(1,max(-1,cos(deg2rad((float)$p['latitude']))*cos(deg2rad((float)$a['latitude']))*cos(deg2rad((float)$a['longitude']-(float)$p['longitude']))+sin(deg2rad((float)$p['latitude']))*sin(deg2rad((float)$a['latitude'])))));return $distance<=4;}
