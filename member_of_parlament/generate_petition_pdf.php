<?php
require_once '../includes/dbcon.inc.php';
require('../fpdf186/fpdf.php'); // adjust path if needed

session_start();
$mp_id = $_SESSION['mp_id'] ?? 15;
$petition_id = isset($_GET['petition_id']) ? intval($_GET['petition_id']) : null;

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,'Petition Report',0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

if ($petition_id) {
    // Single petition
    $stmt = $conn->prepare("SELECT * FROM petitions WHERE petition_id=? AND mp_id=?");
    $stmt->bind_param("ii", $petition_id, $mp_id);
    $stmt->execute();
    $petition = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$petition) {
        $pdf->Cell(0,10,'No petition found for ID '.$petition_id,0,1);
        $pdf->Output('I','no_petition.pdf');
        exit;
    }

    // signatures count
    $sigstmt = $conn->prepare("SELECT COUNT(*) as total FROM petition_signatures WHERE petition_id=?");
    $sigstmt->bind_param("i", $petition_id);
    $sigstmt->execute();
    $sig = $sigstmt->get_result()->fetch_assoc()['total'] ?? 0;
    $sigstmt->close();

    $pdf->Cell(0,10,'Title: '.utf8_decode($petition['title']),0,1);
    $pdf->Cell(0,10,'Status: '.$petition['status'],0,1);
    $pdf->Cell(0,10,'Signatures: '.$sig,0,1);
    $pdf->MultiCell(0,10,'Description: '.utf8_decode($petition['description']));
} else {
    // All petitions report
    $pdf->Cell(0,10,'All petitions for MP ID '.$mp_id,0,1);

    $res = $conn->prepare("SELECT p.petition_id,p.title,p.status,COUNT(ps.signature_id) as sigs 
      FROM petitions p 
      LEFT JOIN petition_signatures ps ON ps.petition_id=p.petition_id 
      WHERE p.mp_id=? 
      GROUP BY p.petition_id");
    $res->bind_param("i",$mp_id);
    $res->execute();
    $r = $res->get_result();

    // Table header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(90,10,'Title',1);
    $pdf->Cell(40,10,'Status',1);
    $pdf->Cell(40,10,'Signatures',1);
    $pdf->Ln();
    $pdf->SetFont('Arial','',12);

    while($row=$r->fetch_assoc()){
        $pdf->Cell(90,10,utf8_decode($row['title']),1);
        $pdf->Cell(40,10,$row['status'],1);
        $pdf->Cell(40,10,$row['sigs'],1);
        $pdf->Ln();
    }
    $res->close();
}

$pdf->Output('I', $petition_id ? "petition_$petition_id.pdf" : "all_petitions.pdf");
exit;
