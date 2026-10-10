const fs=require('node:fs');
const assert=require('node:assert/strict');
const roles=['admin','owner','tenant','provider','agent','supervisor','finance','field_agent'];
const adminPassword=fs.readFileSync('storage/credentials.md','utf8').match(/Admin password: (.+)/)[1].trim();
(async()=>{
for(const role of roles){
 let cookie='',csrf='';
 async function call(path,body){const r=await fetch('http://127.0.0.1:8000/api/'+path,{method:body?'POST':'GET',headers:{Cookie:cookie,...body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{}},body:body?JSON.stringify(body):undefined});const c=r.headers.get('set-cookie');if(c)cookie=c.split(';')[0];const d=await r.json();assert.equal(r.status,200,role+' '+path+': '+JSON.stringify(d));if(d.csrf)csrf=d.csrf;return d;}
 await call('session');await call('login',{email:role+'@rentguardian.local',password:role==='admin'?adminPassword:'Guardian@2026!'});
 const w=await call('workspace');assert.ok(w.user.roles.includes(role));
 if(role==='tenant'){assert.ok(w.orders.some(o=>o.notes.startsWith('DEMO:')));assert.ok(w.subscriptions.length);assert.ok(w.payments.some(p=>p.reference==='DEMO-SAMPLE-confirmed'));}
 if(role==='owner')assert.ok(w.bank_accounts.some(b=>b.bank_name==='DEMO Bank (fictional)'));
 if(role==='provider'){assert.ok(w.jobs.some(j=>j.title==='DEMO: Electrical safety check'));assert.ok(w.requests.some(j=>j.title==='DEMO: Bathroom tap replacement'));assert.ok(w.orders.length);assert.ok(w.subscriptions.length);}
 if(role==='agent')assert.ok(w.appointments.some(a=>a.notes?.startsWith('DEMO:')));
 if(role==='supervisor')assert.ok(w.inspections.some(i=>i.status==='submitted'&&i.summary.startsWith('DEMO:')));
 if(role==='finance')assert.ok(w.payments.some(p=>p.status==='reviewed'&&p.reference==='DEMO-SAMPLE-reviewed'));
 if(role==='field_agent')assert.ok(w.campaigns.length);
 if(role==='admin'){const d=await call('admin/table?table=campaign_checklists');assert.ok(d.rows.some(c=>c.owner_name==='DEMO: prospective property owner'));}
 console.log(role+': login and demo workspace verified');
}
})().catch(e=>{console.error(e);process.exitCode=1;});
