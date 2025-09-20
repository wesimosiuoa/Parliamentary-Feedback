<?php
include 'header.php';
require_once '../includes/fn.inc.php';

// fetch all order papers sorted by date ascending
$sql = "SELECT order_paper_id, title, session_number, created_at as date_created  
        FROM order_papers 
        ORDER BY date_created ASC";
$result = $conn->query($sql);
$orderPapers = [];
while ($row = $result->fetch_assoc()) {
    $orderPapers[] = $row;
}

$order_paper_id = $orderPapers[0]['order_paper_id'] ?? null;
$sql ="SELECT * FROM `agenda_items` WHERE `order_paper_id` = '$order_paper_id' AND `status` = 'Pending';" ;

$result = $conn->query($sql);
$agenda_items = [];
while ($row = $result->fetch_assoc()) {
    $agenda_items[] = $row;
}




$user_id = getUserID($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Papers – Citizen Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4"><i class="fa-regular fa-file-lines me-2"></i>Order Papers</h2>
  <p class="text-muted">Click “View / Act” to submit a suggestion or feedback on an order paper.</p>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Title</th>
        <th>S-NO </th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($orderPapers)): ?>
        <?php foreach ($orderPapers as $op): ?>
          <tr>
            <td><?php echo htmlspecialchars($op['title']); ?></td>
            <td><?php echo htmlspecialchars($op['session_number']); ?></td>
            <td><?php echo date('Y-m-d', strtotime($op['date_created'])); ?></td>
            <td>
              <button class="btn btn-sm btn-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#orderPaperModal"
                      data-id="<?php echo (int)$op['order_paper_id']; ?>"
                      data-title="<?php echo htmlspecialchars($op['title']); ?>">
                <i class="fas fa-eye"></i> View / Act
              </button>
              <a href="../includes/generate_order_paper.inc.php?order_paper=<?= $op['order_paper_id']?>" class="btn btn-success"> Download</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center text-muted">No order papers found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="orderPaperModal" tabindex="-1" aria-labelledby="orderPaperModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderPaperModalLabel">Order Paper</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h5 id="opTitle" class="mb-3"></h5>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="suggest-tab" data-bs-toggle="tab" data-bs-target="#suggest" type="button" role="tab">Post Suggestion</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback" type="button" role="tab">Post Feedback</button>
          </li>
        </ul>
        <div class="tab-content mt-3">
          <!-- Suggestion Form -->
          <div class="tab-pane fade show active" id="suggest" role="tabpanel">
            <form action="../includes/suggestion_post.inc.php" method="POST">
              <label for="agenda_item_id" class="form-label">Select Agenda Item</label>
            <select name="agenda_item_id" id="agenda_item_id" class="form-select" required>
                <option value="">-- Choose Agenda Item --</option>
                <?php foreach ($agenda_items as $item): ?>
                    <option value="<?php echo (int)$item['agenda_item_id']; ?>">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

              <div class="mb-3">
                <label for="suggestionText" class="form-label">Your Suggestion</label>
                <input type="text" class="form-control" name="content" id="suggestionText" placeholder="Type your suggestion here" required>
              </div>
              <button type="submit" class="btn btn-success">Submit Suggestion</button>
            </form>
          </div>
          <!-- Feedback Form -->
          <div class="tab-pane fade" id="feedback" role="tabpanel">
            <form action="../includes/feedback_submiti.inc.php" method="POST">
              <input type="hidden" name="order_paper_id" id="orderPaperIdFb">
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your feedback" required>
              </div>
              <div class="mb-3">
                <select name="agenda_item_id" id="agenda_item_id" class="form-select" required>
                  <option value="">Agenda</option>
                  <?php foreach ($agenda_items as $item): ?>
                      <option value="<?php echo (int)$item['agenda_item_id']; ?>">
                          <?php echo htmlspecialchars($item['title']); ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Write your feedback here" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
const orderPaperModal = document.getElementById('orderPaperModal')
orderPaperModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget
  const opId = button.getAttribute('data-id')
  const opTitle = button.getAttribute('data-title')
  document.getElementById('opTitle').textContent = opTitle
  document.getElementById('orderPaperIdSug').value = opId
  document.getElementById('orderPaperIdFb').value = opId
})
</script>

</body>
</html>
