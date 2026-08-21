<?php
// includes/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Capture the intended URL
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    // Redirect to login with the redirect parameter
    header("Location: ../auth/login.php?redirect=" . $redirect_url);
    exit;
}
