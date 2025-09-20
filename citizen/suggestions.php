<?php 
include '../includes/message.php'; 
include 'header.php';
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

// session_start();
$user_id = getUserID($_SESSION['email']);

// fetch suggestions for this user
$sql = "SELECT suggestion_id, content, votes, status, date_posted 
        FROM suggestions 
        WHERE user_id = ?
        ORDER BY date_posted DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$mySuggestions = [];
while ($row = $result->fetch_assoc()) {
    $mySuggestions[] = $row;
}

$sql = " SELECT agenda_items.title as title, agenda_items.agenda_item_id 
FROM agenda_items 
INNER JOIN order_papers ON agenda_items.order_paper_id = order_papers.order_paper_id 
WHERE agenda_items.status = 'Pending'
ORDER BY agenda_items.created_at DESC;";
$result = $conn->query($sql);
$agenda_items = [];
while ($row = $result->fetch_assoc()) {
    $agenda_items[] = $row;
}

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">

    <!-- Header & Add New Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">My Suggestions</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#suggestionModal">
            <i class="fas fa-plus me-1"></i> Add New Suggestion
        </button>
    </div>

    <!-- Suggestions Table -->
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Suggestion</th>
                    <th>Votes</th>
                    <th>Status</th>
                    <th>Date Posted</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                if (!empty($mySuggestions)): 
                    foreach ($mySuggestions as $sg):
                        $count++;
                        if ($count > 10) break;
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sg['content']); ?></td>
                            <td><span class="badge bg-success"><?php echo (int)$sg['votes']; ?></span></td>
                            <td>
                                <?php
                                $statusClass = match (strtolower($sg['status'])) {
                                    'pending'  => 'badge bg-warning text-dark',
                                    'approved' => 'badge bg-success',
                                    'rejected' => 'badge bg-danger',
                                    default    => 'badge bg-secondary'
                                };
                                ?>
                                <span class="<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($sg['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($sg['date_posted'])); ?></td>
                        </tr>
                <?php 
                    endforeach; 
                else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">You haven’t posted any suggestions yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($mySuggestions) > 10): ?>
        <div class="text-center mt-3">
            <a href="all_suggestions.php" class="btn btn-outline-primary btn-sm">View All Suggestions</a>
        </div>
    <?php endif; ?>

</div>

<!-- Suggestion Modal -->
<div class="modal fade" id="suggestionModal" tabindex="-1" aria-labelledby="suggestionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="suggestionModalLabel">Submit New Suggestion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="../includes/suggestion_post.inc.php" method="POST">
            <div class="mb-3">
                <select name="agenda_item_id" id="agenda_item_id" class="form-select" required>
                  <option value="">Select Agenda Item</option>
                  <?php foreach ($agenda_items as $item): ?>
                      <option value="<?php echo (int)$item['agenda_item_id']; ?>">
                          <?php echo htmlspecialchars($item['title']); ?>
                      </option>
                  <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Your Suggestion</label>
                <textarea class="form-control" id="content" name="content" rows="4" placeholder="Write your suggestion here" required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Submit Suggestion</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
