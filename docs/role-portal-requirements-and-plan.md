# Role portals: requirements and implementation plan

Source: E:/Rent Guardian/My Own Work/User Story Rent Guardian .docx (161 rows, mirrored in database/user-stories.json). Supplemented by Agent Role Permissions (App Access Control).docx, Work of an Agent_.docx, Work of Supervisor.docx and Who are the Field Agents.docx.

The role-permission document defines approval boundaries when operational task prose conflicts with it: agents execute; supervisors review; finance independently confirms. Tenant and administrator experiences are retained.

## Complete user-story features

### Owner

- Story 1: I don’t want to answer lot of questions — I am relaxed and comfortable
- Story 2: I want to list properties on Rent Guardian — So that I have to work less and take less hassle  to find tenant
- Story 3: I want to see the contract, I am signing in — So that , I can know what I am digitally signing in the app
- Story 4: I want to download the contract after both party signed. — So that , I can keep it to myself as a copy
- Story 5: I want to view properties like tenant — As I need to rent properties as well.
- Story 6: I want to update the listing . — As I need to update some parts of my listing.
- Story 7: I want to remove the listing. — As I need to remove the listing if the properties get rented on my end.
- Story 8: I want to give exact flat number — So that Agents and tenants can navigate to my exact flat and do not disturb me through recurring  calls for address
- Story 9: I want to give the exact floor No. — So that the agents and tenants can navigate to my exact floor No and do not disturb me through recurring  calls for address.
- Story 10: I want to give the exact House Name . — So that the agents and tenants can navigate to my exact House and do not disturb me through recurring  calls for address
- Story 11: I want to give exact House No — So that the agents and tenants can navigate to my exact House No and do not disturb me through recurring  calls for address
- Story 12: I want to give exact Road No — So that the agents and tenants can navigate to my exact Road No and do not disturb me through recurring  calls for address
- Story 13: I want to give exact Sub Area — So that the agents can navigate to my exact Sub Area and tenants can search through Sub Areaand do not disturb me through recurring  calls for address
- Story 14: I want to give exact Area — So that the agents can navigate to my exact Area and tenants can search through Area and do not disturb me through recurring  calls for address
- Story 15: I want to give exact District — So that the agents can navigate to my exact District and tenants can search through District and do not disturb me through recurring  calls for address.
- Story 16: I want to give exact Division — So that the agents can navigate to my exact Area and tenants can search through the Area and do not disturb me through recurring  calls for address.
- Story 17: I want to add utility charge and check/tick the charges that are included in it . — As I want to remain transparent about the hidden charges in utilities
- Story 18: I want to rent it to only family/bachelor — So that I can rent it to my exact preferred group of people
- Story 19: I want to provide the month from which it is going to be vacant — So, that tenants can move-in , in the required time
- Story 20: I want to list the exact bedroom count. — So that the tenants can know about the exact Bedroom count and I do not get disturbed by features of the house.
- Story 21: I want to list exact washroom count — So that the tenants can know about the exact washroom count and I do not get disturbed by features of the house.
- Story 22: I want to list the exact Balcony count. — As I don't want to get disturbed due to balcony count and tenants can know about exact balcony count
- Story 23: I want to list exact House size in sq ft — As I don't want to get disturbed due to House size and tenants can know about exact balcony count.
- Story 24: I want to list if there is any garage. — As I don't want to get disturbed due to Garage count and tenants can know about exact Garage.
- Story 25: I want to list house rules ( gate closed , lift open until ) — Tenants get to know house rules beforehand and they can maintain the rules.
- Story 26: I want to list how many months in advance tenants need to pay without utility charge . — So that tenants get to know how many months they need to pay in advance.
- Story 27: I want to provide bank details. — I don’t need to manage payments
- Story 28: I want to book an appointment for agents to visit — So that Agents can visit the house at an appointed time.
- Story 29: I want to get agent details — So that I, with my other tenants and family members, feel safe.
- Story 30: I don’t know how to operate this app after signing in. — So that I can list properties on call
- Story 31: I want to take a look at the house report to see what needs to be fixed, and if I am going to pay or not and let you guys know if I am going to fix it or ask you guys to fix it and send the payment slip. — So that I get to see the house report and know what needs to be fixed . Subsequently, I will let you know if I am going to fix it or not . And If I fix it, I will let you know what I am fixing. And if I don't want to take the hassle of contacting service providers I will let you know and you guys fix it for me and send the payment slip
- Story 32: I want to let you guys know which ones I will fix . — So that, I can let you know what I will be fixing and update the house report
- Story 33: I want to let you take the responsibility of contacting the service provider and get it fixed. — So that you can contact the service providers and get it fixed.
- Story 34: I want you to send me the payment slip for any house fixing — So that I can pay you guys knowing what expenses were done
- Story 35: I want to let you guys know that I don’t want to fix anything. — So that the tenants can see the original house report
- Story 36: I want you guys to contact me to let me know about the house report, as I don't know how to manage the house report procedure on the app . — So that I can know what needs to be fixed  let you guys know what I will be fixing.
- Story 37: I want you guys to contact me as I don't know how to let you guys know what I will be fixing in the app. — So that I can  let you guys know what I will be fixing.
- Story 38: I want you guys to contact me. — As I want to let you guys know that you need to take the responsibility to fix it .
- Story 39: I want you guys to contact me. — To send the payment slip to me
- Story 40: I want  to pay the house report payments to you on the app. — So that I can clear my dues with you
- Story 41: I want to pay the house report payments to you through call — As I want to clear my dues through phone call
- Story 42: I want to pay the house report payments through bank transfer. — As I want to clear my dues through  bank transfer
- Story 43: I want you guys to contact me . — So that I can let you know I won’t be fixing anything
- Story 44: I want to have head tenant and tenant details for a property. — So that I can know my tenants for a specific property
- Story 45: I want to have tenant’s police verification form for a property — So that I can hold their tenants police verification form for a property
- Story 46: I want to know how many amount of money I will be getting as rent. — So that I can check how much rent I will be receiving
- Story 47: I want to download the police verification form of tenants for a property . — So that I can download the police verification form of their tenants
- Story 48: I want to complain about a tenant — As I want  to complain about tenants
- Story 49: I want to have an ID card as an owner. — As I want to list properties under that  ID card
- Story 50: I want to track payments of all tenants of all my properties. — So that I can  track payments of the tenants of the properties I listed
- Story 51: I don't want to go to the app and remove listings if the house gets rented in my end .I want a more easy way through call or push notification. — As I want to remove properties through a push notification just to answer yes or no in the notification  or call .
- Story 52: I want to know if a tenant has issued a problem in the house. — As I want  to choose if I agree to pay or not  .
- Story 53: I want to stay in the loop if I agree to pay. — As I want to track it.
- Story 54: I want to input the payments I did for a house maintenance problem  for a specific property . — So that I can track what expenses I made for a specific property/  And we get to know what price he paid to the service provider to match the input with service provider
- Story 55: I want to contact Rent Guardian directly for Listing for house report issues, for house maintenance issues, for tech related problems. — So that I Can contact Rent Guardian directly to track and update problems
- Story 56: I want to contact Rent Guardian directly for house report issues.(Supervisor) — So that I can contact Rent Guardian for house report problems
- Story 57: I want to contact Rent Guardian directly for house maintenance issues(Agent). — So that I can contact Rent Guardian for house maintenance problems
- Story 58: I want to contact Rent Guardian directly for Listing for tech related problems(Supervisor). — So that I can contact Rent Guardian for tech related problems
- Story 59: I want to get a detailed house inspection report before tenants move out  if anything gets damaged like tiles broken;  3 days before the tenants move out. — So that I can Know about the issue or problem
- Story 60: I want to set up a meeting with the tenant  if something is damaged permanently. — So that I can  settle issues before tenants move out.

