<?php
require_once '../includes/dbcon.inc.php';
include 'header.php';
// Sanitize and get id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT f.feedback_id, f.subject, f.text, f.date_submitted, f.status,
               u.first_name, u.last_name, u.email
        FROM feedback f
        JOIN users u ON f.user_id = u.user_id
        WHERE f.feedback_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$fb = $result->fetch_assoc();

if (!$fb) {
    echo "Feedback not found.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>View Feedback</title>
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
</head>
<body class="p-4">
  <div class="container">
    <h3>Feedback Details</h3>
    <table class="table table-bordered">
      <tr><th>Submitted By</th><td><?= htmlspecialchars($fb['first_name'].' '.$fb['last_name']) ?></td></tr>
      <tr><th>Email</th><td><?= htmlspecialchars($fb['email']) ?></td></tr>
      <tr><th>Subject</th><td><?= htmlspecialchars($fb['subject']) ?></td></tr>
      <tr><th>Message</th><td><?= nl2br(htmlspecialchars($fb['text'])) ?></td></tr>
      <tr><th>Date Submitted</th><td><?= htmlspecialchars($fb['date_submitted']) ?></td></tr>
      <tr>
        <th>Status</th>
        <td>
          <?php
          $status = $fb['status'];
          $badge = $status == 'Pending' ? 'warning' :
                   ($status == 'Closed' ? 'secondary' : 'success');
          ?>
          <span class="badge bg-<?= $badge ?>">
            <?= htmlspecialchars($status) ?>
          </span>
        </td>
      </tr>
    </table>
    <a href="mp_feedback.php" class="btn btn-secondary">Back</a>
  </div>
</body>
</html>
