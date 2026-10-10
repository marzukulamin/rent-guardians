import test from 'node:test';
import assert from 'node:assert/strict';
const base=process.env.RG_TEST_BASE||'http://127.0.0.1:8000';
test('anonymous discovery avoids session creation and hides marked test listings',async()=>{
 const response=await fetch(base+'/api/properties?paged=1&portal=1');
 assert.equal(response.status,200);
 assert.equal(response.headers.get('set-cookie'),null);
 const data=await response.json();
 assert.ok(data.items.every(p=>!/QA[_ ]TEST|\bDEMO\b/i.test(p.title)));
 assert.ok(data.total>=data.items.length);
});
test('readiness checks are session-free and oversized JSON fails before business writes',async()=>{
 const health=await fetch(base+'/health/ready');assert.equal(health.status,200);
 assert.equal(health.headers.get('set-cookie'),null);
 assert.deepEqual(await health.json(),{status:'ready'});
 const response=await fetch(base+'/api/register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({padding:'x'.repeat(1048576)})});
 assert.equal(response.status,413);
});