### Tenant

- Story 61: I want to get asked less questions before I see properties. — As I don't want to feel overwhelmed
- Story 62: I want to search on the basis of location. — So that I can look for houses in preferred district , division, area and block/sector
- Story 63: I want to search on the basis of size. — So that I can look for the house I am getting for.
- Story 64: I want to search on the basis of the month I want the house in. — So that I get the houses in the month I want in
- Story 65: I want to search the house on the basis of bedroom count. — So that I can look for the houses with exact bedroom count
- Story 66: I want to search the house on the basis of the exact washroom count. — So that I can look for houses with the preferred washroom count
- Story 67: I want to search on the basis of garage inclusion. — So that I can look for houses that has a garage
- Story 68: I want to search on the basis of balcony count — So that I can look for houses with  balconies
- Story 69: I want to search on the basis of budget — So that I can look for houses within my budget
- Story 70: I want to see full screen image — As I want to get a full view
- Story 71: I want to see the house report. — As I want to see  the house's condition before moving in .
- Story 72: I want to see the house rules . — As I want to determine if I can abide by it or not.
- Story 73: I want to see the rent. — So that I can see if it is suitable for me or not
- Story 74: I want to see the utility charge and see what’s included in it . — As I need to see what is included in it
- Story 75: I want to get a 3D virtual tour. — As I can get a virtual tour of the house
- Story 76: I want to book an appointment for viewing — An appointment with agent will be booked
- Story 77: I want to see the properties I visited in my app. — As I need to share it with my wife, family members
- Story 78: I want to get agent details before meeting him — As I need to feel secure, and get introduced beforehand.
- Story 79: I want to know how many months I need to pay in advance . — As I need to plan my finances.
- Story 80: I want to proceed with payment. — So that I can start the renting process
- Story 81: I want to create a tenant account.(while proceeding with payment). — As I need a tenant account to manage my house.
- Story 82: I want to verify myself before moving in. — So that I get enlisted as a verified tenant
- Story 83: I want to download the police verification form. — So that I can download and print it out.
- Story 84: I want to submit my police verification form before moving in. — So that I get to my home and not get into a hassle of submitting my police verification form
- Story 85: I want to choose if I need help moving in — So that I don’t get into the hassle to look for people to help.
- Story 86: I want wifi installed of my own choice  before moving in. (Agent) — So that I get my internet connection pre Installed
- Story 87: I want to see the wifi plans to get it installed. — So that I can get it according to my own plan and budget
- Story 88: I want water filter installed of my own choice before moving in (Agent) — So that I have accessibility to fresh drinking water after moving in
- Story 89: I want to see the water filter prices and choose from it.(Agent) — So that I can get it according to my budget range
- Story 90: I want stove of my own choice installed before moving in (Agent) — So that I don’t have to deal with technicians in the day of moving in and elongate the tasks more
- Story 91: I want fridge of own choice  before moving in (Agent) — So that I get fridge of own choice pre installed
- Story 92: I want lights installed before moving in.(Agents) — So that I don't have to take the hassle
- Story 93: I want Cylinder Installed before moving in(Agent) — So that I have it installed
- Story 94: I want to see how much the  cylinders  and its  installation  gas  will cost.(2 different cost) — So that I can get it on my own budget
- Story 95: I want to see how much I have to pay for cylinder gas per month. — So that I can plan it.
- Story 96: I want to see how much I have to pay for pre- installation. — So that I can plan it before moving in
- Story 97: I want to pay the pre installation costs (No pay later for pre installations) on move-in day. — So that I can clear my dues on move in day
- Story 98: I want to see the temporary house maids appointed in my area. — So that I can choose from them.
- Story 99: I want to see the catering companies available in my area. — So that I can get enrolled under a catering service
- Story 100: I want to see the plans provided by the catering company. — So that I can choose from it
- Story 101: I want to post a review about a specific maid that worked in my home. — So that I can rate them and make others aware
- Story 102: I want to order groceries using the app. — So that I don't have to take the hassle for buying Groceries
- Story 103: I want to see the products enlisted by the grocery shops. — So that I can Choose from them
- Story 104: I want the groceries delivered in my house. — So that I don't have to go and carry the groceries
- Story 105: I want to order chicken and which should be included in the groceries. — So that I can get the chickens ordered
- Story 106: I want the chickens delivered to my house. — So that I don't have to go and carry chickens
- Story 107: I want to add a description of the chicken. For example :  in how many pieces it needs to get cut. — So that I can get it in my own preference
- Story 108: I want to pay later for groceries. — So that I can pay it later with rent.   .
- Story 109: I want to pay later for Catering. — So that I can pay it later with rent.
- Story 110: I want to report maintenance issue — As I can  stay home and work or study without dealing this
- Story 111: I want to choose if I want the involvement of the owner. — So that, I can choose if i want to fix it myself  or not.
- Story 112: I want to track if the owner agreed to pay or not. — As I want to see if I need to fix it myself or not
- Story 113: If the owner does not agree , I want to choose to fix the issue by my own cost and track it. — As I may need  to fix the issue through my cost and track it.
- Story 114: If the owner agreed to pay , I want to track the whole maintenance issue . — As I need to get the updates.
- Story 115: I want to get service provider details before getting the service from him. — I will feel secured and relaxed as service provider is verified
- Story 116: I want to get an OTP on the app to match with service providers before letting them get into my house — So that my family feels safe
- Story 117: I want to book an appointment with the service provider for tomorrow or any other day. — So that I can book an appointment when I am at home ,if i am not , then  my wife and children will know a service provider will come at the appointed time and feel secured and relaxed as it is a verified technician
- Story 118: I want instant support from service provider in 1 hour . — So that I get instant support in 1 hour
- Story 119: I want to input the amount of money I used for maintenance issues to keep track of expenses. — As I want  to keep track of expenses.
- Story 120: I want to rate the service provider. — As I want good service
- Story 121: I want to receive points to redeem discounts  for taking any house maintenance service through Rent Guardian . So that I could place the honest input and I don’t directly get in contract with service providers. — As I want to place the honest input and I don’t directly get in contract with service providers
- Story 122: I want to pay later with rent for maintenance issues. — As I don't want to have  an additional fee in the middle or end  of month
- Story 123: I  always  want to see the total fees I have to pay at the end of month — As I want to have a proper financial planning
- Story 124: I want to see how much I paid in advance . — As I will need it while moving out .
- Story 125: I want to pay my rent — As I need to clear the rent
- Story 126: I want to view more properties. — As I want to look for other options
- Story 127: I want to get notified if a new property gets listed in my area . — As I want to be the first to see
- Story 128: I want to refer a friend — As I want to get a discount
- Story 129: I want to get notified  if owner filed any complaint about me — As I want to know the complain details
- Story 130: I need an ID card — As I want to check my history through it
- Story 131: I need to list a room as a tenant . — As I have a room vacant  to rent.
- Story 132: I need to list a house as a tenant — As I have other properties to rent and I don't want to take hassle
- Story 133: I want to rate the owner — As the owners are sometimes rude and annoying
- Story 134: I want to rent other houses other than this. — As I want to  rent houses for my son or personal purposes
- Story 135: I want to move out. — As I need to move out of the house
- Story 136: I may need help moving out. — AsI may choose help from you moving out
- Story 137: I want to book appointment with agent before moving out for house inspection [ cracked tiles, damaged households, light holders broken or stolen ] — To remain transparent with the owner and RentGuardian.  The Agent will get a detailed house inspection and let the owner  know if there are any damaged structures and what to do about it .
- Story 138: I want to know about the damages and keep track of it like house maintenance issues and input for price as agents will give estimates about costs . — As I want to Settle disputes before moving out
- Story 139: I want a check in message every month if everything in my house is ok — As I want to be feel cared and safe
- Story 140: I want to contact Rent Guardian — As I want to report any tech related issues
- Story 141: I want to transfer ownership to my flatmate — As I am moving out I will be able  to transfer ownership

