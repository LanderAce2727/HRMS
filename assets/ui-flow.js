/* DNSC frontend walkthrough. All account, Google, and HR actions are simulations. */
(() => {
'use strict';
const model=window.DNSCUI,base=new URL('../',document.currentScript.src),page=document.body.dataset.flow;
const url=p=>new URL(p,base).href,esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const link=(p,label,primary=false)=>`<a class="demo-button${primary?' primary':''}" href="${esc(url(p))}">${esc(label)}</a>`;
const badge=s=>`<span class="demo-badge">${esc(s)}</span>`;
function notify(text,error=false){const box=document.getElementById('flow-message');if(!box)return;box.hidden=false;box.classList.toggle('flow-error',error);box.setAttribute('role',error?'alert':'status');box.textContent=text;box.focus();}
function errors(form,map){form.querySelectorAll('[aria-invalid]').forEach(i=>i.removeAttribute('aria-invalid'));for(const name of Object.keys(map)){const field=form.elements.namedItem(name);if(field&&field.setAttribute)field.setAttribute('aria-invalid','true');}notify(Object.values(map).join(' '),true);const first=form.elements.namedItem(Object.keys(map)[0]);if(first?.focus)first.focus();}
const values=form=>Object.fromEntries(new FormData(form).entries());
function license(form){const check=form.elements.prcNotApplicable,input=form.elements.prcNumber;if(!check||!input)return;const update=()=>{input.disabled=check.checked;input.required=!check.checked;if(check.checked)input.value='';};check.addEventListener('change',update);update();}
function fillProfile(form){const p=model.getState().profile;for(const name of ['fullName','email','specialization','education'])if(form.elements[name])form.elements[name].value=p[name]||'';if(form.elements.prcNotApplicable)form.elements.prcNotApplicable.checked=!!p.prcNotApplicable;if(form.elements.prcNumber)form.elements.prcNumber.value=p.prcNotApplicable?'':p.prcNumber||'';}
function valid(form){if(!form.reportValidity()){notify('Complete the required fields before continuing.',true);return false;}return true;}
if(page==='login'){
 const f=document.getElementById('credential-login');f.addEventListener('submit',e=>{e.preventDefault();if(!valid(f))return;const a=model.login(f.elements.identity.value,f.elements.password.value);f.elements.password.value='';if(!a){notify('The credentials do not match. Check your username and password.',true);return;}location.href=url(a.destination);});
 document.getElementById('google-continue').onclick=()=>{const profile=model.beginGoogle();try{window.sessionStorage.setItem('dnsc-google-display-name',profile.fullName||'');}catch{}location.href=url('applicant-verification.html');};
}
if(page==='verification'){
 const f=document.getElementById('verification-form');fillProfile(f);license(f);f.addEventListener('submit',e=>{e.preventDefault();if(!valid(f))return;const data=values(f);data.prcNotApplicable=f.elements.prcNotApplicable.checked;const result=model.saveProfile(data);if(result.errors){errors(f,result.errors);return;}notify('Details completed successfully for this demonstration. Continuing to sample vacancies…');f.querySelector('button[type=submit]').disabled=true;setTimeout(()=>location.href=url('job-vacancies.html'),900);});
}
if(page==='vacancies'){
 const account=document.getElementById('vacancy-google-account'),accountName=document.getElementById('vacancy-google-name');
 if(account&&accountName){let name='';try{name=window.sessionStorage.getItem('dnsc-google-display-name')||'';}catch{}account.hidden=!name.trim();accountName.textContent=name;accountName.title=name;}
 const logout=document.getElementById('vacancy-logout');
 if(logout)logout.addEventListener('click',()=>{try{window.sessionStorage.removeItem('dnsc-google-display-name');}catch{}if(account)account.hidden=true;if(accountName)accountName.textContent='';location.href=url('login.html');});
 const grid=document.getElementById('vacancy-grid');grid.innerHTML=model.jobs.map((job,index)=>`<article class="demo-panel vacancy-card" data-job="${esc(job.id)}"><div class="vacancy-card-top"><span class="vacancy-mark" aria-hidden="true">D</span><span class="vacancy-publisher"><small>Posted by</small><strong>DNSC</strong></span><span class="vacancy-index">#${String(index+1).padStart(2,'0')}</span></div><div class="vacancy-media" role="img" aria-label="Image placeholder for ${esc(job.department)}"><span class="vacancy-media-initials" aria-hidden="true">${esc(job.department.split(' ').map(word=>word[0]).join(''))}</span><span class="vacancy-media-caption" aria-hidden="true">${esc(job.department)}</span></div><div class="vacancy-summary"><span class="vacancy-department">${esc(job.department)}</span><h2 class="vacancy-title">${esc(job.title)}</h2><span class="vacancy-excerpt">${esc(job.description)}</span></div><div class="vacancy-card-bottom"><span class="vacancy-type">${esc(job.type)}</span><span class="vacancy-deadline">Apply by <strong>${esc(job.deadline)}</strong></span></div><div class="vacancy-actions"><button type="button" class="vacancy-toggle" id="vacancy-toggle-${esc(job.id)}" aria-expanded="false" aria-controls="vacancy-details-${esc(job.id)}"><span class="vacancy-toggle-label">View details</span><svg class="vacancy-chevron" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>${link('job-application.html?job='+encodeURIComponent(job.id),'Apply Now',true)}</div><div class="vacancy-details" id="vacancy-details-${esc(job.id)}" role="region" aria-labelledby="vacancy-toggle-${esc(job.id)}" aria-hidden="true" inert><div class="vacancy-details-clip"><div class="vacancy-details-content"><h3>Job Details</h3><dl><dt>Qualifications</dt><dd>${esc(job.qualifications)}</dd><dt>Job Description</dt><dd>${esc(job.description)}</dd></dl><button type="button" class="demo-button vacancy-close">Close details</button></div></div></div></article>`).join('');
 grid.querySelectorAll('.vacancy-card').forEach(card=>{
  const toggle=card.querySelector('.vacancy-toggle'),details=card.querySelector('.vacancy-details'),label=card.querySelector('.vacancy-toggle-label');
  const setOpen=open=>{card.classList.toggle('is-open',open);toggle.setAttribute('aria-expanded',String(open));details.setAttribute('aria-hidden',String(!open));details.inert=!open;label.textContent=open?'Hide details':'View details';};
  toggle.addEventListener('click',()=>setOpen(toggle.getAttribute('aria-expanded')!=='true'));
  card.querySelector('.vacancy-close').addEventListener('click',()=>{toggle.focus({preventScroll:true});setOpen(false);toggle.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'nearest'});});
 });
 const search=document.getElementById('vacancy-search'),searchToggle=document.getElementById('vacancy-search-toggle'),searchInput=document.getElementById('vacancy-search-input'),searchStatus=document.getElementById('vacancy-search-status');
 if(search&&searchToggle&&searchInput){
  let searchOpen=false;
  const filter=()=>{const query=searchInput.value.trim().toLowerCase();let count=0;grid.querySelectorAll('.vacancy-card').forEach((card,index)=>{const job=model.jobs[index];const matches=(`${job.title} ${job.department} ${job.type} ${job.qualifications} ${job.description}`).toLowerCase().includes(query);card.hidden=!matches;if(matches)count++;});searchStatus.hidden=!query;searchStatus.textContent=count?`${count} position${count===1?'':'s'} found.`:'No positions found. Try a different keyword.';};
  const setSearch=open=>{searchOpen=open;search.classList.toggle('is-open',open);searchToggle.setAttribute('aria-expanded',String(open));searchToggle.setAttribute('aria-label',open?'Close job search':'Search job vacancies');searchInput.disabled=!open;if(open)searchInput.focus();else{searchInput.value='';filter();searchToggle.focus();}};
  searchToggle.addEventListener('click',()=>setSearch(!searchOpen));
  searchInput.addEventListener('input',filter);
  search.addEventListener('submit',event=>{event.preventDefault();filter();});
  search.addEventListener('keydown',event=>{if(event.key==='Escape'){event.preventDefault();setSearch(false);}});
 }
}
if(page==='application'){
 const key=new URL(location.href).searchParams.get('job'),job=model.jobs.find(j=>j.id===key),f=document.getElementById('application-form');
 if(!job){document.getElementById('application-wrapper').hidden=true;document.getElementById('choose-vacancy').hidden=false;}
 else{
  document.getElementById('selected-job-heading').textContent=job.title+' — '+job.department;f.elements.jobId.value=job.id;f.elements.position.value=job.title;f.elements.department.value=job.department;fillProfile(f);license(f);
  f.querySelectorAll('input[type=file]').forEach(input=>input.addEventListener('change',()=>{const output=document.querySelector('[data-file-list="'+input.name+'"]');if(output)output.textContent=[...input.files].map(f=>f.name).join(', ');}));
  f.addEventListener('submit',e=>{e.preventDefault();if(!valid(f))return;const data=values(f);data.prcNotApplicable=f.elements.prcNotApplicable.checked;const documents={};for(const name of ['resume','letter','transcript','credentials'])documents[name]=[...f.elements[name].files].map(file=>({name:file.name,size:file.size}));const result=model.submitApplication(data,documents);if(result.errors){errors(f,result.errors);return;}
   f.hidden=true;const done=document.getElementById('application-confirmation');done.hidden=false;document.getElementById('confirmation-id').textContent=result.application.id;document.getElementById('confirmation-position').textContent=job.title+' — '+job.department;done.focus();
  });
 }
}
function showApplicant(id){
 const app=model.getState().applications.find(a=>a.id===id),target=document.getElementById('application-detail');if(!app)return;
 const registered=app.role==='Faculty';
 target.hidden=false;target.innerHTML=`<div class="panel-title"><h2>Application Details</h2>${badge(app.status)}</div><dl class="profile-grid"><div><dt>Full Name</dt><dd>${esc(app.fullName)}</dd></div><div><dt>Email Address</dt><dd>${esc(app.email)}</dd></div><div><dt>Position</dt><dd>${esc(app.position)}</dd></div><div><dt>Department</dt><dd>${esc(app.department)}</dd></div><div><dt>Educational Attainment</dt><dd>${esc(app.education)}</dd></div><div><dt>Degree / Specialization</dt><dd>${esc(app.specialization)}</dd></div><div><dt>PRC License</dt><dd>${esc(app.prcNumber||'Not Applicable')}</dd></div><div><dt>Teaching Experience</dt><dd>${esc(app.experience)} year(s)</dd></div><div><dt>Contact Number</dt><dd>${esc(app.contactNumber||'Not supplied')}</dd></div><div><dt>Address</dt><dd>${esc(app.address||'Not supplied')}</dd></div><div><dt>Role</dt><dd>${esc(app.role)}</dd></div></dl><h3>Documents</h3>${app.documents.length?'<ul>'+app.documents.map(d=>'<li>'+esc(d.name)+'</li>').join('')+'</ul>':'<p>No document files are attached to this record.</p>'}<div class="demo-actions">${!registered?`<button type="button" class="demo-button primary" data-review="Approved" data-id="${esc(id)}">Approve</button><button type="button" class="demo-button" data-review="Rejected" data-id="${esc(id)}">Reject</button><button type="button" class="demo-button" data-review="Under Review" data-id="${esc(id)}">Mark Under Review</button>`:''}${app.status==='Approved'?`<button type="button" class="demo-button primary" data-enroll="${esc(id)}">Register as Faculty</button>`:''}${registered?link('faculty-dashboard.html?faculty='+encodeURIComponent(app.facultyId),'View Faculty Dashboard'):''}</div><div id="faculty-registration"></div>`;
 target.querySelectorAll('[data-review]').forEach(b=>b.onclick=()=>{const result=model.review(id,b.dataset.review);if(result.error){notify(result.error,true);return;}renderApplicants();showApplicant(id);notify('Application '+b.dataset.review.toLowerCase()+'.');});
 const enroll=target.querySelector('[data-enroll]');if(enroll)enroll.onclick=()=>registrationForm(id);
 target.focus();
}
function renderApplicants(){
 const rows=model.getState().applications,search=(document.getElementById('applicant-search')?.value||'').toLowerCase(),status=document.getElementById('applicant-filter')?.value||'';
 const filtered=rows.filter(a=>(a.fullName+' '+a.position+' '+a.department+' '+a.email).toLowerCase().includes(search)&&(!status||a.status===status));
 document.getElementById('applicant-rows').innerHTML=filtered.length?filtered.map(a=>`<tr><td>${esc(a.fullName)}<small class="cell-secondary">${esc(a.id)}</small></td><td>${esc(a.position)}<small class="cell-secondary">${esc(a.department)}</small></td><td>${badge(a.status)}</td><td>${esc(a.role)}</td><td>${esc(a.submitted)}</td><td><button class="demo-button" type="button" data-view="${esc(a.id)}">View Details</button></td></tr>`).join(''):'<tr><td colspan="6">No matching applicants.</td></tr>';
 document.getElementById('applicant-count').textContent=filtered.length+' applicant(s)';
 document.querySelectorAll('[data-view]').forEach(b=>b.onclick=()=>showApplicant(b.dataset.view));
}
let rowNumber=0;
function scheduleRow(){const id=++rowNumber;return `<div class="assignment-row" data-assignment="${id}"><div class="assignment-fields"><label>Subject Code<input name="subjectCode" required maxlength="30" placeholder="IT 103"></label><label>Subject Name<input name="subjectName" required maxlength="150" placeholder="Data Structures"></label><label>Section<input name="section" required maxlength="40" placeholder="BSIT 2A"></label><label>Assigned Classroom<input name="room" required maxlength="100" placeholder="Room 201"></label><label>Day<select name="day" required>${model.days.map(d=>'<option>'+d+'</option>').join('')}</select></label><label>Start Time<input name="start" type="time" required></label><label>End Time<input name="end" type="time" required></label></div><button type="button" class="demo-button remove-assignment">Remove Schedule</button></div>`;}
function registrationForm(id){const app=model.getState().applications.find(a=>a.id===id),target=document.getElementById('faculty-registration');target.innerHTML=`<h3>Register Approved Applicant as Faculty</h3><form id="register-faculty" class="demo-form flow-form" novalidate><label>Assigned Department<select name="department" required>${model.jobs.map(j=>`<option ${j.department===app.department?'selected':''}>${esc(j.department)}</option>`).join('')}</select></label><h4>Subjects, Classrooms &amp; Teaching Schedule</h4><div id="assignment-list">${scheduleRow()}</div><button type="button" class="demo-button" id="add-assignment">Add Another Schedule</button><div><button class="demo-button primary" type="submit">Register as Faculty</button></div></form>`;
 const form=target.querySelector('form'),list=target.querySelector('#assignment-list');const bindRemove=()=>list.querySelectorAll('.remove-assignment').forEach(b=>b.onclick=()=>{if(list.children.length===1){notify('Keep at least one teaching schedule.',true);return;}b.closest('[data-assignment]').remove();});bindRemove();target.querySelector('#add-assignment').onclick=()=>{list.insertAdjacentHTML('beforeend',scheduleRow());bindRemove();};
 form.addEventListener('submit',e=>{e.preventDefault();if(!valid(form))return;const assignments=[...list.querySelectorAll('[data-assignment]')].map(row=>Object.fromEntries([...row.querySelectorAll('input,select')].map(i=>[i.name,i.value])));const result=model.registerFaculty(id,{department:form.elements.department.value,assignments});if(result.error){notify(result.error,true);return;}renderApplicants();showApplicant(id);notify(app.fullName+' is now listed as Faculty.');});target.scrollIntoView({behavior:'smooth',block:'start'});
}
if(page==='management'){
 document.querySelectorAll('.table-search').forEach(input=>input.remove());
 renderApplicants();document.getElementById('applicant-search').addEventListener('input',renderApplicants);document.getElementById('applicant-filter').addEventListener('change',renderApplicants);
 document.getElementById('reset-applicants').onclick=()=>{model.reset();renderApplicants();document.getElementById('application-detail').hidden=true;notify('The applicant list and assignments have been reset.');};
}
if(page==='faculty'){
 const faculty=model.getFaculty(new URL(location.href).searchParams.get('faculty'));
 document.getElementById('faculty-name').textContent=faculty.name;document.getElementById('faculty-id').textContent=faculty.id;document.getElementById('faculty-department').textContent=faculty.department;document.getElementById('faculty-position').textContent=faculty.position;document.getElementById('faculty-status').textContent=faculty.status;
 const schedule=[...faculty.assignments].sort((a,b)=>model.days.indexOf(a.day)-model.days.indexOf(b.day)||a.start.localeCompare(b.start));
 document.getElementById('schedule-rows').innerHTML=schedule.map(s=>`<tr><td>${esc(s.day)}</td><td>${esc(s.subjectCode)}<small class="cell-secondary">${esc(s.subjectName)}</small></td><td>${esc(s.start+' – '+s.end)}</td><td>${esc(s.section)}</td><td>${esc(s.room)}</td></tr>`).join('');
 const subjects=new Map();for(const s of schedule){const key=s.subjectCode+'|'+s.section;if(!subjects.has(key))subjects.set(key,{...s,times:[]});subjects.get(key).times.push(s.day+' '+s.start+'–'+s.end);}
 document.getElementById('subject-cards').innerHTML=[...subjects.values()].map(s=>`<article class="subject-card">${badge(s.subjectCode)}<h3>${esc(s.subjectName)}</h3><p>${esc(s.section)}</p><p>${esc(s.times.join(' · '))}</p></article>`).join('');
 const rooms=[...new Set(schedule.map(s=>s.room))];document.getElementById('room-cards').innerHTML=rooms.map(room=>`<article class="subject-card"><h3>${esc(room)}</h3><ul>${schedule.filter(s=>s.room===room).map(s=>`<li>${esc(s.day+' '+s.start+'–'+s.end+' · '+s.subjectCode+' · '+s.section)}</li>`).join('')}</ul></article>`).join('');
}
// All handlers are installed before inputs can be edited; with scripts disabled forms stay disabled.
document.querySelectorAll('form[data-flow-form] fieldset').forEach(f=>f.disabled=false);
document.querySelectorAll('form[data-flow-form]').forEach(f=>{const input=f.elements.prcNumber;if(input)input.disabled=!!f.elements.prcNotApplicable?.checked;});
})();
