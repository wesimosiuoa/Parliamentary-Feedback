<?php
session_start();
require_once '../includes/fn.inc.php';
include '../includes/message.php';
// flashMessage('error', 'MP '.$_SESSION['email']); // Display any flash messages
// Only allow MPs here
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Parliamentarian') {
    header('Location: ../login.html');
    exit;
}
$user_id = getUserID($_SESSION['email']);
?>
<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- MP Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <!-- Logo + Title -->
    <a class="navbar-brand d-flex align-items-center" href="mp_dashboard.php">
      <img src="../images/Coat_of_arms_of_Lesotho.svg" alt="Lesotho Logo" height="40" class="me-2">
      <span class="fw-bold">MP Panel</span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mpNavbar" aria-controls="mpNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Items -->
    <div class="collapse navbar-collapse" id="mpNavbar">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="mp_petitions.php">
            <i class="fas fa-file-signature me-1"></i> Petition
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="mp_signed_petitions.php">
            <i class="fas fa-users me-1"></i> View Signed Petitions
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="mp_suggestions.php">
            <i class="fas fa-lightbulb me-1"></i> Suggestions
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="notifications.php">
            <i class="fas fa-bell me-1"></i>
            <sup style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.85em;">
              <?php echo getNumberOfNotifications(getUserID($_SESSION['email'])) ?>
            </sup>
            Notifications
            </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="petition_report.php">
            <i class="fas fa-chart-line me-1"></i> Reports
          </a>
        </li>

        <!-- User dropdown -->
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user-circle me-1"></i>
              <?php echo htmlspecialchars(getUsername($_SESSION['email'])); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
              <li>
                <a class="dropdown-item" href="mp_profile.php">
                  <i class="fas fa-user me-1"></i> Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item text-danger" href="../logout.php">
                  <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
