<?php
include 'message.php';
require 'fn.inc.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string ($conn, $_POST['email']) ?? '';
    $password = mysqli_real_escape_string ($conn, $_POST['password']) ?? '';
    
    if ($email === 'admin@1234.com' && $password === '1234') {
        $role = 'Admin';
        $_SESSION['email'] = $email;
        flashMessage('success', 'Welcome Admin! Redirecting...', '../admin/dashboard.php', 2);

    }
    else {
        $user = login($email, $password);

        if ($user) {
            // Now $user is a row, session variables are set inside login()
            $role = roleByEmail($email);
            //echo 'User role: ' . $role; // Debug line to check the role
            //flashMessage('success', 'Login successful! Redirecting... for user '.$_SESSION['email'] .' with role: '.$role, );
            switch ($role) {
                case 'Citizen': 
                    flashMessage('success', 'Welcome Citizen! Redirecting...', '../citizen/dashboard.php', 2);
                    break;
                case 'Admin':
                    flashMessage('success', 'Welcome Admin! Redirecting...', '../admin/dashboard.php', 2);
                    break;
                case 'Parliamentarian':
                    flashMessage('success', 'Welcome Honourable Member! Redirecting...', '../member_of_parlament/dashboard.php', 2);
                    break;
                default: // Citizen or any other role
                    flashMessage('success', 'We dont have this role ! Redirecting...', '../index.php', 3);
                    break;
            }   
            
        } else {
            flashMessage('error', 'Invalid email or password.', '../login.html', 2);
        }
    }


    
}

?>
