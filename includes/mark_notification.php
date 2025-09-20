<?php
session_start();
require_once 'dbcon.inc.php';
require_once 'fn.inc.php';

$user_id = getUserID($_SESSION['email']);
$id = $_GET['id'] ?? 0;

$sql = "UPDATE notifications SET is_read=1 WHERE notification_id=? AND user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();

header('Location: ../citizen/notifications.php');
exit;
?>
