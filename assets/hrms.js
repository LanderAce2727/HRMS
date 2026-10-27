/* Browser-only controls. No requests, authentication, uploads, or record storage. */
(() => {
'use strict';
const sun='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
const moon='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 13.1A9 9 0 0 1 10.9 3 9 9 0 1 0 21 13.1Z"/></svg>';
const message=document.getElementById('notice');
const show=()=>{if(message){message.hidden=false;message.textContent='This action is currently unavailable.';message.focus();}};
function theme(value){document.documentElement.dataset.theme=value;document.querySelectorAll('#theme-mode').forEach(b=>{b.innerHTML=value==='dark'?sun:moon;b.title=value==='dark'?'Switch to light mode':'Switch to dark mode';b.setAttribute('aria-label',b.title);b.setAttribute('aria-pressed',String(value==='dark'));});try{localStorage.setItem('dnsc-theme',value);}catch(e){}}
let saved='light';try{saved=localStorage.getItem('dnsc-theme')==='dark'?'dark':'light';}catch(e){}theme(saved);
document.querySelectorAll('#theme-mode').forEach(b=>b.onclick=()=>theme(document.documentElement.dataset.theme==='dark'?'light':'dark'));
const menu=document.getElementById('menu-toggle'),side=document.getElementById('portal-sidebar'),layout=document.querySelector('.hrms-layout');
if(menu&&side&&layout){
 const media=matchMedia('(max-width:760px)');
 const stateKey=()=>media.matches?'dnsc-sidebar-mobile':'dnsc-sidebar-desktop';
 const remembered=()=>{try{const saved=sessionStorage.getItem(stateKey());if(saved!==null)return saved==='open';}catch(e){}return !media.matches;};
 let open=remembered();
 const update=()=>{layout.classList.toggle('hrms-collapsed',!open);side.inert=!open;menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Collapse navigation':'Expand navigation');menu.title=open?'Collapse navigation':'Expand navigation';try{sessionStorage.setItem(stateKey(),open?'open':'closed');}catch(e){}};
 menu.onclick=()=>{open=!open;update();};
 media.addEventListener('change',()=>{open=remembered();update();});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&open){open=false;update();menu.focus();}});update();
}
// Sidebar sections behave as content panels; page links retain their separate HTML files.
if(side&&layout){
 const content=document.getElementById('main-content');
 if(content){
  const reduced=()=>matchMedia('(prefers-reduced-motion: reduce)').matches;
  const top=()=>{content.scrollTop=0;content.scrollLeft=0;};
  let animation=null,navigating=false;
  const reveal=()=>{if(animation)animation.cancel();if(!reduced()&&content.animate)animation=content.animate([{opacity:0},{opacity:1}],{duration:220,easing:'cubic-bezier(.2,.8,.2,1)'});};
  const focusContent=()=>{content.setAttribute('tabindex','-1');content.focus({preventScroll:true});};
  try{history.scrollRestoration='manual';}catch(e){}
  const links=[...side.querySelectorAll('nav a[href]')];
  const panels=links.map(link=>{const href=link.getAttribute('href');if(!href||!href.startsWith('#'))return null;let panel;try{panel=document.getElementById(decodeURIComponent(href.slice(1)));}catch(e){return null;}return panel&&panel.parentElement===content?{link,panel}:null;}).filter(Boolean);
  const selectPanel=(selected,updateAddress=true)=>{
   for(const entry of panels){entry.panel.hidden=entry!==selected;entry.panel.classList.toggle('sidebar-panel-active',entry===selected);if(entry===selected)entry.link.setAttribute('aria-current','location');else entry.link.removeAttribute('aria-current');}
   content.classList.add('sidebar-panel-view');
   if(updateAddress){try{history.replaceState(null,'',selected.link.getAttribute('href'));}catch(e){}}
   top();reveal();
  };
  if(panels.length){
   const initial=panels.find(entry=>entry.link.getAttribute('href')===location.hash)||panels[0];selectPanel(initial,false);
   for(const entry of panels)entry.link.addEventListener('click',event=>{if(event.defaultPrevented||event.button>0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;event.preventDefault();selectPanel(entry);focusContent();});
   window.addEventListener('hashchange',()=>{const target=panels.find(entry=>entry.link.getAttribute('href')===location.hash);if(target)selectPanel(target,false);});
  }else{top();reveal();}
  for(const link of links){
   if(link.getAttribute('href').startsWith('#'))continue;
   link.addEventListener('click',event=>{
    if(event.defaultPrevented||event.button>0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey||link.target==='_blank'||link.hasAttribute('download'))return;
    const target=new URL(link.href,location.href);
    if(target.protocol!==location.protocol||target.origin!==location.origin||!target.pathname.toLowerCase().endsWith('.html'))return;
    event.preventDefault();if(navigating)return;
    const samePage=target.pathname===location.pathname&&target.search===location.search;
    if(samePage){top();reveal();focusContent();return;}
    navigating=true;
    if(reduced()||!content.animate){location.href=target.href;return;}
    if(animation)animation.cancel();
    animation=content.animate([{opacity:1},{opacity:0}],{duration:130,easing:'ease-out',fill:'forwards'});
    let completed=false;const navigate=()=>{if(completed)return;completed=true;location.href=target.href;};
    animation.onfinish=navigate;
    setTimeout(navigate,190);
   });
  }
  window.addEventListener('pageshow',event=>{top();if(event.persisted){navigating=false;if(animation)animation.cancel();reveal();}});
 }
}

document.querySelectorAll('[data-static-submit]').forEach(b=>b.addEventListener('click',show));
document.querySelectorAll('[data-reveal]').forEach(b=>b.onclick=()=>{const input=document.getElementById(b.dataset.reveal);if(input){input.type=input.type==='password'?'text':'password';b.textContent=input.type==='password'?'Show':'Hide';b.setAttribute('aria-pressed',String(input.type==='text'));}});
const roles=[...document.querySelectorAll('[data-login-role]')];const pick=b=>roles.forEach(r=>{r.setAttribute('aria-checked',String(r===b));r.tabIndex=r===b?0:-1;});roles.forEach((b,i)=>{b.onclick=()=>pick(b);b.onkeydown=e=>{if(!['ArrowRight','ArrowLeft','ArrowUp','ArrowDown'].includes(e.key))return;e.preventDefault();const next=roles[(i+(['ArrowRight','ArrowDown'].includes(e.key)?1:roles.length-1))%roles.length];pick(next);next.focus();};});
document.querySelectorAll('.static-form input').forEach(input=>input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();show();}}));
document.querySelectorAll('.demo-table-wrap').forEach(w=>{if(!w.querySelector('table'))return;const search=document.createElement('input');search.type='search';search.className='demo-search table-search';search.placeholder='Search this table';search.setAttribute('aria-label','Search this table');search.oninput=()=>w.querySelectorAll('tbody tr').forEach(r=>r.hidden=!r.textContent.toLowerCase().includes(search.value.toLowerCase()));w.before(search);});
document.querySelectorAll('[data-print]').forEach(b=>b.onclick=()=>window.print());
const tick=()=>{const clock=document.getElementById('live-clock'),date=document.getElementById('live-date');if(clock)clock.textContent=new Intl.DateTimeFormat('en-PH',{timeZone:'Asia/Manila',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date());if(date)date.textContent=new Intl.DateTimeFormat('en-PH',{timeZone:'Asia/Manila',dateStyle:'full'}).format(new Date());};tick();if(document.getElementById('live-clock'))setInterval(tick,1000);
})();
