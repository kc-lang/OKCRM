<?php
session_start();
require 'Config/db.php';
require 'C:\wamp64\www\fpdf19\fpdf.php';
require 'Config/security.php';

if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$id  = (int) $_GET['id'];
$uid = (int) $_SESSION['user']['Uid'];

// ── Récupération facture + infos émetteur ──
$sql = "
    SELECT f.*, c.nom AS client_nom, c.email AS client_email, c.tel AS client_tel,
           p.titre AS projet_titre
    FROM facture f
    LEFT JOIN client c ON c.Cid = f.Cid
    LEFT JOIN projet p ON p.Pid = f.Pid
    WHERE f.Fid = $id
";
$facture = $conn->query($sql)->fetch_assoc();

if (!$facture) { die('Facture introuvable.'); }

// Infos du freelance émetteur (avec logo perso éventuel)
$stmtU = $conn->prepare("SELECT nom, email, logo_path FROM user_ WHERE Uid = ?");
$stmtU->bind_param("i", $uid);
$stmtU->execute();
$emetteur = $stmtU->get_result()->fetch_assoc();

$numero       = 'FAC-' . str_pad($facture['Fid'], 4, '0', STR_PAD_LEFT);
$montant      = number_format($facture['montant'], 0, '.', ' ') . ' FCFA';
$dateFacture  = date('d/m/Y', strtotime($facture['date']));
$statutLabels = ['en_attente' => 'EN ATTENTE', 'payee' => 'PAYEE', 'annulee' => 'ANNULEE'];
$statutLabel  = $statutLabels[$facture['statut_paiement']] ?? strtoupper($facture['statut_paiement']);

// Couleurs du statut (RGB)
$statutColor = match ($facture['statut_paiement']) {
    'payee'   => [5, 150, 105],   // vert
    'annulee' => [220, 38, 38],   // rouge
    default   => [217, 119, 6],   // orange (en attente)
};

