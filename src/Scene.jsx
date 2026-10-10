import {useEffect,useRef,useState} from 'react';
import * as THREE from 'three';
import {OrbitControls} from 'three/addons/controls/OrbitControls.js';
import {RotateCcw,Pause,Play,ChevronLeft,ChevronRight,Plus,Minus} from 'lucide-react';
export default function Scene({large=false}){
 const host=useRef(null),controlsRef=useRef(null),cameraRef=useRef(null);const [fallback,setFallback]=useState(false),[paused,setPaused]=useState(()=>matchMedia('(prefers-reduced-motion: reduce)').matches);
 useEffect(()=>{
  const el=host.current;let renderer,frame,observer,visible=true;const geometries=[],materials=[];
  try{
   renderer=new THREE.WebGLRenderer({antialias:true,alpha:true});renderer.setPixelRatio(Math.min(window.devicePixelRatio,1.7));renderer.shadowMap.enabled=true;renderer.shadowMap.type=THREE.PCFSoftShadowMap;renderer.setClearColor(0x000000,0);el.appendChild(renderer.domElement);
   const scene=new THREE.Scene();const camera=new THREE.PerspectiveCamera(35,1,.1,100);cameraRef.current=camera;camera.position.set(12,12,14);const controls=new OrbitControls(camera,renderer.domElement);controlsRef.current=controls;controls.enableDamping=true;controls.enablePan=false;controls.minDistance=12;controls.maxDistance=25;controls.maxPolarAngle=Math.PI/2.25;controls.target.set(0,0,0);controls.autoRotate=!matchMedia('(prefers-reduced-motion: reduce)').matches;controls.autoRotateSpeed=.38;
   scene.add(new THREE.HemisphereLight(0xffffff,0x7198bb,2.7));const sun=new THREE.DirectionalLight(0xfff5e8,4);sun.position.set(4,12,7);sun.castShadow=true;sun.shadow.mapSize.set(1024,1024);scene.add(sun);
   const group=new THREE.Group();scene.add(group);
   function box(w,h,d,x,y,z,color,r=0){const geo=new THREE.BoxGeometry(w,h,d);const mat=new THREE.MeshStandardMaterial({color,roughness:.65});geometries.push(geo);materials.push(mat);const m=new THREE.Mesh(geo,mat);m.position.set(x,y,z);m.rotation.y=r;m.castShadow=true;m.receiveShadow=true;group.add(m);return m;}
   box(9,.4,7,0,-.25,0,0xe4e9ed);box(8.6,.08,6.6,0,0,0,0xcbb8a0);
   for(let i=0;i<14;i++)box(8.6,.014,.017,0,.06,-3.2+i*.48,0xbca78d);
   box(9,2.7,.17,0,1.35,-3.45,0xf4f1e9);box(.17,2.7,7,-4.45,1.35,0,0xf4f1e9);box(.13,2.3,3.7,1.45,1.15,-1.6,0xf4f1e9);
   box(2.2,1.6,.04,-2.2,1.7,-3.33,0x9bd1ed);box(.05,1.7,.06,-2.2,1.7,-3.29,0xffffff);box(2.3,.05,.07,-2.2,1.7,-3.28,0xffffff);
   box(3.5,.07,2.7,-2.1,.12,.85,0xf1eeea);box(3,.55,1.15,-2.1,.42,-.1,0x597688);box(3,.65,.27,-2.1,.75,-.55,0x597688);box(.22,.55,1.25,-3.62,.66,-.04,0x597688);box(.22,.55,1.25,-.58,.66,-.04,0x597688);for(let i=0;i<3;i++)box(.87,.17,.95,-3.02+i*.92,.79,-.02,0x87a4b2);box(.42,.42,.2,-2.8,1,-.28,0xebe4d7,.1);box(.42,.42,.2,-1.4,1,-.28,0xce9961,-.1);
   box(1.75,.13,.85,-2.1,.62,1.45,0x916f50);for(const x of [-2.8,-1.4])for(const z of [1.17,1.73])box(.06,.5,.06,x,.32,z,0x493c34);box(.35,.04,.25,-2.3,.72,1.4,0x2d526c);
   box(2.7,.5,2.6,2.9,.32,-1.68,0x9f8369);box(2.65,.25,2.56,2.9,.7,-1.68,0xf5f0e7);box(2.7,.9,.12,2.9,.68,-3.07,0x657c89);box(2.65,.08,1.7,2.9,.86,-1.18,0x8bb5bd);box(.9,.18,.6,2.23,.88,-2.47,0xffffff);box(.9,.18,.6,3.47,.88,-2.47,0xffffff);
   box(1.05,.95,.58,-3.8,.55,-2.7,0xe0e5e9);box(1.1,.08,.62,-3.8,1.05,-2.7,0xededdf);box(1.8,.9,.55,-.5,.55,-2.85,0xe0e5e9);
   box(1.8,.06,1.3,2.8,.97,1.65,0xd4bc96);for(const x of [2.1,3.5])for(const z of [1.16,2.16])box(.08,.93,.08,x,.48,z,0x9a826b);for(const x of [1.5,4.1]){box(.6,.45,.65,x,.48,1.65,0x607d89);box(.12,.75,.65,x,.76,1.65,0x607d89);}
   function plant(x,z){const geo=new THREE.CylinderGeometry(.23,.17,.43,12);const mat=new THREE.MeshStandardMaterial({color:0xd8dce2});geometries.push(geo);materials.push(mat);const pot=new THREE.Mesh(geo,mat);pot.position.set(x,.27,z);group.add(pot);for(let i=0;i<5;i++){const g=new THREE.SphereGeometry(.23,10,8),m=new THREE.MeshStandardMaterial({color:0x448b71});geometries.push(g);materials.push(m);const leaf=new THREE.Mesh(g,m);leaf.scale.set(.65,1.7,.7);leaf.position.set(x+Math.sin(i*1.3)*.18,.75+Math.cos(i)*.14,z+Math.cos(i*1.3)*.18);leaf.rotation.z=Math.sin(i)*.7;group.add(leaf);}}
   plant(-3.7,2.75);plant(.6,-2.75);plant(4,2.9);
   box(.8,.8,.025,.25,1.85,-3.32,0x1d81cd);box(.54,.57,.03,.25,1.85,-3.29,0xc3ddec);
   const resize=()=>{const w=el.clientWidth,h=el.clientHeight;if(!w||!h)return;camera.aspect=w/h;camera.updateProjectionMatrix();renderer.setSize(w,h);};observer=new ResizeObserver(resize);observer.observe(el);resize();
   const motion=matchMedia('(prefers-reduced-motion: reduce)');const updateMotion=()=>{controls.autoRotate=!motion.matches;};motion.addEventListener('change',updateMotion);const onVisibility=()=>{visible=!document.hidden;};document.addEventListener('visibilitychange',onVisibility);
   const tick=()=>{frame=requestAnimationFrame(tick);if(visible){controls.update();renderer.render(scene,camera);}};tick();
   return()=>{cancelAnimationFrame(frame);observer.disconnect();motion.removeEventListener('change',updateMotion);document.removeEventListener('visibilitychange',onVisibility);controls.dispose();renderer.dispose();geometries.forEach(g=>g.dispose());materials.forEach(m=>m.dispose());renderer.domElement.remove();};
  }catch{setFallback(true);renderer?.dispose();}
 },[]);
 const rotate=angle=>{const camera=cameraRef.current,controls=controlsRef.current;if(camera&&controls){camera.position.sub(controls.target).applyAxisAngle(new THREE.Vector3(0,1,0),angle).add(controls.target);controls.update();}};
 const zoom=scale=>{const camera=cameraRef.current,controls=controlsRef.current;if(camera&&controls){const vec=camera.position.clone().sub(controls.target);vec.multiplyScalar(scale);vec.clampLength(controls.minDistance,controls.maxDistance);camera.position.copy(vec.add(controls.target));controls.update();}};
 return <div className={`scene ${large?'scene-large':''}`}><div ref={host} className="scene-canvas" aria-label="Interactive conceptual apartment model. Drag to orbit and scroll to zoom, or use the camera buttons." role="img"/>{fallback&&<div className="scene-fallback">3D preview needs WebGL. Property photos are available below.</div>}<div className="scene-label"><span className="mini-dot"/> {large?'Concept apartment · not a scanned tour':'A little perspective on your next home'}</div><div className="scene-controls"><button className="icon-button" aria-label={paused?'Play 3D rotation':'Pause 3D rotation'} onClick={()=>{if(controlsRef.current)controlsRef.current.autoRotate=paused;setPaused(!paused);}}>{paused?<Play size={14}/>:<Pause size={14}/>}</button>{large&&<><button className="icon-button" aria-label="Rotate camera left" onClick={()=>rotate(.2)}><ChevronLeft size={16}/></button><button className="icon-button" aria-label="Rotate camera right" onClick={()=>rotate(-.2)}><ChevronRight size={16}/></button><button className="icon-button" aria-label="Zoom camera in" onClick={()=>zoom(.9)}><Plus size={16}/></button><button className="icon-button" aria-label="Zoom camera out" onClick={()=>zoom(1.1)}><Minus size={16}/></button></>}<button className="icon-button" aria-label="Reset 3D camera" onClick={()=>controlsRef.current?.reset()}><RotateCcw size={16}/></button></div></div>;
}
