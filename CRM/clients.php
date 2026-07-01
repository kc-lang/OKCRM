<?php
session_start();
// if(!isset($_SESSION['user'])){header('Location:login.php');exit;}
require 'Config/db.php';
require 'Config/security.php';
$uid = $_SESSION['user']['Uid'] ?? 1;

// ── CRUD ──
if($_SERVER['REQUEST_METHOD']==='POST'){
  $a = $_POST['action'] ?? '';
  if($a==='add'){
    $s=$conn->prepare("INSERT INTO client(nom,email,tel,Uid) VALUES(?,?,?,?)");
    $s->bind_param('sssi',$_POST['nom'],$_POST['email'],$_POST['tel'],$uid);
    $s->execute();
  } elseif($a==='edit'){
    $s=$conn->prepare("UPDATE client SET nom=?,email=?,tel=? WHERE Cid=? AND Uid=?");
    $s->bind_param('sssii',$_POST['nom'],$_POST['email'],$_POST['tel'],$_POST['Cid'],$uid);
    $s->execute();
  } elseif($a==='del'){
    $s=$conn->prepare("DELETE FROM client WHERE Cid=? AND Uid=?");
    $s->bind_param('ii',$_POST['Cid'],$uid);
    $s->execute();
  }
  header('Location:clients.php'); exit;
}

