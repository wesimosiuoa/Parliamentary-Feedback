<?php
ob_start();
require_once '../includes/dbcon.inc.php';
require('../fpdf186/fpdf.php');
require '../includes/fn.inc.php';

if (!isset($_GET['order_paper'])) die("Order paper ID missing");
$orderPaperId = intval($_GET['order_paper']);

// Fetch order paper
$orderPaper = $conn->query("SELECT * FROM order_papers WHERE order_paper_id=$orderPaperId")->fetch_assoc();
if (!$orderPaper) die("Order paper not found");

// Fetch agenda items (exclude PRAYER and TABLING if they exist in DB)
$agendaItems = $conn->query("
    SELECT * 
    FROM agenda_items 
    WHERE order_paper_id=$orderPaperId 
    AND title NOT LIKE '%PRAYER%' 
    AND (title NOT LIKE '%REPORT%' AND title NOT LIKE '%TABLING%')
    ORDER BY item_number ASC
");

// Prepare attachments query
$attachmentsStmt = $conn->prepare("SELECT file_name, file_path FROM attachments WHERE agenda_item_id=?");

class OrderPaperPDF extends FPDF {
    function Header() {
        $this->SetMargins(25, 25, 25);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'LESOTHO NATIONAL ASSEMBLY', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'ORDER PAPER', 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.5);
        $this->Line(25, $this->GetY(), 185, $this->GetY());
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

ob_end_clean();
$pdf = new OrderPaperPDF();
$pdf->AddPage();

// Date
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, date('l jS F, Y', strtotime($orderPaper['date'])), 0, 1, 'C');
$pdf->Ln(8);

// Chairperson name (hardcoded or from DB)
$chairperson = "Hon. Chairperson of the Committee";

// 1. PRAYER
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 8, "- 1. PRAYER:", 0, 1, 'L', true);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, "($chairperson)", 0, 1, 'C', true);
$pdf->Ln(5);

// 2. TABLING OF REPORT
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "- 2. TABLING OF REPORT:", 0, 1, 'L', true);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, "($chairperson)", 0, 1, 'C', true);
$pdf->Ln(5);

// Function to print attachments as clickable links
function printAttachments($pdf, $attachmentsStmt, $agenda_item_id, $fill) {
    $attachmentsStmt->bind_param("i", $agenda_item_id);
    $attachmentsStmt->execute();
    $result = $attachmentsStmt->get_result();
    if ($result->num_rows > 0) {
        $pdf->SetFont('Arial', 'I', 11);
        while ($att = $result->fetch_assoc()) {
            $pdf->SetTextColor(0, 0, 255);
            $pdf->Cell(5);
            $pdf->Write(6, "Attachment: " . $att['file_name'], $att['file_path']);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->Ln(2);
    }
}

// Print motions
$itemCounter = 3;
$fill = false;
while ($item = $agendaItems->fetch_assoc()) {
    $title = isset($item['title']) ? strtoupper($item['title']) : '';
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor($fill ? 230 : 255, $fill ? 230 : 255, $fill ? 230 : 255);
    $pdf->Cell(0, 8, "- $itemCounter. MOTION:", 0, 1, 'L', true);
    $pdf->Ln(2);

    if (!empty($item['title'])) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 6, strtoupper($item['title']), 0, 'L', $fill);
        $pdf->Ln(2);
    }
    if (!empty($item['description'])) {
        $pdf->MultiCell(0, 6, $item['description'], 0, 'L', $fill);
        $pdf->Ln(2);
    }

    if (!empty($item['presented_by'])) {
        $pdf->SetFont('Arial', 'B', 14);
        $presenter = $item['presented_by'];
        if (strpos($presenter, 'Hon.') === false) $presenter = 'Hon. ' . getUsernameById ($presenter);
        $pdf->Cell(0, 8, "($presenter)", 0, 1, 'C', $fill);
        $pdf->Ln(2);
    }

    // Attachments
    printAttachments($pdf, $attachmentsStmt, $item['agenda_item_id'], $fill);

    $pdf->Ln(4);
    $fill = !$fill;
    $itemCounter++;
}

// Signature line
$pdf->Ln(8);
$pdf->SetFont('Arial', '', 12);
// $pdf->Cell(0, 8, '………………………..', 0, 1, 'C');

$filename = 'Order_Paper_' . date('Y-m-d', strtotime($orderPaper['date'])) . '.pdf';
$pdf->Output('D', $filename);
exit;
?>
