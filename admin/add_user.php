<?php
// session_start();
require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php'; // your DB connection file
include '../includes/message.php';
include 'header.php'; // header code from previous step

// Only allow Admin
// if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Admin') {
//     header('Location: ../login.html');
//     exit;
// }



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];



    if ($first_name && $last_name && $email && $password && $role_id) {
        
                // Check if email already exists
        $existingUser = getUserByEmail($email);
        if ($existingUser) {
            flashMessage('error', 'Email is already registered.', '../dashboard.php', 2);
            exit();
        }

        // insert new user
        add_user($first_name, $last_name, $email, $password, $role_id);
        flashMessage('success', 'Registration successful! You can now log in.', '../admin/dashboard.php', 2);
        exit();
        
    } else {
        flashMessage('success', 'Registration successful! You can now log in.', '../add_user.php', 2);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<?php //include 'admin_header.php'; ?>

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-user-plus me-2 text-primary"></i>Add New User</h2>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role_id" class="form-select" required>
              <option value="">Select Role</option>
              <option value="2">Member of Parliament</option>
              <option value="3">Admin</option>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save User
          </button>
          <a href="manage_users.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
