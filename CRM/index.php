<?php
session_start();
require 'Config/security.php';
$est_connecte = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OkCRM — Gérez vos clients comme un pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>CSS/styles.css">
<link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="site.php">Ok<span>CRM</span></a>
  <div class="nav-links">
    <a href="#features">Fonctionnalités</a>
    <a href="#how">Comment ça marche</a>
    <?php if ($est_connecte): ?>
      <a href="dashboard.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="15" style="margin-right:6px;vertical-align:-2px">
          <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Retour au dashboard
      </a>
    <?php else: ?>
      <a href="register.php" class="btn btn-primary">Signup</a>
      <a href="login.php" class="btn-nav">Se connecter</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>

  <div class="hero-content">
    <div class="hero-badge">Conçu pour les freelances</div>
    <div class="hero-badge1">contactez un freelance</div>

    <h1>Gérez vos clients,<br>projets &amp; <em>factures</em><br>en un seul endroit.</h1>

    <p class="hero-sub">
      OkCRM centralise toute votre activité freelance —
      clients, missions, tâches et paiements — dans un tableau de bord simple et efficace.
    </p>

    <div class="hero-cta">
      <?php if ($est_connecte): ?>
        <a href="dashboard.php" class="btn-primary">
          Retour au dashboard
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      <?php else: ?>
        <a href="login.php" class="btn-primary">
          Accéder à mon espace
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      <?php endif; ?>
      <a href="public_portfolio.php" class="btn-nav">contactez un freelance</a>
      <a href="#features" class="btn-secondary">
        Voir les fonctionnalités
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="14"><polyline points="6 9 12 15 18 9"/></svg>
      </a>
    </div>
  </div>

  <!-- Dashboard mockup -->
  <div class="hero-visual">
    <div class="dashboard-mock">
      <div class="mock-topbar">
        <div class="mock-dot" style="background:#ff5f57"></div>
        <div class="mock-dot" style="background:#febc2e"></div>
        <div class="mock-dot" style="background:#28c840"></div>
        <span class="mock-title">OkCRM — Dashboard</span>
      </div>
      <div class="mock-body">
        <div class="mock-sidebar">
          <div class="mock-icon active">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          </div>
          <div class="mock-icon dim">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div class="mock-icon dim">
            <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg>
          </div>
          <div class="mock-icon dim">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div class="mock-icon dim">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
        </div>
        <div class="mock-main">
          <div class="mock-stats">
            <div class="mock-stat">
              <div class="mock-stat-label">Clients</div>
              <div class="mock-stat-val">12</div>
              <div class="mock-stat-sub">+2 ce mois</div>
            </div>
            <div class="mock-stat">
              <div class="mock-stat-label">Projets actifs</div>
              <div class="mock-stat-val">5</div>
              <div class="mock-stat-sub" style="color:var(--warn)">3 en cours</div>
            </div>
            <div class="mock-stat">
              <div class="mock-stat-label">Revenus</div>
              <div class="mock-stat-val">485k</div>
              <div class="mock-stat-sub">FCFA ce mois</div>
            </div>
          </div>
          <div class="mock-table">
            <div class="mock-table-head">
              <span>Client</span><span>Projet</span><span>Statut</span>
            </div>
            <div class="mock-row">
              <div class="mock-cell name">AfriData SARL</div>
              <div class="mock-cell">Site vitrine</div>
              <div class="mock-cell"><span class="pill green">Terminé</span></div>
            </div>
            <div class="mock-row">
              <div class="mock-cell name">TechCam</div>
              <div class="mock-cell">App mobile</div>
              <div class="mock-cell"><span class="pill amber">En cours</span></div>
            </div>
            <div class="mock-row">
              <div class="mock-cell name">Nail by Mélissa</div>
              <div class="mock-cell">Flyer + logo</div>
              <div class="mock-cell"><span class="pill blue">À faire</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS BAND -->
<div class="stats-band">
  <div class="stat-item reveal">
    <div class="stat-num">6<span>+</span></div>
    <div class="stat-desc">Modules intégrés</div>
  </div>
  <div class="stat-item reveal">
    <div class="stat-num">100<span>%</span></div>
    <div class="stat-desc">Données privées & locales</div>
  </div>
  <div class="stat-item reveal">
    <div class="stat-num">0<span>€</span></div>
    <div class="stat-desc">Coût d'abonnement</div>
  </div>
  <div class="stat-item reveal">
    <div class="stat-num">1<span> clic</span></div>
    <div class="stat-desc">Pour accéder à tout</div>
  </div>
