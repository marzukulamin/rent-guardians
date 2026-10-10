const {chromium}=require('C:/Users/ASUS/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
(async()=>{
 const browser=await chromium.launch({headless:true,channel:'chrome'}),results=[];
 try{
  for(const [width,height] of [[1366,768],[1440,900],[360,800],[390,844],[430,932]]){
   const page=await browser.newPage({viewport:{width,height}});
   await page.goto('http://127.0.0.1:8000');await page.locator('.rg-entrance-film[open]').waitFor();
   await page.waitForFunction(()=>document.querySelector('.rg-entrance-film video')?.currentTime>=10,{},{timeout:20000});
   await page.locator('.rg-entrance-film video').evaluate(v=>v.pause());
   const info=await page.locator('.rg-entrance-film video').evaluate(v=>{const b=v.getBoundingClientRect();return {width:b.width,height:b.height,fit:getComputedStyle(v).objectFit,source:v.currentSrc,videoWidth:v.videoWidth,videoHeight:v.videoHeight,duration:v.duration,currentTime:v.currentTime};});
   assert.equal(info.width,width);assert.equal(info.height,height);assert.equal(info.fit,'cover');assert.equal(info.duration,18);
   assert.equal(info.source.endsWith('intro-phone.mp4'),width/height<=.75);
   await page.screenshot({path:`storage/brand-film/qa/framing-${width}x${height}.png`});
   await page.getByRole('button',{name:'Skip intro'}).click();await page.waitForFunction(()=>!document.querySelector('.rg-entrance-film'));
   results.push({viewport:[width,height],...info});await page.close();
  }
  fs.writeFileSync('storage/brand-film/qa/framing-results.json',JSON.stringify(results,null,2));console.log(JSON.stringify(results,null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
