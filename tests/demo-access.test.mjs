import test from 'node:test';
import assert from 'node:assert/strict';
const base=process.env.RG_TEST_URL||'http://127.0.0.1:8000';
function client(){
 let cookie='',csrf='';
 return async(path,body,status=200)=>{
  const response=await fetch(base+'/api/'+path,{method:body?'POST':'GET',headers:{Cookie:cookie,...(body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{})},body:body?JSON.stringify(body):undefined});
  if(response.headers.get('set-cookie'))cookie=response.headers.get('set-cookie').split(';')[0];
  const data=await response.json();assert.equal(response.status,status,JSON.stringify(data));if(data.csrf)csrf=data.csrf;return data;
 };
}
test('local demo sign-in supports every role and normal workspaces',async()=>{
 for(const role of ['tenant','owner','provider','agent','supervisor','finance','field_agent','admin']){
  const call=client();const session=await call('session');assert.equal(session.demo_enabled,true);
  const login=await call('demo-login',{role});assert.ok(login.user.roles.includes(role));
  assert.ok([`demo.${role}.001@rentguardian.local`,role==='admin'?'demo-admin@rentguardian.local':role+'@rentguardian.local'].includes(login.user.email));
  assert.equal((await call('session')).user.id,login.user.id);
  assert.ok(await call('workspace'));await call('logout',{});
 }
 const call=client();await call('session');await call('demo-login',{role:'unknown'},422);
 assert.equal((await call('session')).user,null);
});
test('six published demo homes each have two accessible interior photos',async()=>{
 const call=client();const homes=(await call('properties')).filter(p=>p.title.startsWith('DEMO: ')&&/Garden apartment|Bright balcony home|Quiet family home|Budget apartment|Spacious residence|Compact city home/.test(p.title));
 assert.equal(homes.length,6);
 for(const home of homes){
  assert.equal(home.status,'published');assert.ok(home.photo);
  const detail=await call('properties/'+home.id);assert.equal(detail.photos.length,2);
  for(const photo of detail.photos){const response=await fetch(base+photo.path);assert.equal(response.status,200);assert.match(response.headers.get('content-type'),/^image\//);assert.ok((await response.arrayBuffer()).byteLength>1000);}
 }
});
