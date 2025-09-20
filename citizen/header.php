<?php
session_start();
require_once '../includes/fn.inc.php';

// Only allow citizens here
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Citizen') {
    header('Location: ../login.html');
    exit;
}
$user_id = getUserID ($_SESSION['email']);
?>
<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <!-- Logo + Title -->
    <a class="navbar-brand d-flex align-items-center" href="citizen_dashboard.php">
      <img src="../images/Coat_of_arms_of_Lesotho.svg" alt="Lesotho Logo" height="40" class="me-2">
      <span class="fw-bold">Lesotho Parliamentary Feedback</span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Items -->
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="feedback_submit.php">
            <i class="fas fa-paper-plane me-1"></i> Feedback 
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="suggestions.php">
            <i class="fas fa-lightbulb me-1"></i> Suggestions & Voting
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="tracking.php">
            <i class="fas fa-tasks me-1"></i> Track
          </a>
        </li>
        <?php
            $user_id = getUserID($_SESSION['email']);
            $countSql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id=? AND is_read=0";
            $stmt = $conn->prepare($countSql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $unread = $stmt->get_result()->fetch_assoc()['unread'];
        ?>
        <li class="nav-item">
            <a class="nav-link" href="notifications.php">
                <i class="fas fa-bell me-1"></i> <?php if ($unread > 0): ?>
                        <sup class="badge bg-danger rounded-pill">
                            <?= $unread; ?>
                        </sup>
                    <?php endif; ?> Notifications
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
                <a class="dropdown-item" href="profile.php">
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
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="../login.html">
              <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
