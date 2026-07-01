<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
 if(!isset($_SESSION['user'])){header('Location:login.php');exit;}
require 'Config/db.php';
require 'Config/security.php';
$uid = $_SESSION['user']['Uid'] ?? 1;

// ── STATISTIQUES GLOBALES ──

// Total clients
$result = $conn->query("SELECT COUNT(*) as total FROM client WHERE Uid=$uid");
$total_clients = $result->fetch_assoc()['total'];

// Clients avec projets
$result = $conn->query("SELECT COUNT(DISTINCT c.Cid) as total 
                        FROM client c 
                        INNER JOIN projet p ON p.Cid = c.Cid 
                        WHERE c.Uid=$uid");
$clients_avec_projets = $result->fetch_assoc()['total'];

// Clients avec factures
$result = $conn->query("SELECT COUNT(DISTINCT c.Cid) as total 
                        FROM client c 
                        INNER JOIN facture f ON f.Cid = c.Cid 
                        WHERE c.Uid=$uid");
$clients_avec_factures = $result->fetch_assoc()['total'];

// Total projets
$result = $conn->query("SELECT COUNT(*) as total FROM projet WHERE Uid=$uid");
$total_projets = $result->fetch_assoc()['total'];

// Projets par statut
$stats_projets = [];
$result = $conn->query("SELECT statut, COUNT(*) as count FROM projet WHERE Uid=$uid GROUP BY statut");
while($row = $result->fetch_assoc()) {
    $stats_projets[$row['statut']] = $row['count'];
}

// Budget total
$result = $conn->query("SELECT SUM(budget) as total FROM projet WHERE Uid=$uid");
$budget_total = $result->fetch_assoc()['total'] ?? 0;

// Tâches totales
$result = $conn->query("SELECT COUNT(*) as total 
                        FROM tache t 
                        INNER JOIN projet p ON p.Pid = t.Pid 
                        WHERE p.Uid=$uid");
$total_taches = $result->fetch_assoc()['total'];

// Tâches par statut
$stats_taches = ['a_faire' => 0, 'en_cours' => 0, 'termine' => 0];
$result = $conn->query("SELECT t.statut, COUNT(*) as count 
                        FROM tache t 
                        INNER JOIN projet p ON p.Pid = t.Pid 
                        WHERE p.Uid=$uid 
                        GROUP BY t.statut");
while($row = $result->fetch_assoc()) {
    $stats_taches[$row['statut']] = $row['count'];
}

// Factures
$result = $conn->query("SELECT COUNT(*) as total FROM facture f 
                        INNER JOIN client c ON c.Cid = f.Cid 
                        WHERE c.Uid=$uid");
$total_factures = $result->fetch_assoc()['total'];

// Factures par statut
$stats_factures = ['en_attente' => 0, 'payee' => 0, 'annulee' => 0];
$result = $conn->query("SELECT f.statut_paiement as statut, COUNT(*) as count 
                        FROM facture f 
                        INNER JOIN client c ON c.Cid = f.Cid 
                        WHERE c.Uid=$uid 
                        GROUP BY f.statut_paiement");
while($row = $result->fetch_assoc()) {
    $stats_factures[$row['statut']] = $row['count'];
}

// CA total (factures payées)
$result = $conn->query("SELECT SUM(f.montant) as total 
                        FROM facture f 
                        INNER JOIN client c ON c.Cid = f.Cid 
                        WHERE c.Uid=$uid AND f.statut_paiement = 'payee'");
$ca_total = $result->fetch_assoc()['total'] ?? 0;

// ── ACTIVITÉS RÉCENTES ──

// Derniers clients
$derniers_clients = $conn->query("
    SELECT Cid, nom, email, created_at 
    FROM client 
    WHERE Uid=$uid 
    ORDER BY Cid DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Derniers projets
$derniers_projets = $conn->query("
    SELECT p.Pid, p.titre, p.statut, p.budget, c.nom as client_nom, p.created_at
    FROM projet p
    LEFT JOIN client c ON c.Cid = p.Cid
    WHERE p.Uid=$uid
    ORDER BY p.Pid DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Dernières factures
$dernieres_factures = $conn->query("
    SELECT f.Fid, f.montant, f.statut_paiement, f.date, c.nom as client_nom, p.titre as projet_titre
    FROM facture f
    LEFT JOIN client c ON c.Cid = f.Cid
    LEFT JOIN projet p ON p.Pid = f.Pid
    WHERE c.Uid=$uid
    ORDER BY f.Fid DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Projets en cours (avec progression basée sur tâches)
$projets_en_cours = $conn->query("
    SELECT p.Pid, p.titre, p.statut, c.nom as client_nom,
           COUNT(t.Tid) as total_taches,
           SUM(CASE WHEN t.statut = 'termine' THEN 1 ELSE 0 END) as taches_terminees
    FROM projet p
    LEFT JOIN client c ON c.Cid = p.Cid
    LEFT JOIN tache t ON t.Pid = p.Pid
    WHERE p.Uid=$uid AND p.statut = 'en_cours'
    GROUP BY p.Pid
    ORDER BY p.created_at DESC
    LIMIT 4
")->fetch_all(MYSQLI_ASSOC);

// Événements à venir (prochains 7 jours)
$date_limite = date('Y-m-d H:i:s', strtotime('+7 days'));
$evenements = $conn->query("
    SELECT e.Eid, e.titre, e.date_heure, p.titre as projet_titre
    FROM evenement e
    LEFT JOIN projet p ON p.Pid = e.Pid
    WHERE p.Uid=$uid AND e.date_heure >= NOW() AND e.date_heure <= '$date_limite'
    ORDER BY e.date_heure ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$page_active = 'dashboard';

// ── DONNÉES POUR GRAPHIQUES ──

// Évolution des projets par mois (6 derniers mois)
$evolution_projets = [];
for($i = 5; $i >= 0; $i--) {
    $mois = date('Y-m', strtotime("-$i months"));
    $annee_mois = date('F Y', strtotime("-$i months"));
    $result = $conn->query("
        SELECT COUNT(*) as total 
        FROM projet 
        WHERE Uid=$uid AND DATE_FORMAT(created_at, '%Y-%m') = '$mois'
    ");
    $evolution_projets[] = [
        'mois' => substr($annee_mois, 0, 3),
        'total' => $result->fetch_assoc()['total']
    ];
}

// Répartition des projets par statut
$repartition_projets = [];
$result = $conn->query("
    SELECT statut, COUNT(*) as total 
    FROM projet 
    WHERE Uid=$uid 
    GROUP BY statut
");
while($row = $result->fetch_assoc()) {
    $repartition_projets[] = $row;
}

// CA par mois (6 derniers mois)
$ca_mensuel = [];
for($i = 5; $i >= 0; $i--) {
    $mois = date('Y-m', strtotime("-$i months"));
    $result = $conn->query("
        SELECT SUM(f.montant) as total 
        FROM facture f
        INNER JOIN client c ON c.Cid = f.Cid
        WHERE c.Uid=$uid 
        AND f.statut_paiement = 'payee'
        AND DATE_FORMAT(f.date, '%Y-%m') = '$mois'
    ");
    $ca_mensuel[] = [
        'mois' => substr(date('F Y', strtotime("-$i months")), 0, 3),
        'total' => $result->fetch_assoc()['total'] ?? 0
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OkCRM — Tableau de bord</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/dash.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
</head>
<body>
<?php include 'sidebar.php' ?>

<main class="main">
    <header class="topbar">
        <div class="tb-left">
            <h1>Tableau de bord</h1>
            <p id="date"></p>
        </div>
        <div class="tb-right">
            <div class="search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="search" placeholder="Rechercher…">
            </div>
        </div>
    </header>

    <div class="page">
        <!-- Bannière de bienvenue -->
        <div class="welcome-banner">
            <h2>Bienvenue, <?= htmlspecialchars($_SESSION['user']['nom'] ?? 'Freelance') ?> 👋</h2>
            <p>Voici un aperçu de votre activité et de vos performances</p>
        </div>

        <!-- Statistiques principales -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="s-icon blue"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                <div><p class="s-val"><?= $total_clients ?></p><p class="s-lbl">Clients</p></div>
            </div>
            <div class="stat-card">
                <div class="s-icon green"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg></div>
                <div><p class="s-val"><?= $total_projets ?></p><p class="s-lbl">Projets</p></div>
            </div>
            <div class="stat-card">
                <div class="s-icon amber"><svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                <div><p class="s-val"><?= $total_taches ?></p><p class="s-lbl">Tâches</p></div>
            </div>
            <div class="stat-card">
                <div class="s-icon purple"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <div><p class="s-val"><?= $total_factures ?></p><p class="s-lbl">Factures</p></div>
            </div>
        </div>

        <!-- Graphiques et statistiques avancées -->
        <div class="grid-2cols">
            <!-- Évolution des projets -->
            <div class="card">
                <div class="card-head">
                    <div><h2>Évolution des projets</h2><p>6 derniers mois</p></div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="chart-container">
                        <?php foreach($evolution_projets as $data): ?>
                            <?php $height = $data['total'] > 0 ? ($data['total'] / max(array_column($evolution_projets, 'total')) * 150) : 5; ?>
                            <div class="chart-bar" style="height: <?= max(30, $height) ?>px;">
                                <div class="chart-value"><?= $data['total'] ?></div>
                                <div class="chart-label"><?= $data['mois'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Répartition des projets -->
            <div class="card">
                <div class="card-head">
                    <div><h2>Répartition des projets</h2><p>Par statut</p></div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php
                        $statut_labels = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                        $statut_colors = ['en_attente' => '#d97706', 'en_cours' => '#2563eb', 'termine' => '#059669', 'annule' => '#dc2626'];
                        foreach($statut_labels as $key => $label):
                            $count = $stats_projets[$key] ?? 0;
                            $percentage = $total_projets > 0 ? ($count / $total_projets * 100) : 0;
                        ?>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                                    <span><?= $label ?></span>
                                    <span style="font-weight: 500;"><?= $count ?> (<?= round($percentage) ?>%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $percentage ?>%; background: <?= $statut_colors[$key] ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CA mensuel -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-head">
                <div><h2>Chiffre d'affaires mensuel</h2><p>6 derniers mois (FCFA)</p></div>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div class="chart-container">
                    <?php foreach($ca_mensuel as $data): ?>
                        <?php $height = $data['total'] > 0 ? ($data['total'] / max(array_column($ca_mensuel, 'total')) * 150) : 5; ?>
                        <div class="chart-bar" style="height: <?= max(30, $height) ?>px; background: var(--success-lt);">
                            <div class="chart-value"><?= number_format($data['total'], 0, '.', ' ') ?></div>
                            <div class="chart-label"><?= $data['mois'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="grid-3cols">
            <!-- Derniers clients -->
            <div class="card">
                <div class="card-head">
                    <div><h2>Derniers clients</h2><p>Les 5 plus récents</p></div>
                    <a href="clients.php" class="btn btn-ghost" style="padding: 4px 10px;">Voir tout</a>
                </div>
                <div style="padding: 0 20px;">
                    <?php if(empty($derniers_clients)): ?>
                        <p class="detail-empty" style="padding: 20px 0;">Aucun client</p>
                    <?php else: ?>
                        <ul class="activity-list">
                        <?php foreach($derniers_clients as $client): ?>
                            <li class="activity-item">
                                <div class="activity-info">
                                    <div class="activity-title"><?= htmlspecialchars($client['nom']) ?></div>
                                    <div class="activity-sub"><?= htmlspecialchars($client['email'] ?? '—') ?></div>
                                </div>
                                <div class="activity-date"><?= date('d/m/Y', strtotime($client['created_at'])) ?></div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Derniers projets -->
            <div class="card">
                <div class="card-head">
                    <div><h2>Derniers projets</h2><p>Les 5 plus récents</p></div>
                    <a href="projets.php" class="btn btn-ghost" style="padding: 4px 10px;">Voir tout</a>
                </div>
                <div style="padding: 0 20px;">
                    <?php if(empty($derniers_projets)): ?>
                        <p class="detail-empty" style="padding: 20px 0;">Aucun projet</p>
                    <?php else: ?>
                        <ul class="activity-list">
                        <?php foreach($derniers_projets as $projet): ?>
                            <li class="activity-item">
                                <div class="activity-info">
                                    <div class="activity-title"><?= htmlspecialchars($projet['titre']) ?></div>
                                    <div class="activity-sub"><?= htmlspecialchars($projet['client_nom'] ?? '—') ?> • <?= number_format($projet['budget'], 0, '.', ' ') ?> FCFA</div>
                                </div>
                                <div class="activity-date">
                                    <span class="badge <?= $statut_class[$projet['statut']] ?? 'gray' ?>" style="font-size: 9px;"><?= $statut_labels[$projet['statut']] ?? $projet['statut'] ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dernières factures -->
            <div class="card">
                <div class="card-head">
                    <div><h2>Dernières factures</h2><p>Les 5 plus récentes</p></div>
                    <a href="factures.php" class="btn btn-ghost" style="padding: 4px 10px;">Voir tout</a>
                </div>
                <div style="padding: 0 20px;">
                    <?php if(empty($dernieres_factures)): ?>
                        <p class="detail-empty" style="padding: 20px 0;">Aucune facture</p>
                    <?php else: ?>
                        <ul class="activity-list">
                        <?php foreach($dernieres_factures as $facture): ?>
                            <li class="activity-item">
                                <div class="activity-info">
                                    <div class="activity-title">FAC-<?= str_pad($facture['Fid'], 4, '0', STR_PAD_LEFT) ?></div>
                                    <div class="activity-sub"><?= htmlspecialchars($facture['client_nom'] ?? '—') ?> • <?= number_format($facture['montant'], 0, '.', ' ') ?> FCFA</div>
                                </div>
                                <div class="activity-date">
                                    <span class="badge <?= $facture['statut_paiement'] === 'payee' ? 'green' : ($facture['statut_paiement'] === 'en_attente' ? 'amber' : 'red') ?>">
                                        <?= $facture['statut_paiement'] === 'payee' ? 'Payée' : ($facture['statut_paiement'] === 'en_attente' ? 'En attente' : 'Annulée') ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Projets en cours avec progression -->
        <?php if(!empty($projets_en_cours)): ?>
        <div class="card">
            <div class="card-head">
                <div><h2>Projets en cours</h2><p>Suivi d'avancement</p></div>
            </div>
            <div style="padding: 20px;">
                <?php foreach($projets_en_cours as $projet): ?>
                    <?php 
                        $progression = $projet['total_taches'] > 0 ? ($projet['taches_terminees'] / $projet['total_taches'] * 100) : 0;
                        $client_nom = $projet['client_nom'] ?? 'Client non défini';
                    ?>
                    <div style="margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <div>
                                <span style="font-weight: 500;"><?= htmlspecialchars($projet['titre']) ?></span>
                                <span style="font-size: 12px; color: var(--ink3); margin-left: 10px;"><?= htmlspecialchars($client_nom) ?></span>
                            </div>
                            <span style="font-size: 12px; font-weight: 500;"><?= round($progression) ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progression ?>%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: var(--ink3);">
                            <span>Tâches: <?= $projet['taches_terminees'] ?>/<?= $projet['total_taches'] ?> terminées</span>
                            <a href="projets.php" style="color: var(--accent); text-decoration: none;">Voir détails →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Événements à venir -->
        <?php if(!empty($evenements)): ?>
        <div class="card" style="margin-top: 24px;">
            <div class="card-head">
                <div><h2>Événements à venir</h2><p>Prochains 7 jours</p></div>
                <a href="agenda.php" class="btn btn-ghost" style="padding: 4px 10px;">Voir agenda</a>
            </div>
            <div style="padding: 0 20px;">
                <ul class="activity-list">
                <?php foreach($evenements as $event): ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <div class="activity-title"><?= htmlspecialchars($event['titre']) ?></div>
                            <div class="activity-sub"><?= htmlspecialchars($event['projet_titre'] ?? 'Sans projet') ?></div>
                        </div>
                        <div class="activity-date">
                            <?= date('d/m/Y H:i', strtotime($event['date_heure'])) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>
<script>
window.addEventListener("pageshow", function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
<script src="JS/dash.js"></script>
</body>
</html>