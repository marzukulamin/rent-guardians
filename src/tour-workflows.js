// Short, opt-in tasks. Actions use the same controls and API as manual testing.
const read=(route,title,body,target)=>({route,title,body,target,kind:'read'});
const click=(route,title,body,target)=>({route,title,body,target,kind:'click'});
const form=(route,path)=>({route,path,kind:'form',title:'Complete the details',body:'We’ll guide you through one field at a time. Review before submitting.'});
const task=(id,title,steps)=>({id,title,steps});
const create=(id,title,route,button,path,body)=>task(id,title,[click(route,title,body,{text:button}),form(route,path),read(route,'See the result','Your saved record appears here. You can continue exploring freely.')]);
const review=(id,title,route,body,target)=>task(id,title,[read(route,title,body,target)]);
const act=(route,title,button,path,action,body)=>({route,title,body:body||'Review the sample record, then use the highlighted button. We’ll continue after the change is saved.',target:{text:button},kind:'action',path,pathPrefix:true,action});
const actionTask=(id,title,route,button,path,action,body)=>task(id,title,[read(route,title,body),act(route,title,button,path,action),read(route,'Check the updated status','The record now shows the saved result. You can continue testing other features.')]);
const recordForm=(id,title,route,button,path,action,body)=>task(id,title,[click(route,title,body,{text:button}),{...form(route,path),pathPrefix:true,action},read(route,'See the result','Your changes are saved in this portal. Related users see the updated record.')]);
const support=create('support','Help & support','support','Ask your guardian','support','Open a request, describe the issue, then follow its status.');
export const discovery=task('discovery','Find a home & book a viewing',[
 read('browse','Choose your preferences','Set a neighbourhood, budget and bedrooms. Leave any filter open to see more homes.',{id:'home-filters'}),
 click('browse','Find matching homes','Select Find homes to apply your filters.',{id:'home-search'}),
 click('browse','Look inside a home','Open a home to compare photos, costs and its inspection.',{id:'home-details'}),
 read('browse','Compare the details','Check the monthly total and advance. The inspection report is available above.',{selector:'.property-detail'}),
 click('browse','Book a viewing','Choose Book a viewing. Guests can use the Tenant demo option in the sign-in popup.',{id:'book-viewing'}),
 form('browse','appointments'),
 read('appointments','Your viewing request','Your request appears here. A supervisor assigns an agent; confirmation and entry details arrive in your updates.',{created:true,resource:'appointments'})
]);
const grocery=task('grocery','Place & follow a grocery order',[
 click('services','Choose groceries','Start with one sample item from one provider.',{id:'grocery'}),
 click('services','Add one item','Select the highlighted plus button.',{id:'service-add'}),
 click('services','Choose delivery','Your basket shows the current price. Continue to select your demo home.',{id:'grocery-checkout'}),
 form('services','orders'),
 click('services','See your new order','Open My orders to see its number, provider and status.',{id:'orders'}),
 read('services','Follow the handoff','Use the result buttons below to open the exact receiving provider, then inspect the same order as admin.',{tracked:true})
]);
const tenancy=review('tenancy','Tenancy & agreements','tenancy','Review your household, agreement and tenancy status. Signing and move-out controls appear when that record is eligible.',{selector:'.tenancy-card'});
const documents=create('documents','Upload verification documents','inspections','Upload document','upload','Choose a sample PDF or image and the relevant tenancy.');
const bills=review('bills','Bills & payment receipts','payments','Compare invoice balances and payment status. Submit a payment only against an open invoice; supervisor review comes before finance confirmation.',{selector:'.invoice-card'});
const repairs=create('repair','Report a repair','maintenance','Report an issue','maintenance','Choose your demo home, describe a small repair and decide who should pay.');
const repairReview=review('repair-review','Repair decisions & costs','maintenance','Open a sample repair. Use the available approval, assignment or cost control for its current status.',{selector:'.request-card'});
const listing=create('listing','List a property','listings','List a property','properties','Enter the property in short sections. Save a draft or review before sending.');
const photos=task('photos','Manage property photos',[click('listings','Open property photos','Choose a property and manage its listing images.',{text:'Manage photos'}),read('listings','Choose photographs','Select a sample interior image, choose its category and enter a caption.',{selector:'.photo-manager > form'}),read('listings','Arrange the gallery','Save captions, move photos earlier or later, or choose a cover. Use Remove only when you want to archive a photo.',{selector:'.photo-management-grid'})]);
const appointments=review('appointments','Visits & appointments','appointments','Review an assigned visit and its time. Entry verification and completion are available at the appropriate status.',{selector:'.appointment-card'});
const inspection=create('inspection','Create an inspection','inspections','New inspection','inspections','Choose an assigned property, document rooms and fixtures, then submit for supervisor review.');
const paymentReview=actionTask('payment-review','Review submitted payments','payments','Review','payments/','review','Check the invoice and receipt. Finance confirms the payment after your review.');
const delivery=task('delivery','Fulfil delivery orders',[
 click('provider','Open delivery orders','See customer details and the current order status.',{id:'deliveries'}),
 ...[['placed','accepted','Accept the order'],['accepted','delivering','Start delivery'],['delivering','completed','Mark the order completed']].map(([from,status,title])=>({route:'provider',title,body:'Use the highlighted order button. We’ll continue once the new status is saved.',kind:'action',orderFrom:from,status})),
 read('provider','Check the handoff','The customer sees the same status. Use View customer result or View admin result to follow this order.',{tracked:true})
]);
export const roleLabels={tenant:'Tenant',owner:'Owner',provider:'Service provider',agent:'Agent',supervisor:'Supervisor',finance:'Finance',field_agent:'Field agent',admin:'Administrator'};
export const workflows={
 tenant:[discovery,task('saved','Save & compare homes',[click('browse','Open a home','Choose a home for your shortlist.',{id:'home-details'}),act('browse','Save this home','Save home','saved',undefined,'Save it to compare later.'),read('saved','Your shortlist','Your saved home appears here.')]),
 task('application','Apply to rent',[click('browse','Open a home','Choose an available demo home.',{id:'home-details'}),click('browse','Start an application','Review the advance and move-in date.',{text:'Apply to rent'}),form('browse','tenancies'),read('tenancy','Your application','Follow verification, signatures and payment before activation.')]),
 tenancy,recordForm('sign','Sign an agreement','tenancy','Sign agreement','tenancies/','sign','Read the sample agreement before entering your name.'),recordForm('household','Add a household member','tenancy','Add household member','tenancies/','add_member','Add a fictional flatmate or family member.'),documents,bills,create('payment','Record a payment','payments','Record payment','payments','Record a fictional receipt against an open demo invoice. No money is charged.'),repairs,grocery,
 ...[['installation','Move-in essentials'],['maid','Home help'],['moving','Moving support']].map(([id,title])=>task(id,title,[click('services',title,'Choose this service category.',{id}),click('services','Arrange a service','Choose one provider and check the price.',{text:'Arrange this service'}),form('services','orders'),read('services','Track your service','Open My orders to follow the booking.')])),
 task('meals','Daily meals',[click('services','Choose daily meals','Compare meal plans and daily prices.',{id:'catering'}),click('services','Choose a plan','Select a plan for your demo home.',{text:'Choose this plan'}),form('services','meals'),read('services','Subscription saved','Find the schedule and status under My orders.')]),recordForm('move-out','Plan a move-out','tenancy','Give move-out notice','tenancies/','move_out','Choose a date at least three days ahead for the demo inspection.'),create('referral','Refer a friend','account','Refer a friend','referrals','Use a fictional email to test referral tracking.'),support],
 owner:[listing,photos,recordForm('edit-listing','Edit listing details','listings','Edit details','properties',undefined,'Update a sample listing, then review before submitting.'),create('inspection-booking','Request a property inspection','listings','Request inspection','appointments','Choose a future appointment time.'),appointments,tenancy,recordForm('sign','Sign an agreement','tenancy','Sign agreement','tenancies/','sign','Read the sample agreement before entering your name.'),documents,
 actionTask('owner-repair','Approve a repair cost','maintenance','Agree to pay','maintenance/','owner_approve','Review a repair waiting for the owner’s decision.'),recordForm('owner-cost','Record a repair expense','maintenance','Enter my cost','maintenance/','cost','Match the fictional provider’s final price to test reconciliation.'),bills,create('bank','Bank details','account','Add account','bank','Add fictional payout details for the demo.'),support],
 provider:[task('jobs','Service work & entry codes',[click('provider','Open service work','Review nearby and assigned jobs.',{id:'jobs'}),act('provider','Claim a nearby job','Accept this job','maintenance/','claim'),click('provider','Verify entry','Ask the demo customer for the six-digit entry code in their notifications.',{text:'Verify entry'}),{...form('provider','maintenance/'),pathPrefix:true,action:'verify_entry'},act('provider','Start the work','Start work','maintenance/','start'),click('provider','Complete the work','Record the final fictional service price.',{text:'Complete & enter price'}),{...form('provider','maintenance/'),pathPrefix:true,action:'complete'},read('provider','Work submitted','The customer can confirm the cost; the supervisor then reconciles it.')]),delivery,
 task('catalog','Add a catalog item',[click('provider','Open My catalog','Manage your products and services.',{id:'catalog'}),...create('catalog-form','Add an item','provider','Add catalog item','provider/products','Add a fictional product or service.').steps]),
 task('meal-plan','Create a meal plan',[click('provider','Open Catering','Manage meal plans and the delivery roster.',{id:'meals'}),...create('meal-form','Create a plan','provider','Add meal plan','provider/meals','Enter the plan and daily price.').steps]),
 create('profile','Provider profile','provider','My profile','provider/profile','Check your area, expertise and availability.'),support],
 agent:[appointments,recordForm('entry','Verify visit entry','appointments','Verify entry','appointments/','verify','Use the entry code from the requesting demo user’s notifications.'),actionTask('visit-complete','Complete a verified visit','appointments','Complete visit','appointments/','complete','Only verified visits can be completed.'),inspection,documents,review('training','Training & identity','training','Read your training materials. Your downloadable ID is available in Account settings.'),support],
 supervisor:[recordForm('assign','Assign appointments','appointments','Assign agent','appointments/','confirm','Choose a verified agent for a requested viewing or inspection.'),actionTask('publish','Review & publish listings','listings','Publish listing','properties',undefined,'Review the inspection before publishing a pending sample property.'),actionTask('approve-documents','Approve verification documents','inspections','Approve','admin/review',undefined,'Review a pending sample document.'),actionTask('approve-report','Approve inspection reports','inspections','Approve report','admin/review',undefined,'Check the rooms and fixtures in a submitted report.'),actionTask('activate','Activate a ready tenancy','tenancy','Verify & activate tenancy','tenancies/','activate','Both signatures, verification and confirmed advance are required.'),repairReview,actionTask('approve-repair','Approve a repair request','maintenance','Approve request','maintenance/','approve','Check responsibility before approving.'),recordForm('assign-provider','Assign a service provider','maintenance','Assign provider','maintenance/','assign','Choose a verified provider.'),actionTask('reconcile','Reconcile repair costs','maintenance','Reconcile costs','maintenance/','reconcile','Provider and customer prices must match.'),{...paymentReview,steps:paymentReview.steps.map(step=>step.target?.text==='Review'?{...step,target:{text:'Review payment'}}:step)},review('team','Agents & providers','team','Find verified contacts and send an in-app follow-up.'),support],
 finance:[actionTask('confirm','Confirm reviewed payments','payments','Confirm payment','payments/','confirm','Only supervisor-reviewed payments can be confirmed. Check the receipt and invoice.'),review('bills','Receipt evidence & balances','payments','Match receipt documents, account names and references. Only confirmed credits reduce invoice balances.'),review('reports','Financial reports','financial-reports','Choose a financial report, inspect the approval trail and export a page.'),support],
 field_agent:[review('campaign','Campaign & training','operations','Read your assigned campaign dates and training before recording outreach.'),create('checklist','Daily outreach checklist','operations','Fill daily checklist','campaigns/checklist','Use fictional owner details and record today’s outreach.'),create('request-visit','Request an appointment','operations','Request appointment','support','Send the owner’s area and preferred appointment time to operations.'),support],
 admin:[...[['users','Accounts & roles','Inspect accounts and permissions.'],['datasets','All datasets','Choose a dataset to inspect records and export the current page.'],['sql','Reports & exports','Choose a report to inspect linked data and export it.'],['audit','Audit history','Trace the actor, action and record number for each saved change.'],['settings','Operational settings','Review settings; open Update setting to test a configuration change.']].map(([id,title,body])=>task(id,title,[click('admin',title,body,{id:'admin-'+id}),read('admin',title,body)])),paymentReview,repairReview,support]
};
// Additional role pages stay optional, one short task at a time.
workflows.owner.push(review('portfolio','Portfolio overview','home','Review occupancy, expected rent and decisions for the properties you own.'));
workflows.agent.push(review('assigned-properties','Assigned property details','listings','Review the address and prepare for your assigned home visit.'),review('handoffs','Services & move-out handoffs','handoffs','Inspect delivery contacts and move-out dates. Use Add note to save the next action.'),recordForm('repair-note','Record a repair follow-up','maintenance','Add follow-up note','operations/note',undefined,'Save a coordination note without changing approval or payment status.'));
workflows.supervisor.push(listing,review('dispatch-summary','Review & dispatch summary','operations','Choose the queue that needs attention first.'),review('handoffs','Service handoffs','handoffs','Follow actual orders, catering and move-out readiness.'),recordForm('team-follow-up','Follow up with the team','team','Send follow-up','operations/notify',undefined,'Select a team member and enter a short in-app follow-up.'),recordForm('support-assignment','Assign a support request','support','Assign request','operations/support','assign','Choose the responsible active agent or supervisor.'));
workflows.provider.push(task('edit-catalog','Edit a catalog item',[click('provider','Open catalog','Prices and availability come from your own database catalog.',{id:'catalog'}),...recordForm('edit-item','Edit catalog item','provider','Edit item','provider/products',undefined,'Update your item and save its database record.').steps]),task('edit-meal','Edit a meal plan',[click('provider','Open catering','Review your own meal plans.',{id:'meals'}),...recordForm('edit-plan','Edit meal plan','provider','Edit plan','provider/meals',undefined,'Update the plan or pause new subscriptions.').steps]),review('bills-documents','Work bills & evidence','provider-documents','Upload a receipt or work evidence against a service job assigned to you.'));
workflows.field_agent.push(review('outreach-history','Outreach history','outreach','Review the owner contacts and checklists you saved.'),review('training','Campaign scripts & training','training','Read the approved materials before starting outreach.'));
export const isDemoUser=u=>!!u&&/^(demo\.[a-z_]+\.\d{3}|tenant|owner|provider|agent|supervisor|finance|field_agent|demo-admin|flatmate)@rentguardian\.local$/.test(u.email);
workflows.admin.push(
 task('demo-permissions','Manage demo account permissions',[click('admin','Open accounts','Find a fictional demo account.',{id:'admin-users'}),click('admin','Manage a demo account','The highlighted account belongs to the fictional demo population.',{id:'demo-account-manage'}),form('admin','admin/users'),read('admin','Account updated','Search for the demo account to check its saved permissions.')]),
 task('configure','Update an operational setting',[click('admin','Open settings','Review the operational settings.',{id:'admin-settings'}),...create('setting-form','Update a setting','admin','Update setting','admin/settings','Choose the setting, enter its value, then save.').steps])
);
// Targets follow the rebuilt portals; tour copy contains no business fixtures.
const section=name=>({selector:'[data-tour-section="'+name+'"]'});
const replaceTask=(role,id,next)=>{const index=workflows[role].findIndex(t=>t.id===id);if(index>=0)workflows[role][index]=next;else workflows[role].push(next);};
replaceTask('owner','portfolio',task('portfolio','Understand your portfolio',[
 read('home','Your portfolio at a glance','See your owned homes, occupancy, expected rent and outstanding decisions.',{selector:'.stats-grid'}),
 read('home','Start with a decision','Agreements and repair decisions appear here when they need you.',section('needs-your-decision')),
 click('home','Open your properties','Choose Manage properties to work with your own listings.',{id:'role-link-listings'}),
 read('listings','Your property workspace','Search your listings, update photos or edit details. Choose another short task to try an action.',{id:'workspace-properties'})]));
