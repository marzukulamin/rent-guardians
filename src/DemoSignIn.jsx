import {useState} from 'react';
const roles=[['tenant','Tenant'],['owner','Owner'],['provider','Service provider'],['agent','Agent'],['supervisor','Supervisor'],['finance','Finance'],['field_agent','Field agent'],['admin','Administrator']];
export default function DemoSignIn({onSignIn}){
 const [busy,setBusy]=useState(''),[error,setError]=useState('');
 return <section className="demo-accounts" aria-label="Demo sign-in"><strong>Explore a demo account</strong><p>Choose a role, then select Test workflow in its portal. Listings, photos and activity are fictional.</p><div>{roles.map(([role,label])=><button type="button" key={role} disabled={!!busy} onClick={async()=>{setBusy(role);setError('');try{await onSignIn(role);}catch(e){setError(e.message);}finally{setBusy('');}}}>{busy===role?'Signing in…':'Demo '+label}</button>)}</div>{error&&<p className="form-error" role="alert">{error}</p>}</section>;
}


