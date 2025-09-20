<?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include '../includes/dbcon.inc.php';
        include '../includes/fn.inc.php';
        include '../includes/message.php';
        session_start();

        if (!isset($_SESSION['email'])) {
            // User is not logged in, redirect to login page
            header('Location: ../login.html');
            exit;
        }

        $user_id = getUserID($_SESSION['email']);
        $content = str_replace("\r\n", "\n", mysqli_real_escape_string($conn, $_POST['content']) ?? '');
        $content = trim($content);
        $agenda_item_id = (int)($_POST['agenda_item_id'] ?? 0);

        $suggestion = $content ?? '';
        

        if (empty($suggestion)) {
            flashMessage('error', 'Suggestion cannot be empty.', 2);
            exit;
        }

       // Example values:
        $user_id    = getUserID($_SESSION['email']);
        $suggestion = mysqli_real_escape_string($conn, $_POST['content']) ?? '';    // or wherever it comes from
        $votes      = 0;                    // initial votes
        $status     = 'Pending';            // initial status

    // Prepare
    $stmt = $conn->prepare("

        INSERT INTO `suggestions` (`suggestion_id`, `user_id`, `content`, `votes`, `status`, `date_posted`, `agenda_item_id`) 
        VALUES (NULL, ?, ?, ?, ?, current_timestamp(), ?);
        
    ");

    // Bind: i = int, s = string, i = int, s = string
    $stmt->bind_param("isisi", $user_id, $suggestion, $votes, $status, $agenda_item_id);

    // Execute
    try {

        if ($stmt->execute()) {
        flashMessage('success', 'Suggestion submitted successfully!', '../citizen/dashboard.php',  2);
        } else {
            flashMessage('error', 'Failed to submit suggestion. Please try again.', '../citizen.dashboard.php',  2);
        
        }
    }
    catch (Exception $e) {
        error_log("Error submitting suggestion: " . $e->getMessage());
        flashMessage('error', 'An error occurred while submitting suggestion. Please try again. ' . $e-> getMessage(), '../citizen/dashboard.php', 2);
        throw $e; // Re-throw the exception after logging it
    }


       

        $stmt->close();
        $conn->close();
    } else {
        // If not a POST request, redirect to dashboard
        header('Location: ../citizen/dashboard.php');
        exit;
    }
?>