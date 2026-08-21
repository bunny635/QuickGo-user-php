<?php
// actions/payment_action.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $booking_id = intval($input['booking_id'] ?? 0);
    $method = $input['method'] ?? 'CARD';
    $save_card = $input['save_card'] ?? false;
    $card_number = str_replace(' ', '', $input['card_number'] ?? '');
    $card_name = trim($input['card_name'] ?? '');
    $expiry = trim($input['expiry'] ?? '');
    $cvv = trim($input['cvv'] ?? '');
    $saved_card_id = intval($input['saved_card_id'] ?? 0);

    try {
        // 1. Validate Booking
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'Pending'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new Exception("Invalid booking or payment already processed.");
        }

        // 2. Validate Payment Details
        $payment_method_id = null;
        $card_brand = 'Visa'; // Mock brand
        $last4 = '0000';

        if ($saved_card_id > 0) {
            // Use existing card
            $cardStmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ? AND user_id = ?");
            $cardStmt->execute([$saved_card_id, $_SESSION['user_id']]);
            $card = $cardStmt->fetch();
            if (!$card) throw new Exception("Invalid saved card selected.");
            $payment_method_id = $card['id'];
            $last4 = $card['last4'];
            $card_brand = $card['card_brand'];
        } else {
            // Validate New Card
            if (empty($card_number) || empty($card_name) || empty($expiry) || empty($cvv)) {
                throw new Exception("Please fill in all card details.");
            }
            if (strlen($cvv) !== 3) {
                throw new Exception("CVV/CVC must be exactly 3 digits.");
            }

            $last4 = substr($card_number, -4);
            $card_brand = str_starts_with($card_number, '5') ? 'MasterCard' : 'Visa';

            // 3. Save Card if requested (NEVER STORE CVV)
            if ($save_card) {
                $exp_parts = explode('/', $expiry);
                $exp_month = intval($exp_parts[0]);
                $exp_year = intval('20' . $exp_parts[1]);
                $token = bin2hex(random_bytes(16)); // Mock token

                $saveStmt = $pdo->prepare("INSERT INTO payment_methods (user_id, card_holder_name, card_brand, last4, expiry_month, expiry_year, token) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $saveStmt->execute([$_SESSION['user_id'], $card_name, $card_brand, $last4, $exp_month, $exp_year, $token]);
                $payment_method_id = $pdo->lastInsertId();
            }
        }

        // Simulate Bank Processing Delay
        sleep(2);

        // 4. Create Payment Record
        $txn_id = 'QG-TXN-' . strtoupper(substr(md5(uniqid()), 0, 9));
        $pay_ref = 'PAY-' . time() . '-' . rand(100, 999);

        $pdo->beginTransaction();

        $payStmt = $pdo->prepare("INSERT INTO payments (payment_ref, booking_id, user_id, provider_id, amount, method, payment_method_id, payment_status, transaction_reference) VALUES (?, ?, ?, ?, ?, ?, ?, 'Success', ?)");
        $payStmt->execute([$pay_ref, $booking_id, $_SESSION['user_id'], $booking['provider_id'], $booking['grand_total'], $method, $payment_method_id, $txn_id]);

        // 5. Update Booking Status
        $updStmt = $pdo->prepare("UPDATE bookings SET payment_status = 'Success', booking_status = 'Confirmed', transaction_id = ?, payment_method = ? WHERE id = ?");
        $updStmt->execute([$txn_id, "CARD ending in $last4", $booking_id]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'transaction_id' => $txn_id,
            'booking_id' => $booking_id
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
