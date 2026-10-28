/* Employee profile edits are stored in this browser, per faculty ID. */
(() => {
 'use strict';
 const model=window.DNSCUI;
 const faculty=model.getFaculty(new URL(location.href).searchParams.get('faculty'));
 const application=model.getState().applications.find(record=>record.id===faculty.applicationId);
 const key='dnsc-employee-profile-v1:'+faculty.id;
 const fields=['full_name','employee_number','email','department','position','contact_number','address'];
 let profile={full_name:faculty.name,employee_number:faculty.id,email:application?.email||'',department:faculty.department,position:faculty.position,contact_number:application?.contactNumber||'',address:application?.address||''};
 try{const saved=JSON.parse(localStorage.getItem(key)||'null');if(saved&&typeof saved==='object')for(const field of fields)if(typeof saved[field]==='string')profile[field]=saved[field];}catch{}
 const view=document.getElementById('employee-profile-view'),form=document.getElementById('employee-profile-form'),edit=document.getElementById('employee-profile-edit'),back=document.getElementById('employee-profile-back'),message=document.getElementById('employee-profile-message');
 const render=()=>{const name=profile.full_name||'Employee Profile';document.getElementById('employee-profile-name').textContent=name;const avatar=document.getElementById('employee-profile-avatar');avatar.textContent=name.trim().split(/\s+/).slice(0,2).map(word=>word[0]).join('').toUpperCase();avatar.setAttribute('aria-label',name+' profile image');for(const field of fields)document.getElementById('profile-detail-'+field).textContent=profile[field]||'Not provided';};
 const fill=()=>{for(const field of fields)form.elements.namedItem(field).value=profile[field]||'';};
 let transitioning=false;
 async function switchMode(editing){
  if(transitioning)return;
  transitioning=true;
  const outgoing=editing?view:form,incoming=editing?form:view;
  message.hidden=true;
  if(editing)fill();
  const motion=!window.matchMedia('(prefers-reduced-motion: reduce)').matches&&!!outgoing.animate;
  if(motion)await outgoing.animate([{opacity:1},{opacity:0,transform:'translateY(5px)'}],{duration:120,easing:'ease-out'}).finished.catch(()=>{});
  outgoing.hidden=true;incoming.hidden=false;
  edit.setAttribute('aria-expanded',String(editing));
  if(motion)await incoming.animate([{opacity:0,transform:'translateY(8px)'},{opacity:1,transform:'translateY(0)'}],{duration:220,easing:'ease-out'}).finished.catch(()=>{});
  transitioning=false;
  (editing?form.elements.namedItem('full_name'):edit).focus({preventScroll:true});
 }
 edit.addEventListener('click',()=>switchMode(true));
 back.addEventListener('click',()=>{fill();switchMode(false);});
 form.addEventListener('submit',async event=>{
  event.preventDefault();if(transitioning||!form.reportValidity())return;
  const next=Object.fromEntries(fields.map(field=>[field,form.elements.namedItem(field).value.trim()]));
  next.employee_number=faculty.id;
  if(!next.full_name){form.elements.namedItem('full_name').focus();return;}
  try{localStorage.setItem(key,JSON.stringify(next));}catch{message.hidden=false;message.textContent='Changes could not be saved in this browser. Please try again.';return;}
  profile=next;render();await switchMode(false);
  message.hidden=false;message.textContent='Profile changes saved in this browser.';
 });
 render();
})();
