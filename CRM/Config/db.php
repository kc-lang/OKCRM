<?php
// db.php — Connexion base de données
$host = 'localhost';
$db   = 'okcrm';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connexion BD échouée : ' . $conn->connect_error]));
}
$conn->set_charset('utf8mb4');