replaceTask('agent','assignments',task('assignments','Start with your assignments',[
 read('operations','Your work today','Counts reflect visits and repairs assigned to you, plus reports awaiting review.',{selector:'.stats-grid'}),
 click('operations','Open your visit schedule','Choose Visit schedule to see the people, time and entry requirements.',{id:'role-link-appointments'}),
 read('appointments','Prepare for the visit','Check the customer and owner contacts. Entry and completion controls appear only when a visit is ready.',{id:'workspace-appointments'})]));
replaceTask('agent','assigned-properties',review('assigned-properties','Assigned property details','listings','Check the address, room counts and property details for the homes assigned to you.',{selector:'.assigned-property'}));
for(const role of ['agent','supervisor']){
 replaceTask(role,'handoffs',task('handoffs','Services & move-out handoffs',[
 read('handoffs','Who is doing what next?','Compare each order’s customer, provider, schedule and status.',section('orders-installations')),
 read('handoffs','Meals and move-outs','Check catering dates and move-out readiness before coordinating a visit.',section('move-out-readiness'))]));
 workflows[role].push(recordForm('handoff-note','Save a handoff note','handoffs','Add note','operations/note',undefined,'Open a real handoff and record the next action.'));
 workflows[role].push(recordForm('take-support','Take a support request','support','Take request','operations/support','take','Read the request, then record your first response.'));
 workflows[role].push(recordForm('resolve-support','Resolve a support request','support','Resolve request','operations/support','resolve','Confirm the work is done and explain the resolution.'));
}
workflows.agent.push(recordForm('escalate-support','Escalate to a supervisor','support','Escalate','operations/support','escalate','Explain what needs a supervisor’s decision.'),recordForm('complaint-note','Follow up on a complaint','support','Record follow-up','operations/note',undefined,'Record the next action against the complaint.'));
replaceTask('supervisor','dispatch-summary',task('dispatch-summary','Start with review & dispatch',[
 read('operations','Choose the next queue','Counts show actual work awaiting review or assignment.',{selector:'.role-queues'}),
 click('operations','Open agent dispatch','Start with visits that need an agent.',{id:'role-link-appointments'}),
 read('appointments','Check the appointment','Read the requester, property and preferred time before assigning a verified agent.',{id:'workspace-appointments'})]));
