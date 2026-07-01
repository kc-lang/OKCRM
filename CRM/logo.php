<?php
/**
 * ── logo.php ── Composant logo OkCRM réutilisable
 * À inclure partout où le logo doit apparaître (sidebar, navbar, pages vitrine…)
 * Le clic ramène toujours vers la page d'accueil publique (site.php)
 *
 * Usage :
 *   <?php include 'logo.php'; ?>
 *
 * Personnalisation optionnelle avant l'include :
 *   $logo_size = 'sm'; // 'sm' | 'md' | 'lg'  (défaut: md)
 *   <?php include 'logo.php'; ?>
 */

$logo_size = $logo_size ?? 'md';
$logo_href = BASE_URL . 'index.php';
?>
<a href="<?= $logo_href ?>" class="okcrm-logo okcrm-logo--<?= htmlspecialchars($logo_size) ?>">
    Ok<span>CRM</span>
</a>