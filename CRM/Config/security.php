<?php
// ── Config/security.php ──
// À inclure en PREMIÈRE ligne de chaque page PHP (après session_start)
// require 'Config/security.php';

// ── 1. Bloquer le path info ──
// Si quelqu'un tape login.php/nimportequoi → rediriger vers la page seule
if (!empty($_SERVER['PATH_INFO']) || !empty($GLOBALS['path_info_detected'])) {
    $script = $_SERVER['SCRIPT_NAME']; // ex: /CRM/login.php
    $query  = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $script . $query);
    exit;
}

// Détection alternative via REQUEST_URI
$request_uri  = $_SERVER['REQUEST_URI']  ?? '';
$script_name  = $_SERVER['SCRIPT_NAME']  ?? '';

// Si l'URI contient quelque chose APRÈS le nom du fichier .php
// ex: /CRM/login.php/logon  →  script=/CRM/login.php, uri=/CRM/login.php/logon
if (
    !empty($request_uri) &&
    !empty($script_name) &&
    strpos($request_uri, $script_name . '/') === 0
) {
    $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $script_name . $query);
    exit;
}

// ── 2. Définir la base URL (utilisée pour les assets) ──
if (!defined('BASE_URL')) {
    define('BASE_URL', '/CRM/');
}