replaceTask('supervisor','team',task('team','Find agents & providers',[
 read('team','Find a verified agent','Use the recorded membership ID and phone to identify the right person.',section('agents')),
 read('team','Find the right provider','Check verified provider contacts. Send follow-up saves an in-app notification.',section('providers'))]));
for(const id of ['approve-documents','approve-report']){const t=workflows.supervisor.find(t=>t.id===id);t.steps[1].entity=id==='approve-documents'?'documents':'inspections';t.steps[1].status='approved';}
replaceTask('finance','bills',task('bills','Inspect receipt evidence & balances',[
 click('payments','Open payment evidence','Choose a payment to inspect its reference and linked invoice.',{id:'payment-evidence'}),
 {...read('payments','Compare the invoice and reference','Check the recorded payer, amount, reviewer and invoice balance. Receipt files are listed when uploaded.',{selector:'.modal-body'}),closeDialog:true},
 read('payments','Receipt documents','Match these files by account and tenancy. Only confirmed credits reduce the invoice balance.',section('submitted-receipt-documents'))]));
replaceTask('finance','reports',task('reports','Explore financial reports',[
 read('financial-reports','Choose a report','Switch between dues, rent roll, approval trail, costs, owner income and margins.',{id:'finance-report-select'}),
 read('financial-reports','Inspect and export','Read the current database results. Use Next for more records or Export this page for the current rows.',{text:'Export this page'})]));
