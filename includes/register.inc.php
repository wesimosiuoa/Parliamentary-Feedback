<?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require 'dbcon.inc.php';
        require 'fn.inc.php';
        include 'message.php';

        $first_name = mysqli_real_escape_string($conn, $_POST['firstName']) ?? '';
        $last_name = mysqli_real_escape_string($conn, $_POST['lastName']) ?? '';
        $email = mysqli_real_escape_string($conn, $_POST['email']) ?? '';
        $password = mysqli_real_escape_string($conn, $_POST['password']) ?? '';
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirmPassword']) ?? '';

        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
            flashMessage('error', 'All fields are required.', '../register.html', 2);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('error', 'Invalid email format.', '../register.html', 2);
            exit();
        }

        if ($password !== $confirm_password) {
            flashMessage('error', 'Passwords do not match.', '../register.html', 2);
            exit();
        }

        // Check if email already exists
        $existingUser = getUserByEmail($email);
        if ($existingUser) {
            flashMessage('error', 'Email is already registered.', '../register.html', 2);
            exit();
        }

        // insert new user
        add_user ($first_name, $last_name, $email, $password);
        flashMessage('success', 'Registration successful! You can now log in.', '../login.html', 3);
        exit();
        
    } else {
        flashMessage('error', 'Invalid request method.', '../register.html', 2);
        exit();
    }
?>