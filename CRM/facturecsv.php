<?php
session_start();
require 'Config/db.php';
require 'Config/security.php';

$id = (int)$_GET['id'];

$sql = "
SELECT f.*, c.nom AS client_nom, p.titre AS projet_titre
FROM facture f
LEFT JOIN client c ON c.Cid=f.Cid
LEFT JOIN projet p ON p.Pid=f.Pid
WHERE f.Fid=$id
";

$facture = $conn->query($sql)->fetch_assoc();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="facture_'.$id.'.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Facture', 'Client', 'Projet', 'Montant', 'Date', 'Statut']);

fputcsv($output, [
    'FAC-'.str_pad($facture['Fid'],4,'0',STR_PAD_LEFT),
    $facture['client_nom'],
    $facture['projet_titre'],
    $facture['montant'],
    $facture['date'],
    $facture['statut_paiement']
]);

fclose($output);
exit;