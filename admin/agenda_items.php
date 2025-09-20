<?php
require_once '../includes/dbcon.inc.php';
include 'header.php';

/* --------- Handle Delete --------- */
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM agenda_items WHERE agenda_item_id=$id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* --------- Handle Update --------- */
if (isset($_POST['update'])) {
    $id = intval($_POST['edit_id']);
    $itemNumber = $conn->real_escape_string($_POST['item_number']);
    $title = $conn->real_escape_string($_POST['title']);
    $status = $conn->real_escape_string($_POST['status']);
    $presentedBy = $conn->real_escape_string($_POST['presented_by']);

    $conn->query("UPDATE agenda_items 
                  SET item_number='$itemNumber', 
                      title='$title', 
                      status='$status',
                      presented_by='$presentedBy'
                  WHERE agenda_item_id=$id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* --------- Fetch Agenda Items --------- */
$sql = "SELECT ai.*, op.title AS order_paper_title 
        FROM agenda_items ai
        JOIN order_papers op ON ai.order_paper_id = op.order_paper_id
        ORDER BY ai.created_at DESC";
$agendaItems = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda Items Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Agenda Items</h3>
    <a href="add_agenda_item.php" class="btn btn-primary">+ Add Agenda Item</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-header">
      Latest Agenda Items
    </div>
    <div class="card-body">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Order Paper</th>
            <th>Item Number</th>
            <th>Title</th>
            <th>Presented By</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($agendaItems->num_rows > 0) {
            while ($row = $agendaItems->fetch_assoc()) {
              echo "<tr>
                <td>{$row['agenda_item_id']}</td>
                <td>{$row['order_paper_title']}</td>
                <td>{$row['item_number']}</td>
                <td>{$row['title']}</td>
                <td>{$row['presented_by']}</td>
                <td>
                  <span class='badge bg-".($row['status']=='Pending'?'warning':($row['status']=='Discussed'?'success':'secondary'))."'>{$row['status']}</span>
                </td>
                <td>
                  <button class='btn btn-sm btn-primary view-btn' 
                    data-bs-toggle='modal' 
                    data-bs-target='#viewModal'
                    data-orderpaper='{$row['order_paper_title']}'
                    data-itemnumber='{$row['item_number']}'
                    data-title='{$row['title']}'
                    data-status='{$row['status']}'
                    data-presentedby='{$row['presented_by']}'>View</button>

                  <button class='btn btn-sm btn-warning edit-btn' 
                    data-bs-toggle='modal' 
                    data-bs-target='#editModal'
                    data-id='{$row['agenda_item_id']}'
                    data-itemnumber='{$row['item_number']}'
                    data-title='{$row['title']}'
                    data-status='{$row['status']}'
                    data-presentedby='{$row['presented_by']}'>Edit</button>

                  <form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this item?');\">
                    <input type='hidden' name='delete_id' value='{$row['agenda_item_id']}'>
                    <button type='submit' class='btn btn-sm btn-danger'>Delete</button>
                  </form>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7'>No agenda items found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agenda Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Order Paper:</strong> <span id="viewOrderPaper"></span></p>
        <p><strong>Item Number:</strong> <span id="viewItemNumber"></span></p>
        <p><strong>Title:</strong> <span id="viewTitle"></span></p>
        <p><strong>Presented By:</strong> <span id="viewPresentedBy"></span></p>
        <p><strong>Status:</strong> <span id="viewStatus"></span></p>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Edit Agenda Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="editId">
          <div class="mb-3">
            <label>Item Number</label>
            <input type="text" name="item_number" id="editItemNumber" class="form-control">
          </div>
          <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" id="editTitle" class="form-control">
          </div>
          <div class="mb-3">
            <label>Presented By</label>
            <input type="text" name="presented_by" id="editPresentedBy" class="form-control">
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" id="editStatus" class="form-control">
              <option value="Pending">Pending</option>
              <option value="Discussed">Discussed</option>
              <option value="Deferred">Deferred</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-success">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // View modal
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('viewOrderPaper').textContent = btn.dataset.orderpaper;
      document.getElementById('viewItemNumber').textContent = btn.dataset.itemnumber;
      document.getElementById('viewTitle').textContent = btn.dataset.title;
      document.getElementById('viewPresentedBy').textContent = btn.dataset.presentedby;
      document.getElementById('viewStatus').textContent = btn.dataset.status;
    });
  });

  // Edit modal
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editId').value = btn.dataset.id;
      document.getElementById('editItemNumber').value = btn.dataset.itemnumber;
      document.getElementById('editTitle').value = btn.dataset.title;
      document.getElementById('editPresentedBy').value = btn.dataset.presentedby;
      document.getElementById('editStatus').value = btn.dataset.status;
    });
  });
});
</script>
</body>
</html>
