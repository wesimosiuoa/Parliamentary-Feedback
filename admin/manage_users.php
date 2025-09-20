<?php
require_once '../includes/fn.inc.php';
require_once '../includes/dbcon.inc.php';
include 'header.php';

// Handle delete if requested
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id !== getUserID($_SESSION['email'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        header('Location: manage_users.php?deleted=1');
        exit;
    } else {
        header('Location: manage_users.php?error=Cannot delete yourself');
        exit;
    }
}

// Fetch users
$result = $conn->query("SELECT user_id, first_name, last_name, email, role_id, created_at FROM users ORDER BY created_at DESC");

function roleName($role_id) {
    switch ($role_id) {
        case 2: return 'Member of Parliament';
        case 3: return 'Admin';
        default: return 'Citizen';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    tr.user-row {cursor:pointer;}
    tr.user-row:hover {background-color:#f8f9fa;}
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-users-cog me-2 text-primary"></i>Manage Users</h2>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">User deleted successfully.</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a href="add_user.php" class="btn btn-primary mb-2">
          <i class="fas fa-user-plus me-1"></i> Add User
        </a>
        <div class="d-flex gap-2 mb-2">
          <!-- Role Filter -->
          <select id="roleFilter" class="form-select">
            <option value="">All Roles</option>
            <option value="Citizen">Citizen</option>
            <option value="Member of Parliament">Member of Parliament</option>
            <option value="Admin">Admin</option>
          </select>
          <!-- Live Search -->
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="usersTable">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="user-row" 
                    data-user-id="<?php echo $row['user_id']; ?>"
                    data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                    data-role="<?php echo roleName($row['role_id']); ?>">
                  <td><?php echo $row['user_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><span class="badge bg-secondary"><?php echo roleName($row['role_id']); ?></span></td>
                  <td><?php echo $row['created_at']; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center">No users found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="userModalLabel"><i class="fas fa-user me-1"></i>User Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="modalName"></span></p>
        <p><strong>Email:</strong> <span id="modalEmail"></span></p>
        <p><strong>Role:</strong> <span id="modalRole" class="badge bg-secondary"></span></p>
      </div>
      <div class="modal-footer justify-content-between">
        <a href="#" id="editLink" class="btn btn-warning">
          <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="#" id="deleteLink" class="btn btn-danger" 
           onclick="return confirm('Are you sure you want to delete this user?');">
          <i class="fas fa-trash me-1"></i> Delete
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Modal logic
const userModal = new bootstrap.Modal(document.getElementById('userModal'));
document.querySelectorAll('tr.user-row').forEach(row => {
  row.addEventListener('click', () => {
    const userId = row.dataset.userId;
    const name = row.dataset.name;
    const email = row.dataset.email;
    const role = row.dataset.role;
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalEmail').textContent = email;
    document.getElementById('modalRole').textContent = role;
    document.getElementById('editLink').href = 'edit_user.php?id=' + userId;
    document.getElementById('deleteLink').href = 'manage_users.php?delete=' + userId;
    userModal.show();
  });
});

// Combined filter logic (search + role)
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');
const rows = document.querySelectorAll('#usersTable tbody tr');

function applyFilters() {
  const searchValue = searchInput.value.toLowerCase();
  const roleValue = roleFilter.value.toLowerCase();
  rows.forEach(row => {
    const rowText = row.innerText.toLowerCase();
    const rowRole = row.dataset.role.toLowerCase();
    const matchesSearch = rowText.indexOf(searchValue) > -1;
    const matchesRole = !roleValue || rowRole === roleValue;
    row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
  });
}

searchInput.addEventListener('keyup', applyFilters);
roleFilter.addEventListener('change', applyFilters);
</script>

</body>
</html>
