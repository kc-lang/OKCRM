const dateEl = document.getElementById('current-date');
if (dateEl) {
    const now = new Date();
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    dateEl.textContent = now.toLocaleDateString('fr-FR', options);
}

// Toggle sidebar mobile
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}

// Fonctions utilitaires
function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '—';
}

// Simulation de données (à remplacer par fetch API)
setTimeout(() => {
    setText('stat-clients', '3');
    setText('sub-clients', '3 client(s) au total');
    setText('stat-ca', '0');
    setText('sub-ca', '0 facture(s) encaissée(s)');
    setText('stat-factures', '3');
    setText('sub-factures', '163 840 FCFA à encaisser');
    setText('stat-projets', '4');
    setText('sub-projets', '4 projet(s) au total');
    setText('nb-clients', '3');
    setText('nb-projets', '4');
    setText('nb-factures', '3');
}, 500);