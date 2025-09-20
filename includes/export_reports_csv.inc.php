<?php
session_start();
require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';

if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
    exit('Unauthorized');
}

// Example: export suggestions counts
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reports.csv');

$output = fopen('php://output', 'w');

// headings
fputcsv($output, ['Report Type','Status','Count']);

// Suggestions
$res = $conn->query("SELECT status, COUNT(*) as c FROM suggestions GROUP BY status");
while ($row=$res->fetch_assoc()) {
    fputcsv($output, ['Suggestions',$row['status'],$row['c']]);
}

// Feedback
$res = $conn->query("SELECT status, COUNT(*) as c FROM feedback GROUP BY status");
while ($row=$res->fetch_assoc()) {
    fputcsv($output, ['Feedback',$row['status'],$row['c']]);
}

// Petitions
$res = $conn->query("SELECT status, COUNT(*) as c FROM petitions GROUP BY status");
while ($row=$res->fetch_assoc()) {
    fputcsv($output, ['Petitions',$row['status'],$row['c']]);
}

fclose($output);
exit;
?>
