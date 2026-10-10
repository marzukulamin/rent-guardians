import test from 'node:test';
import assert from 'node:assert/strict';
const base=process.env.RG_DEMO_URL||'http://127.0.0.1:8000';
function client(){let cookie='',csrf='';return async(path,body,expected=200)=>{
 const r=await fetch(base+'/api/'+path,{method:body?'POST':'GET',headers:{Cookie:cookie,...(body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{})},body:body?JSON.stringify(body):undefined});
 if(r.headers.get('set-cookie'))cookie=r.headers.get('set-cookie').split(';')[0];const data=await r.json();assert.equal(r.status,expected,JSON.stringify(data));if(data.csrf)csrf=data.csrf;return data;
};}
test('all eight portals resolve identities and business records from database datasets',async()=>{
 const admin=client();await admin('session');await admin('demo-login',{role:'admin'});
 const overview=await admin('admin/overview');const tables={};
 for(const table of overview.tables){const first=await admin('admin/table?table='+table);
  // Parallel sign-in tests append audit records and create expiring rate-limit buckets.
  // Their two independently timed snapshots cannot have a fixed count.
  if(['audit_logs','request_budgets','auth_attempts'].includes(table))assert.ok(Number.isInteger(first.total)&&first.total>=0);else assert.equal(first.total,overview.counts[table]);tables[table]=first.rows;
 }
 assert.equal(overview.tables.length,53);
 // Paginate users because each of the eight demo roles contains 100 accounts.
 const users=new Map();for(const role of ['tenant','owner','provider','agent','supervisor','finance','field_agent','admin']){const result=await admin('admin/accounts?search='+encodeURIComponent('demo.'+role+'.'));for(const u of result.items)users.set(u.id,u);}
 const aws=await admin('workspace');for(const t of aws.tenancies){const tenant=users.get(t.head_tenant_id),owner=users.get(t.owner_id);if(!tenant||!owner)continue;
  assert.notEqual(t.head_tenant_id,t.owner_id);assert.equal(t.tenant_name,tenant.name);assert.equal(t.owner_name,owner.name);assert.notEqual(tenant.name,owner.name);assert.equal(t.tenant_public_id,tenant.public_id);assert.equal(t.owner_public_id,owner.public_id);
 }
 const firstOwner=[...users.values()].find(u=>u.public_id==='RGD-OWNER-001');await admin('admin/users',{id:firstOwner.id,roles:['tenant']},422);assert.equal((await admin('admin/accounts?search=RGD-OWNER-001')).items[0].roles,'owner');
 for(const role of ['tenant','owner','provider','agent','supervisor','finance','field_agent']){
  const call=client();await call('session');const {user}=await call('demo-login',{role});const ws=await call('workspace');assert.equal(ws.user.name,users.get(user.id).name);
  for(const m of ws.members){const u=users.get(m.user_id);if(u){assert.equal(m.name,u.name);assert.equal(m.phone,u.phone);assert.equal(m.public_id,u.public_id);}const t=ws.tenancies.find(t=>t.id===m.tenancy_id);if(t)assert.equal(m.relationship==='Head tenant',m.user_id===t.head_tenant_id);}
  for(const o of ws.orders){const db=tables.orders.find(row=>row.id===o.id);assert.ok(db);for(const field of ['user_id','provider_id','tenancy_id','address_id','status'])assert.equal(o[field],db[field]);
   for(const i of o.items){const row=tables.order_items.find(r=>r.id===i.id);assert.ok(row);for(const field of ['quantity','unit_price','installation_cost'])assert.equal(i[field],row[field]);}
   const charge=ws.invoices.flatMap(i=>i.items).find(i=>i.order_id===o.id);if(charge)assert.equal(Number(charge.amount),o.items.reduce((sum,i)=>sum+Number(i.quantity)*(Number(i.unit_price)+Number(i.installation_cost)),0));
  }
  for(const i of ws.invoices){assert.equal(Number(i.total),i.items.reduce((sum,item)=>sum+Number(item.amount),0));assert.equal(Number(i.balance),Number(i.total)-Number(i.paid));}
  if(role==='tenant'){
   const t=ws.tenancies[0];assert.equal(t.head_tenant_id,user.id);const owner=users.get(t.owner_id);
   await call(`tenancies/${t.id}/action`,{action:'add_member',user_id:owner.id,name:owner.name,relationship:'Flatmate'},422);
   await call(`tenancies/${t.id}/action`,{action:'add_member',name:'Wrong label',relationship:'Head tenant'},422);
   await call('transfers',{tenancy_id:t.id,to_user_id:user.id},422);
  }
  await call('logout',{});
 }
 await admin('logout',{});
});
