<?php

require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';


// Only allow MPs
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Parliamentarian') {
    echo "Unauthorized action.";
    exit;
}

$mp_id = getUserID($_SESSION['email']);

// Get petition ID from POST
if (!isset($_POST['petition_id'])) {
    echo "Petition ID missing.";
    exit;
}

$petition_id = (int)$_POST['petition_id'];

// Update petition status to 'Closed'
$sql = "UPDATE petitions SET status = 'Closed' WHERE petition_id = ? AND mp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $petition_id, $mp_id);

if ($stmt->execute()) {
    flashMessage('success', '✅ Petition closed successfully.', 'mp_petitions.php', 1);
    
    exit;
} else {
    flashMessage('error', '❌ Error closing petition: ' . $conn->error, 'mp_petitions.php', 3);
    exit;
}
