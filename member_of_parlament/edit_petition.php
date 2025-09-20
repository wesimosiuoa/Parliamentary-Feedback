<?php

require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

// Only allow MPs
include 'header.php';

$mp_id = getUserID($_SESSION['email']);

// Check petition_id from GET
if (!isset($_GET['id'])) {
    echo "Petition ID missing.";
    exit;
}
$petition_id = (int)$_GET['id'];

// Fetch the petition to edit
$sql = "SELECT petition_id, title, description,  status FROM petitions WHERE petition_id = ? AND mp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $petition_id, $mp_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows < 1) {
    echo "Petition not found or not yours.";
    exit;
}
$petition = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    //$category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_sql = "UPDATE petitions 
                   SET title=?, description=?,  status=?
                   WHERE petition_id=? AND mp_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssii", $title, $description,  $status, $petition_id, $mp_id);
    if ($update_stmt->execute()) {
        $success = "Petition updated successfully!";
        // reload petition values
        $petition['title'] = $title;
        $petition['description'] = $description;
        //$petition['category'] = $category;
        $petition['status'] = $status;

        $petition_title = $_POST['title'];
        $mp_name = getUsername($_SESSION['email']);
        $message = "A petition  : ".$petition_title." has being modified to $status by $mp_name.";
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
        $error = "Error updating petition: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Petition</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-pencil me-1"></i> Edit Petition</h5>
        </div>
        <div class="card-body">
          <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success; ?></div>
          <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label for="title" class="form-label">Petition Title</label>
              <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($petition['title']); ?>" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control" rows="4" required><?= htmlspecialchars($petition['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="Draft" <?= $petition['status']=='Draft'?'selected':''; ?>>Draft</option>
                <option value="Open" <?= $petition['status']=='Open'?'selected':''; ?>>Open</option>
                <option value="Closed" <?= $petition['status']=='Closed'?'selected':''; ?>>Closed</option>
              </select>
            </div>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-save me-1"></i> Save Changes
            </button>
            <a href="mp_petitions.php" class="btn btn-secondary ms-2">Back</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