replaceTask('field_agent','campaign',task('campaign','Prepare for campaign outreach',[
 read('operations','Check your assigned campaign','Check the area and campaign dates. Outreach controls become available during the assigned dates.',{selector:'.tour-page [data-tour-section]'}),
 click('operations','Prepare for conversations','Open the approved scripts before recording an owner conversation.',{id:'role-link-training'}),
 read('training','Read your guidance','These materials are assigned to your role. Return to My campaigns when you are ready.',{selector:'.tour-page [data-tour-section]'})]));
replaceTask('field_agent','outreach-history',review('outreach-history','Review your outreach history','outreach','Review saved campaign, owner phone, conversation date and completed checklist.',section('recorded-conversations')));
replaceTask('provider','bills-documents',task('bills-documents','Work bills & documents',[
 read('provider-documents','Your private work files','Check each receipt or evidence file and its status.',section('your-uploads')),
 ...create('provider-upload','Upload work evidence','provider-documents','Upload document','upload','Choose a sample receipt or work image and the assigned service job.').steps]));
replaceTask('admin','users',task('users','Explore an account & its portal',[
 click('admin','Open accounts','Start with the account directory.',{id:'admin-users'}),
 read('admin','Find the right person','Search by name, email, phone or membership ID. Filters narrow the account role and access status.',{id:'admin-account-search'}),
 click('admin','Open one account','Open an account to see its identity and connected records.',{id:'admin-open-account'}),
 click('admin','View their portal','Open the selected person’s portal with their current database records.',{id:'admin-portal-preview'}),
 read('admin','Explore their portal sections','Choose a section to see that role’s features. Changes keep your administrator identity in the audit history.',{id:'admin-portal-section'})]));
