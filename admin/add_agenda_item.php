<?php
// agenda_items.php
require_once '../includes/dbcon.inc.php';

// -------------------- POST handlers (Add / Update / Delete) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- ADD ---
    if (isset($_POST['add'])) {
        $order_paper_id = intval($_POST['order_paper_id']);
        $item_number = intval($_POST['item_number']);
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $presented_by = $_POST['presented_by'] === '' ? null : intval($_POST['presented_by']);

        $stmt = $conn->prepare("INSERT INTO agenda_items (order_paper_id, item_number, title, description, status, presented_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $order_paper_id, $item_number, $title, $description, $status, $presented_by);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=added");
            exit;
        } else {
            $error = $stmt->error;
        }
    }

    // --- UPDATE ---
    if (isset($_POST['update'])) {
        $id = intval($_POST['edit_id']);
        $order_paper_id = intval($_POST['edit_order_paper_id']);
        $item_number = intval($_POST['item_number']);
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $presented_by = $_POST['presented_by'] === '' ? null : intval($_POST['presented_by']);

        $stmt = $conn->prepare("UPDATE agenda_items SET order_paper_id=?, item_number=?, title=?, description=?, status=?, presented_by=? WHERE agenda_item_id=?");
        $stmt->bind_param("iisssii", $order_paper_id, $item_number, $title, $description, $status, $presented_by, $id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=updated");
            exit;
        } else {
            $error = $stmt->error;
        }
    }

    // --- DELETE ---
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM agenda_items WHERE agenda_item_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
            exit;
        } else {
            $error = $stmt->error;
        }
    }
}

// -------------------- Fetch data for page --------------------
include 'header.php'; // include AFTER possible header() redirects above

// order papers (for Add/Edit select)
$orderPapers = $conn->query("SELECT order_paper_id, title FROM order_papers ORDER BY date DESC");

// parliamentarians (presenters)
$parliamentarians = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role_id = 2 ORDER BY first_name, last_name");

// agenda items with presenter name (LEFT JOIN because presenter may be NULL)
$sql = "SELECT ai.*, op.title AS order_paper_title, u.user_id AS presenter_id, CONCAT(u.first_name, ' ', u.last_name) AS presenter_name
        FROM agenda_items ai
        JOIN order_papers op ON ai.order_paper_id = op.order_paper_id
        LEFT JOIN users u ON ai.presented_by = u.user_id
        ORDER BY ai.created_at DESC";
$agendaItems = $conn->query($sql);

