<?php
    include 'message.php';
    $suggestion = $_GET['suggestion_id'] ?? null;
    $user_id = $_GET['user_id'] ?? null;
    //echo $suggestion;

    if ($suggestion) {
        session_start();
        require 'dbcon.inc.php';
        require 'fn.inc.php';

        $user_id = getUserID ($_SESSION['email']);

        // Check if the user has already voted for this suggestion
        $checkVoteSql = "SELECT * FROM votes WHERE user_id = ? AND suggestion_id = ?";
        $stmt = $conn->prepare($checkVoteSql);
        $stmt->bind_param('ii', $user_id, $suggestion);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User has already voted
            flashMessage('error', 'You have already voted for this suggestion.', '../citizen/dashboard.php', 3);
            exit();
        } else {
            // Record the vote
            $voteSql = "
                INSERT INTO `votes` (`vote_id`, `suggestion_id`, `user_id`, `date_voted`)
                VALUES (NULL, ?, ?, current_timestamp());
                ";
                $stmt = $conn->prepare($voteSql);
                $stmt->bind_param('ii',$suggestion, $user_id);

            if ($stmt->execute()) {
                // Increment the vote count in suggestions table
                $updateVoteSql = "UPDATE suggestions SET votes = votes + 1 WHERE suggestion_id = ?";
                $stmt = $conn->prepare($updateVoteSql);
                $stmt->bind_param('i', $suggestion);
                $stmt->execute();

                flashMessage('success', 'Your vote has been recorded.', '../citizen/dashboard.php', 3);
                exit();
            } else {
                flashMessage('error', 'Failed to record your vote. Please try again.', '../citizen/dashboard.php', 3);
                exit();
            }
        }
    } else {
        flashMessage('error', 'Invalid suggestion ID.', '../citizen/dashboard.php', 3);
        exit();
    }
?>