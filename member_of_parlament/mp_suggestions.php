<?php
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';

$mp_name = getUsername($_SESSION['email']);

// Handle filters
$order_by = "s.date_posted DESC"; // default: newest
$where_clause = "";

// Filter by votes
if (isset($_GET['filter'])) {
    switch ($_GET['filter']) {
        case 'votes':
            $order_by = "s.votes DESC";
            break;
        case 'active':
        case 'pending':
        case 'approved':
        case 'rejected':
            $where_clause = "WHERE s.status='" . ucfirst($_GET['filter']) . "'";
            break;
    }
}

// Fetch filtered suggestions
$stmt = $conn->prepare("
    SELECT s.suggestion_id, s.content, s.votes, s.status, s.date_posted, u.first_name, u.last_name
    FROM suggestions s
    JOIN users u ON s.user_id = u.user_id
    $where_clause
    ORDER BY $order_by
");
$stmt->execute();
$result = $stmt->get_result();
$suggestions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MP Suggestions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.table-responsive {
    max-height: 500px;
    overflow-y: auto;
}
</style>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-4">Suggestions Dashboard</h3>

    <!-- Filters -->
    <form method="get" class="mb-3 d-flex gap-2">
        <select name="filter" class="form-select w-auto">
            <option value="">-- Filter Suggestions --</option>
            <option value="newest" <?= (empty($_GET['filter']) || $_GET['filter']=='newest') ? 'selected' : '' ?>>Newest</option>
            <option value="votes" <?= (isset($_GET['filter']) && $_GET['filter']=='votes') ? 'selected' : '' ?>>Highest Votes</option>
            <option value="active" <?= (isset($_GET['filter']) && $_GET['filter']=='active') ? 'selected' : '' ?>>Active</option>
            <option value="pending" <?= (isset($_GET['filter']) && $_GET['filter']=='pending') ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= (isset($_GET['filter']) && $_GET['filter']=='approved') ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= (isset($_GET['filter']) && $_GET['filter']=='rejected') ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark sticky-top">
                <tr>
                    <th>#</th>
                    <th>Citizen</th>
                    <th>Content</th>
                    <th>Votes</th>
                    <th>Status</th>
                    <th>Date Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($suggestions) === 0): ?>
                <tr>
                    <td colspan="7" class="text-center">No suggestions found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($suggestions as $index => $sugg): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($sugg['first_name'] . ' ' . $sugg['last_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($sugg['content'])) ?></td>
                    <td><?= $sugg['votes'] ?></td>
                    <td><?= $sugg['status'] ?></td>
                    <td><?= $sugg['date_posted'] ?></td>
                    <td>
                        <form method="post" action="suggestion_action.php" class="d-flex gap-1">
                            <input type="hidden" name="suggestion_id" value="<?= $sugg['suggestion_id'] ?>">
                            <button type="submit" name="action" value="Approved" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="action" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                            <button type="submit" name="action" value="Active" class="btn btn-primary btn-sm">Active</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
