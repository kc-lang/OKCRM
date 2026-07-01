<?php
session_start();
require 'Config/security.php';
require 'Config/db.php';

$est_connecte = isset($_SESSION['user']);

// Récupère toutes les réalisations de tous les freelances (page publique)
// ou filtre par ?uid=X si on veut voir un freelance spécifique
$filtre_uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;

if ($filtre_uid) {
    $freelance = $conn->query("SELECT nom, email FROM user_ WHERE Uid=$filtre_uid")->fetch_assoc();
    $realisations = $conn->query("
        SELECT p.*, u.nom AS freelance_nom, u.email AS freelance_email
        FROM portfolio p JOIN user_ u ON u.Uid=p.Uid
        WHERE p.Uid=$filtre_uid
        ORDER BY p.ordre ASC, p.Rid ASC
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    $freelance = null;
    $realisations = $conn->query("
        SELECT p.*, u.nom AS freelance_nom, u.email AS freelance_email
        FROM portfolio p JOIN user_ u ON u.Uid=p.Uid
        ORDER BY p.Uid ASC, p.ordre ASC, p.Rid ASC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OkCRM — Portfolio<?= $freelance ? ' de ' . htmlspecialchars($freelance['nom']) : 's' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/public_portfolio.css">
</head>
<body>

<!-- NAV -->
<nav>
    <a class="nav-logo" href="site.php">Ok<span>CRM</span></a>
    <div class="nav-links">
        <a href="index.php#features">Fonctionnalités</a>
        <a href="index.php#how">Comment ça marche</a>
        <?php if ($est_connecte): ?>
            <a href="dashboard.php" class="btn-nav">← Retour au dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Se connecter</a>
        <?php endif; ?>
    </div>
</nav>

<!-- HERO public portfolio -->
<section class="pub-hero">
    <div class="pub-hero-content">
        <div class="hero-badge">Réalisations freelances</div>
        <?php if ($freelance): ?>
            <h1>Portfolio de <em><?= htmlspecialchars($freelance['nom']) ?></em></h1>
            <p><?= htmlspecialchars($freelance['email']) ?></p>
        <?php else: ?>
            <h1>Nos <em>réalisations</em></h1>
            <p>Découvrez les projets réalisés par nos freelances</p>
        <?php endif; ?>
    </div>
</section>

<!-- GRILLE RÉALISATIONS -->
<section class="pub-grid-section">
    <?php if (empty($realisations)): ?>
        <div class="pub-empty">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <p>Aucune réalisation publiée pour l'instant.</p>
        </div>
    <?php else: ?>
        <div class="pub-grid">
            <?php foreach ($realisations as $r): ?>
            <div class="pub-card" onclick="openLightbox(this)" data-titre="<?= htmlspecialchars($r['titre'], ENT_QUOTES) ?>" data-desc="<?= htmlspecialchars($r['description'] ?? '', ENT_QUOTES) ?>" data-lien="<?= htmlspecialchars($r['lien'] ?? '', ENT_QUOTES) ?>" data-tel="<?= htmlspecialchars($r['tel'] ?? '', ENT_QUOTES) ?>" data-freelance="<?= htmlspecialchars($r['freelance_nom'], ENT_QUOTES) ?>">
                <div class="pub-card-img">
                    <?php if ($r['photo_path']): ?>
                        <img src="<?= htmlspecialchars($r['photo_path']) ?>" alt="<?= htmlspecialchars($r['titre']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="pub-card-placeholder">
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="pub-card-overlay">
                        <span>Voir les détails</span>
                    </div>
                </div>
                <div class="pub-card-body">
                    <h3><?= htmlspecialchars($r['titre']) ?></h3>
                    <?php if (!$filtre_uid): ?>
                        <span class="pub-freelance-tag"><?= htmlspecialchars($r['freelance_nom']) ?></span>
                    <?php endif; ?>
                    <p class="pub-desc"><?= htmlspecialchars(mb_substr($r['description'] ?? '', 0, 100)) ?><?= mb_strlen($r['description'] ?? '') > 100 ? '…' : '' ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- CTA CONTACT -->
<section class="pub-cta">
    <h2>Un projet en tête ?</h2>
    <p>Contactez un freelance OkCRM et donnez vie à votre idée.</p>
    <a href="<?= $est_connecte ? 'dashboard.php' : 'register.php' ?>" class="btn-primary">
        <?= $est_connecte ? '← Retour au dashboard' : 'Rejoindre OkCRM' ?>
    </a>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-logo">Ok<span>CRM</span></div>
    <p>© 2025 OkCRM — Tous droits réservés</p>
</footer>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightbox" onclick="closeLightbox(event)">
    <div class="lightbox-card">
        <button class="lightbox-close" onclick="closeLightbox()">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <h2 id="lb-titre"></h2>
        <p class="lb-freelance" id="lb-freelance"></p>
        <p id="lb-desc"></p>
        <div class="lb-contacts">
            <a id="lb-lien" href="#" target="_blank" class="lb-btn-lien" style="display:none">
                <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Voir le projet
            </a>
            <a id="lb-tel" href="#" class="lb-btn-tel" style="display:none">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 
                19.79 0 0 1 11.37 18a19.5 19.5 0 0 1-5.55-5.55A19.79 19.79 0 0 1 2.12
                 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 5.55 5.55l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 
                1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                Appeler
            </a>
        </div>
    </div>
</div>

<script>
// ── Lightbox
function openLightbox(card) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lb-titre').textContent     = card.dataset.titre;
    document.getElementById('lb-freelance').textContent = card.dataset.freelance ? 'Par ' + card.dataset.freelance : '';
    document.getElementById('lb-desc').textContent      = card.dataset.desc || 'Aucune description.';

    const lienEl = document.getElementById('lb-lien');
    const telEl  = document.getElementById('lb-tel');

    if (card.dataset.lien) {
        lienEl.href = card.dataset.lien;
        lienEl.style.display = 'inline-flex';
    } else { lienEl.style.display = 'none'; }

    if (card.dataset.tel) {
        telEl.href = 'tel:' + card.dataset.tel;
        telEl.querySelector('svg').nextSibling && (telEl.lastChild.textContent = ' ' + card.dataset.tel);
        telEl.style.display = 'inline-flex';
    } else { telEl.style.display = 'none'; }

    lb.classList.add('open');
}

function closeLightbox(e) {
    if (!e || e.target === document.getElementById('lightbox') || e.target.closest('.lightbox-close')) {
        document.getElementById('lightbox').classList.remove('open');
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
</body>
</html>