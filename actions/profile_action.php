<?php
// actions/profile_action.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if this is a Password Update request
    if (isset($_POST['update_password']) && $_POST['update_password'] == '1') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            header("Location: ../pages/profile.php?error=" . urlencode("All password fields are required."));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            header("Location: ../pages/profile.php?error=" . urlencode("New passwords do not match."));
            exit;
        }

        if (strlen($newPassword) < 6) {
            header("Location: ../pages/profile.php?error=" . urlencode("New password must be at least 6 characters long."));
            exit;
        }

        // Fetch current stored password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($currentPassword, $user['password_hash'])) {
            // Hash new password securely
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newHash, $user_id]);

            header("Location: ../pages/profile.php?success=" . urlencode("Password updated successfully!"));
            exit;
        } else {
            header("Location: ../pages/profile.php?error=" . urlencode("Incorrect current password."));
            exit;
        }
    }

    // Otherwise, handle Personal Info Update (Name, Phone, Address)
    else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) {
            header("Location: ../pages/profile.php?error=" . urlencode("Name cannot be empty."));
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $address, $user_id]);

            // Update session data so it reflects immediately in navbar
            $_SESSION['user_name'] = $name;

            header("Location: ../pages/profile.php?success=" . urlencode("Profile details updated successfully!"));
            exit;
        } catch (PDOException $e) {
            header("Location: ../pages/profile.php?error=" . urlencode("Failed to update profile."));
            exit;
        }
    }
}