</div>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-label">Fonctionnalités</div>
  <h2 class="section-title">Tout ce dont un freelance a besoin.</h2>

  <div class="features-grid">
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#eff4ff">
        <svg viewBox="0 0 24 24" stroke="#2563eb"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feat-title">Gestion clients</div>
      <div class="feat-desc">Centralisez les informations de vos clients — contact, historique des projets, et toutes vos interactions en un seul profil.</div>
    </div>
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#f0fdf4">
        <svg viewBox="0 0 24 24" stroke="#059669"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/></svg>
      </div>
      <div class="feat-title">Suivi de projets</div>
      <div class="feat-desc">Suivez chaque mission — budget, statut d'avancement, client associé et tâches à réaliser, tout en un coup d'œil.</div>
    </div>
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#fffbeb">
        <svg viewBox="0 0 24 24" stroke="#d97706"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
      </div>
      <div class="feat-title">Factures & paiements</div>
      <div class="feat-desc">Créez vos factures, suivez les paiements reçus ou en attente, et gardez une vue claire sur vos revenus mensuels.</div>
    </div>
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#fdf4ff">
        <svg viewBox="0 0 24 24" stroke="#9333ea"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>
      <div class="feat-title">Gestion des tâches</div>
      <div class="feat-desc">Organisez votre travail avec des listes de tâches par projet — à faire, en cours ou terminé — pour ne rien oublier.</div>
    </div>
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#fff1f2">
        <svg viewBox="0 0 24 24" stroke="#e11d48"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="feat-title">Agenda & événements</div>
      <div class="feat-desc">Planifiez vos rendez-vous clients, deadlines de projets et rappels importants dans un calendrier intégré.</div>
    </div>
    <div class="feature-card reveal">
      <div class="feat-icon" style="background:#f0f9ff">
        <svg viewBox="0 0 24 24" stroke="#0284c7"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="feat-title">Tableau de bord</div>
      <div class="feat-desc">Un résumé visuel de toute votre activité — clients, revenus, projets en cours — dès que vous vous connectez.</div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how" id="how">
  <div class="how-inner">
    <div class="section-label">Comment ça marche</div>
    <h2 class="section-title">Opérationnel en quelques clics.</h2>
    <div class="steps">
      <div class="step reveal">
        <div class="step-num">1</div>
        <div class="step-title">Créez votre compte</div>
        <div class="step-desc">Inscrivez-vous avec votre nom et email. Votre espace freelance est prêt instantanément.</div>
      </div>
      <div class="step reveal">
        <div class="step-num">2</div>
        <div class="step-title">Ajoutez vos clients</div>
        <div class="step-desc">Renseignez les informations de chaque client — nom, email, téléphone.</div>
      </div>
      <div class="step reveal">
        <div class="step-num">3</div>
        <div class="step-title">Gérez vos projets</div>
        <div class="step-desc">Créez des missions, assignez-les à vos clients, suivez leur avancement et vos tâches.</div>
      </div>
      <div class="step reveal">
        <div class="step-num">4</div>
        <div class="step-title">Suivez vos revenus</div>
        <div class="step-desc">Générez des factures, enregistrez les paiements et visualisez vos finances en temps réel.</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <?php if ($est_connecte): ?>
    <div class="section-label">Déjà connecté</div>
    <h2>Vous avez un compte actif,<br>continuez votre travail.</h2>
    <p>Reprenez exactement là où vous vous étiez arrêté.</p>
    <a href="dashboard.php" class="btn-primary" style="font-size:16px; padding:16px 36px; display:inline-flex">
      Retour au dashboard
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" style="margin-left:8px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  <?php else: ?>
    <div class="section-label">Prêt à commencer ?</div>
    <h2>Votre activité freelance,<br>enfin bien organisée.</h2>
    <p>Connectez-vous et prenez le contrôle de votre business dès aujourd'hui.</p>
    <a href="login.php" class="btn-primary" style="font-size:16px; padding:16px 36px; display:inline-flex">
      Accéder à OkCRM
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" style="margin-left:8px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  <?php endif; ?>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-logo">Ok<span>CRM</span></div>
  <p>© 2025 OkCRM — Tous droits réservés</p>
  <p style="font-size:12px; color:var(--ink-3)">Construit avec PHP & MySQL</p>
</footer>

<script>
const observer = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
    }
  });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>