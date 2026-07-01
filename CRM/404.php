<?php
session_start();
http_response_code(404);
$connecte = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OkCRM — Page introuvable</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #111827;
        }

        .container {
            text-align: center;
            max-width: 480px;
        }

        .code {
            font-family: 'DM Serif Display', serif;
            font-size: 96px;
            line-height: 1;
            color: #4f6ef7;
            letter-spacing: -4px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .url-box {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            color: #6b7280;
            font-family: monospace;
            margin-bottom: 32px;
            word-break: break-all;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }

        .btn-blue {
            background: #4f6ef7;
            color: #fff;
        }

        .btn-ghost {
            background: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .logo {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            color: #4f6ef7;
            margin-bottom: 40px;
            display: block;
        }
        .logo span { opacity: .55; }
    </style>
</head>
<body>
    <div class="container">

        <a class="logo" href="/CRM/<?= $connecte ? 'dashboard.php' : 'login.php' ?>">
            Ok<span>CRM</span>
        </a>

        <div class="code">404</div>

        <h1 class="title">Page introuvable</h1>
        <p class="subtitle">
            L'adresse que vous avez saisie n'existe pas ou a été déplacée.<br>
            Vérifiez l'URL et réessayez.
        </p>

        <div class="url-box">
            <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>
        </div>

        <div class="actions">
            <?php if ($connecte): ?>
                <a href="/CRM/dashboard.php" class="btn btn-blue">
                    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Retour au tableau de bord
                </a>
                <a href="javascript:history.back()" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Page précédente
                </a>
            <?php else: ?>
                <a href="/CRM/login.php" class="btn btn-blue">
                    <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Se connecter
                </a>
                <a href="javascript:history.back()" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Page précédente
                </a>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>