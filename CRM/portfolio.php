<?php
session_start();
require 'Config/security.php';
require 'Config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$uid = (int)$_SESSION['user']['Uid'];

// ── CRUD portfolio ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $titre = trim($_POST['titre'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $lien  = trim($_POST['lien'] ?? '');
        $tel   = trim($_POST['tel'] ?? '');
        $rid   = (int)($_POST['Rid'] ?? 0);

        // Compte existant pour limiter à 6
        $nb = $conn->query("SELECT COUNT(*) c FROM portfolio WHERE Uid=$uid")->fetch_assoc()['c'];

        // Upload photo
        $photoPath = $_POST['photo_existante'] ?? null;
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/portfolio/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $nomFichier = 'portfolio_' . $uid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $nomFichier)) {
                    // Supprimer ancienne photo si édition
                    if ($action === 'edit' && $photoPath) {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . $photoPath);
                    }
                    $photoPath = '/uploads/portfolio/' . $nomFichier;
                }
            }
        }

        if ($action === 'add' && $nb < 6) {
            $stmt = $conn->prepare("INSERT INTO portfolio (titre, description, lien, tel, photo_path, Uid) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("sssssi", $titre, $desc, $lien, $tel, $photoPath, $uid);
            $stmt->execute();
        } elseif ($action === 'edit' && $rid) {
            $stmt = $conn->prepare("UPDATE portfolio SET titre=?, description=?, lien=?, tel=?, photo_path=? WHERE Rid=? AND Uid=?");
            $stmt->bind_param("sssssii", $titre, $desc, $lien, $tel, $photoPath, $rid, $uid);
            $stmt->execute();
        }

    } elseif ($action === 'del') {
        $rid = (int)$_POST['Rid'];
        // Supprimer la photo du disque
        $row = $conn->query("SELECT photo_path FROM portfolio WHERE Rid=$rid AND Uid=$uid")->fetch_assoc();
        if ($row && $row['photo_path']) @unlink($_SERVER['DOCUMENT_ROOT'] . $row['photo_path']);
        $stmt = $conn->prepare("DELETE FROM portfolio WHERE Rid=? AND Uid=?");
        $stmt->bind_param("ii", $rid, $uid);
        $stmt->execute();
    }

    header('Location: portfolio.php'); exit;
}

// ── Données ──
$realisations = $conn->query("SELECT * FROM portfolio WHERE Uid=$uid ORDER BY ordre ASC, Rid ASC")->fetch_all(MYSQLI_ASSOC);
$nb = count($realisations);
$page_active = 'portfolio';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OkCRM — Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/portfolio.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main">
    <header class="topbar">
        <div class="tb-left">
            <h1>Mon Portfolio</h1>
            <p id="date"></p>
        </div>
        <div class="tb-right">
            <?php if ($nb < 6): ?>
            <button class="btn btn-blue" onclick="openAdd()">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter une réalisation
            </button>
            <?php else: ?>
            <span class="limit-badge">Maximum atteint (6/6)</span>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>public_portfolio.php" target="_blank" class="btn btn-ghost">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Voir la page publique
            </a>
        </div>
    </header>

    <div class="page">

        <!-- Compteur -->
        <div class="portfolio-counter">
            <div class="counter-bar">
                <div class="counter-fill" style="width:<?= ($nb/6)*100 ?>%"></div>
            </div>
            <span><?= $nb ?>/6 réalisations publiées</span>
        </div>

        <?php if (empty($realisations)): ?>
        <div class="empty-portfolio">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <h3>Votre portfolio est vide</h3>
            <p>Ajoutez vos réalisations pour les rendre visibles sur votre page publique.</p>
            <button class="btn btn-blue" onclick="openAdd()">Ajouter ma première réalisation</button>
        </div>
        <?php else: ?>

        <div class="portfolio-grid">
            <?php foreach ($realisations as $i => $r): ?>
            <div class="portfolio-card">
                <div class="portfolio-card-img">
                    <?php if ($r['photo_path']): ?>
                        <img src="<?= htmlspecialchars($r['photo_path']) ?>" alt="<?= htmlspecialchars($r['titre']) ?>">
                    <?php else: ?>
                        <div class="portfolio-card-placeholder">
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <span class="portfolio-num"><?= $i+1 ?></span>
                </div>
                <div class="portfolio-card-body">
                    <h3><?= htmlspecialchars($r['titre']) ?></h3>
                    <p class="portfolio-desc"><?= nl2br(htmlspecialchars($r['description'] ?? '')) ?></p>
                    <?php if ($r['lien']): ?>
                    <a href="<?= htmlspecialchars($r['lien']) ?>" target="_blank" class="portfolio-link">
                        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Voir le projet
                    </a>
                    <?php endif; ?>
                    <?php if ($r['tel']): ?>
                    <span class="portfolio-tel">
                        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.37 18a19.5 19.5 0 0 1-5.55-5.55A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 5.55 5.55l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                        <?= htmlspecialchars($r['tel']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="portfolio-card-actions">
                    <button class="act" title="Modifier"
                        onclick="openEdit(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="act del" title="Supprimer"
                        onclick="openDel(<?= $r['Rid'] ?>, '<?= htmlspecialchars($r['titre'], ENT_QUOTES) ?>')">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<!-- Modal Ajouter / Modifier -->
<div class="overlay" id="m-form">
    <div class="modal">
        <div class="modal-head">
            <div><h3 id="m-title">Nouvelle réalisation</h3><p>Maximum 6 réalisations</p></div>
            <button class="close-btn" data-close="m-form"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <form method="POST" action="portfolio.php" enctype="multipart/form-data" class="modal-body">
            <input type="hidden" name="action" id="f-action" value="add">
            <input type="hidden" name="Rid" id="f-rid">
            <input type="hidden" name="photo_existante" id="f-photo-existante">

            <!-- Aperçu photo -->
            <div class="photo-upload-zone">
                <div class="photo-preview" id="photo-preview">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span>Cliquer pour ajouter une photo</span>
                </div>
                <input type="file" name="photo" id="f-photo" accept=".jpg,.jpeg,.png,.webp" style="display:none"
                       onchange="previewPhoto(this)">
                <label for="f-photo" class="btn-upload">Choisir une image</label>
                <p class="upload-hint">JPG, PNG ou WebP — 2 Mo max</p>
            </div>

            <div class="fg"><label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" id="f-titre" placeholder="Ex: Site e-commerce AfriShop" required></div>

            <div class="fg"><label>Description</label>
                <textarea name="description" id="f-desc" rows="3" placeholder="Décrivez ce projet : contexte, technologies, résultats..."></textarea></div>

            <div class="form-row">
                <div class="fg"><label>Lien du projet (URL)</label>
                    <input type="url" name="lien" id="f-lien" placeholder="https://..."></div>
                <div class="fg"><label>Téléphone de contact</label>
                    <input type="tel" name="tel" id="f-tel" placeholder="+237 6XX XXX XXX"></div>
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
            <h3 style="color:var(--danger)">Supprimer cette réalisation ?</h3>
            <button class="close-btn" data-close="m-del"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <form method="POST" action="portfolio.php">
            <input type="hidden" name="action" value="del">
            <input type="hidden" name="Rid" id="del-rid">
            <div class="modal-body">
                <p style="font-size:14px;color:var(--ink2);line-height:1.7">
                    Supprimer <strong id="del-nom"></strong> ? La photo sera également supprimée.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close="m-del">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>JS/portfolio.js"></script>
</body>
</html>