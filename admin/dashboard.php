<?php
// session_start();
require_once '../includes/fn.inc.php';
include 'header.php'; // header code from previous step

//Only allow admins here
// if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
//     header('Location: ../login.html');
//     exit;
// }
// $user_id = getUserID($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .card {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
      transition: transform .2s ease-in-out;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .dashboard-title {
      font-weight: 600;
    }
  </style>
</head>
<body class="bg-light">

<?php //include 'admin_header.php'; // header code from previous step ?>

<div class="container py-4">
  <h2 class="mb-4 dashboard-title"><i class="fas fa-home me-2 text-primary"></i>Admin Dashboard</h2>

  <!-- Top summary cards -->
  <div class="row g-4">
    <div class="col-md-3 col-6">
      <div class="card text-center">
        <div class="card-body">
          <i class="fa-regular fa-file-lines fa-2x text-primary mb-2"></i>
          <h5 class="card-title">Order Papers</h5>
          <p class="display-6"><?php //echo getUsersCount(); ?></p>
          <a href="orders.php" class="btn btn-sm btn-outline-primary">Manage</a>
        </div>
      </div>
      <br>
      <div class="card text-center">
        <div class="card-body">
          <i class="fas fa-users fa-2x text-primary mb-2"></i>
          <h5 class="card-title">Users</h5>
          <p class="display-6"><?php //echo getUsersCount(); ?></p>
          <a href="manage_users.php" class="btn btn-sm btn-outline-primary">Manage</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card text-center">
        <div class="card-body">
          <i class="fas fa-lightbulb fa-2x text-warning mb-2"></i>
          <h5 class="card-title">Suggestions</h5>
          <p class="display-6"><?php //echo getSuggestionsCount(); ?></p>
          <a href="moderate_content.php" class="btn btn-sm btn-outline-warning">Moderate</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card text-center">
        <div class="card-body">
          <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
          <h5 class="card-title">Reports</h5>
          <p class="display-6"><?php //echo getReportsCount(); ?></p>
          <a href="reports.php" class="btn btn-sm btn-outline-success">View</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card text-center">
        <div class="card-body">
          <i class="fas fa-bell fa-2x text-danger mb-2"></i>
          <h5 class="card-title">Notifications</h5>
          <p class="display-6"><?php //echo getUnreadNotificationsCountAdmin(); ?></p>
          <a href="notifications.php" class="btn btn-sm btn-outline-danger">View</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions & Recent Activity -->
  <div class="row g-4 mt-3">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <i class="fas fa-bolt me-1"></i> Quick Actions
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <a href="add_user.php" class="btn btn-outline-primary w-100"><i class="fas fa-user-plus me-1"></i> Add User</a>
            </div>
            <div class="col-md-4">
              <a href="moderate_content.php" class="btn btn-outline-warning w-100"><i class="fas fa-shield-alt me-1"></i> Moderate</a>
            </div>
            <div class="col-md-4">
              <a href="reports.php" class="btn btn-outline-success w-100"><i class="fas fa-file-export me-1"></i> Export Report</a>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-header bg-dark text-white">
          <i class="fas fa-history me-1"></i> Recent Activity
        </div>
        <ul class="list-group list-group-flush">
          <?php //foreach (getRecentAdminActivity() as $activity): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><?php //echo htmlspecialchars($activity['description']); ?></span>
              <span class="text-muted small"><?php //echo $activity['time']; ?></span>
            </li>
          <?php //endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- Right column: System stats or announcements -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header bg-secondary text-white">
          <i class="fas fa-cogs me-1"></i> System Status
        </div>
        <div class="card-body">
          <p><strong>Server Uptime:</strong> <?php echo getServerUptime(); ?></p>
          <p><strong>Active Sessions:</strong> <?php echo getActiveSessionsCount(); ?></p>
          <p><strong>Page Load Time:</strong> <?php echo getPageLoadTime($start_time); ?> ms</p>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-header bg-info text-white">
          <i class="fas fa-bullhorn me-1"></i> Announcements
        </div>
        <ul class="list-group list-group-flush">
          <?php //foreach (getAdminAnnouncements() as $announce): ?>
            <li class="list-group-item"><?php //echo htmlspecialchars($announce['message']); ?></li>
          <?php //endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

</body>
</html>
