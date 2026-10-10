// Navigation follows the user stories and the staff permission document.
export function portalRole(roles=[]){return ['admin','supervisor','finance','agent','field_agent','provider','owner','tenant'].find(r=>roles.includes(r))||'tenant';}
export const rolePortals={
 owner:{name:'Owner portfolio',start:'home',items:[['home','Portfolio overview'],['listings','My properties'],['tenancy','Residents & agreements'],['appointments','Property appointments'],['payments','Collections & expenses'],['maintenance','Repairs & decisions'],['inspections','Reports & documents'],['support','Help & complaints']]},
 agent:{name:'Agent workspace',start:'operations',items:[['operations','My assignments'],['appointments','My visit schedule'],['listings','Assigned properties'],['inspections','House reports & photos'],['maintenance','Repair coordination'],['handoffs','Services & move-outs'],['support','Assigned support'],['training','Training & guidance']]},
 supervisor:{name:'Supervisor workspace',start:'operations',items:[['operations','Review & dispatch'],['listings','Portfolio listings'],['appointments','Agent dispatch'],['tenancy','Tenancy readiness'],['inspections','Report & document review'],['maintenance','Repair approvals'],['payments','Payment review'],['handoffs','Service handoffs'],['support','Support & escalations'],['team','Agents & providers']]},
 finance:{name:'Finance workspace',start:'payments',items:[['payments','Payment confirmation'],['financial-reports','Financial reports'],['support','Internal support']]},
 field_agent:{name:'Field campaign workspace',start:'operations',items:[['operations','My campaigns'],['outreach','Outreach history'],['training','Scripts & training'],['support','Appointment requests']]},
 provider:{name:'Provider workspace',start:'provider',items:[['provider','Jobs, orders & catering'],['provider-documents','My bills & documents'],['support','Help & support']]}
};
export function portalRoutes(role){return rolePortals[role]?[...rolePortals[role].items.map(([key])=>key),'account',...role==='owner'?['browse','saved','services']:[]]:null;}
