<?php
//session_start();
require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';
include 'header.php'; // header code from previous step

// ---- handle actions for suggestions ----
if (isset($_GET['approve_s'])) {
    $id = (int)$_GET['approve_s'];
    $conn->query("UPDATE suggestions SET status='approved' WHERE suggestion_id=$id");
    header('Location: moderate_content.php?updated=1');
    exit;
}
if (isset($_GET['reject_s'])) {
    $id = (int)$_GET['reject_s'];
    $conn->query("UPDATE suggestions SET status='rejected' WHERE suggestion_id=$id");
    header('Location: moderate_content.php?updated=1');
    exit;
}
if (isset($_GET['delete_s'])) {
    $id = (int)$_GET['delete_s'];
    $conn->query("DELETE FROM suggestions WHERE suggestion_id=$id");
    header('Location: moderate_content.php?deleted=1');
    exit;
}

// ---- handle actions for feedback ----
if (isset($_GET['close_f'])) {
    $id = (int)$_GET['close_f'];
    $conn->query("UPDATE feedback SET status='Closed' WHERE feedback_id=$id");
    header('Location: moderate_content.php?updated=1#feedback');
    exit;
}
if (isset($_GET['review_f'])) {
    $id = (int)$_GET['review_f'];
    $conn->query("UPDATE feedback SET status='Reviewed' WHERE feedback_id=$id");
    header('Location: moderate_content.php?updated=1#feedback');
    exit;
}
if (isset($_GET['delete_f'])) {
    $id = (int)$_GET['delete_f'];
    $conn->query("DELETE FROM feedback WHERE feedback_id=$id");
    header('Location: moderate_content.php?deleted=1#feedback');
    exit;
}

// ---- handle actions for petitions ----
if (isset($_GET['close_p'])) {
    $id = (int)$_GET['close_p'];
    $conn->query("UPDATE petitions SET status='Closed' WHERE petition_id=$id");
    header('Location: moderate_content.php?updated=1#petitions');
    exit;
}
if (isset($_GET['review_p'])) {
    $id = (int)$_GET['review_p'];
    $conn->query("UPDATE petitions SET status='Under Review' WHERE petition_id=$id");
    header('Location: moderate_content.php?updated=1#petitions');
    exit;
}
if (isset($_GET['delete_p'])) {
    $id = (int)$_GET['delete_p'];
    $conn->query("DELETE FROM petitions WHERE petition_id=$id");
    header('Location: moderate_content.php?deleted=1#petitions');
    exit;
}

