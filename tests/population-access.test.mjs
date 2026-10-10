import test from 'node:test';
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
const base=process.env.RG_DEMO_URL||'http://127.0.0.1:8000';
const population=JSON.parse(await readFile(new URL('../storage/demo-population.json',import.meta.url)));
function client(){let cookie='',csrf='';const call=async(path,body)=>{
 const r=await fetch(base+'/api/'+path,{method:body?'POST':'GET',headers:{Cookie:cookie,...(body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{})},body:body?JSON.stringify(body):undefined});
 if(r.headers.get('set-cookie'))cookie=r.headers.get('set-cookie').split(';')[0];
 const data=await r.json();assert.equal(r.status,200,JSON.stringify(data));if(data.csrf)csrf=data.csrf;return data;
};call.file=path=>fetch(base+path,{headers:{Cookie:cookie}});return call;}
test('all populated role workspaces use the expected database records',async()=>{
 for(const role of Object.keys(population.account_ids)){
  const call=client();await call('session');const login=await call('demo-login',{role});
  assert.equal(login.user.id,population.account_ids[role][1]);
  const ws=await call('workspace');assert.equal(ws.user.id,login.user.id);assert.doesNotMatch(JSON.stringify(ws),/QA[_ ]TEST/i);
  if(role==='tenant'){
   assert.equal(ws.tenancies.length,1);assert.equal(ws.tenancies[0].property_id,population.property_ids[0]);
   assert.equal(ws.contracts[0].signatures.length,2);assert.ok(ws.requests.length>=1);assert.ok(ws.orders.some(o=>o.notes==='Please call before arrival. Sample service order.'));assert.ok(ws.subscriptions.length>=1);
   for(const invoice of ws.invoices){assert.equal(Number(invoice.total),invoice.items.reduce((n,i)=>n+Number(i.amount),0));assert.equal(Number(invoice.balance),Number(invoice.total)-Number(invoice.paid));}
   const detail=await call('properties/'+population.property_ids[0]);assert.equal(detail.photos.length,2);
   for(const photo of detail.photos){const response=await call.file(photo.path);assert.equal(response.status,200);assert.match(response.headers.get('content-type'),/^image\//);assert.ok((await response.arrayBuffer()).byteLength>1000);}
  }
  if(role==='owner'){assert.equal(ws.properties[0].id,population.property_ids[0]);assert.equal(ws.tenancies.length,1);assert.equal(ws.bank_accounts.length,1);}
  if(role==='provider'){assert.equal(ws.provider_profile.user_id,login.user.id);assert.equal(ws.orders.length,1);assert.equal(ws.subscriptions.length,1);const lookup=await call('lookups');assert.equal(lookup.products.filter(p=>p.provider_id===login.user.id).length,4);}
  if(role==='agent'){assert.equal(ws.inspections.length,1);assert.equal(ws.inspections[0].rooms.length,4);assert.equal(ws.appointments.length,2);}
  if(role==='field_agent'){assert.equal(ws.campaigns.length,1);assert.ok(ws.training.length);}
  if(['admin','supervisor'].includes(role)){assert.equal(ws.properties.filter(p=>population.property_ids.includes(p.id)).length,100);assert.equal(ws.documents.filter(d=>population.property_ids.includes(d.property_id)).length,240);assert.equal(ws.appointments.filter(a=>population.property_ids.includes(a.property_id)).length,200);}
  if(role==='finance')assert.ok(ws.payments.some(p=>p.user_id===population.account_ids.tenant[1]));
  if(role==='admin')for(const name of Object.keys(population.account_ids)){const accounts=await call('admin/accounts?search='+encodeURIComponent('demo.'+name+'.'));assert.equal(accounts.total,100);assert.equal(accounts.items.length,100);}
  await call('logout',{});
 }
});
test('published catalog pagination exposes all 60 available populated homes',async()=>{
 const call=client();let all=[];let total=Infinity;
 for(let page=1;all.length<total;page++){const result=await call('properties?paged=1&page='+page);total=result.total;all.push(...result.items);assert.ok(result.items.length||all.length===total);}
 assert.equal(all.length,total);assert.equal(new Set(all.map(p=>p.id)).size,total);
 assert.equal(all.filter(p=>population.property_ids.includes(p.id)).length,60);
 assert.doesNotMatch(JSON.stringify(all),/QA[_ ]TEST/i);
});
