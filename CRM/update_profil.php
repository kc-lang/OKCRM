<?php
/**
 * ── update_profil.php ──
 * Endpoint AJAX appelé par profil.js
 * Traite : modification infos (nom/email) + changement mot de passe
 * Répond toujours en JSON
 */
session_start();
header('Content-Type: application/json');

require 'Config/security.php';
require 'Config/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez vous reconnecter.']);
    exit;
}

$uid = (int) $_SESSION['user']['Uid'];

// Lecture du corps JSON envoyé par fetch()
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// ══════════════════════════════════════
// 1) MISE À JOUR DES INFOS (nom + email)
// ══════════════════════════════════════
if ($action === 'update_infos') {

    $nom   = trim($data['nom'] ?? '');
    $email = trim($data['email'] ?? '');

    if (empty($nom) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Le nom et l\'email sont obligatoires.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    // Vérifier que l'email n'est pas déjà pris par un AUTRE compte
    $check = $conn->prepare("SELECT Uid FROM user_ WHERE email = ? AND Uid != ?");
    $check->bind_param("si", $email, $uid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre compte.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE user_ SET nom = ?, email = ? WHERE Uid = ?");
    $stmt->bind_param("ssi", $nom, $email, $uid);

    if ($stmt->execute()) {
        // Met à jour la session pour refléter le changement immédiatement
        $_SESSION['user']['nom']   = $nom;
        $_SESSION['user']['email'] = $email;

        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $conn->error]);
    }
    exit;
}

// ══════════════════════════════════════
// 2) CHANGEMENT DE MOT DE PASSE
// ══════════════════════════════════════
if ($action === 'update_password') {

    $oldPwd     = $data['old_pwd'] ?? '';
    $newPwd     = $data['new_pwd'] ?? '';
    $confirmPwd = $data['confirm_pwd'] ?? '';

    if (empty($oldPwd) || empty($newPwd) || empty($confirmPwd)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
        exit;
    }

    // Récupérer le hash actuel
    $stmt = $conn->prepare("SELECT Mot_de_passe FROM user_ WHERE Uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($oldPwd, $row['Mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe actuel incorrect.']);
        exit;
    }

    if (strlen($newPwd) < 8) {
        echo json_encode(['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.']);
        exit;
    }
    if (!preg_match('/[A-Za-z]/', $newPwd)) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins une lettre.']);
        exit;
    }
    if (!preg_match('/[0-9]/', $newPwd)) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins un chiffre.']);
        exit;
    }
    if (!preg_match('/[^A-Za-z0-9]/', $newPwd)) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins un caractère spécial.']);
        exit;
    }
    if ($newPwd !== $confirmPwd) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user_ SET Mot_de_passe = ? WHERE Uid = ?");
    $stmt->bind_param("si", $newHash, $uid);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $conn->error]);
    }
    exit;
}

// Action inconnue
echo json_encode(['success' => false, 'message' => 'Action invalide.']);