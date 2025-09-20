<?php
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $suggestion_id = intval($_POST['suggestion_id']);
    $action = $_POST['action'];

    if (in_array($action, ['Approved', 'Rejected', 'Active'])) {
        $stmt = $conn->prepare("UPDATE suggestions SET status=? WHERE suggestion_id=?");
        $stmt->bind_param("si", $action, $suggestion_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: mp_suggestions.php");
    exit;
}
