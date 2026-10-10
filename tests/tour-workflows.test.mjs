import test from 'node:test';
import assert from 'node:assert/strict';
import {workflows,discovery,isDemoUser,matchesTourSuccess,featuredTasks,taskHint} from '../src/tour-workflows.js';

test('every demo role has distinct short tasks and actionable guidance',()=>{
 assert.deepEqual(Object.keys(workflows),['tenant','owner','provider','agent','supervisor','finance','field_agent','admin']);
 for(const [role,tasks] of Object.entries(workflows)){
  assert.equal(new Set(tasks.map(t=>t.id)).size,tasks.length,role);
  assert.ok(tasks.some(t=>t.steps.some(s=>['click','action','form'].includes(s.kind))),role);
  for(const task of tasks)for(const step of task.steps){assert.ok(step.route&&step.title&&step.body,task.id);assert.ok(['read','click','form','action'].includes(step.kind));}
 }
});
test('demo account recognition excludes real and lookalike domains',()=>{
 for(const role of Object.keys(workflows))assert.ok(isDemoUser({email:`demo.${role}.100@rentguardian.local`}));
 assert.ok(isDemoUser({email:'flatmate@rentguardian.local'}));
 for(const email of ['admin@rentguardian.local','person@gmail.com','tenant@rentguardian.local.evil','demo.tenant.1@rentguardian.local'])assert.equal(isDemoUser({email}),false);
});
test('checkout waits for its successful mutation, not another form or click',()=>{
 const step=workflows.tenant.find(t=>t.id==='grocery').steps.find(s=>s.kind==='form');
 assert.equal(matchesTourSuccess(step,{path:'payments',body:{}}),false);
 assert.equal(matchesTourSuccess(step,{path:'orders/123/action',body:{status:'accepted'}}),false);
 assert.equal(matchesTourSuccess(step,{path:'orders',body:{}}),true);
 assert.equal(matchesTourSuccess({kind:'click',path:'orders'},{path:'orders'}),false);
});
test('delivery follows one exact order and the expected saved status',()=>{
 const step=workflows.provider.find(t=>t.id==='delivery').steps[1];
 assert.equal(matchesTourSuccess(step,{path:'orders/99/action',body:{status:'accepted'}},99),true);
 assert.equal(matchesTourSuccess(step,{path:'orders/100/action',body:{status:'accepted'}},99),false);
 assert.equal(matchesTourSuccess(step,{path:'orders/99/action',body:{status:'completed'}},99),false);
 assert.equal(matchesTourSuccess(step,{path:'orders/99/action',body:{status:'accepted'}}),false);
});
test('finance confirmation cannot be satisfied by review or rejection',()=>{
 const step=workflows.finance.find(t=>t.id==='confirm').steps[1];
 assert.equal(matchesTourSuccess(step,{path:'payments/4/action',body:{action:'confirm'}}),true);
 for(const action of ['review','reject'])assert.equal(matchesTourSuccess(step,{path:'payments/4/action',body:{action}}),false);
 const booking=discovery.steps.find(s=>s.kind==='form');
 assert.equal(booking.path,'appointments');assert.equal(discovery.steps.at(-1).route,'appointments');
});


test('featured workflows follow rebuilt role navigation',()=>{
 for(const role of Object.keys(workflows)){
  assert.ok(featuredTasks[role]?.length,role);
  for(const id of featuredTasks[role])assert.ok(workflows[role].some(t=>t.id===id),role+':'+id);
 }
 assert.ok(workflows.agent.find(t=>t.id==='assignments').steps.some(s=>s.route==='operations'));
 assert.ok(workflows.finance.find(t=>t.id==='reports').steps.some(s=>s.target?.id==='finance-report-select'));
 assert.ok(workflows.admin.find(t=>t.id==='users').steps.some(s=>s.target?.id==='admin-portal-section'));
 assert.ok(workflows.admin.find(t=>t.id==='work-queue').steps.some(s=>s.target?.id==='admin-review-work'));
});
test('review waits for approval of the correct entity and highlighted record',()=>{
 const step=workflows.supervisor.find(t=>t.id==='approve-documents').steps.find(s=>s.kind==='action');
 assert.equal(matchesTourSuccess(step,{path:'admin/review',body:{entity:'documents',id:7,status:'approved'}},undefined,7),true);
 for(const body of [{entity:'inspections',id:7,status:'approved'},{entity:'documents',id:7,status:'rejected'},{entity:'documents',id:8,status:'approved'}])assert.equal(matchesTourSuccess(step,{path:'admin/review',body},undefined,7),false);
 const payment=workflows.finance.find(t=>t.id==='confirm').steps[1];
 assert.equal(matchesTourSuccess(payment,{path:'payments/8/action',body:{action:'confirm'}},undefined,7),false);
 assert.equal(matchesTourSuccess(payment,{path:'payments/7/action',body:{action:'confirm'}},undefined,7),true);
});
test('eligibility hints reflect actual assigned records and campaign dates',()=>{
 assert.match(taskHint('agent','entry',{}, {id:3}),/confirmed visit/);
 assert.equal(taskHint('agent','entry',{appointments:[{assigned_to:3,status:'confirmed',otp_verified_at:null}]},{id:3}),'');
 assert.match(taskHint('agent','entry',{appointments:[{assigned_to:4,status:'confirmed'}]},{id:3}),/confirmed visit/);
 assert.match(taskHint('finance','confirm',{payments:[{status:'reviewed',reviewed_by:3}]},{id:3}),/independently/);
 assert.equal(taskHint('finance','confirm',{payments:[{status:'reviewed',reviewed_by:4}]},{id:3}),'');
 const campaign={starts_on:'2026-10-01',ends_on:'2026-10-10'};
 assert.equal(taskHint('field_agent','checklist',{campaigns:[campaign],server_date:'2026-10-10'}),'');
 assert.match(taskHint('field_agent','checklist',{campaigns:[campaign],server_date:'2026-10-11'}),/active today/);
});
test('receipt and admin detail tours close their dialog before continuing',()=>{
 for(const [role,id] of [['finance','bills'],['admin','datasets'],['admin','documents'],['admin','work-queue']])assert.ok(workflows[role].find(t=>t.id===id).steps.some(s=>s.kind==='read'&&s.closeDialog),role+':'+id);
});