replaceTask('admin','datasets',task('datasets','Inspect & edit connected records',[
 click('admin','Open records','Start with all connected database record types.',{id:'admin-datasets'}),
 read('admin','Choose a record type','Choose the dataset, then narrow it with search or status.',{id:'admin-order-dataset'}),
 click('admin','Inspect one record','Open its complete details and available workflow actions.',{id:'admin-open-record'}),
 {...read('admin','Details, contacts and actions','Review linked people and the record’s current status. Edit details is available where the schema and permissions allow it.',{selector:'.admin-detail'}),closeDialog:true}]));
replaceTask('admin','work-queue',task('work-queue','Guide pending & overdue work',[
 click('admin','Open the work queue','Find requests waiting for assignment, review or follow-up.',{id:'admin-attention'}),
 read('admin','Find work needing attention','Filter by work type, person or overdue status.',{id:'admin-work-filters'}),
 click('admin','Review & guide','Open a request to inspect the exact record and next actions.',{id:'admin-review-work'}),
 {...read('admin','Give the work a next step','Inspect contacts and actions. Follow up sends an in-app notification to the responsible account.',{selector:'.admin-detail'}),closeDialog:true}]));
replaceTask('admin','documents',task('documents','User reports & documents',[
 click('admin','Open the document workspace','Documents and inspection reports are connected to their account and property.',{id:'admin-documents'}),
 read('admin','Choose documents or reports','Choose a record type to find the file or report you need.',{id:'admin-order-dataset'}),
 click('admin','Inspect the stored record','Open the complete details, download links and available review controls.',{id:'admin-open-record'}),
 {...read('admin','Review the evidence','Verify the linked account and tenancy before approving or rejecting a pending record.',{selector:'.admin-detail'}),closeDialog:true}]));
