<?php
// ── portfolio_dashboard.php ── OkCRM
// Page dashboard : le freelance gère son portfolio
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require 'Config/security.php';
$page_active = 'portfolio';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OkCRM — Mon Portfolio</title>
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
      <p id="date-label"></p>
    </div>
    <div class="tb-right">
      <a href="portfolio.php" target="_blank" class="btn btn-ghost">
        <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Voir ma page publique
      </a>
    </div>
  </header>

  <div class="page">

    <!-- Flash message -->
    <div id="flash" class="flash" style="display:none"></div>

    <div class="pf-grid">

      <!-- ── Aperçu carte ── -->
      <div class="preview-col">
        <div class="section-label">Aperçu de votre carte</div>
        <div class="preview-card" id="preview-card">
          <div class="pc-photo-wrap">
            <img id="prev-photo" src="assets/avatar_default.png" alt="Photo">
          </div>
          <div class="pc-body">
            <div class="pc-name"  id="prev-nom">Votre nom</div>
            <div class="pc-spec"  id="prev-spec">Spécialité</div>
            <div class="pc-bio"   id="prev-bio">Votre description apparaîtra ici.</div>
            <div class="pc-real-wrap" id="prev-real-wrap" style="display:none">
              <img id="prev-real" src="" alt="Réalisation">
            </div>
            <a  id="prev-wa" class="btn-wa" href="#" target="_blank">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.557 4.126 1.529 5.855L0 24l6.335-1.505A11.945 11.945 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.8 9.8 0 0 1-5.003-1.368l-.36-.214-3.76.893.952-3.653-.233-.374A9.786 9.786 0 0 1 2.182 12C2.182 6.57 6.57 2.182 12 2.182c5.43 0 9.818 4.388 9.818 9.818 0 5.43-4.388 9.818-9.818 9.818z"/></svg>
              Contacter sur WhatsApp
            </a>
          </div>
        </div>
        <div class="visibility-toggle">
          <label class="toggle-label">
            <input type="checkbox" id="prev-visible" checked>
            <span class="toggle-track"></span>
            <span class="toggle-text">Visible dans l'annuaire</span>
          </label>
        </div>
      </div>

      <!-- ── Formulaire ── -->
      <div class="form-col">
        <div class="section-label">Mes informations</div>
        <form id="form-portfolio" enctype="multipart/form-data">

          <div class="form-row-2">
            <div class="fg">
              <label>Nom complet <span class="req">*</span></label>
              <input type="text" name="nom" id="f-nom" placeholder="Alice Dupont" required>
            </div>
            <div class="fg">
              <label>Spécialité / Service <span class="req">*</span></label>
              <input type="text" name="specialite" id="f-spec" placeholder="Développeuse Web, Designer…" required>
            </div>
          </div>

          <div class="fg">
            <label>Description / Bio</label>
            <textarea name="bio" id="f-bio" rows="3" placeholder="Parlez de vous, de vos services, de votre expérience…"></textarea>
          </div>

          <div class="fg">
            <label>Numéro WhatsApp</label>
            <div class="input-prefix">
              <span>+</span>
              <input type="text" name="whatsapp" id="f-whatsapp" placeholder="237612345678" inputmode="numeric">
            </div>
            <small>Avec indicatif pays, sans espaces ni tirets. Ex : 237612345678</small>
          </div>

          <div class="form-row-2">
            <div class="fg">
              <label>Photo de profil</label>
              <div class="upload-zone" id="zone-photo" onclick="document.getElementById('f-photo').click()">
                <input type="file" name="photo" id="f-photo" accept="image/jpeg,image/png,image/webp" style="display:none">
                <div class="uz-icon">🖼️</div>
                <div class="uz-text">Cliquer pour choisir<br><small>jpg / png / webp — max 2 Mo</small></div>
                <img id="uz-photo-prev" src="" alt="" style="display:none">
              </div>
            </div>
            <div class="fg">
              <label>Image de réalisation</label>
              <div class="upload-zone" id="zone-real" onclick="document.getElementById('f-real').click()">
                <input type="file" name="realisation" id="f-real" accept="image/jpeg,image/png,image/webp" style="display:none">
                <div class="uz-icon">📸</div>
                <div class="uz-text">Cliquer pour choisir<br><small>jpg / png / webp — max 4 Mo</small></div>
                <img id="uz-real-prev" src="" alt="" style="display:none">
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-blue" id="btn-save">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Enregistrer
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</main>

<script src="<?= BASE_URL ?>JS/portfolio_dashboard.js"></script>
</body>
</html>