### Service provider

- Story 142: I want to see if there is any instant work — As I can grasp instant work when I am free as a verified worker
- Story 143: I want to know what categories their work fall into [ plumbing , electrical, AC, gas , stove ] — So that I can grasp work according to my expertise
- Story 144: I want to see if someone booked an appointment  with me in my area . — Can go to work on appointed time
- Story 145: And I want to get reminders if a meeting gets fixed. — So that I don't forget.
- Story 146: I want to get notified if someone booked an appointment — Will get alerted for work
- Story 147: I want to know their location number name and OTP to match — As I Can enter house as a trusted person
- Story 148: I want to give input of the price — As I need to match with tenants and give info to rent guardian
- Story 149: I want to see if the work is pay later with rent. — So that I can choose to accept it.
- Story 150: I want to see the list of groceries ordered in the app. — So that I can provide it.
- Story 151: I want to see the address of the tenant that ordered groceries. — So that I can deliver it to their house
- Story 152: I want to see if the tenant decided to pay later with rent . — So that we can know about it
- Story 153: I want to see the descriptions of how many pieces  the chicken needs to get cut . — So that I can deliver it according to preference.
- Story 154: I want to see if the tenant decided to pay later with rent . — So that we know about it.
- Story 155: I want to create a meal plan for breakfast. — So that I can deliver it according to their preference
- Story 156: I want to create a meal plan for lunch. — So that I can deliver it according to their preference
- Story 157: I want to create a meal plan for dinner. — So that I can deliver it according to their preference.
- Story 158: I want to know how many people I will need to provide the catering service to. — So that I can provide the exact amount
- Story 159: I want to see the name and  address for the tenants enrolled in my catering system — So that I can deliver it to their address  daily.
- Story 160: I want to see if the tenant decided to pay later with rent . — So that we know about it.
- Story 161: I want instant work for all service providers and appointment work to specific service providers[can grasp instant work as well]  based on area — Instant work can be grasped by all workers in a 4 km radius and appointments booked can be grasped by specific service providers based on area.

