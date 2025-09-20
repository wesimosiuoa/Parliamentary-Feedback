<?php
// session_start();
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include '../includes/message.php';
include 'header.php';

// get user id
$user_id = getUserID($_SESSION['email']);

// Fetch Feedback
$feedbackSql = "SELECT feedback_id, subject, text, date_submitted, status 
                FROM feedback 
                WHERE user_id = ?
                ORDER BY date_submitted DESC";
$stmt = $conn->prepare($feedbackSql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$feedback = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Suggestions
$suggSql = "SELECT suggestion_id, content, votes, status, date_posted
            FROM suggestions 
            WHERE user_id = ?
            ORDER BY date_posted DESC";
$stmt = $conn->prepare($suggSql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$suggestions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Petitions
// Build SQL: join petitions with petition_signatures
$petSql = "
    SELECT 
        p.petition_id,
        p.title,
        p.description,
        p.status,
        p.created_at,
        COUNT(ps.signature_id) AS signature_count
    FROM petitions p
    LEFT JOIN petition_signatures ps 
        ON p.petition_id = ps.petition_id
    -- WHERE p.mp_id = ?  // uncomment if you want only petitions for one MP/user
    GROUP BY 
        p.petition_id, p.title, p.description, p.status, p.created_at
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($petSql);

// If you want to filter by mp_id or user_id uncomment below:
// $stmt->bind_param('i', $user_id);

$stmt->execute();
$petitions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Tracking – Lesotho Parliamentary Feedback Platform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
  body { background:#f8f9fa; }
  .tab-content { margin-top: 20px; }
  .table-responsive {
    max-height: 400px;
    overflow-y: auto;
  }
  .badge { font-size:0.85rem; }
</style>
</head>
<body>

<div class="container mt-4">
  <h2 class="mb-3"><i class="fas fa-chart-line me-2"></i>My Submissions Tracking</h2>
  <p class="text-muted">Track the status of your Feedback, Suggestions and Petitions.</p>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="trackingTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback" type="button" role="tab">
        <i class="fas fa-comment-dots me-1"></i> Feedback
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="suggestions-tab" data-bs-toggle="tab" data-bs-target="#suggestions" type="button" role="tab">
        <i class="fas fa-lightbulb me-1"></i> Suggestions
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="petitions-tab" data-bs-toggle="tab" data-bs-target="#petitions" type="button" role="tab">
        <i class="fas fa-file-signature me-1"></i> Petitions
      </button>
    </li>
  </ul>

  <!-- Tab contents -->
  <div class="tab-content" id="trackingTabsContent">
    <!-- Feedback Tab -->
    <div class="tab-pane fade show active" id="feedback" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-light">
            <tr>
              <th>Subject</th>
              <th>Message</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($feedback): foreach($feedback as $fb): ?>
            <tr>
              <td><?= htmlspecialchars($fb['subject']); ?></td>
              <td><?= htmlspecialchars($fb['text']); ?></td>
              <td><?= date('Y-m-d H:i', strtotime($fb['date_submitted'])); ?></td>
              <td>
                <?php 
                  $statusClass = match(strtolower($fb['status'])) {
                    'pending' => 'badge bg-warning text-dark',
                    'approved' => 'badge bg-success',
                    'rejected' => 'badge bg-danger',
                    default => 'badge bg-secondary'
                  };
                ?>
                <span class="<?= $statusClass; ?>"><?= htmlspecialchars($fb['status']); ?></span>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center text-muted">No feedback submitted yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Suggestions Tab -->
    <div class="tab-pane fade" id="suggestions" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-light">
            <tr>
              <th>Suggestion</th>
              <th>Votes</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($suggestions): foreach($suggestions as $sg): ?>
            <tr>
              <td><?= nl2br(htmlspecialchars(trim($sg['content']))); ?></td>
              <td><i class="fas fa-thumbs-up text-success me-1"></i><?= $sg['votes']; ?></td>
              <td><?= date('Y-m-d H:i', strtotime($sg['date_posted'])); ?></td>
              <td>
                <?php 
                  $statusClass = match(strtolower($sg['status'])) {
                    'pending' => 'badge bg-warning text-dark',
                    'approved' => 'badge bg-success',
                    'rejected' => 'badge bg-danger',
                    default => 'badge bg-secondary'
                  };
                ?>
                <span class="<?= $statusClass; ?>"><?= htmlspecialchars($sg['status']); ?></span>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center text-muted">No suggestions posted yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Petitions Tab -->
    <div class="tab-pane fade" id="petitions" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-light">
            <tr>
              <th>Petition</th>
              <th>Signatures</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($petitions): foreach($petitions as $pt): ?>
            <tr>
              <td><?= nl2br(htmlspecialchars(trim($pt['title']))); ?></td>
              <td><i class="fas fa-pen-fancy text-primary me-1"></i><?= $pt['signature_count']; ?></td>
              <td><?= date('Y-m-d H:i', strtotime($pt['created_at'])); ?></td>
              <td>
                <?php 
                  $statusClass = match(strtolower($pt['status'])) {
                    'pending' => 'badge bg-warning text-dark',
                    'approved' => 'badge bg-success',
                    'rejected' => 'badge bg-danger',
                    default => 'badge bg-secondary'
                  };
                ?>
                <span class="<?= $statusClass; ?>"><?= htmlspecialchars($pt['status']); ?></span>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center text-muted">No petitions submitted yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
