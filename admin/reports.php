<?php

require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';
include 'header.php';



// --- Counts for cards ---
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalSuggestions = $conn->query("SELECT COUNT(*) AS c FROM suggestions")->fetch_assoc()['c'];
$totalFeedback = $conn->query("SELECT COUNT(*) AS c FROM feedback")->fetch_assoc()['c'];
$totalPetitions = $conn->query("SELECT COUNT(*) AS c FROM petitions")->fetch_assoc()['c'];

// --- Status breakdown for charts ---
$suggStatuses = $conn->query("SELECT status, COUNT(*) AS c FROM suggestions GROUP BY status");
$suggStatusData = [];
while ($row = $suggStatuses->fetch_assoc()) {
  $suggStatusData[$row['status']] = (int)$row['c'];
}

$feedbackStatuses = $conn->query("SELECT status, COUNT(*) AS c FROM feedback GROUP BY status");
$feedbackStatusData = [];
while ($row = $feedbackStatuses->fetch_assoc()) {
  $feedbackStatusData[$row['status']] = (int)$row['c'];
}

$petitionStatuses = $conn->query("SELECT status, COUNT(*) AS c FROM petitions GROUP BY status");
$petitionStatusData = [];
while ($row = $petitionStatuses->fetch_assoc()) {
  $petitionStatusData[$row['status']] = (int)$row['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-container {
      position: relative;
      height: 300px;
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-chart-line me-2 text-primary"></i>Reports</h2>

  <!-- Filter -->
  <div class="row mb-4">
    <div class="col-md-4">
      <input type="date" class="form-control" id="startDate">
    </div>
    <div class="col-md-4">
      <input type="date" class="form-control" id="endDate">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
    </div>
  </div>

  <!-- Cards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Users</h5>
          <h2 class="fw-bold"><?php echo $totalUsers; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Suggestions</h5>
          <h2 class="fw-bold"><?php echo $totalSuggestions; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Feedback</h5>
          <h2 class="fw-bold"><?php echo $totalFeedback; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Petitions</h5>
          <h2 class="fw-bold"><?php echo $totalPetitions; ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">Suggestions Status</div>
        <div class="card-body chart-container">
          <canvas id="suggestionsChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">Feedback Status</div>
        <div class="card-body chart-container">
          <canvas id="feedbackChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">Petitions Status</div>
        <div class="card-body chart-container">
          <canvas id="petitionsChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Buttons -->
  <div class="mt-4 text-end">
  <a href="../includes/export_reports_csv.inc.php" class="btn btn-outline-success">
    <i class="fas fa-file-excel me-1"></i>Export CSV
  </a>
  <a href="../includes/export_reports_pdf.inc.php" class="btn btn-outline-danger">
    <i class="fas fa-file-pdf me-1"></i>Export PDF
  </a>
</div>

</div>

<script>
// Data from PHP
const suggLabels = <?php echo json_encode(array_keys($suggStatusData)); ?>;
const suggData = <?php echo json_encode(array_values($suggStatusData)); ?>;

const feedbackLabels = <?php echo json_encode(array_keys($feedbackStatusData)); ?>;
const feedbackData = <?php echo json_encode(array_values($feedbackStatusData)); ?>;

const petitionLabels = <?php echo json_encode(array_keys($petitionStatusData)); ?>;
const petitionData = <?php echo json_encode(array_values($petitionStatusData)); ?>;

new Chart(document.getElementById('suggestionsChart'), {
  type: 'pie',
  data: {
    labels: suggLabels,
    datasets: [{
      data: suggData,
      backgroundColor: ['#0d6efd','#ffc107','#dc3545','#198754']
    }]
  }
});

new Chart(document.getElementById('feedbackChart'), {
  type: 'pie',
  data: {
    labels: feedbackLabels,
    datasets: [{
      data: feedbackData,
      backgroundColor: ['#0d6efd','#ffc107','#dc3545','#198754']
    }]
  }
});

new Chart(document.getElementById('petitionsChart'), {
  type: 'pie',
  data: {
    labels: petitionLabels,
    datasets: [{
      data: petitionData,
      backgroundColor: ['#0d6efd','#ffc107','#dc3545','#198754']
    }]
  }
});
</script>

</body>
</html>