replaceTask('admin','sql',task('sql','Reports & exports',[
 click('admin','Open reports','Read live property, financial, service and team reports.',{id:'admin-sql'}),
 read('admin','Choose a report','Select a report and inspect its current database results. Export page downloads the rows currently shown.',{selector:'.admin-reports .admin-filters'}),
 read('admin','Check complete portal coverage','All portal features opens the role-by-role inventory.',{text:'All portal features'})]));
workflows.admin.push(task('edit-record','Edit a stored record',[
 click('admin','Open records','Find the record you want to update.',{id:'admin-datasets'}),
 click('admin','Open its details','Check its identity and linked account before editing.',{id:'admin-open-record'}),
 click('admin','Edit details','Editable fields follow the database schema. Locked or audited records retain their protections.',{text:'Edit details'}),
 form('admin','admin/record/save'),read('admin','Check the saved record','Your changes and reason are recorded in the database audit history.')]),
 task('queue-follow-up','Follow up on missed work',[
 click('admin','Open pending work','Choose a request with an assigned responsible person.',{id:'admin-attention'}),
 click('admin','Send a follow-up','Check the assigned person and linked request before composing your message.',{text:'Send follow-up'}),
 form('admin','admin/notify'),read('admin','Follow-up recorded','The responsible account receives an in-app notification linked to the work.')])
);
workflows.owner.find(t=>t.id==='photos').steps.at(-1).closeDialog=true;
// The redesigned administrator performs operations in the connected work queue.
workflows.admin=workflows.admin.filter(t=>!['payment-review','repair-review'].includes(t.id));
for(const role of ['agent','supervisor','finance','field_agent'])workflows[role].push(recordForm('identity','Update your work identity','account','Edit profile','profile',undefined,'Keep your name and contact phone current. Your ID and reminder preferences are available here too.'));
workflows.owner.push(create('complaint','Raise a tenant complaint','tenancy','Raise a complaint','complaints','Describe the concern against the correct tenancy.'));
workflows.supervisor.push(actionTask('close-tenancy','Settle & close a tenancy','tenancy','Settle & close tenancy','tenancies/','end','Review the move-out inspection and outstanding fees first.'),actionTask('approve-transfer','Approve a tenant transfer','tenancy','Approve transfer','transfers/','approve','Check the accepted transfer and recipient verification.'));
export const featuredTasks={
 tenant:['discovery','grocery','repair','payment','tenancy','documents'],
 owner:['portfolio','listing','photos','tenancy','owner-repair','bills'],
 agent:['assignments','assigned-properties','inspection','entry','handoffs','repair-note'],
 supervisor:['dispatch-summary','assign','approve-report','approve-repair','payment-review','team'],
 finance:['bills','confirm','reports','support','identity'],
 field_agent:['campaign','checklist','outreach-history','request-visit','training','support'],
 provider:['jobs','delivery','catalog','meal-plan','bills-documents','profile'],
 admin:['users','work-queue','datasets','documents','sql','audit']
};
// Eligibility hints read the signed-in account's database workspace; they never create fixtures.
export function taskHint(role,id,ws={},user={}){
 const some=(key,predicate)=>Array.isArray(ws[key])&&ws[key].some(predicate);
 if(role==='agent'&&id==='entry'&&!some('appointments',a=>a.assigned_to==user.id&&a.status==='confirmed'&&!a.otp_verified_at))return 'Needs an assigned, confirmed visit';
 if(role==='agent'&&id==='visit-complete'&&!some('appointments',a=>a.assigned_to==user.id&&a.status==='confirmed'&&a.otp_verified_at))return 'Needs a visit with verified entry';
 if(role==='finance'&&id==='confirm'&&!some('payments',p=>p.status==='reviewed'&&p.reviewed_by!=user.id))return 'Needs an independently reviewed payment';
 if(role==='provider'&&id==='jobs'&&!some('jobs',()=>true))return 'Claiming needs an available nearby job';
 if(role==='provider'&&id==='delivery'&&!some('orders',o=>['placed','accepted','delivering'].includes(o.status)))return 'Needs an active customer order';
 if(role==='field_agent'&&['checklist','request-visit'].includes(id)&&!some('campaigns',c=>ws.server_date>=c.starts_on&&ws.server_date<=c.ends_on))return 'Needs a campaign active today';
 return '';
}

export function matchesTourSuccess(step,{path,body={}}={},orderId,recordId){
 if(!step||!['form','action'].includes(step.kind))return false;
 if(step.orderFrom)return !!orderId&&path===`orders/${orderId}/action`&&body.status===step.status;
 if(!step.path||typeof path!=='string')return false;
 const matches=step.pathPrefix?path.startsWith(step.path):path===step.path;
 const recordMatches=!recordId||(!step.pathPrefix&&!step.entity)||
  (step.pathPrefix&&path.match(/\/(\d+)\/action$/)?.[1]==recordId)||body.id==recordId;
 return matches&&recordMatches&&(!step.action||body.action===step.action)&&(!step.status||body.status===step.status)&&(!step.entity||body.entity===step.entity);
}
