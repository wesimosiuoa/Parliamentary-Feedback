<?php

require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';
include '../includes/message.php';
include 'header.php'; // header code from previous step

// Must be logged in
// if (!isset($_SESSION['email'])) {
//     header('Location: ../login.html');
//     exit;
// }

$user_id = getUserID($_SESSION['email']);
$id = intval($_GET['id']);
$user = $conn->query("SELECT first_name, last_name, email FROM users WHERE user_id='".$id."';")->fetch_assoc();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // optional

    if ($first_name && $last_name && $email) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password_hash=? WHERE user_id=?");
            $stmt->bind_param('ssssi', $first_name, $last_name, $email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE user_id=?");
            $stmt->bind_param('sssi', $first_name, $last_name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['email'] = $email; // update session if email changed
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    } else {
        $error = "First name, last name, and email are required.";
    }

    // Refresh $user after update
    $user = $conn->query("SELECT first_name, last_name, email FROM users WHERE user_id=$user_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Update Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<?php //include 'admin_header.php'; // or your normal header ?>

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-user-edit me-2 text-primary"></i>Update Profile</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" 
                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Changes
          </button>
          <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
