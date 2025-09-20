<?php
echo 'Logged out successfully.';
session_start();
session_unset();
session_destroy();

header('Location: login.html');

?>

