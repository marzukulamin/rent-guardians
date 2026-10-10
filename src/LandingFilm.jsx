import {useEffect,useRef,useState} from 'react';
import {Pause,Play,X} from 'lucide-react';
import './landing-film.css';

const media='/media/rent-guardian/';
export function LandingIntro(){
 const dialog=useRef(null),video=useRef(null),skip=useRef(null);
 const [portrait]=useState(()=>window.matchMedia('(max-aspect-ratio: 3/4)').matches);
 const [finished,setFinished]=useState(()=>window.matchMedia('(prefers-reduced-motion: reduce)').matches),[revealing,setRevealing]=useState(false);
 const finish=()=>setFinished(true);
 useEffect(()=>{
  if(finished)return;
  const el=dialog.current,previous=document.activeElement;
  const motion=window.matchMedia('(prefers-reduced-motion: reduce)');
  const reduce=()=>{if(motion.matches)setFinished(true);};
  if(typeof el?.showModal!=='function'){setFinished(true);return;}
  el.showModal();skip.current?.focus();
  const previousOverflow=document.body.style.overflow;document.body.style.overflow='hidden';
  // Never keep the landing page behind a stalled or blocked video.
  const deadline=setTimeout(()=>setFinished(true),24000);
  motion.addEventListener('change',reduce);
  const play=video.current?.play();play?.catch(()=>setFinished(true));
  return()=>{clearTimeout(deadline);motion.removeEventListener('change',reduce);el.close();document.body.style.overflow=previousOverflow;if(previous?.isConnected)previous.focus();};
 },[finished]);
 if(finished)return null;
 return <dialog ref={dialog} className={`rg-entrance-film${revealing?' rg-film-reveal':''}`} aria-label="Rent Guardian introduction" onCancel={finish}>
  <video ref={video} autoPlay muted playsInline preload="auto" poster={media+(portrait?'intro-phone-poster.jpg':'intro-poster.jpg')} onTimeUpdate={e=>{if(e.currentTarget.currentTime>=17.5)setRevealing(true);}} onEnded={finish} onError={finish} aria-label="An architectural journey into a home, introducing rental records and everyday support">
   <source src={media+(portrait?'intro-phone.mp4':'intro-web.mp4')} type="video/mp4"/>
  </video>
  <button ref={skip} className="rg-film-skip" onClick={finish}>Skip intro <X size={17}/></button>
 </dialog>;
}

export function HeroFilm(){
 const video=useRef(null);
 const [paused,setPaused]=useState(()=>window.matchMedia('(prefers-reduced-motion: reduce)').matches);
 const [failed,setFailed]=useState(false);
 useEffect(()=>{
  const motion=window.matchMedia('(prefers-reduced-motion: reduce)');
  const reduce=()=>setPaused(motion.matches);motion.addEventListener('change',reduce);
  return()=>motion.removeEventListener('change',reduce);
 },[]);
 useEffect(()=>{
  if(paused||failed){video.current?.pause();return;}
  video.current?.play()?.catch(()=>setPaused(true));
 },[paused,failed]);
 return <div className="rg-hero-film">
  {failed?<img src={media+'hero-poster.jpg'} alt="Concept home with a welcoming living room"/>:<video ref={video} muted playsInline loop preload="metadata" poster={media+'hero-poster.jpg'} onError={()=>setFailed(true)} aria-label="Silent seven-second concept apartment animation"><source src={media+'hero-web.mp4'} type="video/mp4"/></video>}
  <span className="rg-hero-caption">Concept home</span>
  {!failed&&<button className="rg-hero-play" onClick={()=>setPaused(v=>!v)} aria-label={paused?'Play home animation':'Pause home animation'}>{paused?<Play size={15}/>:<Pause size={15}/>}</button>}
 </div>;
}
