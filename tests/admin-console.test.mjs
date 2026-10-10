import test from 'node:test';
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
const base=process.env.RG_DEMO_URL||'http://127.0.0.1:8000';
function client(){let cookie='',csrf='';const call=async(path,body,status=200)=>{const r=await fetch(base+'/api/'+path,{method:body?'POST':'GET',headers:{Cookie:cookie,...body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{}},body:body?JSON.stringify(body):undefined});if(r.headers.get('set-cookie'))cookie=r.headers.get('set-cookie').split(';')[0];const data=await r.json();assert.equal(r.status,status,JSON.stringify(data));if(data.csrf)csrf=data.csrf;return data;};call.file=url=>fetch(base+url,{headers:{Cookie:cookie}});return call;}
test('administrator console covers the entire schema, all portals, documents, queues and reports',async()=>{
 const c=client();await c('session');await c('demo-login',{role:'admin'});const initial=(await c('session')).user;const resources=await c('admin/resources');const overview=await c('admin/overview');assert.deepEqual(resources.resources.map(r=>r.table).sort(),overview.tables.sort());
 const pop=JSON.parse(await readFile(new URL('../storage/demo-population.json',import.meta.url)));
 for(const role of ['tenant','owner','provider','agent','supervisor','finance','field_agent','admin']){assert.ok(resources.features[role].length);const ws=await c(`admin/accounts/${pop.account_ids[role][1]}/workspace`);assert.equal(ws.user.id,pop.account_ids[role][1]);assert.ok(ws.user.roles.includes(role));const docs=await c('admin/records?table=documents&account_id='+ws.user.id);if(docs.items.length){const file=await c.file('/files/download/'+docs.items[0].data.id);assert.equal(file.status,200);}}
 const targetSaved=await c(`admin/accounts/${pop.account_ids.tenant[1]}/read?`+new URLSearchParams({path:'properties?paged=1&saved=1'}));assert.ok(Array.isArray(targetSaved.items));await c(`admin/accounts/${pop.account_ids.tenant[1]}/read?`+new URLSearchParams({path:'session'}),undefined,422);
 assert.equal((await c('session')).user.id,initial.id,'viewing a portal must not switch administrator identity');
 for(const resource of resources.resources){const records=await c('admin/records?table='+resource.table);assert.equal(records.resource.table,resource.table);if(records.items.length){const item=records.items[0];const detail=await c('admin/record?'+new URLSearchParams({table:resource.table,key:JSON.stringify(item.key)}));assert.deepEqual(detail.key,item.key);assert.ok(detail.revision);assert.ok(!('password_hash' in detail.data));assert.ok(!('otp_hash' in detail.data));}}
 for(const report of ['rent_roll','monthly_dues','full_address','cost_reconciliation','payment_trail','provider_jobs','move_out','owner_income','appointments','service_orders','catering','verification','inspection_reports','support','campaigns','reviews','referrals','accounts','story_coverage','relationships']){const data=await c('admin/query?key='+report);assert.ok(Array.isArray(data.rows));}
 const queue=await c('admin/attention');assert.ok(queue.summary.length);assert.ok(queue.items.every(x=>x.source&&x.id));const overdue=await c('admin/attention?overdue=1');assert.ok(overdue.items.every(x=>x.overdue===1));
 const options=await c('admin/options?table=users&selected='+pop.account_ids.owner[100]);assert.ok(options.items.some(x=>x.value===pop.account_ids.owner[100]),'a selected FK outside the first page must be visible');
 const limited=await c('admin/accounts?page_size=25');assert.equal(limited.items.length,25);assert.equal(limited.page_size,25);
 const contact=await c('admin/accounts?search=00000000101');assert.ok(contact.items.some(x=>x.id===pop.account_ids.owner[1]));
 const exported=await c.file('/api/admin/accounts/export');assert.equal(exported.status,200);assert.match(exported.headers.get('content-type'),/text\/csv/);const text=await exported.text();assert.ok(text.includes('RGD-OWNER-100'));assert.ok(text.split('\n').length>=overview.counts.users+1);
 // No signatures, session changes, credential mutations or arbitrary endpoints through delegation.
 await c(`admin/accounts/${pop.account_ids.tenant[1]}/action`,{path:'logout',data:{}},422);
 await c(`admin/accounts/${pop.account_ids.tenant[1]}/action`,{path:'tenancies/13/action',data:{action:'sign',signed_name:'Someone else'}},422);
 assert.equal((await c('session')).user.id,initial.id);
 await c(`admin/accounts/${initial.id}/archive`,{reason:'Self deletion must fail'},422);
 const tenant=client();await tenant('session');await tenant('demo-login',{role:'tenant'});await tenant('admin/resources',undefined,403);await tenant('admin/records?table=users',undefined,403);await tenant(`admin/accounts/${pop.account_ids.owner[1]}/workspace`,undefined,403);
 await tenant('logout',{});await c('logout',{});
});
