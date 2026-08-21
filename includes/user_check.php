<?php
// includes/user_check.php
require_once 'auth_check.php'; // Ensures they are at least logged in

function requireRole($requiredRole)
{
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
        // Not authorized for this role
        header("Location: ../pages/home.php?error=" . urlencode("Access Denied: Insufficient permissions."));
        exit;
    }
}
