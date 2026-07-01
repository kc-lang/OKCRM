<?php
session_start();
// if(!isset($_SESSION['user'])){header('Location:login.php');exit;}
require 'Config/db.php';
require 'Config/security.php';
$uid = $_SESSION['user']['Uid'] ?? 1;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['action']??'';
  if($a==='add'){
    $s=$conn->prepare("INSERT INTO projet(titre,statut,budget,Cid,Uid) VALUES(?,?,?,?,?)");
    $s->bind_param('ssdii',$_POST['titre'],$_POST['statut'],$_POST['budget'],$_POST['Cid'],$uid);
    $s->execute();
  } elseif($a==='edit'){
    $s=$conn->prepare("UPDATE projet SET titre=?,statut=?,budget=?,Cid=? WHERE Pid=? AND Uid=?");
    $s->bind_param('ssdiid',$_POST['titre'],$_POST['statut'],$_POST['budget'],$_POST['Cid'],$_POST['Pid'],$uid);
    $s->execute();
  } elseif($a==='del'){
    $s=$conn->prepare("DELETE FROM projet WHERE Pid=? AND Uid=?");
    $s->bind_param('ii',$_POST['Pid'],$uid);
    $s->execute();
  }
  header('Location:projets.php'); exit;
}

$projets = $conn->query("
  SELECT p.*, c.nom AS client_nom,
    COUNT(DISTINCT t.Tid) nb_taches
  FROM projet p
  LEFT JOIN client c ON c.Cid=p.Cid
  LEFT JOIN tache  t ON t.Pid=p.Pid
  WHERE p.Uid=$uid
  GROUP BY p.Pid ORDER BY p.Pid DESC
")->fetch_all(MYSQLI_ASSOC);

$clients = $conn->query("SELECT Cid,nom FROM client WHERE Uid=$uid ORDER BY nom")->fetch_all(MYSQLI_ASSOC);

$total    = count($projets);
$en_cours = count(array_filter($projets,fn($p)=>$p['statut']==='en_cours'));
$termines = count(array_filter($projets,fn($p)=>$p['statut']==='termine'));
$budget   = array_sum(array_column($projets,'budget'));

$statut_labels=['en_attente'=>'En attente','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé'];
$statut_class =['en_attente'=>'gray','en_cours'=>'blue','termine'=>'green','annule'=>'red'];

$page_active='projets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OkCRM — Projets</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/projet.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
</head>
<body>
<?php include 'sidebar.php' ?>

<main class="main">
  <header class="topbar">
    <div class="tb-left"><h1>Projets</h1><p id="date"></p></div>
    <div class="tb-right">
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="search" placeholder="Rechercher…">
      </div>
      <button class="btn btn-blue" onclick="openAdd()">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau projet
      </button>
    </div>
  </header>

  <div class="page">
    <div class="stats-row">
      <div class="stat-card">
        <div class="s-icon blue"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg></div>
        <div><p class="s-val"><?= $total ?></p><p class="s-lbl">Total projets</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon amber"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div><p class="s-val"><?= $en_cours ?></p><p class="s-lbl">En cours</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon green"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div><p class="s-val"><?= $termines ?></p><p class="s-lbl">Terminés</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon purple"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div><p class="s-val"><?= number_format($budget,0,'.',' ') ?></p><p class="s-lbl">Budget total (FCFA)</p></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <div><h2>Liste des projets</h2><p id="count"><?= $total ?> projet<?= $total>1?'s':'' ?></p></div>
        <div class="card-actions">
          <div class="filters">
            <button class="f-btn active" data-f="tous">Tous</button>
            <button class="f-btn" data-f="en_attente">En attente</button>
            <button class="f-btn" data-f="en_cours">En cours</button>
            <button class="f-btn" data-f="termine">Terminés</button>
          </div>
        </div>
      </div>
      <div class="tbl-wrap">
        <table id="tbl">
          <thead><tr>
            <th>Titre</th><th>Client</th><th>Budget (FCFA)</th>
            <th>Tâches</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php if(empty($projets)): ?>
            <tr><td colspan="6">
              <div class="empty">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg>
                <p>Aucun projet pour l'instant</p>
                <button class="btn btn-blue" onclick="openAdd()">Créer un projet</button>
              </div>
            </td></tr>
          <?php else: ?>
            <?php foreach($projets as $p): ?>
            <tr data-statut="<?= $p['statut'] ?>">
              <td class="bold"><?= htmlspecialchars($p['titre']) ?></td>
              <td><?= htmlspecialchars($p['client_nom']??'—') ?></td>
              <td><?= number_format($p['budget'],0,'.',' ') ?></td>
              <td><span class="num-badge <?= $p['nb_taches']>0?'blue':'gray' ?>"><?= $p['nb_taches'] ?></span></td>
              <td><span class="badge <?= $statut_class[$p['statut']] ?>"><?= $statut_labels[$p['statut']] ?></span></td>
              <td>
                <div class="row-acts">
                  <button class="act" title="Modifier"
                    onclick="openEdit(<?= $p['Pid'] ?>,'<?= addslashes($p['titre']) ?>','<?= $p['statut'] ?>',<?= $p['budget'] ?>,<?= $p['Cid'] ?>)">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="act del" title="Supprimer"
                    onclick="openDel(<?= $p['Pid'] ?>,'<?= addslashes($p['titre']) ?>')">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach ?>
          <?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Modal Ajouter/Modifier -->
<div class="overlay" id="m-form">
  <div class="modal">
    <div class="modal-head">
      <div><h3 id="m-title">Nouveau projet</h3><p id="m-sub">Renseigner les informations</p></div>
      <button class="close-btn" data-close="m-form"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="projets.php" class="modal-body" id="form-projet" novalidate>
      <input type="hidden" name="action" id="f-action" value="add">
      <input type="hidden" name="Pid" id="f-pid">
      <div class="form-row">
        <div class="fg" style="grid-column:1/-1"><label>Titre <span class="req">*</span></label><input type="text" name="titre" id="f-titre" placeholder="Nom du projet"></div>
      </div>
      <div class="form-row">
        <div class="fg">
          <label>Client <span class="req">*</span></label>
          <select name="Cid" id="f-cid">
            <option value="">-- Sélectionner --</option>
            <?php foreach($clients as $c): ?>
            <option value="<?= $c['Cid'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="fg">
          <label>Statut</label>
          <select name="statut" id="f-statut">
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="termine">Terminé</option>
            <option value="annule">Annulé</option>
          </select>
        </div>
      </div>
      <div class="fg"><label>Budget (FCFA)</label><input type="number" name="budget" id="f-budget" placeholder="0" min="0"></div>
      <p class="f-err" id="f-err"></p>
      <div class="modal-foot" style="padding:0;border:none;background:none;margin-top:16px">
        <button type="button" class="btn btn-ghost" data-close="m-form">Annuler</button>
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
      <h3 style="color:var(--danger)">Supprimer ce projet ?</h3>
      <button class="close-btn" data-close="m-del"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="projets.php">
      <input type="hidden" name="action" value="del">
      <input type="hidden" name="Pid" id="del-pid">
      <div class="modal-body">
        <p style="font-size:14px;color:var(--ink2);line-height:1.7">Supprimer <strong id="del-nom"></strong> ? Cette action est irréversible.</p>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" data-close="m-del">Annuler</button>
        <button type="submit" class="btn btn-danger">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          Supprimer
        </button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"></div>
<script src="JS/projet.js"></script>
</body>
</html>