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
    $service_id = intval($_POST['service_id']);
    $booking_date = trim($_POST['booking_date']);
    $booking_time = trim($_POST['booking_time']);
    $booked_hours = intval($_POST['booked_hours']);
    $address = trim($_POST['address']);

    // Payment info (Normally sent to Stripe/Gateway, simulated here)
    $card_number = trim($_POST['card_number'] ?? '');

    // 3. Server-Side Validation
    if (empty($service_id) || empty($booking_date) || empty($booking_time) || empty($address) || empty($card_number)) {
        die("Invalid submission. Missing required fields.");
    }

    if ($booked_hours < 1 || $booked_hours > 8) {
        die("Invalid duration selected.");
    }

    try {
        // 4. Fetch Service & Provider Securely to calculate price server-side
        $stmt = $pdo->prepare("SELECT s.*, p.id AS provider_id, p.availability FROM services s JOIN providers p ON s.provider_id = p.id WHERE s.id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();

        if (!$service) {
            die("Service not found.");
        }

        if ($service['availability'] !== 'Online') {
            die("Provider is currently unavailable.");
        }

        // 5. Check for Double Booking (Availability Validation)
        $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE provider_id = ? AND booking_date = ? AND booking_time = ? AND booking_status NOT IN ('Cancelled', 'Rejected')");
        $checkStmt->execute([$service['provider_id'], $booking_date, $booking_time]);
        if ($checkStmt->rowCount() > 0) {
            die("This time slot has already been booked. Please go back and select another time.");
        }

        // 6. Secure Financial Calculations (Ignoring Client-Side DOM values entirely)
        $hourlyPay = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
        $providerFee = $hourlyPay * $booked_hours;
        $platformFee = 49.00;
        $gst = round($providerFee * 0.18, 2);
        $grandTotal = $providerFee + $platformFee + $gst;

        // Generate References
        $booking_ref = 'BK-' . time() . '-' . rand(100, 999);
        $payment_ref = 'PAY-' . time() . '-' . rand(100, 999);
        $txn_id = 'QG-TXN-' . strtoupper(substr(md5(uniqid()), 0, 9));

        // 7. Database Transaction (Atomicity)
        $pdo->beginTransaction();

        // Insert Booking
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

        // 8. Confirmation Redirect
        // Store success message in session for the UI to pick up
        $_SESSION['toast_success'] = "Booking Confirmed! Your transaction ID is $txn_id.";
        header("Location: ../pages/my-bookings.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage()); // Log internally
        die("A system error occurred while processing your booking. Please try again.");
    }
}
