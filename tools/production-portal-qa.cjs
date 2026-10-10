const {chromium}=require('C:/Users/ASUS/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({headless:true,channel:'chrome'});
 try{
  const page=await browser.newPage({viewport:{width:1440,height:1000}});const errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://127.0.0.1:8000');await page.locator('main h1').waitFor();
  await page.locator('.loading').waitFor({state:'hidden'});
  assert.doesNotMatch(await page.locator('body').innerText(),/\bdemo\b|Sample data|QA[_ ]TEST/i);
  const response=await page.request.get('http://127.0.0.1:8000/api/properties?paged=1&portal=1');const data=await response.json();
  assert.equal(await page.locator('.property-card').count(),data.items.length);
  assert.ok(data.items.every(p=>!/QA[_ ]TEST|\bDEMO\b/i.test(p.title)));
  await page.locator('.topbar').getByRole('button',{name:'Sign in'}).click();
  const dialog=page.getByRole('dialog');
  assert.equal(await dialog.getByLabel('Email address').inputValue(),'');
  assert.equal(await dialog.getByLabel('Password',{exact:true}).inputValue(),'');
  assert.equal(await page.locator('.demo-accounts').count(),0);
  await page.getByRole('button',{name:'Close dialog'}).click();
  await page.screenshot({path:'storage/production-portal-desktop.png',fullPage:true});
  await page.setViewportSize({width:390,height:844});await page.waitForTimeout(350);
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
  await page.screenshot({path:'storage/production-portal-mobile.png',fullPage:true});
  assert.deepEqual(errors,[]);
  console.log('Portal copy, fixture isolation, empty sign-in fields, desktop/mobile and browser errors passed.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
