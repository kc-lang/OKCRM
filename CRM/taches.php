<?php
// ── taches.php ── OkCRM  (remplace la section PHP en haut du fichier)
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require 'Config/db.php';
require 'Config/security.php';

$uid = (int)$_SESSION['user']['Uid'];

// ── CRUD TÂCHES ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $titre  = trim($_POST['titre']);
        $statut = $_POST['statut'];
        $pid    = !empty($_POST['Pid']) ? (int)$_POST['Pid'] : null;

        // bind_param ne supporte pas null direct → utilise une variable
        $stmt = $conn->prepare("INSERT INTO tache (titre, statut, Pid) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $titre, $statut, $pid);
        $stmt->execute();

    } elseif ($action === 'edit') {
        $titre  = trim($_POST['titre']);
        $statut = $_POST['statut'];
        $pid    = !empty($_POST['Pid']) ? (int)$_POST['Pid'] : null;
        $tid    = (int)$_POST['Tid'];

        $stmt = $conn->prepare("UPDATE tache SET titre=?, statut=?, Pid=? WHERE Tid=?");
        $stmt->bind_param("ssii", $titre, $statut, $pid, $tid);
        $stmt->execute();

    } elseif ($action === 'del') {
        $tid  = (int)$_POST['Tid'];
        $stmt = $conn->prepare("DELETE FROM tache WHERE Tid=?");
        $stmt->bind_param("i", $tid);
        $stmt->execute();

    } elseif ($action === 'change_status') {
        $statut = $_POST['statut'];
        $tid    = (int)$_POST['Tid'];
        $stmt   = $conn->prepare("UPDATE tache SET statut=? WHERE Tid=?");
        $stmt->bind_param("si", $statut, $tid);
        $stmt->execute();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    header('Location: taches.php');
    exit;
}

// ── FILTRES ──
$filtre_statut = $_GET['statut'] ?? 'tous';
$filtre_projet = $_GET['projet'] ?? 'tous';

// ── DONNÉES ──
// FIX : LEFT JOIN + filtre Uid sur projet OU tâche sans projet
$where = ["(p.Uid = $uid OR t.Pid IS NULL)"];
if ($filtre_statut !== 'tous') $where[] = "t.statut = '" . $conn->real_escape_string($filtre_statut) . "'";
if ($filtre_projet !== 'tous') $where[] = "t.Pid = " . (int)$filtre_projet;
$where_sql = implode(' AND ', $where);

