<?php
include 'header.php';
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

$mp_id = getUserID($_SESSION['email']);

// Get petition ID from URL
$petition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch petition details
$sql = "SELECT * FROM petitions WHERE petition_id = ? AND mp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $petition_id, $mp_id);
$stmt->execute();
$petition = $stmt->get_result()->fetch_assoc();

if (!$petition) {
    echo "<div class='alert alert-danger'>Petition not found.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>View Petition</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Petition Details</h5>
            <a href="mp_petitions.php" class="btn btn-light btn-sm"><i class="bi bi-x-circle me-1"></i> Close</a>
        </div>
        <div class="card-body">
            <h4 class="mb-3"><?php echo htmlspecialchars($petition['title']); ?></h4>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($petition['description']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge <?php echo $petition['status']=='Published'?'bg-success':($petition['status']=='Draft'?'bg-warning':'bg-secondary'); ?>">
                    <?php echo htmlspecialchars($petition['status']); ?>
                </span>
            </p>
            <p><strong>Created At:</strong> <?php echo date('Y-m-d', strtotime($petition['created_at'])); ?></p>
            <hr>
            <p><?php echo nl2br(htmlspecialchars($petition['description'])); ?></p>

            <!-- Close Petition button -->
            <?php if($petition['status'] !== 'Closed'): ?>
            <form method="POST" action="close_petition.php">
                <input type="hidden" name="petition_id" value="<?php echo $petition['petition_id']; ?>">
                <button type="submit" class="btn btn-danger mt-3">
                    <i class="bi bi-x-circle me-1"></i> Close Petition
                </button>
            </form>
            <?php else: ?>
            <span class="text-danger">This petition is closed.</span>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