## Agent and supervisor permissions

Agent Role Permissions (App Access Control)
This section defines who can do what inside the RentGuardian app. Permissions are strictly role-based to prevent misuse, confusion, or authority overlap.

1️⃣  Field Agent (Campaign / Outreach Only)
Status: Temporary | Time-bound | Campaign-specific
CAN DO:
View assigned campaign area
View daily checklist
Fill Campaign Day Checklist Form
Fill Owner Information Form
Add owner name & contact number
Assist owner with app installation (no login access)
Book request for agent appointment 
View educational material & scripts
CANNOT DO:
Create or edit house reports
Upload property photos
View tenant data
View payments or commissions
Approve listings
Communicate with finance
Handle maintenance

2️⃣ Agent (Core Operations Agent)
Status: Active | Verified | ID-based
CAN DO:
View assigned appointments
Verify appointment via OTP + visible ID
Create House Report (full access)
Upload & tag property photos
(Future) Create 3D virtual tour
Submit reports to Supervisor
Conduct property viewings
View house report during viewing
Explain features to tenants
Guide tenant registration (post-payment)
Coordinate maintenance & technicians
Upload maintenance bills
Coordinate pre-installations (WiFi, filter, stove, fridge, lights, cylinder)
Confirm move-in presence & fee collection status.
Handle tenant complaints (first level)
Perform move-out inspection
Submit move-out inspection report
CANNOT DO:
Approve listings
Modify rent or owner terms
Approve payments
View full financial dashboard
Remove listings

3️⃣ Property Supervisor / Customer Support
Status: Permanent | Authority Role
CAN DO:
Create & edit property listings
Book & confirm agent appointments
Approve or reject house reports
Communicate with owners & tenants
Decide repair responsibility (owner / RentGuardian)
Approve maintenance requests
Accept & verify maintenance bills
Choose payment method (instant / rent deduction)
Approve bank transfer & bKash slips
Notify finance team
Remove or pause listings
Handle escalations
Approve move-out inspection reports
Notify owner of permanent damages
Act as customer support hub
CANNOT DO:
Edit financial records directly
Change commission rules

