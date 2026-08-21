<?php
// actions/add_card_action.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $name = trim($input['cardHolderName'] ?? '');
    $number = str_replace(' ', '', $input['cardNumber'] ?? '');
    $expMonth = intval($input['expiryMonth'] ?? 0);
    $expYear = intval($input['expiryYear'] ?? 0);
    $cvv = trim($input['cvv'] ?? '');
    $isDefault = isset($input['isDefault']) && $input['isDefault'] ? 1 : 0;

    // 1. Validation
    if (empty($name) || empty($number) || empty($expMonth) || empty($expYear) || empty($cvv)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (strlen($number) < 15 || strlen($number) > 16) {
        echo json_encode(['success' => false, 'message' => 'Invalid card number length.']);
        exit;
    }

    if (strlen($cvv) < 3) {
        echo json_encode(['success' => false, 'message' => 'CVV must be at least 3 digits.']);
        exit;
    }

    if ($expMonth < 1 || $expMonth > 12) {
        echo json_encode(['success' => false, 'message' => 'Invalid expiry month.']);
        exit;
    }

    // Determine Brand (Mock logic based on first digit)
    $firstDigit = substr($number, 0, 1);
    $cardBrand = 'Visa';
    if ($firstDigit === '5') $cardBrand = 'Mastercard';
    if ($firstDigit === '3') $cardBrand = 'Amex';
    if ($firstDigit === '6') $cardBrand = 'Discover';

    // Extract safe data for DB
    $last4 = substr($number, -4);
    $token = 'tok_' . bin2hex(random_bytes(12)); // Simulated Stripe/Gateway token

    try {
        $pdo->beginTransaction();

        // If setting as default, remove default status from other cards
        if ($isDefault) {
            $updateStmt = $pdo->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
            $updateStmt->execute([$_SESSION['user_id']]);
        }

        // 2. Insert secure tokenized record (DO NOT STORE FULL NUMBER OR CVV)
        $stmt = $pdo->prepare("
            INSERT INTO payment_methods (user_id, card_holder_name, card_brand, last4, expiry_month, expiry_year, token, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $name,
            $cardBrand,
            $last4,
            $expMonth,
            $expYear,
            $token,
            $isDefault
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Card saved securely.'
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
