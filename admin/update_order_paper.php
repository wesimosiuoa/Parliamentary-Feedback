<?php
// edit_order_paper.php
require_once '../includes/dbcon.inc.php';

if (!isset($_GET['id'])) {
    header("Location: order_papers.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch current order paper data
$stmt = $conn->prepare("SELECT * FROM order_papers WHERE order_paper_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$orderPaper = $result->fetch_assoc();

if (!$orderPaper) {
    echo "Order Paper not found!";
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $session_number = $_POST['session_number'];
    $title = $_POST['title'];
    $details = $_POST['details'];
    $status = $_POST['status'];

    $stmtUpdate = $conn->prepare("UPDATE order_papers 
        SET date=?, session_number=?, title=?, details=?, status=? 
        WHERE order_paper_id=?");
    $stmtUpdate->bind_param("sisssi", $date, $session_number, $title, $details, $status, $id);

    if ($stmtUpdate->execute()) {
        header("Location: order_papers.php?msg=updated");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Order Paper</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-header">Edit Order Paper</div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?php echo $orderPaper['date']; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Session Number</label>
          <input type="number" name="session_number" class="form-control" value="<?php echo $orderPaper['session_number']; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?php echo $orderPaper['title']; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Details</label>
          <textarea name="details" class="form-control" rows="5" required><?php echo $orderPaper['details']; ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-control" required>
            <option value="Draft" <?php if($orderPaper['status']=='Draft') echo 'selected'; ?>>Draft</option>
            <option value="Published" <?php if($orderPaper['status']=='Published') echo 'selected'; ?>>Published</option>
            <option value="Archived" <?php if($orderPaper['status']=='Archived') echo 'selected'; ?>>Archived</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="order_papers.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