// Chemin logo : perso si présent et fichier existant, sinon fallback texte "OkCRM"
$logoPath = null;
if (!empty($emetteur['logo_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $emetteur['logo_path'])) {
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . $emetteur['logo_path'];
}

// ══════════════════════════════════════
// Classe PDF personnalisée avec en-tête / pied de page automatiques
// ══════════════════════════════════════
class FacturePDF extends FPDF
{
    public $logoPath;
    public $emetteurNom;
    public $emetteurEmail;
    public $numeroFacture;

    function Header()
    {
        // Logo (perso ou texte par défaut)
        if ($this->logoPath) {
            // Image proportionnelle, hauteur max 18mm
            $this->Image($this->logoPath, 15, 12, 0, 18);
        } else {
            $this->SetFont('Arial', 'B', 20);
            $this->SetTextColor(37, 99, 235);
            $this->SetXY(15, 14);
            $this->Cell(40, 10, 'Ok', 0, 0);
            $this->SetTextColor(20, 20, 32);
            $this->Cell(30, 10, 'CRM', 0, 0);
        }

        // Bloc "FACTURE" + numéro à droite
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(20, 20, 32);
        $this->SetXY(120, 12);
        $this->Cell(75, 10, 'FACTURE', 0, 1, 'R');

        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(100, 100, 120);
        $this->SetXY(120, 22);
        $this->Cell(75, 6, $this->numeroFacture, 0, 1, 'R');

        // Ligne de séparation sous l'en-tête
        $this->SetDrawColor(228, 228, 240);
        $this->SetLineWidth(0.4);
        $this->Line(15, 34, 195, 34);

        $this->SetY(40);
    }

    function Footer()
    {
        $this->SetY(-22);
        $this->SetDrawColor(228, 228, 240);
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        $this->SetY(-18);
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(140, 140, 160);
        $this->Cell(0, 5, $this->emetteurNom . ($this->emetteurEmail ? ' — ' . $this->emetteurEmail : ''), 0, 1, 'C');
        $this->Cell(0, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// ══════════════════════════════════════
// Construction du PDF
// ══════════════════════════════════════
$pdf = new FacturePDF();
$pdf->logoPath      = $logoPath;
$pdf->emetteurNom    = $emetteur['nom'] ?? 'OkCRM';
$pdf->emetteurEmail  = $emetteur['email'] ?? '';
$pdf->numeroFacture  = $numero;
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 28); // marge basse pour laisser la place au footer

// ── Bloc infos émetteur / client (deux colonnes) ──
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(140, 140, 160);
$pdf->SetXY(15, 42);
$pdf->Cell(85, 5, 'EMIS PAR', 0, 0);
$pdf->SetXY(110, 42);
$pdf->Cell(85, 5, 'FACTURE A', 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(20, 20, 32);
$pdf->SetXY(15, 48);
$pdf->Cell(85, 6, $emetteur['nom'] ?? '—', 0, 0);
$pdf->SetXY(110, 48);
$pdf->Cell(85, 6, $facture['client_nom'] ?? '—', 0, 1);

$pdf->SetFont('Arial', '', 9.5);
$pdf->SetTextColor(90, 90, 110);
$pdf->SetXY(15, 54);
$pdf->Cell(85, 5, $emetteur['email'] ?? '', 0, 0);
$pdf->SetXY(110, 54);
$pdf->Cell(85, 5, $facture['client_email'] ?? '', 0, 1);

$pdf->SetXY(110, 59);
$pdf->Cell(85, 5, $facture['client_tel'] ?? '', 0, 1);

// ── Bandeau date + statut ──
$pdf->SetXY(15, 70);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(140, 140, 160);
$pdf->Cell(40, 5, 'DATE D \'EMISSION', 0, 0);
$pdf->Cell(40, 5, 'STATUT', 0, 1);

$pdf->SetXY(15, 75);
$pdf->SetFont('Arial', 'B', 10.5);
$pdf->SetTextColor(20, 20, 32);
$pdf->Cell(40, 6, $dateFacture, 0, 0);

list($r, $g, $b) = $statutColor;
$pdf->SetFillColor($r, $g, $b);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('arial', 'B', 8.5);
$pdf->Cell(32, 7, $statutLabel, 0, 0, 'C', true);

$pdf->Ln(18);

// ── Tableau prestation (gère les titres longs avec MultiCell) ──
$startY = $pdf->GetY();
$pdf->SetFillColor(20, 20, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->SetXY(15, $startY);
$pdf->Cell(120, 9, '  DESIGNATION', 0, 0, 'L', true);
$pdf->Cell(60, 9, 'MONTANT', 0, 1, 'R', true);

// Ligne du projet — titre long pris en charge par MultiCell
$pdf->SetTextColor(20, 20, 32);
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(250, 250, 252);

$titreProjet = $facture['projet_titre'] ?: 'Prestation de service';

// Hauteur de ligne dynamique selon le nombre de lignes que prendra le titre
$pdf->SetXY(15, $pdf->GetY());
$xStart = 15;
$yStart = $pdf->GetY();

// On calcule combien de lignes le titre va occuper (largeur de cellule = 120mm)
$nbLignes = max(1, ceil($pdf->GetStringWidth($titreProjet) / (120 - 4)));
$ligneHauteur = 7;
$hauteurCell = $nbLignes * $ligneHauteur;

$pdf->Rect(15, $yStart, 180, $hauteurCell, 'F');
$pdf->SetXY(17, $yStart + 1);
$pdf->MultiCell(116, $ligneHauteur, $titreProjet, 0, 'L');

$pdf->SetXY(135, $yStart + ($hauteurCell / 2) - 3.5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 7, $montant, 0, 0, 'R');

$pdf->SetY($yStart + $hauteurCell + 2);

// Ligne de séparation
$pdf->SetDrawColor(228, 228, 240);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// ── Total ──
$pdf->SetX(110);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 120);
$pdf->Cell(45, 8, 'Sous-total', 0, 0, 'L');
$pdf->SetTextColor(20, 20, 32);
$pdf->Cell(40, 8, $montant, 0, 1, 'R');

$pdf->SetX(110);
$pdf->SetFillColor(239, 244, 255);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(37, 99, 235);
$pdf->Cell(45, 11, '  TOTAL', 0, 0, 'L', true);
$pdf->Cell(40, 11, $montant, 0, 1, 'R', true);

// ── Note de bas ──
$pdf->Ln(14);
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->SetTextColor(140, 140, 160);
$pdf->MultiCell(180, 5, "Merci pour votre confiance.", 0, 'L');

$pdf->Output('D', $numero . '.pdf');
exit;