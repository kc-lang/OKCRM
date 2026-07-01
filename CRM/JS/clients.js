/* clients.js */
const COLS=['#2563eb','#059669','#d97706','#9333ea','#0284c7','#dc2626','#0891b2','#65a30d'];
const col = s=>{let h=0;for(let c of s)h=c.charCodeAt(0)+((h<<5)-h);return COLS[Math.abs(h)%8]};
const ini = n=>n.trim().split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);

// Date
const D=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
const M=['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
const n=new Date();
const el=document.getElementById('date');
if(el) el.textContent=D[n.getDay()]+' '+n.getDate()+' '+M[n.getMonth()]+' '+n.getFullYear();

// Modals
const open =id=>document.getElementById(id).classList.add('open');
const close=id=>document.getElementById(id).classList.remove('open');
document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',()=>close(b.dataset.close)));
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)close(o.id)}));
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.overlay.open').forEach(o=>close(o.id))});

// Ouvrir ajout
function openAdd(){
  document.getElementById('m-title').textContent='Nouveau client';
  document.getElementById('m-sub').textContent='Renseigner les informations';
  document.getElementById('f-action').value='add';
  document.getElementById('f-cid').value='';
  document.getElementById('f-nom').value='';
  document.getElementById('f-email').value='';
  document.getElementById('f-tel').value='';
  document.getElementById('f-err').textContent='';
  open('m-form');
}

// Ouvrir modification
function openEdit(cid,nom,email,tel){
  document.getElementById('m-title').textContent='Modifier le client';
  document.getElementById('m-sub').textContent=nom;
  document.getElementById('f-action').value='edit';
  document.getElementById('f-cid').value=cid;
  document.getElementById('f-nom').value=nom;
  document.getElementById('f-email').value=email;
  document.getElementById('f-tel').value=tel;
  document.getElementById('f-err').textContent='';
  open('m-form');
}

// Ouvrir détail
function openDetail(cid,nom,email,tel,nbp,nbf){
  const av=document.getElementById('d-av');
  av.textContent=ini(nom); av.style.background=col(nom);
  document.getElementById('d-nom').textContent=nom;
  document.getElementById('d-email').textContent=email||'—';
  document.getElementById('d-tel').textContent=tel||'—';
  document.getElementById('d-proj').innerHTML=nbp>0
    ?`<div class="detail-item"><span>${nbp} projet(s)</span><span class="num-badge blue">${nbp}</span></div>`
    :'<p class="detail-empty">Aucun projet</p>';
  document.getElementById('d-fact').innerHTML=nbf>0
    ?`<div class="detail-item"><span>${nbf} facture(s)</span><span class="num-badge amber">${nbf}</span></div>`
    :'<p class="detail-empty">Aucune facture</p>';
  document.getElementById('btn-edit-d').onclick=()=>{close('m-detail');setTimeout(()=>openEdit(cid,nom,email,tel),150)};
  open('m-detail');
}

// Ouvrir suppression
function openDel(cid,nom){
  document.getElementById('del-cid').value=cid;
  document.getElementById('del-nom').textContent=nom;
  open('m-del');
}

// Validation formulaire
document.getElementById('form-client').addEventListener('submit',function(e){
  const nom=document.getElementById('f-nom').value.trim();
  const email=document.getElementById('f-email').value.trim();
  const err=document.getElementById('f-err');
  err.textContent='';
  if(!nom||!email||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    e.preventDefault();
    err.textContent='Nom et email valide sont obligatoires.';
  }
});

// Recherche
document.getElementById('search').addEventListener('input',function(){
  const q=this.value.toLowerCase();
  document.querySelectorAll('#tbl tbody tr[data-projets]').forEach(tr=>{
    tr.style.display=tr.textContent.toLowerCase().includes(q)?'':'none';
  });
});

// Filtres
document.querySelectorAll('.f-btn').forEach(b=>b.addEventListener('click',function(){
  document.querySelectorAll('.f-btn').forEach(x=>x.classList.remove('active'));
  this.classList.add('active');
  const f=this.dataset.f;
  document.querySelectorAll('#tbl tbody tr[data-projets]').forEach(tr=>{
    const show = f==='tous' ||
      (f==='projets'  && parseInt(tr.dataset.projets)>0) ||
      (f==='factures' && parseInt(tr.dataset.factures)>0);
    tr.style.display=show?'':'none';
  });
  const visible=[...document.querySelectorAll('#tbl tbody tr[data-projets]')].filter(t=>t.style.display!=='none').length;
  document.getElementById('count').textContent=visible+' client'+(visible>1?'s':'');
}));