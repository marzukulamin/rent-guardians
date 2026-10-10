let csrf='';
export let demoEnabled=false;
export async function request(url,options={},timeoutMs=30000){
 const controller=new AbortController();const timer=setTimeout(()=>controller.abort(),timeoutMs);
 try{
  const response=await fetch(url,{...options,signal:controller.signal});
  let data;try{data=await response.json();}catch(e){if(e.name==='AbortError')throw e;throw new Error('The server returned an unreadable response. Please try again.');}
  if(!response.ok)throw new Error(data?.error||'Request failed.');return data;
 }catch(e){if(e.name==='AbortError')throw new Error('The request timed out. Check its status before submitting again.');throw e;}finally{clearTimeout(timer);}
}
export async function api(path,body){
 const data=await request('/api/'+path+(!body&&!demoEnabled?(path.includes('?')?'&':'?')+'portal=1':''),{credentials:'same-origin',headers:body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:undefined,method:body?'POST':'GET',body:body?JSON.stringify(body):undefined});if(data?.csrf)csrf=data.csrf;if(path==='session')demoEnabled=data.demo_enabled===true;return data;
}
export async function upload(form){return request('/files/upload',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-Token':csrf},body:form},120000);}
export const money=value=>'৳'+Number(value||0).toLocaleString('en-BD',{maximumFractionDigits:2});
export const date=value=>value?new Date(value.replace(' ','T')).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}):'—';
export const daysFromNow=(days=0)=>new Intl.DateTimeFormat('en-CA',{timeZone:'Asia/Dhaka',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date(Date.now()+days*86400000));
export const tomorrow=()=>daysFromNow(1);
