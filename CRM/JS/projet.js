/* projets.js */
const D=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
const M=['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
const n=new Date();
const el=document.getElementById('date');
if(el) el.textContent=D[n.getDay()]+' '+n.getDate()+' '+M[n.getMonth()]+' '+n.getFullYear();

const open =id=>document.getElementById(id).classList.add('open');
const close=id=>document.getElementById(id).classList.remove('open');
document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',()=>close(b.dataset.close)));
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)close(o.id)}));
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.overlay.open').forEach(o=>close(o.id))});

function openAdd(){
  document.getElementById('m-title').textContent='Nouveau projet';
  document.getElementById('f-action').value='add';
  document.getElementById('f-pid').value='';
  document.getElementById('f-titre').value='';
  document.getElementById('f-cid').value='';
  document.getElementById('f-statut').value='en_attente';
  document.getElementById('f-budget').value='';
  document.getElementById('f-err').textContent='';
  open('m-form');
}

function openEdit(pid,titre,statut,budget,cid){
  document.getElementById('m-title').textContent='Modifier le projet';
  document.getElementById('f-action').value='edit';
  document.getElementById('f-pid').value=pid;
  document.getElementById('f-titre').value=titre;
  document.getElementById('f-statut').value=statut;
  document.getElementById('f-budget').value=budget;
  document.getElementById('f-cid').value=cid;
  document.getElementById('f-err').textContent='';
  open('m-form');
}

function openDel(pid,nom){
  document.getElementById('del-pid').value=pid;
  document.getElementById('del-nom').textContent=nom;
  open('m-del');
}

document.getElementById('form-projet').addEventListener('submit',function(e){
  const titre=document.getElementById('f-titre').value.trim();
  const cid=document.getElementById('f-cid').value;
  const err=document.getElementById('f-err');
  err.textContent='';
  if(!titre||!cid){ e.preventDefault(); err.textContent='Titre et client sont obligatoires.'; }
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