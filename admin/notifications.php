<?php
//session_start();
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';

// // Only allow admin
// if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
//     exit('Unauthorized');
// }

// Get filter inputs
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$startDate    = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate      = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Build query
$sql = "SELECT * FROM notifications WHERE 1=1";

// map status filter to is_read values
if ($statusFilter) {
    $is_read = ($statusFilter === 'Read') ? 1 : 0;
    $sql .= " AND is_read=" . intval($is_read);
}

if ($startDate) {
    $sql .= " AND date_sent >= '" . $conn->real_escape_string($startDate) . " 00:00:00'";
}
if ($endDate) {
    $sql .= " AND date_sent <= '" . $conn->real_escape_string($endDate) . " 23:59:59'";
}

$sql .= " ORDER BY date_sent DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Notifications 
        <span class="badge bg-warning">
            <?php 
            $unreadCount = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE is_read=0")->fetch_assoc()['c']; 
            echo $unreadCount;
            ?>
        </span>
    </h3>
    <form class="d-flex" method="GET" action="">
      <select class="form-select me-2" name="status">
        <option value="">All Status</option>
        <option value="Unread" <?= $statusFilter=='Unread'?'selected':'' ?>>Unread</option>
        <option value="Read" <?= $statusFilter=='Read'?'selected':'' ?>>Read</option>
      </select>
      <input type="date" class="form-control me-2" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
      <input type="date" class="form-control me-2" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
      <button type="submit" class="btn btn-primary">Filter</button>
    </form>
  </div>

  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th></th>
        <th>Message</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['is_read']==0 ? 'table-light fw-bold' : '' ?>">
            <td>
              <?php 
                // placeholder for icons if you need them later
              ?>
            </td>
            <td><?= htmlspecialchars($row['message']) ?></td>
            <td><?= htmlspecialchars($row['date_sent']) ?></td>
            <td>
                <span class="badge <?= $row['is_read']==0 ? 'bg-warning' : 'bg-success' ?>">
                    <?= $row['is_read']==0 ? 'Unread' : 'Read' ?>
                </span>
            </td>
            <td>
              <a href="view_notification.php?id=<?= $row['notification_id'] ?>" class="btn btn-sm btn-info">
                View <?= $row['notification_id'] ?>
              </a>
              <a href="delete_notification.php?id=<?= $row['notification_id'] ?>" 
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Are you sure you want to delete this notification?');">
                 Delete
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">No notifications found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
