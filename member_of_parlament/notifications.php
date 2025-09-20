<?php
include 'header.php';
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';


$user_id = getUserID($_SESSION['email']);
$user_name = getUsername($_SESSION['email']);

// Handle mark as read (single notification)
if (isset($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE notification_id=? AND user_id=?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php"); // Refresh page
    exit;
}

// Handle mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php"); // Refresh page
    exit;
}

// Optional: Filter by type
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

// Fetch notifications
if ($type_filter === 'all') {
    $stmt = $conn->prepare("SELECT notification_id, message, type, is_read, date_sent FROM notifications WHERE user_id=? ORDER BY date_sent DESC");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT notification_id, message, type, is_read, date_sent FROM notifications WHERE user_id=? AND type=? ORDER BY date_sent DESC");
    $stmt->bind_param("is", $user_id, $type_filter);
}

$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.table-responsive { max-height: 500px; overflow-y: auto; }
.table-warning { font-weight: bold; }
</style>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-4">Notifications for <?= htmlspecialchars($user_name) ?></h3>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="notifications.php?mark_all_read=1" class="btn btn-sm btn-success">Mark All as Read</a>
        </div>
        <div>
            <form method="get" class="d-flex align-items-center gap-2">
                <label>Filter:</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $type_filter==='all'?'selected':'' ?>>All</option>
                    <option value="petition" <?= $type_filter==='petition'?'selected':'' ?>>Petitions</option>
                    <option value="suggestion" <?= $type_filter==='suggestion'?'selected':'' ?>>Suggestions</option>
                    <option value="vote" <?= $type_filter==='vote'?'selected':'' ?>>Votes</option>
                    <option value="general" <?= $type_filter==='general'?'selected':'' ?>>General</option>
                </select>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark sticky-top">
                <tr>
                    <th>#</th>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date Sent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($notifications) === 0): ?>
                <tr><td colspan="6" class="text-center">No notifications found.</td></tr>
                <?php else: ?>
                <?php foreach($notifications as $index => $n): ?>
                <tr class="<?= $n['is_read']==0 ? 'table-warning' : '' ?>">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($n['message']) ?></td>
                    <td><?= ucfirst($n['type']) ?></td>
                    <td><?= $n['is_read']==0 ? 'Unread' : 'Read' ?></td>
                    <td><?= $n['date_sent'] ?></td>
                    <td>
                        <?php if($n['is_read']==0): ?>
                        <a href="notifications.php?mark_read=<?= $n['notification_id'] ?>" class="btn btn-sm btn-primary">Mark as Read</a>
                        <?php else: ?>
                        <span class="text-muted">Read</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
