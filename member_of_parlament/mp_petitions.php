
<?php 

    include 'header.php';
    require_once '../includes/fn.inc.php';
?>
<?php
require_once '../includes/dbcon.inc.php'; // adjust path to your DB connection

// assuming you have an MP logged in via session
$mp_id = $_SESSION['email'] ?? 0; // or however you store it
$mp = getUserID($mp_id);

$sql = " SELECT p.petition_id, p.title, p.description, p.status, p.created_at, COALESCE(s.total_signatures,0) AS signatures FROM petitions p LEFT JOIN ( SELECT petition_id, COUNT(*) AS total_signatures FROM petition_signatures GROUP BY petition_id ) s ON s.petition_id = p.petition_id WHERE p.mp_id = ? ORDER BY p.created_at DESC ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mp );
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MP Dashboard – Petitions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php 
        // include 'sidebar.php';
    ?>
    <!-- Main content -->
    <main class="col-md-0 ms-sm-auto col-lg-0 content">
      <!-- Topbar -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Petitions Dashboard</h2>
        
        <a href="create_petition.php" class="btn btn-primary">
          <i class="bi bi-plus-circle"></i> New Petition
        </a>
      </div>

      <!-- Quick stats -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card card-stat shadow-sm p-3">
            <h5>Total Petitions</h5>
            <h3><?php echo getNumberOfPetitions(getUserID($_SESSION['email']))?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-stat shadow-sm p-3">
            <h5>Active Petitions</h5>
            <h3><?php 
                echo getNumberOfActivePetitions(getUserID($_SESSION['email']));
            ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-stat shadow-sm p-3">
            <h5>Total Signatures</h5>
            <h3><?php 
                if (getNumberofSignedPetitions(getUserID($_SESSION['email']) < 1)){
                    echo 0;
                } else {
                    echo getNumberofSignedPetitions(getUserID($_SESSION['email']));
                }
            ?></h3>
          </div>
        </div>
      </div>

      <!-- Petitions list -->
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <h5 class="mb-0">My Petitions</h5>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Desc</th>
          <th>Status</th>
          <th>Signatures</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i = 1;
        while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><?= htmlspecialchars($row['title']); ?></td>
            <td><?= htmlspecialchars($row['description']); ?></td>
            <td>
              <?php
                $badge = $row['status']=='Published' ? 'success' : ($row['status']=='Draft'?'warning':'secondary');
              ?>
              <span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($row['status']); ?></span>
            </td>
            <td><?= $row['signatures']; ?></td>
            <td><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
            <td>
              <a href="view_petition.php?id=<?= $row['petition_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
              <a href="edit_petition.php?id=<?= $row['petition_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
