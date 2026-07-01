<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require 'Config/db.php';
require 'Config/security.php';
$uid = $_SESSION['user']['Uid'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $pid = $_POST['Pid'] ?: null;
        $stmt = $conn->prepare("INSERT INTO evenement (titre, date_heure, Pid) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $_POST['titre'], $_POST['date_heure'], $pid);
        $stmt->execute();

    } elseif ($action === 'edit') {
        $pid = $_POST['Pid'] ?: null;
        $stmt = $conn->prepare("UPDATE evenement SET titre=?, date_heure=?, Pid=? WHERE Eid=?");
        $stmt->bind_param("ssii", $_POST['titre'], $_POST['date_heure'], $pid, $_POST['Eid']);
        $stmt->execute();

    } elseif ($action === 'del') {
        $stmt = $conn->prepare("DELETE FROM evenement WHERE Eid=?");
        $stmt->bind_param("i", $_POST['Eid']);
        $stmt->execute();
    }
    header('Location: agenda.php');
    exit;
}

// Récupération des événements pour le mois en cours
$mois_courant = $_GET['mois'] ?? date('n');
$annee_courante = $_GET['annee'] ?? date('Y');
$date_debut = "$annee_courante-$mois_courant-01";
$date_fin = date('Y-m-t', strtotime($date_debut));

