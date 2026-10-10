import test from 'node:test';
import assert from 'node:assert/strict';
import {money,daysFromNow} from '../src/api.js';
import {isDemoPhoto,preparePropertyPhoto} from '../src/photo-upload.js';
test('BDT summaries retain fractional charges and show zero balances',()=>{assert.equal(money(1250.75),'৳1,250.75');assert.equal(money('0.25'),'৳0.25');assert.equal(money(0),'৳0');assert.equal(money(-125.5),'৳-125.5');});
test('demo and generated fixtures are never classified as property-specific uploads',()=>{assert.equal(isDemoPhoto('/home-1.jpg','Living room'),true);assert.equal(isDemoPhoto('/files/photo/17','DEMO sample interior'),true);assert.equal(isDemoPhoto('/files/photo/18','QA_TEST generated image'),true);assert.equal(isDemoPhoto('/files/photo/19','North-facing kitchen'),false);assert.equal(isDemoPhoto(null),false);});
test('client upload rejects disguised non-images and oversized photos before decoding',async()=>{await assert.rejects(preparePropertyPhoto({type:'application/pdf',size:100}),/JPEG, PNG or WebP/);await assert.rejects(preparePropertyPhoto({type:'image/jpeg',size:11*1024*1024}),/10 MB/);});
test('availability date defaults follow Bangladesh midnight',()=>{const previous=Date.now;Date.now=()=>Date.parse('2026-10-08T20:00:00Z');try{assert.equal(daysFromNow(0),'2026-10-09');assert.equal(daysFromNow(1),'2026-10-10');}finally{Date.now=previous;}});
