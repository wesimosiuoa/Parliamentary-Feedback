<?php 
include '../includes/message.php';
include 'header.php';
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

// session_start();
$user_id = getUserID($_SESSION['email']);

// fetch all feedback for this user
$sql = "SELECT feedback_id, subject, text, date_submitted, status 
        FROM feedback 
        WHERE user_id = ?
        ORDER BY date_submitted DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$myFeedback = [];
while ($row = $result->fetch_assoc()) {
    $myFeedback[] = $row;
}



?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">

    <!-- Header & Add New Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">My Feedback</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#feedbackModal">
            <i class="fas fa-plus me-1"></i> Add New Feedback
        </button>
    </div>

    <!-- Feedback Table -->
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                if (!empty($myFeedback)): 
                    foreach ($myFeedback as $fb):
                        $count++;
                        if ($count > 10) break; // limit initial display to 10
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fb['subject']); ?></td>
                            <td><?php echo htmlspecialchars($fb['text']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($fb['date_submitted'])); ?></td>
                            <td>
                                <?php
                                $statusClass = match (strtolower($fb['status'])) {
                                    'pending'  => 'badge bg-warning text-dark',
                                    'approved' => 'badge bg-success',
                                    'rejected' => 'badge bg-danger',
                                    default    => 'badge bg-secondary'
                                };
                                ?>
                                <span class="<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($fb['status']); ?>
                                </span>
                            </td>
                        </tr>
                <?php 
                    endforeach; 
                else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">You haven’t submitted any feedback yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($myFeedback) > 10): ?>
        <div class="text-center mt-3">
            <a href="all_feedback.php" class="btn btn-outline-primary btn-sm">View All Feedback</a>
        </div>
    <?php endif; ?>

</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="feedbackModalLabel">Submit New Feedback</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="../includes/feedback_submiti.inc.php" method="POST">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your feedback" required>
            </div>
            <div class="mb-3">
                <label for="agenda_item_id" class="form-label">Select Agenda Item</label>
                <select name="agenda_item_id" id="agenda_item_id" class="form-select" required>
                    <option value="">-- Choose Agenda Item --</option>
                    <?php foreach ($agenda_items as $item): ?>
                        <option value="<?php echo (int)$item['agenda_item_id']; ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Write your feedback or petition here" required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success">Submit Feedback</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
