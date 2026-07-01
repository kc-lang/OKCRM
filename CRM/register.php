<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }
require 'Config/db.php';
require 'Config/security.php';
$erreur  = '';
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';
    $mdp2  = $_POST['mdp2'] ?? '';

    if (empty($nom) || empty($email) || empty($mdp) || empty($mdp2)) {

        $erreur = 'Tous les champs sont obligatoires.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = 'Adresse email invalide.';

    } elseif (strlen($mdp) < 8) {

        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';

    } elseif (!preg_match('/[A-Za-z]/', $mdp)) {

        $erreur = 'Le mot de passe doit contenir au moins une lettre.';

    } elseif (!preg_match('/[0-9]/', $mdp)) {

        $erreur = 'Le mot de passe doit contenir au moins un chiffre.';

    } elseif (!preg_match('/[^A-Za-z0-9]/', $mdp)) {

        $erreur = 'Le mot de passe doit contenir au moins un caractère spécial (ex: ! @ # $ % &).';

    } elseif ($mdp !== $mdp2) {

        $erreur = 'Les mots de passe ne correspondent pas.';

    } else {

        $check = $conn->prepare("SELECT Uid FROM user_ WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $erreur = 'Cet email est déjà utilisé.';

        } else {

            $hash = password_hash($mdp, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO user_ (nom, email, Mot_de_passe)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param("sss", $nom, $email, $hash);

            if ($stmt->execute()) {

                header('Location: login.php?inscription=ok');
                exit;

            } else {

                $erreur = 'Erreur lors de l\'inscription : ' . $conn->error;

            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OkCRM — Créer un compte</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/auth.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">

    <!-- Panneau gauche : branding -->
    <div class="auth-side">
        <div class="auth-brand">
             <?php include 'logo.php'; ?>
            <p class="auth-tagline">Gérez vos clients,<br>projets et tâches<br>en toute simplicité.</p>
        </div>
        <div class="auth-deco">
            <div class="deco-card">
                <div class="deco-dot blue"></div>
                <span>3 projets en cours</span>
            </div>
            <div class="deco-card">
                <div class="deco-dot green"></div>
                <span>5 tâches terminées</span>
            </div>
            <div class="deco-card">
                <div class="deco-dot orange"></div>
                <span>2 factures à envoyer</span>
            </div>
        </div>
    </div>

    <!-- Panneau droit : formulaire -->
    <div class="auth-main">
        <div class="auth-card">
            <div class="auth-card-head">
                <h1>Créer un compte</h1>
                <p>Rejoignez OkCRM et organisez votre activité</p>
            </div>

            <?php if ($erreur): ?>
                <div class="auth-alert error">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="auth-form" id="form-register">

                <div class="form-row">
                    <div class="fg">
                        <label for="nom">Nom <span class="req">*</span></label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                               placeholder="Dupont" required autocomplete="family-name">
                    </div>
                </div>

                <div class="fg">
                    <label for="email">Adresse email <span class="req">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="marie@exemple.com" required autocomplete="email">
                </div>

                <div class="fg">
                    <label for="mdp">Mot de passe <span class="req">*</span></label>
                    <div class="input-eye">
                        <input type="password" id="mdp" name="mdp"
                               placeholder="8 caractères min., avec lettre, chiffre et symbole" required autocomplete="new-password">
                        <button type="button" class="eye-btn" onclick="togglePassword('mdp', this)" title="Afficher/Masquer">
                            <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="pwd-strength" id="pwd-strength">
                        <div class="pwd-bar" id="pwd-bar"></div>
                    </div>
                    <span class="pwd-label" id="pwd-label"></span>

                    <!-- Checklist des règles, mise à jour en direct -->
                    <ul class="pwd-rules" id="pwd-rules">
                        <li id="rule-len"><span class="rule-dot"></span>Au moins 8 caractères</li>
                        <li id="rule-letter"><span class="rule-dot"></span>Au moins une lettre</li>
                        <li id="rule-digit"><span class="rule-dot"></span>Au moins un chiffre</li>
                        <li id="rule-special"><span class="rule-dot"></span>Au moins un caractère spécial (! @ # $ % …)</li>
                    </ul>
                </div>

                <div class="fg">
                    <label for="mdp2">Confirmer le mot de passe <span class="req">*</span></label>
                    <div class="input-eye">
                        <input type="password" id="mdp2" name="mdp2"
                               placeholder="Répéter le mot de passe" required autocomplete="new-password">
                        <button type="button" class="eye-btn" onclick="togglePassword('mdp2', this)" title="Afficher/Masquer">
                            <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <span class="match-msg" id="match-msg"></span>
                </div>

                <p class="f-err" id="f-err"></p>

                <button type="submit" class="btn btn-blue btn-full">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Créer mon compte
                </button>

            </form>

            <p class="auth-switch">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>
</div>

<script src="JS/register.js"></script>
</body>
</html>