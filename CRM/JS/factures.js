/* factures.js */
const D=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
const M=['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
const n=new Date();
const el=document.getElementById('date');
if(el) el.textContent=D[n.getDay()]+' '+n.getDate()+' '+M[n.getMonth()]+' '+n.getFullYear();

// Date d'aujourd'hui par défaut
document.getElementById('f-date').valueAsDate = new Date();

const open =id=>document.getElementById(id).classList.add('open');
const close=id=>document.getElementById(id).classList.remove('open');
document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',()=>close(b.dataset.close)));
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)close(o.id)}));
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.overlay.open').forEach(o=>close(o.id))});

// Filtrer les projets selon le client sélectionné
function filtrerProjets(){
  const cid=document.getElementById('f-cid').value;
  document.querySelectorAll('#f-pid option').forEach(opt=>{
    if(!opt.value) return;
    opt.style.display=(!cid||opt.dataset.cid===cid)?'':'none';
  });
  document.getElementById('f-pid').value='';
}

function openAdd(){
  document.getElementById('m-title').textContent='Nouvelle facture';
  document.getElementById('f-action').value='add';
  document.getElementById('f-fid').value='';
  document.getElementById('f-cid').value='';
  document.getElementById('f-pid').value='';
  document.getElementById('f-montant').value='';
  document.getElementById('f-date').valueAsDate=new Date();
  document.getElementById('f-statut').value='en_attente';
  document.getElementById('f-err').textContent='';
  filtrerProjets();
  open('m-form');
}

function openEdit(fid,montant,statut,date,pid,cid){
  document.getElementById('m-title').textContent='Modifier la facture';
  document.getElementById('f-action').value='edit';
  document.getElementById('f-fid').value=fid;
  document.getElementById('f-cid').value=cid;
  filtrerProjets();
  document.getElementById('f-pid').value=pid;
  document.getElementById('f-montant').value=montant;
  document.getElementById('f-date').value=date;
  document.getElementById('f-statut').value=statut;
  document.getElementById('f-err').textContent='';
  open('m-form');
}

// Marquer payée rapidement (soumet le formulaire d'édition avec statut=payee)
function marquerPayee(fid,montant,date,pid,cid){
  const form=document.createElement('form');
  form.method='POST'; form.action='factures.php';
  const fields={action:'edit',Fid:fid,montant,statut_paiement:'payee',date,Pid:pid,Cid:cid};
  Object.entries(fields).forEach(([k,v])=>{
    const i=document.createElement('input');
    i.type='hidden'; i.name=k; i.value=v; form.appendChild(i);
  });
  document.body.appendChild(form); form.submit();
}

function openDel(fid,nom){
  document.getElementById('del-fid').value=fid;
  document.getElementById('del-nom').textContent=nom;
  open('m-del');
}

document.getElementById('form-facture').addEventListener('submit',function(e){
  const m=document.getElementById('f-montant').value;
  const d=document.getElementById('f-date').value;
  const c=document.getElementById('f-cid').value;
  const p=document.getElementById('f-pid').value;
  const err=document.getElementById('f-err');
  err.textContent='';
  if(!m||!d||!c||!p){ e.preventDefault(); err.textContent='Tous les champs obligatoires sont requis.'; }
});

document.getElementById('search').addEventListener('input',function(){
  const q=this.value.toLowerCase();
  document.querySelectorAll('#tbl tbody tr[data-statut]').forEach(tr=>{
    tr.style.display=tr.textContent.toLowerCase().includes(q)?'':'none';
  });
});

document.querySelectorAll('.f-btn').forEach(b=>b.addEventListener('click',function(){
  document.querySelectorAll('.f-btn').forEach(x=>x.classList.remove('active'));
  this.classList.add('active');
  const f=this.dataset.f;
  document.querySelectorAll('#tbl tbody tr[data-statut]').forEach(tr=>{
    tr.style.display=(f==='tous'||tr.dataset.statut===f)?'':'none';
  });
}));