4️⃣ Finance Team
Status: Restricted | Audit-focused
CAN DO:
View approved payment entries
Confirm bank & bKash deposits
Track rent collection
Generate financial reports
Track commissions
CANNOT DO:
Communicate with tenants or owners
Edit house reports
Approve maintenance without supervisor

5️⃣ Admin / System Owner
Status: Full Authority
CAN DO:
Create / suspend users
Assign roles & permissions
Modify SOP logic
Access all data
Override decisions
View analytics & growth metrics

🔒 Permission Design Principles
No role has end-to-end control
Every critical action requires handoff or approval
Financial actions are always 2-step verified
Field Agents are non-operational
Agents execute, Supervisors decide, Finance confirms

This role-permission structure ensures:
 ✔ Zero power abuse
 ✔ Clean accountability
 ✔ Scalable operations


## Agent operational features

Work of an Agent:

Make sure to see if an appointment is booked for creating a house report. 
Make sure to be at the address at the allotted time.
Make sure to keep the ID card visible. 
Make sure to tell your OTP to the security Guard.
Make sure to create the house report 1st.
Make sure to click the best photos for each room 2nd. 
Make sure to make the 3D virtual tour 3rd.(Not for now)
Make sure you submit these to the supervisor as soon as possible.
Make sure to fix the house report issue that is going to be fixed by Rent Guardian and send the bill to the supervisor.
Make sure to see if an appointment is set for viewing .
Make sure to get on time for viewing the property. 
Make sure to carry the house report while viewing.  
Make sure to mention after payment he must fill the tenant registration form for all the people that will be living in the house and submit it on the app.
Look after every maintenance problem.
Let the supervisor know if any maintenance problem arose and requires the involvement of the owner.
After the owner tells you that the house report issues have been fixed , go and confirm it and let the supervisor know.. 

Direct  the payment to bank transfer if the customer wants to confirm on the spot
Make sure to tell the customer about the feature and give him a feature list
Make sure to check if the tenant wants wifi pre-installed and install it by being present on the property.
Call the product provider company and book the appointment on your required time.[Best to invite them all on same time]
Make sure to check if the tenant wants a filter pre installed.
Make sure to check if the tenant wants a stove pre-installed.
Make sure to check if the tenant wants a fridge pre-installed. 
Make sure to check if the tenants want lights pre-installed.
Make sure to check if the tenants wants Cylinder pre-installed
Make sure to be present on move-in day and accept the pre-installation fee through bank transfer or cash. If it is cash, deposit it to the bkash account by making payment and using the Tenant ID as reference. 
Make sure to check  if anyone has subscribed to a catering service. If anyone does , make sure they get the service by calling the catering service and advising them to check the app.
Make sure to check if the tenant ordered any groceries or chicken and make sure to get them delivered. By calling the grocery shop and advising them to see the app. 
Make sure to call the grocery shop and chicken store every day to update their pricing on a daily basis.
Look after the complaints if an owner complains about a tenant.
Stay in the loop of the issues if someone listed a maintenance problem. 
Make sure the tenants get access to service providers even if it is an instant work. 
Call the tenants to clear their rent payment through the app or Bank deposit on the 8th of every month.
Approve the payment done through Bank deposit and bank transfer by reviewing the picture of Bank Slip and Bank payments. And update it to let the finance team know.
Make sure to check if anyone is moving out. 
‘Make sure to go for a house inspection on the allotted time. 
Make sure to submit it to the supervisor. 
Make sure to check if the tenants applied for help while moving out. 
Make sure to inform the supervisor to set a meeting with the owner if something gets permanently damaged.  And make sure to  inform the tenant about the meeting. 
Make sure to collect payments within 10th of every month

## Supervisor operational features

Work of Property Supervisor or Customer Support:

Take the listing of properties on a call. 
Books the appointment for an agent on call.
Confirms the appointment by calling the owner again if done on the app.
Update the listing of properties on call.
Let the agents know about the listing.
Approves the property listing after the agent uploads the photos.
Save the data of each property on our data system for later listing.
Accepts House report from agent.
Reviews the House Report from the Agent.
Call the Owner and confirm which ones are going to get fixed. 
Confirms with the owner if they want us to fix it or not. Or if he doesn't want to fix it at all. 
If we fix it, let him know we are gonna send the bill to him.
Let the agent know that we have to fix the house report issues and to send the bill to him/her (supervisor).
Confirm with the owner if he wants to pay the bill now or we should deduct it from house rent. 
Accept payment of house report issues through call. (Through Bank Transfer or bkash)
Let the finance team know about any payments. 
 Remove the listing on call.
Let the owner know if there is a maintenance problem in the house and confirm if he is going to fix it or not.
Let the agents know that tenants are moving out and advise him to do the house inspection report. 
 Updates the house inspection report while tenants are moving out.. 
