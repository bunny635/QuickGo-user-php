<?php
// actions/upload_avatar.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_image'];

    // Validation
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB max

    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!in_array($file['type'], $allowedTypes)) {
            header("Location: ../pages/profile.php?error=" . urlencode("Invalid image format. Only JPG, PNG, and WebP are allowed."));
            exit;
        }

        if ($file['size'] > $maxSize) {
            header("Location: ../pages/profile.php?error=" . urlencode("Image size exceeds the 2MB limit."));
            exit;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate a unique filename to prevent overwriting
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;
        $destination = $uploadDir . $newFilename;
        $dbPath = '../uploads/profiles/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Update Database using Prepared Statement
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$dbPath, $user_id]);

            header("Location: ../pages/profile.php?success=" . urlencode("Profile picture updated successfully!"));
            exit;
        }
    }

    header("Location: ../pages/profile.php?error=" . urlencode("Failed to upload image. Please try again."));
    exit;
}
