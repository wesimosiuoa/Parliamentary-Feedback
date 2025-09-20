<?php
require_once '../includes/dbcon.inc.php';
include 'header.php';
require_once '../includes/fn.inc.php';

// Logged in MP
$mp_id = getUserID($_SESSION['email']);

// 1. Counts by status
$status_sql = "SELECT status, COUNT(*) AS total 
               FROM petitions WHERE mp_id = ? 
               GROUP BY status";
$status_stmt = $conn->prepare($status_sql);
$status_stmt->bind_param("i", $mp_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$status_counts = [];
while ($row = $status_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['total'];
}

// 2. Most signed petitions (only once)
$top_sql = "
SELECT p.petition_id, p.title, COUNT(ps.signature_id) AS total_signatures 
FROM petitions p
LEFT JOIN petition_signatures ps ON ps.petition_id = p.petition_id
WHERE p.mp_id = ?
GROUP BY p.petition_id, p.title
ORDER BY total_signatures DESC LIMIT 5";
$top_stmt = $conn->prepare($top_sql);
$top_stmt->bind_param("i", $mp_id);
$top_stmt->execute();
$top_result = $top_stmt->get_result();

// Build arrays for chart and keep rows for table
$labels = [];
$values = [];
$top_rows = [];
while ($row = $top_result->fetch_assoc()) {
    $top_rows[] = $row;
    $labels[] = $row['title'];
    $values[] = $row['total_signatures'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Petition Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📊 Petition Reports</h2>

  <a href="generate_petition_pdf.php" class="btn btn-danger mb-3">
    Download Full Report PDF
  </a>

  <!-- Quick table with PDF links -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Top 5 Most Signed Petitions</h5>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>Title</th><th>Signatures</th><th>PDF</th></tr></thead>
          <tbody>
          <?php if (count($top_rows) === 0): ?>
            <tr><td colspan="3" class="text-center text-muted">No petitions found.</td></tr>
          <?php else: ?>
            <?php foreach($top_rows as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['total_signatures'] ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary"
                     href="generate_petition_pdf.php?petition_id=<?= $row['petition_id'] ?>">
                    Download PDF
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Status Cards -->
  <div class="row mb-4">
    <?php foreach(['Open','Closed','Draft'] as $st): ?>
      <div class="col-md-4">
        <div class="card shadow-sm text-center">
          <div class="card-body">
            <h5 class="card-title"><?= $st ?> Petitions</h5>
            <h2 class="display-6"><?= $status_counts[$st] ?? 0 ?></h2>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Signatures by Petition</h5>
          <canvas id="topPetitionsChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Status Breakdown</h5>
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
const ctx1 = document.getElementById('topPetitionsChart');
const ctx2 = document.getElementById('statusChart');

// PHP arrays for chart
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(ctx1, {
  type: 'bar',
  data: {
    labels: labels,
    datasets:[{
      label:'Signatures',
      data: values,
      backgroundColor:'rgba(54,162,235,0.5)'
    }]
  }
});

new Chart(ctx2, {
  type: 'pie',
  data: {
    labels:['Open','Closed','Draft'],
    datasets:[{
      data:[
        <?= $status_counts['Open']??0 ?>,
        <?= $status_counts['Closed']??0 ?>,
        <?= $status_counts['Draft']??0 ?>
      ],
      backgroundColor:['#0d6efd','#198754','#6c757d']
    }]
  }
});
</script>
</body>
</html>
