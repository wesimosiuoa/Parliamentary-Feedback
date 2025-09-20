<?php
// session_start();
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include '../includes/message.php';
include 'header.php';

$user_id = getUserID($_SESSION['email']);

// Get all notifications
$sql = "SELECT notification_id, message, is_read, date_sent 
        FROM notifications 
        WHERE user_id=? 
        ORDER BY date_sent DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
  body { background:#f8f9fa; }
  .notif-card { border-left:5px solid #0d6efd; }
  .notif-read { background:#f1f1f1; border-left:5px solid #6c757d; }
  .notif-date { font-size:0.85rem; color:#6c757d; }
  .mark-read { text-decoration:none; }
</style>
</head>
<body>

<div class="container mt-4">
  <h3><i class="fas fa-bell me-2"></i>Notifications</h3>
  <p class="text-muted">Click the eye icon to mark a notification as read.</p>

  <?php if ($notifications): ?>
    <?php foreach ($notifications as $nt): ?>
      <div class="card mb-3 notif-card <?= $nt['is_read'] ? 'notif-read' : '' ?>">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-1"><?= nl2br(htmlspecialchars($nt['message'])); ?></p>
            <span class="notif-date"><i class="far fa-clock me-1"></i><?= date('Y-m-d H:i', strtotime($nt['date_sent'])); ?></span>
          </div>
          <?php if (!$nt['is_read']): ?>
          <a href="../includes/mark_notification.php?id=<?= $nt['notification_id']; ?>" class="btn btn-sm btn-outline-primary" title="Mark as read">
            <i class="fas fa-eye"></i>
          </a>
          <?php else: ?>
          <span class="badge bg-secondary">Read</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="alert alert-info">You have no notifications yet.</div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
