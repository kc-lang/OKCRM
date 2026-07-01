/* agenda.js */
const D = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
const M = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

// Date du jour
const n = new Date();
const el = document.getElementById('date');
if(el) el.textContent = D[n.getDay()] + ' ' + n.getDate() + ' ' + M[n.getMonth()] + ' ' + n.getFullYear();

// Modals
const open = id => document.getElementById(id).classList.add('open');
const close = id => document.getElementById(id).classList.remove('open');

document.querySelectorAll('[data-close]').forEach(b => {
    b.addEventListener('click', () => close(b.dataset.close));
});

document.querySelectorAll('.overlay').forEach(o => {
    o.addEventListener('click', e => {
        if(e.target === o) close(o.id);
    });
});

document.addEventListener('keydown', e => {
    if(e.key === 'Escape') {
        document.querySelectorAll('.overlay.open').forEach(o => close(o.id));
    }
});

// Ouvrir modal d'ajout
function openEventModal(event = null, jour = null, mois = null, annee = null) {
    document.getElementById('m-title').textContent = event ? 'Modifier événement' : 'Nouvel événement';
    document.getElementById('f-action').value = event ? 'edit' : 'add';
    
    if(event) {
        document.getElementById('f-eid').value = event.Eid;
        document.getElementById('f-titre').value = event.titre;
        document.getElementById('f-pid').value = event.Pid || '';
        let dateHeure = event.date_heure.replace(' ', 'T');
        document.getElementById('f-date_heure').value = dateHeure.substring(0, 16);
    } else {
        document.getElementById('f-eid').value = '';
        document.getElementById('f-titre').value = '';
        document.getElementById('f-pid').value = '';
        
        let now = new Date();
        if(jour && typeof jour === 'number') {
            let moisValue = mois !== null ? mois - 1 : now.getMonth();
            let anneeValue = annee !== null ? annee : now.getFullYear();
            now = new Date(anneeValue, moisValue, jour, 9, 0);
        }
        // Ajuster pour le timezone
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('f-date_heure').value = now.toISOString().slice(0, 16);
    }
    
    document.getElementById('f-err').textContent = '';
    open('m-event');
}

// Modifier événement
function editEvent(event) {
    openEventModal(event);
}

// Supprimer événement
function deleteEvent(eid, titre) {
    document.getElementById('del-eid').value = eid;
    document.getElementById('del-nom').textContent = titre;
    open('m-del');
}

// Afficher détails d'un événement (popup rapide)
function showEventDetails(event) {
    const date = new Date(event.date_heure);
    const formattedDate = date.toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let msg = `📅 ${event.titre}\n\n`;
    msg += `🕐 ${formattedDate}\n`;
    if(event.projet_titre) msg += `📁 Projet: ${event.projet_titre}\n`;
    msg += `\n🖱️ Cliquez sur Modifier pour changer cet événement`;
    
    alert(msg);
}

// Afficher tous les événements d'un jour
function showAllEvents(jour) {
    const cells = document.querySelectorAll('.calendar-day');
    let targetCell = null;
    let events = [];
    
    for(let cell of cells) {
        if(cell.querySelector('.day-number')?.textContent == jour) {
            targetCell = cell;
            break;
        }
    }
    
    if(targetCell) {
        const badges = targetCell.querySelectorAll('.event-badge');
        badges.forEach(badge => {
            // Extraire les infos de l'événement depuis le onclick
            const onclickAttr = badge.getAttribute('onclick');
            if(onclickAttr) {
                const match = onclickAttr.match(/showEventDetails\(({.*?})\)/);
                if(match) {
                    try {
                        const event = eval('(' + match[1] + ')');
                        events.push(event);
                    } catch(e) {}
                }
            }
        });
    }
    
    const titleEl = document.getElementById('day-events-title');
    const listEl = document.getElementById('day-events-list');
    const addBtn = document.getElementById('add-event-day');
    
    titleEl.textContent = `Événements du ${jour} ${M[new Date().getMonth()]}`;
    
    if(events.length === 0) {
        listEl.innerHTML = '<p class="detail-empty">Aucun événement ce jour</p>';
    } else {
        listEl.innerHTML = events.map(event => `
            <div class="event-item" style="margin-bottom: 8px;">
                <div class="event-info">
                    <div class="event-title">${escapeHtml(event.titre)}</div>
                    <div class="event-details">
                        ${event.projet_titre ? `<span class="event-project">📁 ${escapeHtml(event.projet_titre)}</span>` : ''}
                    </div>
                </div>
                <div class="event-date">
                    ${new Date(event.date_heure).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})}
                </div>
                <div class="event-actions">
                    <button class="act" onclick="editEvent(${JSON.stringify(event).replace(/"/g, '&quot;')}); close('m-day-events')">
                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    // Stocker le jour pour l'ajout rapide
    addBtn.onclick = () => {
        close('m-day-events');
        openEventModal(null, jour);
    };
    
    open('m-day-events');
}

// Helper pour échapper le HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Validation du formulaire
document.getElementById('form-event')?.addEventListener('submit', function(e) {
    const titre = document.getElementById('f-titre').value.trim();
    const dateHeure = document.getElementById('f-date_heure').value;
    const err = document.getElementById('f-err');
    
    err.textContent = '';
    
    if(!titre) {
        e.preventDefault();
        err.textContent = 'Le titre est obligatoire.';
        return;
    }
    if(!dateHeure) {
        e.preventDefault();
        err.textContent = 'La date et l\'heure sont obligatoires.';
        return;
    }
});

// Recherche
document.getElementById('search')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const items = document.querySelectorAll('.event-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const searchText = item.dataset.search || item.textContent.toLowerCase();
        if(query === '' || searchText.includes(query)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
});

// Mettre en surbrillance les jours avec événements au chargement
document.addEventListener('DOMContentLoaded', function() {
    const daysWithEvents = document.querySelectorAll('.calendar-day:not(.empty)');
    daysWithEvents.forEach(day => {
        const hasEvents = day.querySelector('.event-badge');
        if(hasEvents) {
            day.style.position = 'relative';
        }
    });
});

// ── Alerte sonore : événement imminent (aujourd'hui / dans les 2h) ──

// Génère un petit bip avec la Web Audio API (pas besoin de fichier son)
function playAlertBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();

        // Deux petites notes successives pour un son de "notification"
        const playNote = (freq, startTime, duration) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.type = 'sine';
            osc.frequency.value = freq;

            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(0.2, startTime + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);

            osc.start(startTime);
            osc.stop(startTime + duration);
        };

        const now = ctx.currentTime;
        playNote(880, now, 0.15);        // première note
        playNote(1175, now + 0.18, 0.2); // deuxième note, légèrement plus aiguë
    } catch (e) {
        console.warn('Alerte sonore impossible :', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    let evenements = [];

    try {
        evenements = JSON.parse(body.dataset.evenementsImminents || '[]');
    } catch (e) {
        evenements = [];
    }

    if (evenements.length === 0) return;

    // Évite de rejouer le son à chaque rechargement dans la même session
    const todayKey = 'agenda_alert_played_' + new Date().toISOString().slice(0, 10) + '_' + new Date().getHours();
    if (sessionStorage.getItem(todayKey)) return;

    playAlertBeep();
    sessionStorage.setItem(todayKey, '1');

    // Petit message visuel optionnel dans la console (peut être remplacé par un toast)
    const titres = evenements.map(e => e.titre).join(', ');
    console.info('🔔 Événement(s) proche(s) :', titres);
});