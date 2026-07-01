/* ══════════════════════════════════════
   taches.js — OkCRM
   ══════════════════════════════════════ */

/* ── Date ── */
document.getElementById('date').textContent =
  new Date().toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' });

/* ── Overlay helpers ── */
function openOverlay(id)  { document.getElementById(id).classList.add('open'); }
function closeOverlay(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('[data-close]').forEach(btn =>
  btn.addEventListener('click', () => closeOverlay(btn.dataset.close))
);
document.querySelectorAll('.overlay').forEach(o =>
  o.addEventListener('click', e => { if (e.target === o) closeOverlay(o.id); })
);

/* ── Modal tâche ── */
function openTaskModal(task) {
  const isEdit = !!task;
  document.getElementById('m-title').textContent  = isEdit ? 'Modifier la tâche' : 'Nouvelle tâche';
  document.getElementById('m-sub').textContent    = isEdit ? 'Mettre à jour les informations' : 'Renseigner les informations';
  document.getElementById('f-action').value       = isEdit ? 'edit' : 'add';
  document.getElementById('f-tid').value          = isEdit ? task.Tid    : '';
  document.getElementById('f-titre').value        = isEdit ? task.titre  : '';
  document.getElementById('f-statut').value       = isEdit ? task.statut : 'a_faire';
  document.getElementById('f-pid').value          = isEdit && task.Pid ? task.Pid : '';
  document.getElementById('f-err').textContent    = '';
  openOverlay('m-task');
  setTimeout(() => document.getElementById('f-titre').focus(), 120);
}

function editTask(task) { openTaskModal(task); }

/* Validation côté client */
document.getElementById('form-task').addEventListener('submit', e => {
  const titre = document.getElementById('f-titre').value.trim();
  if (!titre) {
    e.preventDefault();
    document.getElementById('f-err').textContent = 'Le titre est obligatoire.';
  }
});

/* ── Modal suppression ── */
function deleteTask(tid, nom) {
  document.getElementById('del-tid').value      = tid;
  document.getElementById('del-nom').textContent = nom;
  openOverlay('m-del');
}

/* ── Recherche ── */
document.getElementById('search').addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  document.querySelectorAll('#tasks-list tr[data-search]').forEach(tr => {
    tr.style.display = tr.dataset.search.includes(q) ? '' : 'none';
  });
});

/* ── Filtres URL ── */
function applyFilters() {
  const statut  = document.getElementById('filter-statut').value;
  const projet  = document.getElementById('filter-projet').value;
  const params  = new URLSearchParams();
  if (statut !== 'tous') params.set('statut', statut);
  if (projet !== 'tous') params.set('projet', projet);
  window.location.href = 'taches.php' + (params.toString() ? '?' + params : '');
}

/* ── Toggle vue Liste / Kanban ── */
function setView(v) {
  document.getElementById('list-view').style.display   = v === 'list'   ? '' : 'none';
  document.getElementById('kanban-view').style.display = v === 'kanban' ? '' : 'none';
  document.querySelectorAll('.view-btn').forEach(b => b.classList.toggle('active', b.dataset.view === v));
  localStorage.setItem('taches_view', v);
}
// Restaurer vue préférée
const savedView = localStorage.getItem('taches_view') || 'list';
setView(savedView);

/* ── Quick status (clic sur le rond) ── */
const statusCycle = { a_faire: 'en_cours', en_cours: 'termine', termine: 'a_faire' };
const statusLabel = { a_faire: 'À faire', en_cours: 'En cours', termine: 'Terminé' };

function quickChangeStatus(tid, current) {
  const next = statusCycle[current];
  const fd   = new FormData();
  fd.append('action', 'change_status');
  fd.append('Tid', tid);
  fd.append('statut', next);

  fetch('taches.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (!res.success) return;
      // Mettre à jour le DOM sans reload
      const row  = document.querySelector(`tr[data-id="${tid}"]`);
      if (!row) return;
      row.dataset.statut = next;

      const dot  = row.querySelector('.task-status');
      dot.className = 'task-status ' + next;
      dot.setAttribute('onclick', `quickChangeStatus(${tid},'${next}')`);

      const badge = row.querySelector('.badge');
      badge.className = 'badge ' + {a_faire:'gray',en_cours:'blue',termine:'green'}[next];
      badge.textContent = statusLabel[next];

      // Mettre à jour le bouton edit
      const editBtn = row.querySelector('.act:not(.del)');
      const taskData = JSON.parse(editBtn.getAttribute('onclick').match(/editTask\((.*)\)/)[1]);
      taskData.statut = next;
      editBtn.setAttribute('onclick', `editTask(${JSON.stringify(taskData)})`);
    });
}

/* ══ KANBAN DRAG & DROP ══ */
let draggedId   = null;
let draggedEl   = null;

function dragStart(e) {
  draggedId  = e.currentTarget.dataset.id;
  draggedEl  = e.currentTarget;
  e.dataTransfer.effectAllowed = 'move';
  setTimeout(() => draggedEl.classList.add('dragging'), 0);
}

function allowDrop(e) { e.preventDefault(); }

function dropOnColumn(e) {
  e.preventDefault();
  const col    = e.currentTarget;
  const newStatus = col.dataset.status;
  const tasks  = col.querySelector('.kanban-tasks');

  if (!draggedId || !draggedEl) return;
  tasks.appendChild(draggedEl);
  draggedEl.classList.remove('dragging');
  draggedEl.dataset.status = newStatus;
  col.querySelectorAll('.kanban-tasks').forEach(t => t.classList.remove('drag-over'));

  // Persistance AJAX
  const fd = new FormData();
  fd.append('action', 'change_status');
  fd.append('Tid', draggedId);
  fd.append('statut', newStatus);
  fetch('taches.php', { method: 'POST', body: fd });

  draggedId = null;
  draggedEl = null;
}

// Highlight colonne cible
document.querySelectorAll('.kanban-col').forEach(col => {
  col.addEventListener('dragenter', () => col.querySelector('.kanban-tasks')?.classList.add('drag-over'));
  col.addEventListener('dragleave', e => {
    if (!col.contains(e.relatedTarget)) col.querySelector('.kanban-tasks')?.classList.remove('drag-over');
  });
});