<?php
session_start();
require 'Config/security.php';
require 'Config/db.php';

$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??'');
  $pass=$_POST['password']??'';
  $r=$conn->prepare("SELECT * FROM user_ WHERE email=?");
  $r->bind_param('s',$email);
  $r->execute();
  $user=$r->get_result()->fetch_assoc();
  if($user && password_verify($pass,$user['Mot_de_passe'])){
    $_SESSION['user']=$user;
    header('Location:dashboard.php'); exit;
  }
  $err='Email ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OkCRM — Connexion</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/responsive.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/login.css">
  
  
</head>
<body>
<div class="login-box">
 <?php include 'logo.php'; ?>
  <p class="login-sub">Connectez-vous à votre espace</p>
  <?php if($err): ?>
  <p class="login-err"><?= htmlspecialchars($err) ?></p>
  <?php endif ?>
  <form  autocomplete="off" method="POST" >
    <div class="fg"><label>Email</label><input type="email" name="email" placeholder="@okcrm.com" required autofocus></div>
    <div class="fg"><label>Mot de passe</label><input type="password" name="password" placeholder="••••••••" required></div>
    <button type="submit" class="btn btn-blue">Se connecter</button>
    <p class="signup-link">
    Vous n'avez pas de compte ?
    <a href="register.php">Sign Up</a>
</p>
  </form>
 
</div>
</body>
</html>