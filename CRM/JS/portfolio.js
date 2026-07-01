/* ══ portfolio.js ══ */

// Date
const D=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
const M=['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
const n=new Date();
const el=document.getElementById('date');
if(el) el.textContent=D[n.getDay()]+' '+n.getDate()+' '+M[n.getMonth()]+' '+n.getFullYear();

// Modals
const open = id => document.getElementById(id).classList.add('open');
const close= id => document.getElementById(id).classList.remove('open');
document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => close(b.dataset.close)));
document.querySelectorAll('.overlay').forEach(o => o.addEventListener('click', e => { if(e.target===o) close(o.id); }));
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.overlay.open').forEach(o => close(o.id)); });

function openAdd() {
    document.getElementById('m-title').textContent = 'Nouvelle réalisation';
    document.getElementById('f-action').value = 'add';
    document.getElementById('f-rid').value = '';
    document.getElementById('f-titre').value = '';
    document.getElementById('f-desc').value = '';
    document.getElementById('f-lien').value = '';
    document.getElementById('f-tel').value = '';
    document.getElementById('f-photo-existante').value = '';
    resetPreview();
    open('m-form');
}

function openEdit(r) {
    document.getElementById('m-title').textContent = 'Modifier la réalisation';
    document.getElementById('f-action').value = 'edit';
    document.getElementById('f-rid').value = r.Rid;
    document.getElementById('f-titre').value = r.titre;
    document.getElementById('f-desc').value = r.description || '';
    document.getElementById('f-lien').value = r.lien || '';
    document.getElementById('f-tel').value = r.tel || '';
    document.getElementById('f-photo-existante').value = r.photo_path || '';

    const preview = document.getElementById('photo-preview');
    if (r.photo_path) {
        preview.innerHTML = `<img src="${r.photo_path}" alt="Aperçu">`;
    } else {
        resetPreview();
    }
    open('m-form');
}

function openDel(rid, nom) {
    document.getElementById('del-rid').value = rid;
    document.getElementById('del-nom').textContent = nom;
    open('m-del');
}

function resetPreview() {
    document.getElementById('photo-preview').innerHTML = `
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <span>Cliquer pour ajouter une photo</span>`;
}

// Prévisualisation photo avant upload
function previewPhoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('photo-preview');
        preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu">`;
    };
    reader.readAsDataURL(input.files[0]);
}

// Clic sur la zone de preview = déclenche le sélecteur de fichier
document.getElementById('photo-preview').addEventListener('click', () => {
    document.getElementById('f-photo').click();
});