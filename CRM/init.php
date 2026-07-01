<?php
if (strpos($_SERVER['REQUEST_URI'], '.php/') !== false) {
    header("Location: dashboard.php");
    exit;
}
?>