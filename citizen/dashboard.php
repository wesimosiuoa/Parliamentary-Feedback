<?php
include '../citizen/header.php';
require_once '../includes/fn.inc.php';
$suggestions = getSuggestions();


$user_id = getUserID($_SESSION['email']);

// fetch the submissions for this user
$sql = "SELECT feedback_id AS id, subject, text, date_submitted, status
        FROM feedback 
        WHERE user_id = ?
        ORDER BY date_submitted DESC"; // you can also LIMIT 5 here

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$submissions = [];
while ($row = $result->fetch_assoc()) {
    $submissions[] = $row;
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Citizen Dashboard – Lesotho Parliamentary Feedback Platform</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar {
      background: #00209F;
      min-height: 100vh;
      color: #fff;
    }
    .sidebar a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
      transition: background .2s;
    }
    .sidebar a:hover {
      background: #009639;
    }
    .dashboard-content {
      padding: 20px;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,.1);
    }
    .card i {
      font-size: 2rem;
      color: #009639;
    }
    .vote-btn {
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="p-3">
        <h5 class="mb-4">Citizen Panel</h5>
        <a href="#submit"><i class="fas fa-paper-plane me-2"></i> Submit Feedback </a>
        <a href="#suggestions"><i class="fas fa-lightbulb me-2"></i> Suggestions & Voting</a>
        <a href="#track"><i class="fas fa-tasks me-2"></i> Track Submissions</a>
        <a href="#notifications"><i class="fas fa-bell me-2"></i> Notifications</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-content">
      <h2 class="mt-4">Welcome, 
      <?php echo htmlspecialchars(getUsername($_SESSION['email'])); ?>!</h2>
      <p class="text-muted">This is your citizen dashboard. You can submit feedback, petitions, and suggestions to Parliament here.</p>
      <hr>
      <a href="order_papers.php">Order Papers </a>
      <div class="row mt-4">
        <div class="col-md-4 mb-4">
          <div class="card p-4 text-center">
            <i class="fas fa-paper-plane mb-3"></i>
            <h5>Submit Feedback </h5>
            <p class="text-muted">Send your comments, petitions, or proposals directly to Parliament.</p>
            <a href="#submit" class="btn btn-success btn-sm">Go</a>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card p-4 text-center">
            <i class="fas fa-lightbulb mb-3"></i>
            <h5>Suggestions & Voting</h5>
            <p class="text-muted">Post your suggestions and vote on others’ suggestions.</p>
            <a href="#suggestions" class="btn btn-success btn-sm">Go</a>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card p-4 text-center">
            <i class="fas fa-bell mb-3"></i>
            <h5>Notifications</h5>
            <p class="text-muted">Stay updated on responses or actions taken.</p>
            <a href="#notifications" class="btn btn-success btn-sm">Go</a>
          </div>
        </div>
      </div>

      
  <!-- Existing suggestions -->
<p class="text-muted">Vote on suggestions posted by others:</p>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Suggestion</th>
      <th>Posted By</th>
      <th>Votes</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($suggestions)): ?>
        <?php 
        // limit to 5 latest suggestions
        $latestFive = array_slice($suggestions, 0, 5);
        foreach ($latestFive as $s): ?>
          <tr>
            <td><?php echo nl2br(htmlspecialchars($s['content'])); ?></td>
            <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
            <td><?php echo (int)$s['votes']; ?></td>
            <td>
              <a class="btn btn-sm btn-success" 
                 href="../includes/suggestion_vote.inc.php?user_id=<?php echo (int)getUserID($_SESSION['email']); ?>&suggestion_id=<?php echo (int)$s['suggestion_id']; ?>">
                 <i class="fas fa-thumbs-up"></i> Vote
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
          <td colspan="4" class="text-center text-muted">No suggestions yet.</td>
        </tr>
    <?php endif; ?>
  </tbody>
</table>

<?php if (count($suggestions) > 5): ?>
  <div class="text-end">
    <a href="suggestions.php" class="btn btn-link">View All Suggestions &raquo;</a>
  </div>
<?php endif; ?>


<!-- Track Submissions -->
<section id="track" class="mt-5">
  <h4>Track Submissions</h4>
  <p class="text-muted">Below is a summary of your 5 most recent submissions.</p>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Subject</th>
        <th>Message</th>
        <th>Date Submitted</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($submissions)): ?>
        <?php 
        // only 5 latest
        $latestSubs = array_slice($submissions, 0, 5);
        foreach ($latestSubs as $sub): ?>
          <tr>
            <td><?php echo htmlspecialchars($sub['subject']); ?></td>
            <td><?php echo htmlspecialchars($sub['text']); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($sub['date_submitted'])); ?></td>
            <td>
              <?php
              $statusClass = match (strtolower($sub['status'])) {
                'pending'  => 'badge bg-warning text-dark',
                'approved' => 'badge bg-success',
                'rejected' => 'badge bg-danger',
                default    => 'badge bg-secondary'
              };
              ?>
              <span class="<?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($sub['status']); ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center text-muted">No submissions yet.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (count($submissions) > 5): ?>
    <div class="text-end">
      <a href="all_submissions.php" class="btn btn-outline-primary btn-sm">View All Submissions &raquo;</a>
    </div>
  <?php endif; ?>
</section>

        <?php

        // Fetch the latest notifications for this user
        $sql = "SELECT notification_id, message, date_sent, is_read 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY date_sent DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        $newCount = 0;
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
            if (empty($row['is_read']) || $row['is_read'] == 0) {
                $newCount++;
            }
        }
        ?>
      <!-- Notifications -->

<section id="notifications" class="mt-5">
  <h4>Notifications</h4>

  <?php if ($newCount > 0): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="fas fa-bell me-2"></i>
      You have <strong><?php echo $newCount; ?></strong> new notification<?php echo $newCount>1?'s':''; ?>.
      <a href="notifications.php" class="alert-link">Open your inbox</a> to read them.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <p class="text-muted">Here are your latest notifications:</p>

  <ul class="list-group">
    <?php if (!empty($notifications)): ?>
      <?php foreach ($notifications as $n): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <?php echo htmlspecialchars($n['message']); ?>
            <small class="text-muted d-block">
              <?php echo date('Y-m-d H:i', strtotime($n['date_sent'])); ?>
            </small>
          </span>
          <?php if (empty($n['is_read'])): ?>
            <a href="../includes/notification_open.php?id=<?php echo (int)$n['notification_id']; ?>" 
               class="btn btn-sm btn-outline-primary">
               <i class="fas fa-envelope-open"></i> Mark Open
            </a>
          <?php else: ?>
            <span class="badge bg-success">Read</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li class="list-group-item text-muted text-center">No notifications yet.</li>
    <?php endif; ?>
  </ul>
</section>

    </main>
  </div>
</div>

</body>
</html>