Review the house inspection report and approve it. 
Let the owner know if there are any permanent damages and send him the house inspection report. 
Work as a customer support for both Tenant and Owner . 
Report the tech team about Tech related problems. 
Report the Tenant related problems to Agents. 


## Field-agent scope

Field agents 

Who are the field Agents? 
The working agents that will be there for our initial marketing and outreach campaign  to have a good amount of properties enlisted and homeowners enrolled with us. 

After that, there will be no field agents until it's needed. 

## Implementation plan

| Role | Start page | Features and dedicated sections | Database scope |
|---|---|---|---|
| Owner | Property portfolio | Listing/draft/photos, residents and agreements, expected income/tenant payments, repair decisions/expenses, reports/verification, appointments, complaints/support, bank/identity; optional home discovery | Owned properties and residents; personal rentals remain separate |
| Agent | My assignments | Schedule/entry verification, assigned properties and photos, reports/move-out inspections, maintenance/pre-installation/delivery coordination, customer contacts, assigned complaints and handoffs, bills, training/ID | Assigned appointments and their properties; no financial approvals or portfolio-wide access |
| Supervisor | Review and dispatch | Pending listings/reports/verification, appointments and agent dispatch, maintenance responsibility/provider assignment/cost reconciliation, submitted-payment review, tenancy readiness/transfers/move-out, support/escalations and staff handoffs | Operational portfolio; independent finance confirmation required |
| Finance | Payment confirmation | Reviewed payment queue/receipts, collections/outstanding rent, maintenance commissions, financial exports and audit history | Financial records and receipts; no tenant support, house reports or operational decisions |
| Service provider | My work and orders | Matching nearby/appointment work, entry verification, final cost/bills, delivery orders/customer addresses/customization, catering plans/people/dates/payment timing, own catalog/profile/reviews | Assigned work and own products, meal subscriptions and documents |
| Field agent | Campaign workspace | Assigned areas/dates, active campaign checklist, owner contact collection, appointment requests, saved outreach history and scripts/training | Own campaign assignments/checklists; no tenant data, payments, inspections or maintenance |

1. Replace the generic resident navigation fallback with explicit role navigation and route guards.
2. Build role-specific overview pages with a short next-action queue and role-relevant metrics. Keep complex forms and history behind dedicated sections.
3. Reuse validated workflow components where behavior is already correct, with role-specific labels and filters.
4. Add missing database-backed operational contacts, handoffs, campaign history and financial reporting; enforce role boundaries in endpoints and private downloads.
5. Update guided tours and administrator portal previews to match each actual role.
6. Verify role scoping, forbidden actions, cross-account isolation, build, regression tests and responsive browser flows.

## Existing tenant and administrator features

## All portal features

Shared features apply to all account roles. Visitor features describe the public landing-page and home-discovery experience. The administrator can inspect every underlying database record and the appropriate role portal; changes use validated record editing or the original workflow endpoints.

### Shared

- Sign in and sign out
- Profile name, email, phone and verification
- Role permissions and account access
- Membership ID card
- Notifications and read status
- Listing, appointment and monthly check-in preferences
- Help requests, callbacks and request status
- Demo role sign-in and guided workflow tours

### Visitor

- Neighbourhood, division, district and sector search
- Budget, bedrooms, washrooms, balconies, parking, size and move-in filters
- Family, bachelor and everyone-welcome tabs
- Price, newest and recommended sorting
- Paginated home results
- Photos, captions, categories and fullscreen gallery
- Rent, utilities, advance and recurring costs
- House rules and availability
- Inspection rooms, fixtures, damage and estimates
- Virtual tour link
- Save homes after sign-in
- Book a viewing after sign-in
- Apply to rent after sign-in

### Tenant

- Home summary, invoices, appointment counts and repair status
- Find, save and compare homes
- Viewing requests, appointment details, agent contact and entry codes
- Rental application and reserved-property status
- Tenancy rent, utilities, advance and move-in/move-out dates
- Head tenant and household members
- Add registered or unregistered household members
- Agreement body, signatures and signed download
- Police verification template, upload and review status
- Maintenance categories, preferred provider, urgency and appointment
- Owner payment decision and tenant self-funding
- Repair entry codes, event history, progress, expense confirmation and reconciliation
- Service provider profile and reviews
- Grocery basket, quantity and customization
- Installation, moving and housemaid orders
- Delivery location, schedule, payment timing, cancellation and order tracking
- Catering plans, people, dates and subscription status
- Invoice line items, balances and payment receipts
- Submit payment method, reference, channel and receipt
- Provider and owner reviews
- Move-out notice and inspection
- Head tenancy transfer and acceptance
- Complaints and resolutions
- Reward balance, points redemption and referrals
- List a property, save draft and review submission
- Property details, photo upload, captions, categories, order, cover and archive
- Reports, room/fixture details and document downloads

### Owner

