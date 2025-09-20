<?php
// delete_order_paper.php
require_once '../includes/dbcon.inc.php'; // DB connection

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM order_papers WHERE order_paper_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // success: redirect back with message
        header("Location: order_papers.php?msg=deleted");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: order_papers.php");
    exit;
}
?>
