<?php
//session_start();
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';


$notification_id = intval($_GET['id']);

// Fetch notification
$stmt = $conn->prepare("SELECT n.*, u.user_id AS user_name, u.email AS user_email 
                        FROM notifications n
                        LEFT JOIN users u ON n.user_id=u.user_id
                        WHERE n.notification_id=?");
$stmt->bind_param("i", $notification_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Notification not found');
}
$notification = $result->fetch_assoc();

// Mark as read if not already
if ($notification['is_read'] == 0) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE notification_id=".$notification_id);
    $notification['is_read'] = 1; // reflect immediately
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Notification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Notification Details</h5>
    </div>
    <div class="card-body">
      <p><strong>ID:</strong> <?= htmlspecialchars($notification['notification_id']) ?></p>
      <p><strong>Sent To (User):</strong> <?= htmlspecialchars($notification['user_name'] ?? 'N/A') ?> 
        (<?= htmlspecialchars($notification['user_email'] ?? '') ?>)</p>
      <p><strong>Message:</strong><br> <?= nl2br(htmlspecialchars($notification['message'])) ?></p>
      <p><strong>Status:</strong> 
        <span class="badge <?= $notification['is_read'] ? 'bg-success' : 'bg-warning' ?>">
          <?= $notification['is_read'] ? 'Read' : 'Unread' ?>
        </span>
      </p>
      <p><strong>Date Sent:</strong> <?= htmlspecialchars($notification['date_sent']) ?></p>
    </div>
    <div class="card-footer text-end">
      <a href="notifications.php" class="btn btn-secondary">Back to Notifications</a>
    </div>
  </div>
</div>
</body>
</html>
