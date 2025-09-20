<!-- mp_feedback.php -->
<?php
include 'header.php';
require_once '../includes/fn.inc.php';


$feedbacks = getFeedbackForMP($_SESSION['email']); // write this function to filter by MP's constituency
?>
<div class="container mt-4">
  <h3 class="mb-3">Citizen Feedback</h3>
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>Citizen</th>
        <th>Subject</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($feedbacks as $index => $fb): ?>
      <tr>
        <td><?= $index+1 ?></td>
        <td><?= htmlspecialchars($fb['user_name'].' '.$fb['last_name']) ?></td>
        <td><?= htmlspecialchars($fb['subject']) ?></td>
        <td><?= htmlspecialchars($fb['date_submitted']) ?></td>
        <td><?php
          $status = $fb['status']; 
$badgeClass = '';

if ($status == 'Pending') {
    $badgeClass = 'warning'; // yellow
} elseif ($status == 'Closed') {
    $badgeClass = 'secondary'; // grey (or whatever you prefer)
} else {
    $badgeClass = 'success'; // green for approved/handled
}
?>
<span class="badge bg-<?= $badgeClass ?>">
    <?= htmlspecialchars($status) ?>
</span>
            
          
        </td>
        <td>
          <a href="mp_feedback_view.php?id=<?= $fb['feedback_id'] ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-eye"></i> View
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
