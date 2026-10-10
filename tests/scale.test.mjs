import test from 'node:test';
import assert from 'node:assert/strict';
import {request} from '../src/api.js';

test('timeouts abort a stalled request once without replaying a mutation',async()=>{
 const original=globalThis.fetch;let calls=0;
 globalThis.fetch=async(url,{signal})=>{calls++;return new Promise((resolve,reject)=>{signal.addEventListener('abort',()=>reject(new DOMException('Aborted','AbortError')),{once:true});});};
 try{await assert.rejects(request('/api/orders',{method:'POST'},5),/Check its status before submitting again/);assert.equal(calls,1);}finally{globalThis.fetch=original;}
});
test('proxy HTML errors become readable failures and successful responses remain intact',async()=>{
 const original=globalThis.fetch;
 try{globalThis.fetch=async()=>new Response('<html>Gateway error</html>',{status:502});await assert.rejects(request('/api/workspace'),/unreadable response/);globalThis.fetch=async()=>Response.json({ok:true});assert.deepEqual(await request('/api/workspace'),{ok:true});}finally{globalThis.fetch=original;}
});
