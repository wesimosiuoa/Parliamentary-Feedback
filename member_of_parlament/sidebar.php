<!-- Include Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="d-flex">
  <!-- Sidebar -->
  <nav class="sidebar p-3">
    <h4 class="text-white mb-3">Petitions</h4>
    <hr class="bg-light">
    <ul class="nav flex-column">
      <li class="nav-item mb-1">
        <a class="nav-link active d-flex align-items-center" href="mp_petitions.php">
          <i class="bi bi-file-earmark-text me-2"></i> Petitions
        </a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link d-flex align-items-center" href="petition_feedback.php">
          <i class="bi bi-chat-left-text me-2"></i> Feedback
        </a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link d-flex align-items-center" href="mp_reports.php">
          <i class="bi bi-bar-chart me-2"></i> Reports
        </a>
      </li>
    </ul>
    
  </nav>

  <!-- Main Content Wrapper -->
  <div class="content flex-grow-1">
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
  }
  .sidebar {
    width: 240px;
    height: 100vh;
    background-color: #0d6efd; /* blue */
    color: white;
    position: fixed;
    top: 0;
    left: 0;
  }
  .sidebar .nav-link {
    color: white;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
  }
  .sidebar .nav-link.active,
  .sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: .375rem;
  }
  .content {
    margin-left: 240px; /* equal to sidebar width */
    padding: 20px;
    min-height: 100vh;
  }
  .card-stat {
    border-left: 5px solid #0d6efd;
  }
</style>
