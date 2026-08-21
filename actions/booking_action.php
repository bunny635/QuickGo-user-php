<?php
// actions/booking_action.php
session_start();
require_once '../config/database.php';

// 1. Authorization Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Sanitize & Collect Input
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id'] ?? 0);
    $booking_date = trim($_POST['booking_date'] ?? '');
    $booking_time = trim($_POST['booking_time'] ?? '');
    $booked_hours = intval($_POST['booked_hours'] ?? 1);
    $address = trim($_POST['address'] ?? '');

    // 3. Server-Side Validation
    if (empty($service_id) || empty($booking_date) || empty($booking_time) || empty($address)) {
        die("Invalid submission. Missing required fields. Please go back and make sure all inputs are filled.");
    }

    if ($booked_hours < 1 || $booked_hours > 8) {
        die("Invalid duration selected.");
    }

    try {
        // 4. Fetch Service & Provider Securely
        $stmt = $pdo->prepare("SELECT s.*, p.id AS provider_id, p.availability FROM services s JOIN providers p ON s.provider_id = p.id WHERE s.id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();

        if (!$service) {
            die("Service not found.");
        }

        // 5. Check for Double Booking (Availability Validation)
        $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE provider_id = ? AND booking_date = ? AND booking_time = ? AND booking_status NOT IN ('Cancelled', 'Rejected')");
        $checkStmt->execute([$service['provider_id'], $booking_date, $booking_time]);
        if ($checkStmt->rowCount() > 0) {
            die("This time slot has already been booked. Please go back and select another time.");
        }

        // 6. Secure Financial Calculations
        $hourlyPay = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
        $providerFee = $hourlyPay * $booked_hours;
        $platformFee = 49.00;
        $gst = round($providerFee * 0.18, 2);
        $grandTotal = $providerFee + $platformFee + $gst;

        // Generate References
        $booking_ref = 'BK-' . time() . '-' . rand(100, 999);
        $payment_ref = 'PAY-' . time() . '-' . rand(100, 999);
        $txn_id = 'QG-TXN-' . strtoupper(substr(md5(uniqid()), 0, 9));

        // 7. Database Transaction
        $pdo->beginTransaction();

        // Insert Booking (Marked Confirmed for demo flow)
        $bookStmt = $pdo->prepare("
            INSERT INTO bookings 
            (booking_ref, user_id, provider_id, service_id, booking_date, booking_time, address, hourly_pay, booked_hours, amount, provider_fee, platform_fee, gst, grand_total, payment_status, booking_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Success', 'Confirmed')
        ");

        $bookStmt->execute([
            $booking_ref,
            $user_id,
            $service['provider_id'],
            $service_id,
            $booking_date,
            $booking_time,
            $address,
            $hourlyPay,
            $booked_hours,
            $providerFee,
            $providerFee,
            $platformFee,
            $gst,
            $grandTotal
        ]);

        $booking_db_id = $pdo->lastInsertId();

        // Insert Payment Record
        $payStmt = $pdo->prepare("
            INSERT INTO payments 
            (payment_ref, booking_id, user_id, provider_id, amount, method, payment_status, transaction_reference) 
            VALUES (?, ?, ?, ?, ?, 'Card', 'Success', ?)
        ");

        $payStmt->execute([
            $payment_ref,
            $booking_db_id,
            $user_id,
            $service['provider_id'],
            $grandTotal,
            $txn_id
        ]);

        $pdo->commit();

        // 8. Redirect to My Bookings with Success
        header("Location: ../pages/my-bookings.php?success=1");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("A system error occurred while processing your booking: " . htmlspecialchars($e->getMessage()));
    }
} else {
    header("Location: ../pages/services.php");
    exit;
}