$evenements = $conn->query("
    SELECT e.*, p.titre as projet_titre 
    FROM evenement e
    LEFT JOIN projet p ON p.Pid = e.Pid
    LEFT JOIN user_ u ON u.Uid = p.Uid
    WHERE (p.Uid = $uid OR e.Pid IS NULL)
    AND e.date_heure BETWEEN '$date_debut' AND '$date_fin 23:59:59'
    ORDER BY e.date_heure ASC
")->fetch_all(MYSQLI_ASSOC);

// Événements par jour pour le calendrier
$events_by_day = [];
foreach($evenements as $event){
    $jour = date('j', strtotime($event['date_heure']));
    if(!isset($events_by_day[$jour])) $events_by_day[$jour] = [];
    $events_by_day[$jour][] = $event;
}

// Projets pour le select
$projets = $conn->query("SELECT Pid, titre FROM projet WHERE Uid=$uid ORDER BY titre")->fetch_all(MYSQLI_ASSOC);

// Navigation mois précédent/suivant
$mois_precedent = $mois_courant == 1 ? 12 : $mois_courant - 1;
$annee_precedent = $mois_courant == 1 ? $annee_courante - 1 : $annee_courante;
$mois_suivant = $mois_courant == 12 ? 1 : $mois_courant + 1;
$annee_suivant = $mois_courant == 12 ? $annee_courante + 1 : $annee_courante;

$nom_mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$jours_semaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

// Construction du calendrier
$premier_jour = new DateTime("$annee_courante-$mois_courant-01");
$num_jour_semaine = $premier_jour->format('N') - 1; // 0 = Lundi
$nb_jours_mois = cal_days_in_month(CAL_GREGORIAN, $mois_courant, $annee_courante);

$semaines = [];
$semaine = [];
for($i = 0; $i < $num_jour_semaine; $i++) $semaine[] = null;
for($jour = 1; $jour <= $nb_jours_mois; $jour++){
    $semaine[] = $jour;
    if(count($semaine) == 7){
        $semaines[] = $semaine;
        $semaine = [];
    }
}
if(count($semaine) > 0){
    while(count($semaine) < 7) $semaine[] = null;
    $semaines[] = $semaine;
}

// Récupération des événements à venir (prochains 30 jours)
$evenements_a_venir = $conn->query("
    SELECT e.*, p.titre as projet_titre 
    FROM evenement e
    LEFT JOIN projet p ON p.Pid = e.Pid
    LEFT JOIN user_ u ON u.Uid = p.Uid
    WHERE (p.Uid = $uid OR e.Pid IS NULL)
    AND e.date_heure >= NOW()
    ORDER BY e.date_heure ASC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Événements imminents (aujourd'hui ou dans les 2 prochaines heures) → pour l'alerte sonore
$evenements_imminents = $conn->query("
    SELECT e.Eid, e.titre, e.date_heure
    FROM evenement e
    LEFT JOIN projet p ON p.Pid = e.Pid
    WHERE (p.Uid = $uid OR e.Pid IS NULL)
    AND e.date_heure >= NOW()
    AND e.date_heure <= DATE_ADD(NOW(), INTERVAL 2 HOUR)
    ORDER BY e.date_heure ASC
")->fetch_all(MYSQLI_ASSOC);

$page_active = 'agenda';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OkCRM — Agenda</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/agenda.css">
    <link rel="stylesheet" href="CSS/responsive.css">
</head>
<body data-evenements-imminents='<?= htmlspecialchars(json_encode($evenements_imminents), ENT_QUOTES) ?>'>
<?php include 'sidebar.php' ?>

<main class="main">
    <header class="topbar">
        <div class="tb-left">
            <h1>Agenda</h1>
            <p id="date"></p>
        </div>
        <div class="tb-right">
            <div class="search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="search" placeholder="Rechercher événement…">
            </div>
            <button class="btn btn-blue" onclick="openEventModal()">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvel événement
            </button>
        </div>
    </header>

    <div class="page">
        <!-- Calendrier -->
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?mois=<?= $mois_precedent ?>&annee=<?= $annee_precedent ?>">← Mois précédent</a>
                <a href="?mois=<?= date('n') ?>&annee=<?= date('Y') ?>">Aujourd'hui</a>
                <a href="?mois=<?= $mois_suivant ?>&annee=<?= $annee_suivant ?>">Mois suivant →</a>
            </div>
            <div class="calendar-month-title"><?= $nom_mois[$mois_courant-1] ?> <?= $annee_courante ?></div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-weekdays">
                <?php foreach($jours_semaine as $jour): ?>
                    <div class="calendar-weekday"><?= $jour ?></div>
                <?php endforeach; ?>
            </div>
            <div class="calendar-days">
                <?php foreach($semaines as $semaine): ?>
                    <div class="calendar-week">
                        <?php foreach($semaine as $jour): ?>
                            <?php if($jour === null): ?>
                                <div class="calendar-day empty"></div>
                            <?php else: 
                                $is_today = ($jour == date('j') && $mois_courant == date('n') && $annee_courante == date('Y'));
                                $events_today = $events_by_day[$jour] ?? [];
                                $has_events = count($events_today) > 0;
                            ?>
                                <div class="calendar-day <?= $is_today ? 'today' : '' ?>" onclick="openEventModal(null, <?= $jour ?>, <?= $mois_courant ?>, <?= $annee_courante ?>)">
                                    <div class="day-number"><?= $jour ?></div>
                                    <?php 
                                    $display_count = 0;
                                    foreach($events_today as $event):
                                        if($display_count >= 2) break;
                                        $is_past = strtotime($event['date_heure']) < time();
                                        $is_today_event = date('Y-m-d', strtotime($event['date_heure'])) == date('Y-m-d');
                                    ?>
                                        <div class="event-badge <?= $is_past ? 'past' : '' ?> <?= $is_today_event && !$is_past ? 'today-event' : '' ?>" 
                                             onclick="event.stopPropagation(); showEventDetails(<?= htmlspecialchars(json_encode($event)) ?>)">
                                            🕐 <?= htmlspecialchars(substr($event['titre'], 0, 18)) ?>
                                        </div>
                                    <?php 
                                        $display_count++;
                                    endforeach; 
                                    ?>
                                    <?php if(count($events_today) > 2): ?>
                                        <div class="more-events" onclick="event.stopPropagation(); showAllEvents(<?= $jour ?>)">
                                            +<?= count($events_today) - 2 ?> autre(s)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Événements à venir -->
        <div class="upcoming-section">
            <div class="upcoming-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Événements à venir
            </div>
            <div class="events-list">
                <?php if(empty($evenements_a_venir)): ?>
                    <div class="empty">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p>Aucun événement à venir</p>
                        <button class="btn btn-blue" onclick="openEventModal()">Ajouter un événement</button>
                    </div>
                <?php else: ?>
                    <?php foreach($evenements_a_venir as $event): ?>
                        <div class="event-item" data-search="<?= strtolower($event['titre'] . ' ' . ($event['projet_titre'] ?? '')) ?>">
                            <div class="event-info">
                                <div class="event-title"><?= htmlspecialchars($event['titre']) ?></div>
                                <div class="event-details">
                                    <?php if($event['projet_titre']): ?>
                                        <span class="event-project">📁 <?= htmlspecialchars($event['projet_titre']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="event-date">
                                <?= date('d/m/Y H:i', strtotime($event['date_heure'])) ?>
                            </div>
                            <div class="event-actions">
                                <button class="act" onclick="editEvent(<?= htmlspecialchars(json_encode($event)) ?>)">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="act del" onclick="deleteEvent(<?= $event['Eid'] ?>, '<?= addslashes($event['titre']) ?>')">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Ajouter/Modifier Événement -->
<div class="overlay" id="m-event">
    <div class="modal">
        <div class="modal-head">
            <div><h3 id="m-title">Nouvel événement</h3><p id="m-sub">Renseigner les informations</p></div>
            <button class="close-btn" data-close="m-event"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <form method="POST" action="agenda.php" class="modal-body" id="form-event">
            <input type="hidden" name="action" id="f-action" value="add">
            <input type="hidden" name="Eid" id="f-eid">
            
            <div class="fg">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" id="f-titre" required placeholder="Réunion, Deadline, Appel...">
            </div>
            
            <div class="form-row">
                <div class="fg">
                    <label>Date et heure <span class="req">*</span></label>
                    <input type="datetime-local" name="date_heure" id="f-date_heure" required>
                </div>
                <div class="fg">
                    <label>Projet lié</label>
                    <select name="Pid" id="f-pid">
                        <option value="">-- Aucun --</option>
                        <?php foreach($projets as $p): ?>
                            <option value="<?= $p['Pid'] ?>"><?= htmlspecialchars($p['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <p class="f-err" id="f-err"></p>
            
            <div class="modal-foot" style="padding:0;border:none;background:none;margin-top:16px">
                <button type="button" class="btn btn-ghost" data-close="m-event">Annuler</button>
                <button type="submit" class="btn btn-blue">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Suppression -->
<div class="overlay" id="m-del">
    <div class="modal sm">
        <div class="modal-head">
            <h3 style="color:var(--danger)">Supprimer cet événement ?</h3>
            <button class="close-btn" data-close="m-del"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <form method="POST" action="agenda.php">
            <input type="hidden" name="action" value="del">
            <input type="hidden" name="Eid" id="del-eid">
            <div class="modal-body">
                <p style="font-size:14px;color:var(--ink2);line-height:1.7">
                    Supprimer l'événement <strong id="del-nom"></strong> ? Cette action est irréversible.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close="m-del">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tous les événements du jour -->
<div class="overlay" id="m-day-events">
    <div class="modal">
        <div class="modal-head">
            <div><h3 id="day-events-title">Événements du jour</h3></div>
            <button class="close-btn" data-close="m-day-events"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body" id="day-events-list"></div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="m-day-events">Fermer</button>
            <button class="btn btn-blue" id="add-event-day">Ajouter un événement</button>
        </div>
    </div>
</div>

<script src="JS/agenda.js"></script>
</body>
</html>