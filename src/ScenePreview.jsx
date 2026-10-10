import {lazy,Suspense} from 'react';
const Scene=lazy(()=>import('./Scene'));
export default function ScenePreview(props){
 return <Suspense fallback={<div className="scene" aria-label="Home illustration loading"/>}><Scene {...props}/></Suspense>;
}
