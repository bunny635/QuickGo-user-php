<?php
// actions/create_pending_booking.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id'] ?? 0);
    $booking_date = trim($_POST['booking_date'] ?? '');
    $booking_time = trim($_POST['booking_time'] ?? '');
    $booked_hours = intval($_POST['booked_hours'] ?? 1);
    $address = trim($_POST['address'] ?? '');

    if (empty($service_id) || empty($booking_date) || empty($booking_time) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Missing required booking fields.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT s.*, p.id AS provider_id FROM services s JOIN providers p ON s.provider_id = p.id WHERE s.id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();

        if (!$service) {
            throw new Exception("Service not found.");
        }

        $hourlyPay = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
        $providerFee = $hourlyPay * $booked_hours;
        $platformFee = 49.00;
        $gst = round($providerFee * 0.18, 2);
        $grandTotal = $providerFee + $platformFee + $gst;
        $booking_ref = 'BK-' . time() . '-' . rand(100, 999);

        // Insert as Pending Booking
        $bookStmt = $pdo->prepare("
            INSERT INTO bookings 
            (booking_ref, user_id, provider_id, service_id, booking_date, booking_time, address, hourly_pay, booked_hours, amount, provider_fee, platform_fee, gst, grand_total, payment_status, booking_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending')
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

        $booking_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'booking_id' => $booking_id
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