// friendly message from redirects
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Agenda Items Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Agenda Items</h3>
    <!-- Add button opens Add Modal -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Agenda Item</button>
  </div>

  <?php if ($msg === 'added'): ?>
    <div class="alert alert-success">Agenda item added.</div>
  <?php elseif ($msg === 'updated'): ?>
    <div class="alert alert-success">Agenda item updated.</div>
  <?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success">Agenda item deleted.</div>
  <?php elseif (!empty($error)): ?>
    <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header">Latest Agenda Items</div>
    <div class="card-body">
      <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Order Paper</th>
            <th>Item No.</th>
            <th>Title</th>
            <th>Presenter</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($agendaItems && $agendaItems->num_rows > 0): ?>
            <?php while ($row = $agendaItems->fetch_assoc()): ?>
              <?php
                // safely prepare data- attributes
                $data_orderpaper_title = htmlspecialchars($row['order_paper_title'], ENT_QUOTES);
                $data_itemnumber = htmlspecialchars($row['item_number'], ENT_QUOTES);
                $data_title = htmlspecialchars($row['title'], ENT_QUOTES);
                $data_description = htmlspecialchars($row['description'], ENT_QUOTES);
                $data_status = htmlspecialchars($row['status'], ENT_QUOTES);
                $data_presenter = $row['presenter_id'] ? intval($row['presenter_id']) : '';
                $presenter_name = $row['presenter_name'] ? htmlspecialchars($row['presenter_name']) : '—';
              ?>
              <tr>
                <td><?= $row['agenda_item_id'] ?></td>
                <td><?= $data_orderpaper_title ?></td>
                <td><?= $row['item_number'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $presenter_name ?></td>
                <td>
                  <span class="badge <?= $row['status'] === 'Pending' ? 'bg-warning' : ($row['status']==='Discussed' ? 'bg-success' : 'bg-secondary') ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                <td class="text-end">
                  <!-- View button sends the fields to the View modal -->
                  <button
                    class="btn btn-sm btn-outline-primary me-1 view-btn"
                    data-bs-toggle="modal" data-bs-target="#viewModal"
                    data-orderpaper="<?= $data_orderpaper_title ?>"
                    data-itemnumber="<?= $data_itemnumber ?>"
                    data-title="<?= $data_title ?>"
                    data-description="<?= $data_description ?>"
                    data-status="<?= $data_status ?>"
                    data-presenter-name="<?= htmlspecialchars($presenter_name, ENT_QUOTES) ?>"
                  >View</button>

                  <!-- Edit button (includes ids for selects) -->
                  <button
                    class="btn btn-sm btn-outline-warning me-1 edit-btn"
                    data-bs-toggle="modal" data-bs-target="#editModal"
                    data-id="<?= $row['agenda_item_id'] ?>"
                    data-orderpaperid="<?= $row['order_paper_id'] ?>"
                    data-itemnumber="<?= $data_itemnumber ?>"
                    data-title="<?= $data_title ?>"
                    data-description="<?= $data_description ?>"
                    data-status="<?= $data_status ?>"
                    data-presenter="<?= $data_presenter ?>"
                  >Edit</button>

                  <!-- Delete form -->
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this agenda item?');">
                    <input type="hidden" name="delete_id" value="<?= $row['agenda_item_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">No agenda items found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<!-- ---------------------- Add Modal ---------------------- -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add Agenda Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Order Paper</label>
            <select name="order_paper_id" class="form-select" required>
              <option value="">Select Order Paper</option>
              <?php if ($orderPapers && $orderPapers->num_rows): while ($op = $orderPapers->fetch_assoc()): ?>
                <option value="<?= $op['order_paper_id'] ?>"><?= htmlspecialchars($op['title']) ?></option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Item Number</label>
            <input type="number" name="item_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Presenter (Presented By)</label>
            <select name="presented_by" class="form-select">
              <option value="">— Select presenter —</option>
              <?php if ($parliamentarians && $parliamentarians->num_rows): while ($p = $parliamentarians->fetch_assoc()): ?>
                <option value="<?= $p['user_id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="Pending">Pending</option>
              <option value="Discussed">Discussed</option>
              <option value="Deferred">Deferred</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-success">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ---------------------- View Modal ---------------------- -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agenda Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Order Paper:</strong> <span id="vOrderPaper"></span></p>
        <p><strong>Item Number:</strong> <span id="vItemNumber"></span></p>
        <p><strong>Title:</strong> <span id="vTitle"></span></p>
        <p><strong>Description:</strong><br><span id="vDescription"></span></p>
        <p><strong>Presenter:</strong> <span id="vPresenter"></span></p>
        <p><strong>Status:</strong> <span id="vStatus"></span></p>
      </div>
    </div>
  </div>
</div>

<!-- ---------------------- Edit Modal ---------------------- -->
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
            <label class="form-label">Order Paper</label>
            <select name="edit_order_paper_id" id="editOrderPaper" class="form-select" required>
              <option value="">Select Order Paper</option>
              <?php
                // We need order papers again for the edit select: re-run query
                $orderPapersForEdit = $conn->query("SELECT order_paper_id, title FROM order_papers ORDER BY date DESC");
                if ($orderPapersForEdit && $orderPapersForEdit->num_rows) {
                  while ($op2 = $orderPapersForEdit->fetch_assoc()) {
                    echo "<option value='" . $op2['order_paper_id'] . "'>" . htmlspecialchars($op2['title']) . "</option>";
                  }
                }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Item Number</label>
            <input type="number" name="item_number" id="editItemNumber" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" id="editTitle" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="editDescription" class="form-control" rows="4"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Presenter (Presented By)</label>
            <select name="presented_by" id="editPresenter" class="form-select">
              <option value="">— Select presenter —</option>
              <?php
                // repopulate parliamentarians for edit select
                $parlForEdit = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role_id = 2 ORDER BY first_name, last_name");
                if ($parlForEdit && $parlForEdit->num_rows) {
                  while ($pp = $parlForEdit->fetch_assoc()) {
                    echo "<option value='" . $pp['user_id'] . "'>" . htmlspecialchars($pp['first_name'] . ' ' . $pp['last_name']) . "</option>";
                  }
                }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editStatus" class="form-select" required>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // VIEW modal population
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('vOrderPaper').textContent = btn.dataset.orderpaper || '—';
      document.getElementById('vItemNumber').textContent = btn.dataset.itemnumber || '—';
      document.getElementById('vTitle').textContent = btn.dataset.title || '—';
      document.getElementById('vDescription').textContent = btn.dataset.description || '';
      document.getElementById('vPresenter').textContent = btn.dataset.presenterName || '—';
      document.getElementById('vStatus').textContent = btn.dataset.status || '—';
    });
  });

  // EDIT modal population
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editId').value = btn.dataset.id || '';
      // select order paper
      const orderPaperSel = document.getElementById('editOrderPaper');
      if (orderPaperSel) orderPaperSel.value = btn.dataset.orderpaperid || '';
      document.getElementById('editItemNumber').value = btn.dataset.itemnumber || '';
      document.getElementById('editTitle').value = btn.dataset.title || '';
      document.getElementById('editDescription').value = btn.dataset.description || '';
      document.getElementById('editStatus').value = btn.dataset.status || 'Pending';
      document.getElementById('editPresenter').value = btn.dataset.presenter || '';
    });
  });
});
</script>
</body>
</html>
