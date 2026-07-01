<?php
// sidebar.php — inclure dans chaque page avec :
// $page_active = 'clients'; include 'sidebar.php';
$pages = [
  'dashboard' => ['href'=>'dashboard.php','label'=>'Dashboard',  'icon'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
  'clients'   => ['href'=>'clients.php',  'label'=>'Clients',    'icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
  'projets'   => ['href'=>'projets.php',  'label'=>'Projets',    'icon'=>'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/>'],
  'taches'    => ['href'=>'taches.php',   'label'=>'Tâches',     'icon'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
  'factures'  => ['href'=>'factures.php', 'label'=>'Factures',   'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
  'agenda'    => ['href'=>'agenda.php',   'label'=>'Agenda',     'icon'=>'<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
 
   'portfolio'   =>['href'=>'portfolio.php', 'label'=>'portfolio', 'icon' => '<path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/>'],
    'site'      => ['href'=>'index.php',   'label'=>'site',     'icon'=>'<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
  'profil'    => ['href'=>'profil.php', 'label'=>'Profil', 'icon'=>'<rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="9" cy="10" r="3"/><path d="M15 8h4M15 12h4M9 16H5a1 1 0 0 1 0-2h4"/>'],
];
$finance = ['factures'];
$vitrine= ['site', 'portfolio'];
$planning = ['agenda'];
$nom = $_SESSION['user']['nom'] ?? 'Freelance';
$ini = strtoupper(substr($nom,0,1));
?>
<aside class="sidebar">
  <div class="sb-head">
    <?php include 'logo.php'; ?>
    <p class="sb-sub">Mon espace freelance</p>
  </div>
  <nav class="sb-nav">
    <span class="nav-lbl">Principal</span>
    <?php foreach($pages as $key => $p): ?>
      <?php if($key==='factures'): ?><span class="nav-lbl">Finance</span><?php endif ?>
      <?php if($key==='agenda'):   ?><span class="nav-lbl">Planning</span><?php endif ?>
        
           <?php if($key==='portfolio'):   ?><span class="nav-lbl">vitrine</span><?php endif ?>
      <a href="<?= $p['href'] ?>" class="nav-a <?= ($page_active??'')===$key?'active':'' ?>">
        <svg viewBox="0 0 24 24"><?= $p['icon'] ?></svg>
        <?= $p['label'] ?>
      </a>
    <?php endforeach ?>
  </nav>
  <div class="sb-foot">
    <div class="user-card">
      <div class="u-av"></a><?= $ini ?></div>
      <div>
        <p class="u-name"><?= htmlspecialchars($nom) ?></p>
        <p class="u-role">Freelance</p>
      </div>
      <a href="logout.php" class="logout" title="Déconnexion">
        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </div>
</aside>