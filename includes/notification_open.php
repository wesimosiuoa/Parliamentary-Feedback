<?php
require_once 'dbcon.inc.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
// Redirect back to dashboard
header('Location: ../citizen/dashboard.php#notifications');
exit;