$taches = $conn->query("
    SELECT t.*, p.titre AS projet_titre
    FROM tache t
    LEFT JOIN projet p ON p.Pid = t.Pid
    WHERE $where_sql
    ORDER BY FIELD(t.statut,'a_faire','en_cours','termine'), t.Tid DESC
")->fetch_all(MYSQLI_ASSOC);

$projets = $conn->query("
    SELECT Pid, titre FROM projet WHERE Uid = $uid ORDER BY titre
")->fetch_all(MYSQLI_ASSOC);

$stats = $conn->query("
    SELECT
        COUNT(t.Tid)                                              AS total,
        SUM(CASE WHEN t.statut='a_faire'  THEN 1 ELSE 0 END)    AS a_faire,
        SUM(CASE WHEN t.statut='en_cours' THEN 1 ELSE 0 END)    AS en_cours,
        SUM(CASE WHEN t.statut='termine'  THEN 1 ELSE 0 END)    AS termine
    FROM tache t
    LEFT JOIN projet p ON p.Pid = t.Pid
    WHERE p.Uid = $uid OR t.Pid IS NULL
")->fetch_assoc();

$statut_labels = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
$statut_class  = ['a_faire' => 'gray',    'en_cours' => 'blue',     'termine' => 'green'];

$page_active = 'taches';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OkCRM — Tâches</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/taches.css">
  
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main">
    <header class="topbar">
        <div class="tb-left">
            <h1>Tâches</h1>
            <p id="date"></p>
        </div>
        <div class="tb-right">
            <div class="search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="search" placeholder="Rechercher une tâche…">
            </div>
            <button class="btn btn-blue" onclick="openTaskModal()">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvelle tâche
            </button>
        </div>
    </header>

    <div class="page">

        <!-- Statistiques -->
        <div class="stats-mini">
            <div class="stat-mini">
                <div class="number"><?= $stats['total']   ?? 0 ?></div>
                <div class="label">Total tâches</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?= $stats['a_faire']  ?? 0 ?></div>
                <div class="label">À faire</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?= $stats['en_cours'] ?? 0 ?></div>
                <div class="label">En cours</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?= $stats['termine']  ?? 0 ?></div>
                <div class="label">Terminées</div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="card">
            <div class="card-head">
                <div><h2>Gestion des tâches</h2></div>
                <div class="card-head-right">
                    <!-- Filtres -->
                    <div class="filter-bar">
                        <select id="filter-projet" class="filter-select" onchange="applyFilters()">
                            <option value="tous">Tous les projets</option>
                            <?php foreach ($projets as $p): ?>
                                <option value="<?= $p['Pid'] ?>" <?= $filtre_projet == $p['Pid'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter-statut" class="filter-select" onchange="applyFilters()">
                            <option value="tous"     <?= $filtre_statut === 'tous'     ? 'selected' : '' ?>> Tous les statuts</option>
                            <option value="a_faire"  <?= $filtre_statut === 'a_faire'  ? 'selected' : '' ?>>À faire</option>
                            <option value="en_cours" <?= $filtre_statut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                            <option value="termine"  <?= $filtre_statut === 'termine'  ? 'selected' : '' ?>>Terminé</option>
                        </select>
                    </div>
                    <!-- Toggle vue -->
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="list"   onclick="setView('list')"> Liste</button>
                        <button class="view-btn"        data-view="kanban" onclick="setView('kanban')">📊 Kanban</button>
                    </div>
                </div>
            </div>

            <!-- Vue Liste -->
            <div id="list-view">
                <div class="tbl-wrap">
                    <table style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:40%">Tâche</th>
                                <th>Projet</th>
                                <th>Statut</th>
                                <th style="width:80px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-list">
                            <?php if (empty($taches)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty">
                                            <p>Aucune tâche pour l'instant</p>
                                            <button class="btn btn-blue" onclick="openTaskModal()">Créer une tâche</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($taches as $t): ?>
                                    <tr data-search="<?= htmlspecialchars(strtolower($t['titre'] . ' ' . ($t['projet_titre'] ?? ''))) ?>"
                                        data-id="<?= $t['Tid'] ?>"
                                        data-statut="<?= $t['statut'] ?>">
                                        <td class="bold">
                                            <div class="task-cell">
                                                <div class="task-status <?= $t['statut'] ?>"
                                                     onclick="quickChangeStatus(<?= $t['Tid'] ?>, '<?= $t['statut'] ?>')"
                                                     title="Changer le statut"></div>
                                                <span class="task-name"><?= htmlspecialchars($t['titre']) ?></span>
                                            </div>
                                        </td>
                                        <td><span class="task-project"><?= htmlspecialchars($t['projet_titre'] ?? '—') ?></span></td>
                                        <td><span class="badge <?= $statut_class[$t['statut']] ?>"><?= $statut_labels[$t['statut']] ?></span></td>
                                        <td>
                                            <div class="row-acts">
                                                <button class="act" onclick="editTask(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </button>
                                                <button class="act del" onclick="deleteTask(<?= $t['Tid'] ?>, '<?= htmlspecialchars($t['titre'], ENT_QUOTES) ?>')">
                                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vue Kanban -->
            <div id="kanban-view" style="display:none">
                <div class="kanban-board">
                    <?php foreach (['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'] as $key => $label): ?>
                        <div class="kanban-col" data-status="<?= $key ?>" ondrop="dropOnColumn(event)" ondragover="allowDrop(event)">
                            <div class="kanban-title <?= $key ?>"><?= $label ?></div>
                            <div class="kanban-tasks" id="kanban-<?= $key ?>">
                                <?php foreach (array_filter($taches, fn($t) => $t['statut'] === $key) as $t): ?>
                                    <div class="kanban-task"
                                         draggable="true"
                                         data-id="<?= $t['Tid'] ?>"
                                         data-status="<?= $t['statut'] ?>"
                                         ondragstart="dragStart(event)">
                                        <div class="kanban-task-title"><?= htmlspecialchars($t['titre']) ?></div>
                                        <div class="kanban-task-project"> <?= htmlspecialchars($t['projet_titre'] ?? 'Sans projet') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div><!-- /.card -->
    </div><!-- /.page -->
</main>

<!-- ── MODAL : Ajouter / Modifier ── -->
<div class="overlay" id="m-task">
    <div class="modal">
        <div class="modal-head">
            <div>
                <h3 id="m-title">Nouvelle tâche</h3>
                <p id="m-sub">Renseigner les informations</p>
            </div>
            <button class="close-btn" data-close="m-task">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="taches.php" class="modal-body" id="form-task">
            <input type="hidden" name="action" id="f-action" value="add">
            <input type="hidden" name="Tid"    id="f-tid">

            <div class="fg">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" id="f-titre" required placeholder="Nom de la tâche">
            </div>

            <div class="form-row">
                <div class="fg">
                    <label>Statut</label>
                    <select name="statut" id="f-statut">
                        <option value="a_faire">À faire</option>
                        <option value="en_cours">En cours</option>
                        <option value="termine">Terminé</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Projet lié</label>
                    <select name="Pid" id="f-pid">
                        <option value="">-- Sélectionner un projet --</option>
                        <?php foreach ($projets as $p): ?>
                            <option value="<?= $p['Pid'] ?>"><?= htmlspecialchars($p['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <p class="f-err" id="f-err"></p>

            <div class="modal-foot" style="padding:0;border:none;background:none;margin-top:16px">
                <button type="button" class="btn btn-ghost" data-close="m-task">Annuler</button>
                <button type="submit" class="btn btn-blue">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL : Suppression ── -->
<div class="overlay" id="m-del">
    <div class="modal sm">
        <div class="modal-head">
            <h3 style="color:var(--danger)">Supprimer cette tâche ?</h3>
            <button class="close-btn" data-close="m-del">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="taches.php">
            <input type="hidden" name="action" value="del">
            <input type="hidden" name="Tid" id="del-tid">
            <div class="modal-body">
                <p style="font-size:14px;color:var(--ink2);line-height:1.7">
                    Supprimer la tâche <strong id="del-nom"></strong> ? Cette action est irréversible.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close="m-del">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script src="JS/taches.js"></script>
</body>
</html>