// ── DONNÉES ──
$clients = $conn->query("
  SELECT c.*,
    COUNT(DISTINCT p.Pid) nb_projets,
    COUNT(DISTINCT f.Fid) nb_factures
  FROM client c
  LEFT JOIN projet  p ON p.Cid=c.Cid
  LEFT JOIN facture f ON f.Cid=c.Cid
  WHERE c.Uid=$uid
  GROUP BY c.Cid ORDER BY c.Cid DESC
")->fetch_all(MYSQLI_ASSOC);

$total    = count($clients);
$av_proj  = count(array_filter($clients,fn($c)=>$c['nb_projets']>0));
$av_fact  = count(array_filter($clients,fn($c)=>$c['nb_factures']>0));

$page_active = 'clients';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OkCRM — Clients</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/clients.css">
</head>
<body>
<?php include 'sidebar.php' ?>

<main class="main">
  <header class="topbar">
    <div class="tb-left">
      <h1>Clients</h1>
      <p id="date"></p>
    </div>
    <div class="tb-right">
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="search" placeholder="Rechercher…">
      </div>
      <button class="btn btn-blue" onclick="openAdd()">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau client
      </button>
    </div>
  </header>

  <div class="page">
    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="s-icon blue"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div><p class="s-val"><?= $total ?></p><p class="s-lbl">Total clients</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon green"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg></div>
        <div><p class="s-val"><?= $av_proj ?></p><p class="s-lbl">Avec projets</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon amber"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div><p class="s-val"><?= $av_fact ?></p><p class="s-lbl">Factures en attente</p></div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card">
      <div class="card-head">
        <div><h2>Liste des clients</h2><p id="count"><?= $total ?> client<?= $total>1?'s':'' ?></p></div>
        <div class="card-actions">
          <div class="filters">
            <button class="f-btn active" data-f="tous">Tous</button>
            <button class="f-btn" data-f="projets">Avec projets</button>
            <button class="f-btn" data-f="factures">Avec factures</button>
          </div>
        </div>
      </div>
      <div class="tbl-wrap">
        <table id="tbl">
          <thead><tr>
            <th>Nom</th><th>Email</th><th>Téléphone</th>
            <th>Projets</th><th>Factures</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php if(empty($clients)): ?>
            <tr><td colspan="6">
              <div class="empty">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <p>Aucun client pour l'instant</p>
                <button class="btn btn-blue" onclick="openAdd()">Ajouter un client</button>
              </div>
            </td></tr>
          <?php else: ?>
            <?php foreach($clients as $c): ?>
            <?php $ini=strtoupper(substr($c['nom'],0,1)); ?>
            <tr data-projets="<?= $c['nb_projets'] ?>" data-factures="<?= $c['nb_factures'] ?>">
              <td class="bold">
                <div class="av-cell">
                  <div class="av av-<?= $c['Cid']%8 ?>"><?= $ini ?></div>
                  <?= htmlspecialchars($c['nom']) ?>
                </div>
              </td>
              <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
              <td><?= htmlspecialchars($c['tel']   ?? '—') ?></td>
              <td><span class="num-badge <?= $c['nb_projets']>0?'blue':'gray' ?>"><?= $c['nb_projets'] ?></span></td>
              <td><span class="num-badge <?= $c['nb_factures']>0?'amber':'gray' ?>"><?= $c['nb_factures'] ?></span></td>
              <td>
                <div class="row-acts">
                  <button class="act" title="Détails"
                    onclick="openDetail(<?= $c['Cid'] ?>,'<?= addslashes($c['nom']) ?>','<?= addslashes($c['email']??'') ?>','<?= addslashes($c['tel']??'') ?>',<?= $c['nb_projets'] ?>,<?= $c['nb_factures'] ?>)">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button class="act" title="Modifier"
                    onclick="openEdit(<?= $c['Cid'] ?>,'<?= addslashes($c['nom']) ?>','<?= addslashes($c['email']??'') ?>','<?= addslashes($c['tel']??'') ?>')">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="act del" title="Supprimer"
                    onclick="openDel(<?= $c['Cid'] ?>,'<?= addslashes($c['nom']) ?>')">
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
      <div><h3 id="m-title">Nouveau client</h3><p id="m-sub">Renseigner les informations</p></div>
      <button class="close-btn" data-close="m-form"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="clients.php" class="modal-body" id="form-client" novalidate>
      <input type="hidden" name="action" id="f-action" value="add">
      <input type="hidden" name="Cid"    id="f-cid">
      <div class="form-row">
        <div class="fg"><label>Nom <span class="req">*</span></label><input type="text"  name="nom"   id="f-nom"   placeholder="Jean Dupont"></div>
        <div class="fg"><label>Email <span class="req">*</span></label><input type="email" name="email" id="f-email" placeholder="jean@mail.com"></div>
      </div>
      <div class="fg"><label>Téléphone</label><input type="tel" name="tel" id="f-tel" placeholder="+237 6XX XXX XXX"></div>
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

<!-- Modal Détail -->
<div class="overlay" id="m-detail">
  <div class="modal lg">
    <div class="modal-head">
      <div style="display:flex;align-items:center;gap:12px">
        <div class="av" id="d-av" style="width:44px;height:44px;font-size:17px"></div>
        <div><h3 id="d-nom"></h3><p id="d-email"></p></div>
      </div>
      <button class="close-btn" data-close="m-detail"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body detail-grid">
      <div class="detail-sec full">
        <p class="sec-lbl">Contact</p>
        <p class="detail-line"><span>Téléphone</span><strong id="d-tel"></strong></p>
      </div>
      <div class="detail-sec">
        <p class="sec-lbl">Projets liés</p>
        <div id="d-proj"></div>
      </div>
      <div class="detail-sec">
        <p class="sec-lbl">Factures</p>
        <div id="d-fact"></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" data-close="m-detail">Fermer</button>
      <button class="btn btn-warn" id="btn-edit-d">
        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Modifier
      </button>
    </div>
  </div>
</div>

<!-- Modal Suppression -->
<div class="overlay" id="m-del">
  <div class="modal sm">
    <div class="modal-head">
      <h3 style="color:var(--danger)">Supprimer ce client ?</h3>
      <button class="close-btn" data-close="m-del"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="clients.php">
      <div class="modal-body">
        <input type="hidden" name="action" value="del">
        <input type="hidden" name="Cid" id="del-cid">
        <p style="font-size:14px;color:var(--ink2);line-height:1.7">
          Supprimer <strong id="del-nom"></strong> ? Cette action est irréversible.
        </p>
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
<script src="JS/clients.js"></script>
</body>
</html>