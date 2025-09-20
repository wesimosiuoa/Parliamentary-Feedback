<?php
require_once '../includes/dbcon.inc.php';
include 'header.php'; // include the admin header

$msg = "";
$user_id = getUserID($_SESSION['email']) ?: 0;

// Handle Add new Order Paper
if (isset($_POST['add_order_paper'])) {
    $date = $_POST['date'];
    $session_number = $_POST['session_number'];
    $title = $date . ' Order Paper No. ' .$session_number;

    $stmt = $conn->prepare("INSERT INTO order_papers (date, session_number, title) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $date, $session_number, $title);
    if ($stmt->execute()) {
        sendNotification($conn, $user_id, "Order paper posted on " . date("Y-m-d H:i:s"), $link = null, $type = 'general');
        $msg = "Order Paper added successfully!";
    } else {
        $msg = "Error adding Order Paper: " . $conn->error;
    }
    $stmt->close();
}

// Handle Update Order Paper
if (isset($_POST['edit_order_paper'])) {
    $id = $_POST['order_paper_id'];
    $date = $_POST['date'];
    $session_number = $_POST['session_number'];
    $title = $_POST['title'];

    $stmt = $conn->prepare("UPDATE order_papers SET date=?, session_number=?, title=? WHERE order_paper_id=?");
    $stmt->bind_param("sssi", $date, $session_number, $title, $id);
    if ($stmt->execute()) {
        $msg = "Order Paper updated successfully!";
    } else {
        $msg = "Error updating Order Paper: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM order_papers WHERE order_paper_id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Order Paper deleted successfully!";
    } else {
        $msg = "Error deleting: " . $conn->error;
    }
    $stmt->close();
}

// Fetch order papers
$orderPapers = $conn->query("SELECT * FROM order_papers ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Papers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Order Papers</h2>
    <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <!-- Add Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Order Paper</button>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Session</th>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($orderPapers->num_rows > 0) {
                while ($row = $orderPapers->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['order_paper_id']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['session_number']}</td>
                        <td>{$row['title']}</td>
                        <td>
                            <button class='btn btn-sm btn-warning' data-bs-toggle='modal' 
                                data-bs-target='#editModal{$row['order_paper_id']}'>Edit</button>

                            <a href='order_papers.php?delete={$row['order_paper_id']}' 
                               onclick=\"return confirm('Are you sure you want to delete this?')\" 
                               class='btn btn-sm btn-danger'>Delete</a>

                            <a href='generate_order_paper.php?id={$row['order_paper_id']}' 
                               class='btn btn-sm btn-success'>Download</a>
                        </td>
                    </tr>";

                    // Edit Modal for each row
                    echo "<div class='modal fade' id='editModal{$row['order_paper_id']}' tabindex='-1'>
                        <div class='modal-dialog'>
                          <div class='modal-content'>
                            <form method='POST'>
                              <div class='modal-header'>
                                <h5 class='modal-title'>Edit Order Paper</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                              </div>
                              <div class='modal-body'>
                                <input type='hidden' name='order_paper_id' value='{$row['order_paper_id']}'>
                                <div class='mb-3'>
                                  <label class='form-label'>Date</label>
                                  <input type='date' class='form-control' name='date' value='{$row['date']}' required>
                                </div>
                                <div class='mb-3'>
                                  <label class='form-label'>Session Number</label>
                                  <input type='text' class='form-control' name='session_number' value='{$row['session_number']}'>
                                </div>
                                <div class='mb-3'>
                                  <label class='form-label'>Title</label>
                                  <input type='text' class='form-control' name='title' value='{$row['title']}' required>
                                </div>
                              </div>
                              <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                <button type='submit' name='edit_order_paper' class='btn btn-primary'>Update</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>";
                }
            } else {
                echo "<tr><td colspan='5'>No Order Papers found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add Order Paper</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Session Number</label>
            <input type="text" class="form-control" name="session_number">
          </div>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_order_paper" class="btn btn-primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
