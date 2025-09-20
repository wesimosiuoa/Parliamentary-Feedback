<?php

require_once '../includes/fn.inc.php';
include 'header.php';

// Only admins allowed
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
    header('Location: ../login.html');
    exit;
}

$email    = $_SESSION['email'];
$user_id  = getUserID($email);
$username = getUsername($email); // your fn.inc.php should have this
$role     = roleByEmail($email);

// Handle form submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize
    $newName = trim($_POST['username']);
    $newPass = trim($_POST['password']);

    // Update name if provided
    if (!empty($newName) && $newName !== $username) {
        if (updateUsername($user_id, $newName)) { // implement in fn.inc.php
            $msg = "Name updated successfully.";
            $username = $newName;
        } else {
            $msg = "Error updating name.";
        }
    }

    // Update password if provided
    if (!empty($newPass)) {
        if (updatePassword($user_id, $newPass)) { // implement in fn.inc.php
            $msg .= " Password updated successfully.";
        } else {
            $msg .= " Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">
  <!-- Reuse your navbar -->
  <?php //include 'navbar.php'; ?> <!-- if you saved the navbar separately -->

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3"><i class="fas fa-user-circle me-1"></i> My Profile</h4>

            <?php if ($msg): ?>
              <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Email (cannot change)</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($role) ?>" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
              </div>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
              </button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
