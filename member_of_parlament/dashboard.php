<?php
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';



$mp_id = getUserID($_SESSION['email']);
$mp_name = getUsername($_SESSION['email']);

$petition_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM petitions WHERE mp_id=?");
$stmt->bind_param("i", $mp_id);
$stmt->execute();
$stmt->bind_result($petition_count);
$stmt->fetch();
$stmt->close();


// quick counts
// feedback count for this MP
$feedback_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id=?");
$stmt->bind_param("i", $mp_id);
$stmt->execute();
$stmt->bind_result($feedback_count);
$stmt->fetch();
$stmt->close();

// petitions count for this MP

$feedback_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback");
$stmt->execute();
$stmt->bind_result($feedback_count);
$stmt->fetch();
$stmt->close();

// suggestions count for this MP (assuming you have a suggestions table)

$suggestion_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM suggestions");
$stmt->execute();
$stmt->bind_result($suggestion_count);
$stmt->fetch();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MP Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-4">
  <h3 class="mb-4">Welcome, <?= htmlspecialchars($mp_name) ?>!</h3>
  
  <div class="row g-4">
    <!-- Card: Feedback -->
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-inbox me-2 text-primary"></i>Feedback</h5>
          <p class="card-text">View and respond to citizen feedback.</p>
          <a href="mp_feedback.php" class="btn btn-outline-primary btn-sm">View Feedback</a>
        </div>
      </div>
    </div>
    <!-- Card: Petitions -->
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-clipboard-list me-2 text-success"></i>Petitions</h5>
          <p class="card-text">Create new petitions or monitor signatures.</p>
          <a href="mp_petitions.php" class="btn btn-outline-success btn-sm">Manage Petitions</a>
        </div>
      </div>
    </div>
    <!-- Card: Reports -->
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-chart-bar me-2 text-warning"></i>Reports</h5>
          <p class="card-text">View statistics about your constituency issues.</p>
          <a href="petition_report.php" class="btn btn-outline-warning btn-sm">View Reports</a>
        </div>
      </div>
    </div>

    <!-- Card: Suggestions -->
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-lightbulb me-2 text-info"></i>Suggestions</h5>
          <p class="card-text">Review and act on citizen suggestions.</p>
          <a href="mp_suggestions.php" class="btn btn-outline-info btn-sm">View Suggestions</a>
        </div>
      </div>
</div>
    <!-- Card: Notifications -->
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-bell me-2 text-danger"></i>Notifications</h5>
          <p class="card-text">Get alerts of new feedback or petitions.</p>
          <a href="notifications.php" class="btn btn-outline-danger btn-sm">View Notifications <sup><?php echo getNumberOfNotifications(getUserId($_SESSION['email']))?></sup></a>
        </div>
      </div>
    </div>
  </div>

  <!-- quick stats row -->
  <div class="row g-3 mt-5">
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-primary">Feedback Received</h5>
          <h2 class="display-6"><?= $feedback_count ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-success">Petitions Created</h5>
          <h2 class="display-6"><?= $petition_count ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-warning">Suggestions</h5>
          <h2 class="display-6"><?= $suggestion_count ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
