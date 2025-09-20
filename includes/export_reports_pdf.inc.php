 <?php
session_start();
require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';
require('../fpdf186/fpdf.php'); // adjust path

if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
    exit('Unauthorized');
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Lesotho Parliamentary Feedback Platform Reports',0,1,'C');
$pdf->Ln(10);

// function to add a table section
function addSection($pdf, $title, $sql, $conn){
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,$title,0,1);
    $pdf->SetFont('Arial','',10);
    $res=$conn->query($sql);
    while($row=$res->fetch_assoc()){
        $pdf->Cell(60,8,'Status: '.$row['status'],1,0);
        $pdf->Cell(40,8,'Count: '.$row['c'],1,1);
    }
    $pdf->Ln(5);
}

addSection($pdf,'Suggestions',"SELECT status, COUNT(*) as c FROM suggestions GROUP BY status",$conn);
addSection($pdf,'Feedback',"SELECT status, COUNT(*) as c FROM feedback GROUP BY status",$conn);
addSection($pdf,'Petitions',"SELECT status, COUNT(*) as c FROM petitions GROUP BY status",$conn);

$pdf->Output('D','reports.pdf');
exit;
?>