- Owned properties and status
- Create property, address hierarchy, coordinates and terms
- Save and resume listing drafts
- Edit vacant listing details and included utilities
- Upload, caption, categorize, reorder, set cover and archive photos
- Pause or remove eligible listings
- Inspection and viewing appointment requests
- Agent identity, phone and appointment status
- Resident and household details
- Rental agreement, owner signature and signed download
- Verification documents and inspection reports
- Room and fixture condition, permanent damage, estimates and repair responsibility
- Maintenance owner approval/decline, provider details and event history
- Record owner-paid repair expense
- Tenancy invoices, payments and balances
- Tenant complaints and resolutions
- Move-out and head-tenant transfer history
- Bank payout details
- Reviews, rewards and referrals

### Provider

- Business profile, operating area, coordinates, expertise and availability
- Verified identity and membership card
- Matching nearby jobs and distance
- Claim eligible urgent jobs
- Assigned scheduled work and customer contact/address
- Entry code verification
- Start work, complete work and final price
- Customer price confirmation and reconciliation state
- Repair event history
- Incoming grocery, installation, moving and maid orders
- Order items, quantity, installation charge and instructions
- Customer phone and exact delivery address
- Accept, start delivery and complete order
- Product/service catalog, descriptions, units, prices, installation and recurring cost
- Meal-plan name, meal, description and daily price
- Catering customer roster, people, dates, address, phone and payment timing
- Provider ratings and customer reviews

### Agent

- Assigned viewing, inspection and move-out appointments
- Appointment time, property, entry code and completion
- Agent phone, verified identity and ID card
- Verified property access and property photos
- Create move-in/move-out inspection
- Room type, size, electrical ports, light holders and amenities
- Fixture condition, description, estimate and permanent damage
- Add/remove rooms and fixtures before submission
- Submit inspection for supervisor review
- Inspection photo/document upload and downloads
- Property and inspection review status
- Training materials

### Supervisor

- Operations overview and all properties/tenancies
- Review pending listings and approved inspections
- Publish eligible homes
- Assign verified agents and issue appointment entry codes
- Review submitted inspections and fixture repair decisions
- Review identity/police documents
- Activate verified, signed and advance-paid tenancies
- Review maintenance responsibility and approve requests
- Assign matching verified providers and issue service codes
- Review costs, mismatches and reconcile completed work
- Review submitted payments and receipts
- Handle support, callbacks and tenant complaints
- Approve verified head-tenant transfers
- Manage move-out settlement and tenancy closure
- All operational documents and reports

### Finance

- All invoices and line items
- Balances, paid totals and outstanding amounts
- Payment method, reference, receipt, channel and timestamps
- Supervisor review and independent confirmation
- Confirm reviewed payment within remaining balance
- Reject unconfirmed payment
- Financial approval trail and collection reports

### Field Agent

- Assigned campaigns, locations and dates
- Daily outreach checklist
- Owner name, phone and follow-up notes
- Request owner appointment through support
- Assigned campaign and checklist history
- Training materials

### Admin

- All accounts, profile/contact details, verification and roles
- Create, suspend, restore and archive accounts
- Every role portal and linked record
- Edit supported records with database validation
- Create requests and services on behalf of active accounts
- Pending and overdue work queue
- Assign staff/providers and guide request transitions
- Send targeted in-app follow-ups with linked work
- Agent/provider names, phones and assignments
- Remove household members or end a tenancy with an audit reason
- All documents, receipt files, ID cards and inspection reports
- Full database record explorer with search, status and account filters
- Paginated financial and operational reports and CSV exports
- All portal-feature inventory
- Append-only actor history
- Operational settings, address hierarchy, campaigns and training

## Administrator workspace

- **Accounts:** every account first; profile/contact editor, role verification/access, recoverable Delete account and Restore account, and role portal preview. Portal actions record the administrator actor and the account they act for without changing the signed-in session.
- **Work queue:** requested/confirmed appointments, maintenance and unreconciled costs, active orders, pending documents and inspections, pending listings, submitted/reviewed payments, support/callbacks, complaints, transfers and pending/notice tenancies. Overdue work uses stored dates; assigned person contact and a targeted in-app follow-up are shown.
- **Records:** all schema tables with human-readable groups, search, status/account/relationship filters, complete record details, linked contacts, database-backed FK choices, typed validation, conflict detection, create/edit and workflow controls.
- **Documents:** every user file, receipt, verification document, photo and inspection report, with private download access, review and upload on behalf of an account.
- **Reports:** twenty SQL reports with paginated exports, including account, staff contact, appointment, order, catering, document, inspection, support, campaign and financial reports.
- **Overview:** live pending/overdue counts, balances, collections, reconciliation mismatches and recent audit activity.
- **System:** live settings, location management, all portal features and searchable paginated audit history.

## Editing, removal and historical records

Account deletion is recoverable and disables sign-in. It preserves foreign keys and history. A current head tenant must first be transferred or removed from the active property. Ending a tenancy records a reason, preserves outstanding invoices and signed documents, notifies both parties and returns the property to inspection review. Removing a non-head household member revokes their household access and rejects any pending transfer to that member.

