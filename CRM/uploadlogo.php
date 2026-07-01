<?php
/**
 * ── upload_logo.php ──
 * Endpoint appelé depuis profil.php pour uploader le logo personnel
 * du freelance, utilisé ensuite sur les factures PDF.
 */
session_start();
header('Content-Type: application/json');

require 'Config/security.php';
require 'Config/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée.']);
    exit;
}

$uid = (int) $_SESSION['user']['Uid'];

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier valide reçu.']);
    exit;
}

$fichier   = $_FILES['logo'];
$extOK     = ['jpg', 'jpeg', 'png'];
$extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $extOK)) {
    echo json_encode(['success' => false, 'message' => 'Format non supporté. Utilisez JPG ou PNG.']);
    exit;
}

if ($fichier['size'] > 2 * 1024 * 1024) { // 2 Mo max
    echo json_encode(['success' => false, 'message' => 'Le fichier dépasse 2 Mo.']);
    exit;
}

// Dossier de stockage (à créer si absent) : /uploads/logos/
$dossier = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logos/';
if (!is_dir($dossier)) {
    mkdir($dossier, 0755, true);
}

$nomFichier = 'logo_' . $uid . '_' . time() . '.' . $extension;
$cheminAbsolu = $dossier . $nomFichier;
$cheminRelatif = '/uploads/logos/' . $nomFichier; // stocké en BD, utilisé dans facture_pdf.php

if (!move_uploaded_file($fichier['tmp_name'], $cheminAbsolu)) {
    echo json_encode(['success' => false, 'message' => 'Échec de l\'enregistrement du fichier.']);
    exit;
}

// Supprime l'ancien logo s'il existe, pour ne pas accumuler les fichiers
$stmtOld = $conn->prepare("SELECT logo_path FROM user_ WHERE Uid = ?");
$stmtOld->bind_param("i", $uid);
$stmtOld->execute();
$old = $stmtOld->get_result()->fetch_assoc();
if (!empty($old['logo_path'])) {
    $oldFile = $_SERVER['DOCUMENT_ROOT'] . $old['logo_path'];
    if (file_exists($oldFile)) { @unlink($oldFile); }
}

// Mise à jour en BD
$stmt = $conn->prepare("UPDATE user_ SET logo_path = ? WHERE Uid = ?");
$stmt->bind_param("si", $cheminRelatif, $uid);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Logo mis à jour avec succès.', 'logo_url' => $cheminRelatif]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour en base.']);
}