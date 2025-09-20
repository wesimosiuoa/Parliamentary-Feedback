<?php
session_start();
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

// only admin allowed
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
    exit('Unauthorized access');
}

// validate id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid Notification ID');
}
$notification_id = intval($_GET['id']);

// delete notification safely
$stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id=?");
$stmt->bind_param("i", $notification_id);

if ($stmt->execute()) {
    // redirect back to list with success message
    header("Location: notifications.php?deleted=1");
    exit;
} else {
    echo "Error deleting notification: " . $conn->error;
}
