const {chromium}=require('C:/Users/ASUS/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
(async()=>{
 const browser=await chromium.launch({headless:true,channel:'chrome'}),checks=[],errors=[];
 const open=async(options={})=>{const context=await browser.newContext(options);const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));return {context,page};};
 try{
  const {page,context}=await open({viewport:{width:1440,height:1000}});
  await page.goto('http://127.0.0.1:8000');
  await page.locator('.rg-entrance-film[open]').waitFor();
  await page.locator('.rg-entrance-film video').evaluate(v=>new Promise(resolve=>{if(v.readyState>=2)resolve();else v.addEventListener('loadeddata',resolve,{once:true});}));
  assert.equal(await page.locator('.rg-entrance-film video').evaluate(v=>v.muted),true);
  await page.screenshot({path:'storage/brand-film/qa/entrance-desktop.png'});
  await page.waitForFunction(()=>!document.querySelector('.rg-entrance-film'),{},{timeout:26000});checks.push('Autoplay finishes and releases landing page');
  assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
  assert.equal(await page.locator('.banner-visual canvas').count(),0);
  assert.equal(await page.locator('.floating-house-note').count(),0);
  assert.equal(await page.locator('.rg-hero-film video').evaluate(v=>Math.round(v.duration)),7);
  await page.screenshot({path:'storage/brand-film/qa/landing-desktop.png'});
  await page.getByRole('button',{name:'Pause home animation'}).click();assert.equal(await page.locator('.rg-hero-film video').evaluate(v=>v.paused),true);checks.push('Hero replacement loops and pauses; floating model removed');
  await page.getByRole('button',{name:'Find a home',exact:true}).click();assert.equal(await page.evaluate(()=>document.activeElement?.tagName),'SELECT');
  await page.getByRole('button',{name:'List your property',exact:true}).click();await page.getByRole('heading',{name:'Welcome back.'}).waitFor();checks.push('Hero actions reach existing search and sign-in');
  await context.close();
  const mobile=await open({viewport:{width:390,height:844},isMobile:true});await mobile.page.goto('http://127.0.0.1:8000');await mobile.page.getByRole('button',{name:'Skip intro'}).click();await mobile.page.locator('.rg-hero-film').waitFor();
  assert.equal(await mobile.page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
  await mobile.page.screenshot({path:'storage/brand-film/qa/landing-mobile.png',fullPage:true});checks.push('Mobile skip, hero and page width');await mobile.context.close();
  const reduced=await open({viewport:{width:390,height:844},reducedMotion:'reduce'});await reduced.page.goto('http://127.0.0.1:8000');await reduced.page.locator('.rg-hero-film').waitFor();assert.equal(await reduced.page.locator('.rg-entrance-film').count(),0);assert.equal(await reduced.page.locator('.rg-hero-film video').evaluate(v=>v.paused),true);checks.push('Reduced motion bypasses intro and pauses hero');await reduced.context.close();
  const failure=await open();await failure.page.route('**/media/rent-guardian/*.mp4',r=>r.abort());await failure.page.goto('http://127.0.0.1:8000');await failure.page.locator('.rg-hero-film>img').waitFor();await failure.page.waitForFunction(()=>!document.querySelector('.rg-entrance-film'));checks.push('Video failures reveal landing and hero poster');await failure.context.close();
  const escape=await open();await escape.page.goto('http://127.0.0.1:8000');await escape.page.locator('.rg-entrance-film[open]').waitFor();await escape.page.keyboard.press('Escape');await escape.page.waitForFunction(()=>!document.querySelector('.rg-entrance-film'));checks.push('Escape dismisses intro');await escape.context.close();
  assert.deepEqual(errors,[]);
  const report={checks,pageErrors:errors};fs.writeFileSync('storage/brand-film/qa/browser-results.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
