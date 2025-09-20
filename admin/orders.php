<?php
// session_start();
require_once '../includes/dbcon.inc.php';
include 'header.php';

// fetch order papers
$orderPapers = $conn->query("SELECT * FROM order_papers ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parliament Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #0d6efd;
      color: #fff;
    }
    .sidebar a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }
    .sidebar a:hover {
      background-color: #0b5ed7;
    }
    .content {
      padding: 20px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="row">
    <!-- Content -->
    <div class="col-md-10 content">
      <h3 class="mb-4">Dashboard</h3>

      <!-- Cards -->
      <div class="row mb-4">
        <!-- Order Papers -->
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Order Papers</h5>
              <p class="card-text display-6">
                <?php 
                  $countOP = $conn->query("SELECT COUNT(*) AS total FROM order_papers")->fetch_assoc();
                  echo $countOP['total'];
                ?>
              </p>
              <a href="order_papers.php">Manage</a>
            </div>
          </div>
        </div>
        <!-- Agenda Items -->
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Agenda Items</h5>
              <p class="card-text display-6">
                <?php 
                  $countAI = $conn->query("SELECT COUNT(*) AS total FROM agenda_items")->fetch_assoc();
                  echo $countAI['total'];
                ?>
              </p>
              <a href="agenda_items.php">Manage</a>
            </div>
          </div>
        </div>
        <!-- Attachments -->
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Attachments</h5>
              <p class="card-text display-6">
                <?php 
                  $countAtt = $conn->query("SELECT COUNT(*) AS total FROM attachments")->fetch_assoc();
                  echo $countAtt['total'];
                ?>
              </p>
              <a href="agenda_attachments.php">Manage</a>
            </div>
          </div>
        </div>
        <!-- Parliamentarians -->
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Parliamentarians</h5>
              <p class="card-text display-6">
                <?php 
                  $countMP = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role_id = 2")->fetch_assoc();
                  echo $countMP['total'];
                ?>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Latest Order Papers Table -->
      <div class="card shadow-sm">
        <div class="card-header">
          Latest Order Papers
        </div>
        <div class="card-body">
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
              if($orderPapers->num_rows > 0){
                while($row = $orderPapers->fetch_assoc()){
                  echo "<tr>
                    <td>{$row['order_paper_id']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['session_number']}</td>
                    <td>{$row['title']}</td>
                    
                    <td>
                      <button 
                        class='btn btn-sm btn-primary viewOrderPaper' 
                        data-id='{$row['order_paper_id']}' 
                        data-title='{$row['title']}'
                        data-date='{$row['date']}'
                        data-session='{$row['session_number']}'
                        
                        
                      >View / Edit</button>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='6'>No order papers found</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="orderPaperModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="update_order_paper.php">
        <div class="modal-header">
          <h5 class="modal-title">Order Paper Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="order_paper_id" id="order_paper_id">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="title">
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" id="date">
          </div>
          <div class="mb-3">
            <label class="form-label">Session Number</label>
            <input type="text" class="form-control" name="session_number" id="session_number">
          </div>
          
         
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-danger" id="deleteOrderPaper">Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const orderModal = new bootstrap.Modal(document.getElementById('orderPaperModal'));

  document.querySelectorAll('.viewOrderPaper').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('order_paper_id').value = btn.dataset.id;
      document.getElementById('title').value = btn.dataset.title;
      document.getElementById('date').value = btn.dataset.date;
      document.getElementById('session_number').value = btn.dataset.session;
      
      
      orderModal.show();
    });
  });

  document.getElementById('deleteOrderPaper').addEventListener('click', () => {
    if(confirm('Are you sure you want to delete this order paper?')){
      const id = document.getElementById('order_paper_id').value;
      window.location.href = 'delete_order_paper.php?id=' + id;
    }
  });
</script>
</body>
</html>