// ---- fetch data ----
$suggestions = $conn->query("SELECT s.suggestion_id, s.content, s.status, s.date_posted,
                                   u.first_name, u.last_name
                             FROM suggestions s
                             JOIN users u ON s.user_id=u.user_id
                             ORDER BY s.date_posted DESC");

$feedback = $conn->query("SELECT f.feedback_id, f.text, f.status, f.date_submitted, f.agenda_item_id,
                                 u.first_name, u.last_name
                          FROM feedback f
                          JOIN users u ON f.user_id=u.user_id
                          ORDER BY f.date_submitted DESC");

$petitions = $conn->query("
    SELECT p.petition_id, p.title as content, p.status, p.created_at AS date_submitted,
           u.first_name, u.last_name,
           COUNT(s.signature_id) AS signatures
    FROM petitions p
    JOIN users u ON p.mp_id  = u.user_id
    LEFT JOIN petition_signatures s ON p.petition_id = s.petition_id
    GROUP BY p.petition_id
    ORDER BY date_submitted DESC
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Moderate Content</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    tr.data-row {cursor:pointer;}
    tr.data-row:hover {background-color:#f8f9fa;}
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-shield-alt me-2 text-primary"></i>Moderate Content</h2>

  <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Status updated successfully.</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert-danger">Record deleted successfully.</div>
  <?php endif; ?>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-3" id="moderationTabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#suggestions">Suggestions</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#feedback">Feedback</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#petitions">Petitions</a></li>
  </ul>

  <div class="tab-content">
    <!-- Suggestions Tab -->
    <div class="tab-pane fade show active" id="suggestions">
      <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr><th>#</th><th>Content</th><th>Posted By</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php while($row=$suggestions->fetch_assoc()): ?>
            <tr class="data-row" 
                data-type="s"
                data-id="<?=$row['suggestion_id'];?>"
                data-user="<?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?>"
                data-content="<?=htmlspecialchars($row['content']);?>"
                data-status="<?=$row['status'];?>">
              <td><?=$row['suggestion_id'];?></td>
              <td><?=htmlspecialchars($row['content']);?></td>
              <td><?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?></td>
              <td><span class="badge bg-secondary"><?=$row['status'];?></span></td>
              <td><?=$row['date_posted'];?></td>
            </tr>
            <?php endwhile;?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Feedback Tab -->
    <div class="tab-pane fade" id="feedback">
      <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr><th>#</th><th>Text</th><th>By</th><th>Agenda</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php while($row=$feedback->fetch_assoc()): ?>
            <tr class="data-row" 
                data-type="f"
                data-id="<?=$row['feedback_id'];?>"
                data-user="<?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?>"
                data-content="<?=htmlspecialchars($row['text']);?>"
                data-status="<?=$row['status'];?>">
              <td><?=$row['feedback_id'];?></td>
              <td><?=htmlspecialchars($row['text']);?></td>
              <td><?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?></td>
              <td><?=htmlspecialchars(getAgendaTitle($row['agenda_item_id']))?></td>
              <td><span class="badge bg-secondary"><?=$row['status'];?></span></td>
              <td><?=$row['date_submitted'];?></td>
            </tr>
            <?php endwhile;?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Petitions Tab -->
    <!-- Petitions Tab -->
<div class="tab-pane fade" id="petitions">
  <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
    <table class="table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Content</th>
          <th>By</th>
          <th>Signatures</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $petitions->fetch_assoc()): ?>
        <tr class="data-row" 
            data-type="p"
            data-id="<?=$row['petition_id'];?>"
            data-user="<?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?>"
            data-content="<?=htmlspecialchars($row['content']);?>"
            data-status="<?=$row['status'];?>"
            
        >
          <td><?=$row['petition_id'];?></td>
          <td><?=htmlspecialchars($row['content']);?></td>
          <td><?=htmlspecialchars($row['first_name'].' '.$row['last_name']);?></td>
          <td><?=$row['signatures'];?></td>
          <td><span class="badge bg-secondary"><?=$row['status'];?></span></td>
          <td><?=$row['date_submitted'];?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

  </div>
</div>

<!-- Modal for details -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-eye me-1"></i>Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1 text-muted">By <span id="modalUser"></span></p>
        <p class="mb-1 text-muted"> <span id="modalAgenda"></span> <span id="modalUser"></span></p>

        <p><strong>Status:</strong> <span id="modalStatus" class="badge bg-secondary"></span></p>
        <hr>
        <p id="modalContent"></p>
      </div>
      <div class="modal-footer">
        <a href="#" id="action1" class="btn btn-success"></a>
        <a href="#" id="action2" class="btn btn-warning"></a>
        <a href="#" id="actionDelete" class="btn btn-danger"
           onclick="return confirm('Are you sure you want to delete this record?');"><i class="fas fa-trash me-1"></i> Delete</a>
      </div>
    </div>
  </div>
</div>

<script>
const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
document.querySelectorAll('tr.data-row').forEach(row => {
  row.addEventListener('click', () => {
    const type = row.dataset.type; // s/f/p
    const id = row.dataset.id;
    const user = row.dataset.user;
    const content = row.dataset.content;
    const status = row.dataset.status;
    document.getElementById('modalAgenda').textContent = row.dataset.agenda || '';

    document.getElementById('modalUser').textContent = user;
    document.getElementById('modalContent').textContent = content;
    document.getElementById('modalStatus').textContent = status;

    let action1 = document.getElementById('action1');
    let action2 = document.getElementById('action2');
    let actionDelete = document.getElementById('actionDelete');

    if (type === 's') {
      action1.textContent = 'Approve';
      action1.href = 'moderate_content.php?approve_s=' + id;
      action2.textContent = 'Reject';
      action2.href = 'moderate_content.php?reject_s=' + id;
      actionDelete.href = 'moderate_content.php?delete_s=' + id;
    } else if (type === 'f') {
      action1.textContent = 'Mark Reviewed';
      action1.href = 'moderate_content.php?review_f=' + id;
      action2.textContent = 'Close';
      action2.href = 'moderate_content.php?close_f=' + id;
      actionDelete.href = 'moderate_content.php?delete_f=' + id;
    } else if (type === 'p') {
      action1.textContent = 'Under Review';
      action1.href = 'moderate_content.php?review_p=' + id;
      action2.textContent = 'Close';
      action2.href = 'moderate_content.php?close_p=' + id;
      actionDelete.href = 'moderate_content.php?delete_p=' + id;
    }

    itemModal.show();
  });
});
</script>
<style>
.table-scroll {
  max-height: 500px;
  overflow-y: auto;
}
@media (max-width: 768px) {
  .table-scroll {
    max-height: 300px; /* smaller height on mobile */
  }
}
</style>

</body>
</html>
