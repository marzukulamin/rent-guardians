import {useEffect,useLayoutEffect,useRef,useState} from 'react';
import {createPortal} from 'react-dom';
import {Compass,MousePointer2,X,CheckCircle2,ChevronRight,Play,Pause} from 'lucide-react';
import {api,demoEnabled,money} from './api';
import {discovery,workflows,roleLabels,isDemoUser,matchesTourSuccess,featuredTasks,taskHint} from './tour-workflows';
import './demo-tour.css';
import {portalRole} from './role-portals';

const stored=key=>{try{return JSON.parse(sessionStorage.getItem(key)||'null');}catch{return null;}};
const save=(key,value)=>{try{sessionStorage.setItem(key,JSON.stringify(value));}catch{/* Storage is optional. */}};
const visible=el=>!!el&&el.getClientRects().length>0&&!el.disabled&&(!el.checkVisibility||el.checkVisibility({checkVisibilityCSS:true,checkOpacity:true}));
const labelOf=el=>{if(el.dataset.tourLabel)return el.dataset.tourLabel;const label=el.matches('label')?el:el.querySelector('label');if(label){const copy=label.cloneNode(true);copy.querySelectorAll('input,select,textarea,button,.optional').forEach(x=>x.remove());return copy.textContent.trim();}return el.querySelector('strong')?.textContent||'Complete this field';};
function findTarget(target,tracked,saved){
 if(target?.created&&saved?.path===target.resource)return [...document.querySelectorAll('.tour-page [data-tour-record]')].find(el=>el.dataset.tourRecord==saved.id&&visible(el));
 if(target?.tracked)return document.querySelector(`[data-order-id="${Number(tracked?.id)}"]`);
 if(target?.id)return [...document.querySelectorAll(`[data-tour-id="${target.id}"]`)].find(visible);
 if(target?.selector)return [...document.querySelectorAll(target.selector)].find(el=>visible(el)&&!el.closest('.tour-coach'));
 if(target?.text)return [...document.querySelectorAll('.tour-page button,.modal button,.tour-page a')].find(el=>visible(el)&&!el.closest('.tour-coach')&&el.textContent.trim()===target.text);
 return document.querySelector('.tour-page .page-head,.tour-page .discover-head')||document.querySelector('.tour-page');
}
export default function DemoTour({user,ws,navigate,refresh,route,dismissModal,modal}){
 const role=portalRole(user?.roles),allowed=demoEnabled&&isDemoUser(user),key='rg-tour-v3-'+(user?.id||'guest');
 const [state,setState]=useState(()=>stored(key)||{mode:'idle'}),[picker,setPicker]=useState(false),[showAll,setShowAll]=useState(false),[field,setField]=useState(0),[fieldCount,setFieldCount]=useState(0),[fieldLabel,setFieldLabel]=useState(''),[spot,setSpot]=useState(null),[available,setAvailable]=useState(false),[host,setHost]=useState(null),[tracked,setTracked]=useState(()=>stored('rg-tour-order-v2')),[switching,setSwitching]=useState(false),[error,setError]=useState('');
 const identity=useRef(user?.id),saveKey=useRef(key),targetRef=useRef(null),fieldSignature=useRef(''),launcher=useRef(null),deliveryRef=useRef(null),positionRef=useRef(null),recordRef=useRef(null);
 const tasks=allowed?[...workflows[role]].sort((a,b)=>{const ids=featuredTasks[role]||[];return (ids.includes(a.id)?ids.indexOf(a.id):ids.length)-(ids.includes(b.id)?ids.indexOf(b.id):ids.length);}):[discovery],workflow=(state.id==='discovery'?discovery:tasks.find(w=>w.id===state.id)),current=workflow?.steps[state.step||0],active=state.mode==='active'&&!!current;
 useLayoutEffect(()=>{if(identity.current!==user?.id){identity.current=user?.id;setPicker(false);setShowAll(false);setField(0);setError('');setState(old=>old.id==='discovery'&&old.mode==='active'?old:stored(key)||{mode:'idle'});}},[user?.id,key]);
 useEffect(()=>{if(saveKey.current!==key){saveKey.current=key;return;}save(key,state);},[key,state]);
 const advance=(skipped=false,savedRecord)=>{setField(0);setError('');setState(old=>old.id!==workflow?.id||old.step!==state.step||old.mode!=='active'?old:({...old,...(savedRecord?{lastSaved:savedRecord}:{}),skipped:(old.skipped||0)+(skipped?1:0),step:(old.step||0)+1,mode:(old.step||0)+1>=workflow.steps.length?'complete':'active'}));};
 const pause=()=>{positionRef.current=null;setState(old=>({...old,mode:'paused'}));setSpot(null);(document.querySelector('.modal .modal-head button')||launcher.current)?.focus();};
 const start=w=>{dismissModal?.();setPicker(false);setField(0);setError('');deliveryRef.current=null;recordRef.current=null;fieldSignature.current='';if(w.id==='grocery'){setTracked(null);save('rg-tour-order-v2',null);}setState({id:w.id,step:0,mode:'active',skipped:0});};
 useLayoutEffect(()=>{
  if(!active||!current)return;
  if(current.target?.id==='book-viewing'&&modal?.path==='appointments'){advance();return;}
  if(current.kind==='form'&&workflow.id==='discovery'&&!user&&!modal){setState(s=>({...s,step:4}));return;}
  const signature=`${user?.id||'guest'}:${state.id}:${state.step}`;
  if(positionRef.current?.signature!==signature)positionRef.current={signature,seen:false};
  if(route!==current.route&&!modal){if(positionRef.current.seen)pause();else navigate(current.route);return;}
  if(route===current.route)positionRef.current.seen=true;
  let scrollTarget=null,timer,attempts=0;
  const locate=()=>{
   const dialog=document.querySelector('.modal');setHost(dialog||null);let el;
   if(current.kind==='form'){
    const form=modal?.type==='auth'?null:dialog?.querySelector('form');
    const fields=[...(form?.querySelectorAll('.field,.upload-drop,.fixture-row,.check-option')||[])].filter(el=>visible(el)&&!el.parentElement?.closest('.field,.upload-drop,.fixture-row,.check-option'));
    if(modal?.path){const id=modal.path.match(/\/(\d+)\/action$/)?.[1];if(id)recordRef.current=id;}
    const signature=fields.map(el=>el.querySelector('input,select,textarea')?.name||labelOf(el)).join('|');
    if(signature!==fieldSignature.current){fieldSignature.current=signature;setField(0);}
    setFieldCount(fields.length);el=fields[Math.min(field,fields.length)];
    if(el)setFieldLabel(labelOf(el));
    else {el=[...(form?.querySelectorAll('[data-tour-id="form-submit"],button[type="submit"],.form-actions button:last-child,button:not([type])')||[])].filter(visible).at(-1);setFieldLabel(el?.textContent.trim()||'Review & submit');}
   }else if(current.orderFrom){const orderId=tracked?.id||deliveryRef.current;const order=orderId?ws.orders.find(o=>o.id==orderId):ws.orders.find(o=>o.status===current.orderFrom);if(order)deliveryRef.current=order.id;el=order?.status===current.orderFrom?document.querySelector(`[data-tour-id="provider-action-${order.id}"]`):null;}
   else el=findTarget(current.target,tracked,state.lastSaved);
   if(current.kind==='action'&&el?.closest('[data-tour-record]'))recordRef.current=el.closest('[data-tour-record]').dataset.tourRecord;
   targetRef.current=el;setAvailable(!!el);
   document.querySelectorAll('.tour-highlight').forEach(x=>{if(x!==el)x.classList.remove('tour-highlight');});
   if(el){el.classList.add('tour-highlight');if(scrollTarget!==el){scrollTarget=el;if(current.kind==='form'&&field>0)el.querySelector('input,select,textarea')?.focus({preventScroll:true});el.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'center',inline:'nearest'});}const r=el.getBoundingClientRect();setSpot({x:Math.min(innerWidth-25,Math.max(12,r.right-18)),y:Math.max(12,Math.min(innerHeight-25,r.top+Math.min(24,r.height/2))),top:r.top});}else setSpot(null);
   if(attempts++<12)timer=setTimeout(locate,180);
  };
  locate();const observer=new MutationObserver(()=>{clearTimeout(timer);timer=setTimeout(locate,80);});observer.observe(document.querySelector('.tour-page')||document.querySelector('.app-shell'),{childList:true,subtree:true});
  const dialogObserver=new MutationObserver(()=>{clearTimeout(timer);timer=setTimeout(locate,80);});if(document.querySelector('.modal'))dialogObserver.observe(document.querySelector('.modal'),{childList:true,subtree:true});
  const move=()=>{const el=targetRef.current;if(el){const r=el.getBoundingClientRect();setSpot({x:Math.min(innerWidth-25,Math.max(12,r.right-18)),y:Math.max(12,Math.min(innerHeight-25,r.top+Math.min(24,r.height/2))),top:r.top});}};
  const click=e=>{if(e.target.closest('.tour-coach'))return;const equivalent=current.target?.id&&e.target.closest(`[data-tour-id="${current.target.id}"]`);if(current.kind==='click'&&(targetRef.current?.contains(e.target)||equivalent)){recordRef.current=e.target.closest('[data-tour-record]')?.dataset.tourRecord||null;advance();}};
  const success=e=>{const {path,result}=e.detail;if(matchesTourSuccess(current,e.detail,tracked?.id||deliveryRef.current,recordRef.current)){if(path==='orders'&&result.id){const o=ws.orders.find(o=>o.id==result.id),item={id:result.id,tenant:o?.user_id,provider:o?.provider_id};setTracked(item);save('rg-tour-order-v2',item);}advance(false,result?.id?{path,id:result.id}:undefined);}};
  document.addEventListener('click',click,true);window.addEventListener('rg-tour-success',success);window.addEventListener('scroll',move,true);window.addEventListener('resize',move);
  return()=>{clearTimeout(timer);observer.disconnect();dialogObserver.disconnect();document.removeEventListener('click',click,true);window.removeEventListener('rg-tour-success',success);window.removeEventListener('scroll',move,true);window.removeEventListener('resize',move);document.querySelectorAll('.tour-highlight').forEach(x=>x.classList.remove('tour-highlight'));};
 },[active,current,field,route,modal,user?.id,tracked?.id,ws.orders]);
 useEffect(()=>{if(!tracked)return;const o=ws.orders.find(o=>o.id==tracked.id);if(o&&(tracked.total!==undefined||tracked.tenant!==o.user_id||tracked.provider!==o.provider_id||tracked.status!==o.status)){const next={id:o.id,tenant:o.user_id,provider:o.provider_id,status:o.status};setTracked(next);save('rg-tour-order-v2',next);}},[ws.orders,tracked]);
 useEffect(()=>{if(!active)return;const escape=e=>{if(e.key==='Escape'){e.stopImmediatePropagation();pause();}};document.addEventListener('keydown',escape,true);return()=>document.removeEventListener('keydown',escape,true);},[active]);
 async function switchRole(nextRole){setSwitching(true);setError('');try{const order=ws.orders.find(o=>o.id==tracked?.id);if(!order)throw new Error('Refresh to load this order from the database.');const account=nextRole==='provider'?order.provider_id:nextRole==='tenant'?order.user_id:undefined;if(nextRole!=='admin'&&!account)throw new Error('The receiving demo account is not available yet. Refresh and try again.');const result=await api('demo-login',{role:nextRole,...(account?{account_id:account}:{})});dismissModal?.();await refresh();navigate(nextRole==='admin'?'admin':nextRole==='provider'?'provider':'services');const next=nextRole==='provider'?{mode:'active',id:'delivery',step:0}:{mode:'idle'};save('rg-tour-v3-'+result.user.id,next);setState(next);}catch(e){setError(e.message);}finally{setSwitching(false);}}
 const record=tracked&&ws.orders.find(o=>o.id==tracked.id);
 const results=allowed&&['tenant','provider','admin'].includes(role)&&record&&(['grocery','delivery'].includes(state.id)||(!state.id&&role==='admin'))&&<div className="tour-result"><strong>Sample order #{record.id} · {record.status}</strong><span>{money(record.items.reduce((total,i)=>total+Number(i.quantity)*(Number(i.unit_price)+Number(i.installation_cost)),0))} · {record.property_title}</span><div>{role!=='provider'&&<button type="button" disabled={switching} onClick={()=>switchRole('provider')}>View receiving provider</button>}{role!=='tenant'&&<button type="button" disabled={switching} onClick={()=>switchRole('tenant')}>View customer result</button>}{role!=='admin'&&<button type="button" disabled={switching} onClick={()=>switchRole('admin')}>View admin result</button>}</div></div>;
 if(!allowed&&user&&route!=='browse'&&state.id!=='discovery')return null;
 const coach=active?<section className={'tour-coach '+(spot?.top>innerHeight/2?'tour-coach-top':'')+(spot?.x>innerWidth/2&&innerWidth>700?' tour-coach-left':'')} aria-label="Guided workflow" role="region">
  <div className="tour-coach-head"><span><Compass size={17}/> {workflow.title}</span><button type="button" aria-label="Pause guided tour" onClick={pause}><X size={18}/></button></div>
  <div className="tour-progress" role="progressbar" aria-label="Tour progress" aria-valuemin={0} aria-valuemax={workflow.steps.length} aria-valuenow={state.step+1}><i style={{width:((state.step+1)/workflow.steps.length*100)+'%'}}/></div>
  <div aria-live="polite" aria-atomic="true"><small>Step {state.step+1} of {workflow.steps.length}{current.kind==='form'&&field<fieldCount?` · Field ${field+1} of ${fieldCount}`:''}</small><h3>{modal?.type==='auth'?'Sign in to continue':current.kind==='form'?fieldLabel:current.title}</h3><p>{modal?.type==='auth'?'Choose Demo Tenant to resume this viewing with a fictional account.':current.kind==='form'?(field<fieldCount?'Fill the highlighted field. Optional fields can be left empty.':'Review this section, then use the highlighted button. You control when to submit; we continue after the database saves it.'):current.body}</p></div>
  {!available&&<p className="tour-hint">{modal?.type==='auth'?'Choose Demo Tenant below to continue the viewing, or sign in to your own account.':modal?'Complete or close the open dialog to continue.':current.kind==='form'?'Open the form to continue, or go back to its starting button.':'This action is not available on the records currently shown. Check the status or search this workspace. You can skip or choose another task.'}</p>}
  {current.kind==='form'&&available&&field<fieldCount&&<button type="button" className="tour-primary" onClick={()=>{const control=[...(targetRef.current?.querySelectorAll('input,select,textarea')||[])].find(el=>!el.checkValidity());if(control){control.reportValidity();control.focus();return;}setField(n=>n+1);}}>Next field <ChevronRight size={16}/></button>}
  {((state.id==='grocery'&&state.step>=4)||(state.id==='delivery'&&state.step===workflow.steps.length-1))&&results}
  <div className="tour-controls"><button type="button" disabled={!state.step} onClick={()=>{if(current.kind==='form'&&field>0){setField(n=>n-1);return;}dismissModal?.();setField(0);setState(s=>({...s,step:s.step-1}));}}>Back</button><button type="button" onClick={pause}><Pause size={14}/> Pause</button><button type="button" onClick={()=>{if(current.kind!=='read'||current.closeDialog)dismissModal?.();advance(current.kind!=='read');}}>{current.kind==='read'?'Continue':'Skip step'}</button></div>{!available&&<button type="button" className="tour-change-task" onClick={()=>{dismissModal?.();pause();setPicker(true);launcher.current?.scrollIntoView({block:'center'});}}>Choose another task</button>}{error&&<p role="alert">{error}</p>}
 </section>:null;
 return <>
  <section className="tour-launcher" aria-label="Demo workflows"><div><Compass size={24}/><div><strong>{allowed?'Explore the '+roleLabels[role].toLowerCase()+' portal':'Find your home, one step at a time'}</strong><p>{state.mode==='complete'?'Workflow complete. Try another feature or explore on your own.':'Short guided tasks. You choose where to start and when to stop.'}</p></div></div><div className="tour-launcher-actions">{state.mode==='paused'&&workflow&&<button className="btn secondary" onClick={()=>setState(s=>({...s,mode:'active'}))}><Play size={16}/>Resume tour</button>}<button ref={launcher} className="btn primary" onClick={()=>{if(allowed){if(active)pause();setPicker(p=>!p);}else start(discovery);}} aria-expanded={allowed?picker:undefined}>{allowed?'Test '+roleLabels[role]+' workflow':'Show me how'}<ChevronRight size={17}/></button></div></section>
  {picker&&<section className="tour-picker" aria-label="Portal workflow tasks"><div><h2>What would you like to try?</h2><p>Pick one short task. Explore all portal workflows at your own pace.</p></div><div className="tour-task-grid">{(showAll?tasks:tasks.slice(0,6)).map(task=><button key={task.id} onClick={()=>start(task)}><span>{task.title}</span><small>{taskHint(role,task.id,ws,user)||`${task.steps.length} ${task.steps.length===1?'step':'steps'}`}</small><ChevronRight size={17}/></button>)}</div>{tasks.length>6&&<button className="btn secondary" style={{marginTop:16}} aria-expanded={showAll} onClick={()=>setShowAll(s=>!s)}>{showAll?'Show fewer features':`Show all ${tasks.length} features`}</button>}</section>}
  {!active&&state.mode==='complete'&&<div className="tour-finished" role="status"><CheckCircle2 size={20}/><span>{workflow?.title} {state.skipped?'walkthrough finished. Some steps were skipped; you can retry them anytime.':'complete. You’re ready to use this feature.'}</span></div>}{!active&&results}{error&&!active&&<p className="form-error" role="alert">{error}</p>}
  {coach&&(host?createPortal(coach,host):coach)}{active&&spot&&<MousePointer2 className="tour-pointer" style={{left:spot.x,top:spot.y}} size={28} aria-hidden="true"/>}
 </>;
}
