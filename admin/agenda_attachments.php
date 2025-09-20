<?php
// agenda_attachments.php
require_once '../includes/dbcon.inc.php';

// ensure upload dir exists
$uploadDir = __DIR__ . "/uploads/attachments/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $agenda_item_id = intval($_POST['agenda_item_id']);
    if (!empty($_FILES['attachment']['name'])) {
        $fileName = basename($_FILES['attachment']['name']);
        $targetPath = $uploadDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
            // save in DB (store relative path for download)
            $relativePath = "uploads/attachments/" . basename($targetPath);
            $stmt = $conn->prepare("INSERT INTO attachments (agenda_item_id, file_name, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $agenda_item_id, $fileName, $relativePath);
            $stmt->execute();
            header("Location: " . $_SERVER['PHP_SELF'] . "?agenda_item_id=$agenda_item_id&msg=uploaded");
            exit;
        } else {
            $error = "Upload failed.";
        }
    } else {
        $error = "No file selected.";
    }
}

// handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    // get path then delete record
    $res = $conn->query("SELECT file_path, agenda_item_id FROM agenda_attachments WHERE attachment_id=$id");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $agenda_item_id = $row['agenda_item_id'];
        $filePath = __DIR__ . "/" . $row['file_path'];
        if (file_exists($filePath)) unlink($filePath);
        $conn->query("DELETE FROM agenda_attachments WHERE attachment_id=$id");
        header("Location: " . $_SERVER['PHP_SELF'] . "?agenda_item_id=$agenda_item_id&msg=deleted");
        exit;
    }
}

// fetch agenda items for dropdown
$agendaItems = $conn->query("SELECT agenda_item_id, title FROM agenda_items ORDER BY agenda_item_id DESC");

// filter attachments by agenda_item_id if passed
$where = '';
if (!empty($_GET['agenda_item_id'])) {
    $aid = intval($_GET['agenda_item_id']);
    $where = "WHERE a.agenda_item_id=$aid";
}
$sql = "SELECT a.*, ai.title AS agenda_title
        FROM attachments a
        JOIN agenda_items ai ON a.agenda_item_id=ai.agenda_item_id
        $where
        ORDER BY uploaded_at DESC";
$attachments = $conn->query($sql);

include 'header.php';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agenda Attachments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Agenda Attachments</h3>
    <!-- Add/Upload button -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">+ Upload Attachment</button>
  </div>

  <?php if ($msg==='uploaded'): ?>
    <div class="alert alert-success">Attachment uploaded successfully.</div>
  <?php elseif ($msg==='deleted'): ?>
    <div class="alert alert-success">Attachment deleted.</div>
  <?php elseif (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header">All Attachments</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Agenda Item</th>
              <th>File</th>
              <th>Uploaded At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($attachments && $attachments->num_rows): ?>
              <?php while ($row = $attachments->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['attachment_id'] ?></td>
                  <td><?= htmlspecialchars($row['agenda_title']) ?></td>
                  <td>
                    <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                      <?= htmlspecialchars($row['file_name']) ?>
                    </a>
                  </td>
                  <td><?= $row['uploaded_at'] ?></td>
                  <td class="text-end">
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this file?');">
                      <input type="hidden" name="delete_id" value="<?= $row['attachment_id'] ?>">
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5">No attachments found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Upload Attachment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Agenda Item</label>
            <select name="agenda_item_id" class="form-select" required>
              <option value="">Select Agenda Item</option>
              <?php if ($agendaItems && $agendaItems->num_rows): while ($ai=$agendaItems->fetch_assoc()): ?>
                <option value="<?= $ai['agenda_item_id'] ?>"><?= htmlspecialchars($ai['title']) ?></option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">File</label>
            <input type="file" name="attachment" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="upload" class="btn btn-success">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
