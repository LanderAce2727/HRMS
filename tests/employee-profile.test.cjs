const assert=require('node:assert/strict'),fs=require('node:fs'),vm=require('node:vm'),path=require('node:path');
const source=fs.readFileSync(path.join(__dirname,'../assets/employee-profile.js'),'utf8');
const fields=['full_name','employee_number','email','department','position','contact_number','address'];
const store=new Map();
function setup(id='FAC-1'){
 const node=()=>({hidden:false,value:'',textContent:'',events:{},addEventListener(type,handler){this.events[type]=handler;},setAttribute(){},focus(){this.focused=true;}});
 const nodes={};const get=id=>nodes[id]??=node();
 const view=get('employee-profile-view'),form=get('employee-profile-form');form.hidden=true;form.elements=Object.fromEntries(fields.map(field=>[field,node()]));form.elements.namedItem=field=>form.elements[field];form.reportValidity=()=>true;
 const context={document:{getElementById:get},window:{DNSCUI:{getFaculty:()=>({id,name:'Employee '+id,department:'Mathematics',position:'Instructor I'}),getState:()=>({applications:[]})},matchMedia:()=>({matches:true})},location:{href:'https://example.test/employee/Employee_Employee_Profile.html'},URL,localStorage:{getItem:key=>store.get(key)||null,setItem:(key,value)=>store.set(key,value)}};
 vm.runInNewContext(source,context);return {nodes,view,form,edit:get('employee-profile-edit'),back:get('employee-profile-back')};
}
(async()=>{
 const p=setup();assert.equal(p.view.hidden,false);assert.equal(p.form.hidden,true);assert.equal(p.nodes['employee-profile-name'].textContent,'Employee FAC-1');
 await p.edit.events.click();assert.equal(p.view.hidden,true);assert.equal(p.form.hidden,false);assert.equal(p.form.elements.full_name.focused,true);
 p.form.elements.full_name.value='Discard this change';p.back.events.click();assert.equal(p.view.hidden,false);assert.equal(p.form.hidden,true);assert.equal(p.nodes['employee-profile-name'].textContent,'Employee FAC-1');
 await p.edit.events.click();assert.equal(p.form.elements.full_name.value,'Employee FAC-1');
 p.form.elements.full_name.value='Saved name';await p.form.events.submit({preventDefault(){}});assert.equal(p.nodes['employee-profile-name'].textContent,'Saved name');assert.equal(p.form.hidden,true);assert.equal(p.edit.focused,true);
 assert.equal(setup().nodes['employee-profile-name'].textContent,'Saved name');assert.equal(setup('FAC-2').nodes['employee-profile-name'].textContent,'Employee FAC-2');
 console.log('PASS profile display, edit mode, Back discard, save, reload, and employee isolation');
})().catch(error=>{console.error(error);process.exitCode=1;});
