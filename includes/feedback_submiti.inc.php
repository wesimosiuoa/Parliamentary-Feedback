<?php 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'dbcon.inc.php';
        include 'fn.inc.php';
        include 'message.php';

        session_start();

        $username = $_SESSION['email'] ?? null;
        $subject = mysqli_real_escape_string($conn, $_POST['subject']) ?? '';
        $message = mysqli_real_escape_string($conn, $_POST['message']) ?? '';
        $agenda_item_id = (int)($_POST['agenda_item_id'] ?? 0);

        if (!$username) {
            flashMessage('error', 'You must be logged in to submit feedback.', '../login.html', 2);
            exit;
        }

        if (empty($subject) || empty($message)) {
            flashMessage('error', 'Subject and message cannot be empty.', '../citizen/dashboard.php#submit', 2);
            exit;
        }
        
        // Prepare
        $stmt = $conn->prepare("
            

            INSERT INTO `feedback` (`feedback_id`, `user_id`, `subject`, `text`, `date_submitted`, `status`, `agenda_item_id`) 
            VALUES (NULL, ?, ?, ?, current_timestamp(), ?, ?);
        ");

        // Get user_id and status
        $user_id = getUserID($username); // your own function
        $status  = 'Pending';            // or whatever default

        // Bind all four params (i = int, s = string, s = string, s = string)
        $stmt->bind_param("isssi", $user_id, $subject, $message, $status, $agenda_item_id);

        // Execute
       try{

        if ($stmt->execute()) {
            flashMessage('success', 'Feedback submitted successfully!', 2);
        } else {
            flashMessage('error', 'Feedback not submitted successfully!', 2);
        }

       }catch (Exception $e) {
        error_log("Error submitting feedback: " . $e->getMessage());
        flashMessage('error', 'An error occurred while submitting feedback. Please try again. ' . $e-> getMessage(), '../citizen/dashboard.php#submit', 2);
        throw $e; // Re-throw the exception after logging it
         }


        if ($stmt->execute()) {
            flashMessage('success', 'Feedback submitted successfully!', '../citizen/dashboard.php#submit', 2);
        } else {
            flashMessage('error', 'Failed to submit feedback. Please try again.', '../citizen/dashboard.php#submit', 2);
        }

        $stmt->close();
        $conn->close();
    } else {
        header('Location: ../index.php');
        exit;

    }
?>