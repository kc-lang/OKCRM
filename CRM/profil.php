<?php
session_start();
require 'Config/security.php';
require 'Config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user']['Uid'];
$stmt = $conn->prepare("SELECT nom, email, created_at FROM user_ WHERE Uid=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$initiale = strtoupper(substr($user['nom'], 0, 1));

// Quelques stats légères pour donner du contexte en arrière-plan (optionnel)
$nb_clients = $conn->query("SELECT COUNT(*) c FROM client WHERE Uid=$uid")->fetch_assoc()['c'] ?? 0;
$nb_projets = $conn->query("SELECT COUNT(*) c FROM projet WHERE Uid=$uid")->fetch_assoc()['c'] ?? 0;
    $logoUser = $conn->prepare("SELECT logo_path FROM user_ WHERE Uid=?");
    $logoUser->bind_param("i", $uid);
    $logoUser->execute();
    $emetteur_logo = $logoUser->get_result()->fetch_assoc()['logo_path'] ?? null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OkCRM – Profil</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/profil.css">
</head>
<body class="profil-page">

<!-- ── Fond flouté qui rappelle le dashboard ── -->
<div class="bg-ghost">
    <div class="bg-ghost-sidebar"></div>
    <div class="bg-ghost-content">
        <div class="bg-ghost-card c1"></div>
        <div class="bg-ghost-card c2"></div>
        <div class="bg-ghost-card c3"></div>
        <div class="bg-ghost-card c4"></div>
        <div class="bg-ghost-table"></div>
    </div>
</div>

<!-- ── Modal profil centré ── -->
<div class="profil-modal-overlay">
    <div class="profil-modal">

        <button class="modal-close-x" onclick="location.href='dashboard.php'" title="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <div class="profil-modal-head">
            <div class="avatar-circle"><?= htmlspecialchars($initiale) ?></div>
            <div>
                <h2><?= htmlspecialchars($user['nom']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <span class="since-badge">Membre depuis <?= date('M Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <div class="profil-mini-stats">
            <div class="mini-stat"><strong><?= $nb_clients ?></strong><span>Clients</span></div>
            <div class="mini-stat"><strong><?= $nb_projets ?></strong><span>Projets</span></div>
        </div>

        <div class="profil-tabs">
            <button class="tab-btn active" data-tab="infos">Informations</button>
            <button class="tab-btn" data-tab="password">Mot de passe</button>
        </div>

        <!-- Onglet infos -->
        <div class="tab-panel active" id="tab-infos">
            <div id="infos-flash" class="flash"></div>
            <form id="form-infos" autocomplete="off">
                <div class="form-group">
                    <label for="p-nom">Nom complet</label>
                    <input type="text" id="p-nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="p-email">Adresse e-mail</label>
                    <input type="email" id="p-email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                    <div class="form-group">
        <label>Logo personnel (utilisé sur vos factures PDF)</label>
        <div class="logo-upload-zone">
            <div class="logo-preview" id="logo-preview">
                <?php if (!empty($emetteur_logo)): ?>
                    <img src="<?= htmlspecialchars($emetteur_logo) ?>" alt="Logo">
                <?php else: ?>
                    <span>Aucun logo</span>
                <?php endif; ?>
            </div>
            <label for="logo-input" class="btn-upload-logo">
                Choisir une image
            </label>
            <input type="file" id="logo-input" accept=".jpg,.jpeg,.png" style="display:none">
            <p class="logo-hint">JPG ou PNG, 2 Mo max</p>
        </div>
    </div>

                <button type="submit" class="btn-profil">Enregistrer les modifications</button>
            </form>
        </div>

        <!-- Onglet mot de passe -->
        <div class="tab-panel" id="tab-password">
            <div id="pwd-flash" class="flash"></div>
            <form id="form-password" autocomplete="off">
                <div class="form-group">
                    <label for="p-oldpwd">Mot de passe actuel</label>
                    <input type="password" id="p-oldpwd" autocomplete="current-password" required>
                </div>
                <div class="form-group">
                    <label for="p-newpwd">Nouveau mot de passe</label>
                    <input type="password" id="p-newpwd" autocomplete="new-password" required>
                    <div class="pwd-strength"><div class="pwd-strength-bar" id="pwd-bar"></div></div>
                    <div class="pwd-hint" id="pwd-hint"></div>
                </div>
                <div class="form-group">
                    <label for="p-confirmpwd">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="p-confirmpwd" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn-profil">Mettre à jour le mot de passe</button>
            </form>
        </div>

        <a href="dashboard.php" class="btn-back-bottom">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Retour au dashboard
        </a>

    </div>
</div>

<script src="<?= BASE_URL ?>JS/profil.js"></script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>
</body>
</html>