Editable records require a reason and optimistic revision check. Workflow-managed states use their existing transition endpoints. Verified signatures cannot be forged by the administrator; genuine signed contracts and confirmed payment history are preserved. Invoice corrections are explicit database line-item adjustments, and reviewed/confirmed payments still require different actual approving accounts. OTP secrets, password hashes, private file paths and authentication internals are not exposed as editable business fields.

Staff reminders are delivered through the app's database-backed notifications. Phone numbers are available for contact; SMS/email/phone delivery is not integrated into this app.

## Validation

Run `npm test`, `E:\Xamppp\php\php.exe tests/admin-mutations.php`, and `E:\Xamppp\php\php.exe server/consistency-audit.php`. The administrator tests cover every schema resource, all eight role workspaces, reports/downloads, permissions, delegation, editing conflicts, recoverable account deletion, tenant removal and audited adjustments. Mutation fixtures are held in one transaction and rolled back.


## Integration boundaries

Callbacks and reminders are recorded as in-app requests/notifications; phone/SMS/push delivery still needs external services. The agent source explicitly marks creating a 3D tour as future work; existing property tour links and the public conceptual 3D illustration remain available.

## Implemented portal map (10 October 2026)

| Role | Main workspace and complete navigation | Data source / scope |
| --- | --- | --- |
| Owner | Portfolio; properties; residents/agreements; appointments; collections/expenses; repair decisions; reports/documents; help/complaints; home search/saved homes; profile, bank details and referrals | Owned property IDs and related tenancies, household identities, agreements, requests, invoices, payments and documents. Portfolio totals are SQL aggregates. |
| Agent | Assignments; visit schedule; assigned properties; house reports/photos; repair coordination; service/move-out handoffs; assigned support; training; work identity | Appointment-assigned property IDs. Contacts, reports, room fixtures, jobs, orders and subscriptions follow those relationships. Notes are saved in request_events or support_tickets. No payment approvals or listing-term edits. |
| Supervisor | Review/dispatch; portfolio listings; agent dispatch; tenancy readiness; document/report review; repair approvals; payment review; service handoffs; support/escalations; agent/provider directory; work identity | Operational portfolio from SQL. Creates a listing only for an explicitly selected active owner. Assignments and notifications persist in the database; no automatic role grant. |
| Finance | Independent payment confirmation; invoice evidence; receipt files; financial reports; internal support; work identity | Database invoice balances, submitted/reviewed/confirmed credits and approval actors. Six whitelisted financial reports, with pagination/export. Private household documents and contracts are denied. |
| Field agent | Assigned campaign area/dates; outreach checklist; owner appointment request; outreach history; scripts/training; own support; work identity | campaign_assignments + campaigns + locations, own campaign_checklists and training audience. Outreach actions require a currently active assignment. |
| Provider | Service jobs/entry codes/completion; orders/delivery; catering roster; own product and meal-plan create/edit/pause; profile/expertise/availability; customer feedback; own bills/evidence; help and work identity | Expertise/area-matched available jobs, assigned jobs, own provider products/plans, linked customer addresses, reviews and uploaded files. Prices in historical orders remain stored snapshots when the catalog changes. |
| Tenant | Existing portal retained, including discovery, household, appointments, payments, repairs, services, reports, support and account settings | Existing database-backed resident scope retained. |
| Administrator | Existing account-first console retained, including records, work queue, documents, reports, system and recoverable account management | All schema resources and updated role-specific portal previews; administrator identity remains the audit actor. |

Large workflows use search and pages of 20 records. Operational tables use 25-record pages; financial report queries use 50-record pages. Queue summaries retain complete source counts instead of counting a displayed page. All records, contact names, phone numbers, dates, prices and statuses come from the database. No front-end sample records were introduced.

Role-specific guided tasks were updated for the new navigation. Training, handoffs, support assignment, provider catalog editing and financial reports remain optional short tasks with Back/Pause/Skip. Staff portals no longer show the unrelated customer sample-order banner.

### Verification

- Production build succeeded. All 23 tests passed.
- New role mutation checks exercise coordination notes, cross-property rejection, support assignment/escalation, supervisor creation for the selected owner, catalog ownership and complete transaction rollback. No mutation test records remain.
- Read-only consistency audit: 53 tables, 102 foreign keys, 36 semantic checks, zero failures. Screenshot tenancy resolves Ayesha Rahman as head tenant and Nadia Ahmed as owner.
- Browser checks covered all six rebuilt workspaces, provider editing controls, finance reports, owner/head-tenant identities, the new agent training tour and the administrator's rebuilt-agent preview. Mobile navigation and page width were checked at 390px. Screenshots are in storage/ui-review.
- Existing role requirements still apply to multi-role use: renting requires tenant access and listing ownership requires owner access, explicitly assigned through administrator Manage access. This rebuild does not automatically grant a new role. Future 3D-tour authoring and external phone/SMS/push delivery remain outside the current integration.
