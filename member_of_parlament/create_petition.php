<?php

require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php'; 
include 'header.php';

// Only allow MPs
if (!isset($_SESSION['email']) || roleByEmail($_SESSION['email']) !== 'Parliamentarian') {
    header('Location: ../login.html');
    exit;
}
$mp_id = getUserID($_SESSION['email']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO petitions (mp_id , title, description) VALUES ('$mp_id', '$title', '$description')";
    if (mysqli_query($conn, $sql)) {
        $success = "✅ Petition added successfully!";
        $petition_title = $_POST['title'];
        $mp_name = getUsername($_SESSION['email']);
        $message = "A new petition has been created: \"$petition_title\" by $mp_name.";
        $type = "petition";

        // Notify all citizens
        $result = $conn->query("SELECT user_id FROM users WHERE role_id=1"); // assuming role_id 1 = Citizen, 2 = Member of Parliament
        while ($row = $result->fetch_assoc()) {
            sendNotification($conn, $row['user_id'], $message, $type);
        }
        // Notify all citizens
        $result = $conn->query("SELECT user_id FROM users WHERE role_id=2"); // assuming role_id 1 = Citizen, 2 = Member of Parliament
        while ($row = $result->fetch_assoc()) {
            sendNotification($conn, $row['user_id'], $message, $type);
        }
        $result = $conn->query("SELECT user_id FROM users WHERE role_id=3"); // assuming role_id 1 = Citizen, 2 = Member of Parliament
        while ($row = $result->fetch_assoc()) {
            sendNotification($conn, $row['user_id'], $message, $type);
        }

    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<?php //include 'sidebar.php'; // your improved sidebar/navbar ?>
<br>
<div class="container-fluid px-4">
  

  <!-- Alert Messages -->
  <?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo $success; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Petition Form Card -->
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-lg">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Petition</h5>
        </div>
        <div class="card-body p-4">
          <form method="POST" action="">
            <div class="mb-3">
              <label for="title" class="form-label fw-semibold">Petition Title</label>
              <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Enter a clear, short title" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea name="description" id="description" class="form-control" rows="6" placeholder="Describe the petition details, goals, and why citizens should sign…" required></textarea>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-success btn-lg px-4">
                <i class="fas fa-paper-plane me-1"></i> Submit Petition
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
