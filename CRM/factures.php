<?php
session_start();
// if(!isset($_SESSION['user'])){header('Location:login.php');exit;}
require 'Config/db.php';
require 'Config/security.php';
$uid = $_SESSION['user']['Uid'] ?? 1;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['action']??'';
  if($a==='add'){
    $s=$conn->prepare("INSERT INTO facture(montant,statut_paiement,date,Pid,Cid) VALUES(?,?,?,?,?)");
    $s->bind_param('dssii',$_POST['montant'],$_POST['statut_paiement'],$_POST['date'],$_POST['Pid'],$_POST['Cid']);
    $s->execute();
  } elseif($a==='edit'){
    $s=$conn->prepare("UPDATE facture SET montant=?,statut_paiement=?,date=?,Pid=?,Cid=? WHERE Fid=?");
    $s->bind_param('dssiid',$_POST['montant'],$_POST['statut_paiement'],$_POST['date'],$_POST['Pid'],$_POST['Cid'],$_POST['Fid']);
    $s->execute();
  } elseif($a==='del'){
    $conn->query("DELETE FROM facture WHERE Fid=".(int)$_POST['Fid']);
  }
  header('Location:factures.php'); exit;
}

$factures = $conn->query("
  SELECT f.*, c.nom AS client_nom, p.titre AS projet_titre
  FROM facture f
  LEFT JOIN client  c ON c.Cid=f.Cid
  LEFT JOIN projet  p ON p.Pid=f.Pid
  LEFT JOIN user_   u ON u.Uid=p.Uid
  WHERE p.Uid=$uid OR c.Uid=$uid
  ORDER BY f.Fid DESC
")->fetch_all(MYSQLI_ASSOC);

$clients = $conn->query("SELECT Cid,nom FROM client WHERE Uid=$uid ORDER BY nom")->fetch_all(MYSQLI_ASSOC);
$projets = $conn->query("SELECT Pid,titre,Cid FROM projet WHERE Uid=$uid ORDER BY titre")->fetch_all(MYSQLI_ASSOC);

$total    = count($factures);
$payees   = count(array_filter($factures,fn($f)=>$f['statut_paiement']==='payee'));
$attente  = count(array_filter($factures,fn($f)=>$f['statut_paiement']==='en_attente'));
$ca       = array_sum(array_column(array_filter($factures,fn($f)=>$f['statut_paiement']==='payee'),'montant'));

$sp_labels=['en_attente'=>'En attente','payee'=>'Payée','annulee'=>'Annulée'];
$sp_class =['en_attente'=>'amber','payee'=>'green','annulee'=>'red'];

$page_active='factures';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OkCRM — Factures</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/factures.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
</head>
<body>
<?php include 'sidebar.php' ?>

<main class="main">
  <header class="topbar">
    <div class="tb-left"><h1>Factures</h1><p id="date"></p></div>
    <div class="tb-right">
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="search" placeholder="Rechercher…">
      </div>
      <button class="btn btn-blue" onclick="openAdd()">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle facture
      </button>
    </div>
  </header>

  <div class="page">
    <div class="stats-row">
      <div class="stat-card">
        <div class="s-icon blue"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div><p class="s-val"><?= $total ?></p><p class="s-lbl">Total factures</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon amber"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div><p class="s-val"><?= $attente ?></p><p class="s-lbl">En attente</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon green"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div><p class="s-val"><?= $payees ?></p><p class="s-lbl">Payées</p></div>
      </div>
      <div class="stat-card">
        <div class="s-icon purple"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div><p class="s-val"><?= number_format($ca,0,'.',' ') ?></p><p class="s-lbl">CA encaissé (FCFA)</p></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <div><h2>Liste des factures</h2><p id="count"><?= $total ?> facture<?= $total>1?'s':'' ?></p></div>
        <div class="card-actions">
          <div class="filters">
            <button class="f-btn active" data-f="tous">Toutes</button>
            <button class="f-btn" data-f="en_attente">En attente</button>
            <button class="f-btn" data-f="payee">Payées</button>
            <button class="f-btn" data-f="annulee">Annulées</button>
          </div>
        </div>
      </div>
      <div class="tbl-wrap">
        <table id="tbl">
          <thead><tr>
            <th>N° Facture</th><th>Client</th><th>Projet</th>
            <th>Montant (FCFA)</th><th>Date</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php if(empty($factures)): ?>
            <tr><td colspan="7">
              <div class="empty">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>Aucune facture pour l'instant</p>
                <button class="btn btn-blue" onclick="openAdd()">Créer une facture</button>
              </div>
            </td></tr>
          <?php else: ?>
            <?php foreach($factures as $f): ?>
            <tr data-statut="<?= $f['statut_paiement'] ?>">
              <td class="bold fac-num">FAC-<?= str_pad($f['Fid'],4,'0',STR_PAD_LEFT) ?></td>
              <td><?= htmlspecialchars($f['client_nom']??'—') ?></td>
              <td><?= htmlspecialchars($f['projet_titre']??'—') ?></td>
              <td class="bold"><?= number_format($f['montant'],0,'.',' ') ?></td>
              <td><?= date('d/m/Y',strtotime($f['date'])) ?></td>
              <td><span class="badge <?= $sp_class[$f['statut_paiement']] ?>"><?= $sp_labels[$f['statut_paiement']] ?></span></td>
              <td>
                <div class="row-acts">
                  <button class="act" title="Modifier"
                    onclick="openEdit(<?= $f['Fid'] ?>,<?= $f['montant'] ?>,'<?= $f['statut_paiement'] ?>','<?= $f['date'] ?>',<?= $f['Pid'] ?>,<?= $f['Cid'] ?>)">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="act marquer <?= $f['statut_paiement']==='payee'?'done':'' ?>" title="Marquer payée"
                    onclick="marquerPayee(<?= $f['Fid'] ?>,<?= $f['montant'] ?>,'<?= $f['date'] ?>',<?= $f['Pid'] ?>,<?= $f['Cid'] ?>)">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <a href="facturepdf.php?id=<?= $f['Fid'] ?>" class="act" title="PDF">
    PDF
</a>

<a href="facturecsv.php?id=<?= $f['Fid'] ?>" class="act" title="CSV">
    CSV
</a>
                  <button class="act del" title="Supprimer"
                    onclick="openDel(<?= $f['Fid'] ?>,'FAC-<?= str_pad($f['Fid'],4,'0',STR_PAD_LEFT) ?>')">
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
      <div><h3 id="m-title">Nouvelle facture</h3><p>Renseigner les informations</p></div>
      <button class="close-btn" data-close="m-form"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="factures.php" class="modal-body" id="form-facture" novalidate>
      <input type="hidden" name="action" id="f-action" value="add">
      <input type="hidden" name="Fid" id="f-fid">
      <div class="form-row">
        <div class="fg">
          <label>Client <span class="req">*</span></label>
          <select name="Cid" id="f-cid" onchange="filtrerProjets()">
            <option value="">-- Sélectionner --</option>
            <?php foreach($clients as $c): ?>
            <option value="<?= $c['Cid'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="fg">
          <label>Projet <span class="req">*</span></label>
          <select name="Pid" id="f-pid">
            <option value="">-- Sélectionner --</option>
            <?php foreach($projets as $p): ?>
            <option value="<?= $p['Pid'] ?>" data-cid="<?= $p['Cid'] ?>"><?= htmlspecialchars($p['titre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="fg"><label>Montant (FCFA) <span class="req">*</span></label><input type="number" name="montant" id="f-montant" placeholder="0" min="0"></div>
        <div class="fg"><label>Date <span class="req">*</span></label><input type="date" name="date" id="f-date"></div>
      </div>
      <div class="fg">
        <label>Statut paiement</label>
        <select name="statut_paiement" id="f-statut">
          <option value="en_attente">En attente</option>
          <option value="payee">Payée</option>
          <option value="annulee">Annulée</option>
        </select>
      </div>
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
      <h3 style="color:var(--danger)">Supprimer cette facture ?</h3>
      <button class="close-btn" data-close="m-del"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="factures.php">
      <input type="hidden" name="action" value="del">
      <input type="hidden" name="Fid" id="del-fid">
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
<script src="JS/factures.js"></script>
</body>
</html>