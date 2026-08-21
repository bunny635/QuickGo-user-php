<?php
// pages/payment-history.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Fetch the user's combined booking and payment history
$stmt = $pdo->prepare("
    SELECT 
        b.id AS booking_id, 
        b.booking_ref, 
        b.booking_date, 
        b.booking_time, 
        b.booking_status, 
        b.grand_total,
        s.title AS service_name, 
        s.images AS service_images,
        p.name AS provider_name, 
        p.profile_image AS provider_image, 
        p.experience,
        pay.payment_status, 
        pay.method AS payment_method, 
        pm.card_brand, 
        pm.last4
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN providers p ON b.provider_id = p.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    LEFT JOIN payment_methods pm ON pay.payment_method_id = pm.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<style>
    /* ==========================================
   HISTORY PAGE STYLES (From History.css)
========================================== */
    .history-page-wrapper {
        background-color: var(--primary-black, #0F1115);
        min-height: 100vh;
        padding-bottom: 60px;
    }

    .history-item-card {
        background: rgba(22, 22, 22, 0.7);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(197, 160, 89, 0.15);
        border-radius: 20px;
        padding: 25px;
        transition: 0.3s ease;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    .history-item-card:hover {
        border-color: var(--gold-accent, #D4AF37);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transform: translateY(-3px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .history-img {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .provider-mini-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gold-accent);
    }

    .border-end-gold {
        border-right: 1px solid rgba(255, 255, 255, 0.05);
    }

    .status-badge-success {
        color: #28a745;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        background: rgba(40, 167, 69, 0.1);
        padding: 4px 12px;
        border-radius: 50px;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .status-badge-pending {
        color: #f59e0b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        background: rgba(245, 158, 11, 0.1);
        padding: 4px 12px;
        border-radius: 50px;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .status-indicator {
        color: white;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dot-blink {
        height: 8px;
        width: 8px;
        background-color: var(--gold-accent);
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        box-shadow: 0 0 8px var(--gold-accent);
        animation: pulse-dot 1.5s infinite;
    }

    @keyframes pulse-dot {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(197, 160, 89, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(197, 160, 89, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(197, 160, 89, 0);
        }
    }

    .btn-rebook-link {
        background: rgba(197, 160, 89, 0.1);
        border: 1px solid rgba(197, 160, 89, 0.3);
        color: var(--gold-accent);
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        text-decoration: none;
    }

    .btn-rebook-link:hover {
        background: var(--gold-accent);
        color: black;
    }

    .x-small {
        font-size: 10px;
        letter-spacing: 1px;
    }

    @media (max-width: 991px) {
        .border-end-gold {
            border-right: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 20px;
            margin-bottom: 10px;
        }

        .status-indicator {
            justify-content: flex-start;
        }
    }
</style>

<div class="history-page-wrapper py-5">
    <div class="container pt-4">

        <div class="text-start mb-5" style="animation: fadeInUp 0.8s ease;">
            <h2 class="text-white fw-bold display-5" style="font-family: 'Playfair Display', serif;">Service <span style="color: #D4AF37;">History</span></h2>
            <p class="text-muted">Review your past bookings, payments, and assigned professionals.</p>
        </div>

        <?php if (empty($history)): ?>
            <div class="empty-history text-center py-5 border border-secondary rounded-4" style="background: rgba(0,0,0,0.4)">
                <i class="fa-solid fa-file-lines text-muted mb-3" style="font-size: 50px;"></i>
                <h4 class="text-white">No Activity Found</h4>
                <p class="text-muted">Your service history will appear here once you make a booking.</p>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php
                $delay = 0;
                foreach ($history as $item):
                    // Parse Data
                    $serviceImages = json_decode($item['service_images'], true);
                    $sImg = !empty($serviceImages) ? $serviceImages[0] : '../assets/images/fallback.jpg';
                    $pImg = !empty($item['provider_image']) ? $item['provider_image'] : '../assets/images/default-avatar.png';

                    // Formatting
                    $displayDate = date('M d, Y', strtotime($item['booking_date']));
                    $paymentStatus = $item['payment_status'] ?? 'Pending';
                    $isPaid = ($paymentStatus === 'Paid' || $paymentStatus === 'Success');
                    $badgeClass = $isPaid ? 'status-badge-success' : 'status-badge-pending';

                    $paymentText = $paymentStatus;
                    if ($isPaid) {
                        $paymentText = "Paid via " . ($item['card_brand'] ? "{$item['card_brand']} •••• {$item['last4']}" : ($item['payment_method'] ?? 'Online'));
                    }
                ?>
                    <div class="history-item-card mb-4" style="animation-delay: <?= $delay ?>s;">
                        <div class="row align-items-center g-4">

                            <!-- 1. SERVICE DETAILS -->
                            <div class="col-lg-4 border-end-gold">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= htmlspecialchars($sImg) ?>" alt="Service" class="history-img">
                                    <div>
                                        <span class="text-muted x-small fw-bold">ID: <?= htmlspecialchars($item['booking_ref']) ?></span>
                                        <h5 class="mb-1 text-white fw-bold"><?= htmlspecialchars($item['service_name']) ?></h5>
                                        <p class="mb-0 small text-muted">
                                            <i class="fa-regular fa-calendar me-1"></i> <?= $displayDate ?> | <?= htmlspecialchars($item['booking_time']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. PROVIDER HISTORY -->
                            <div class="col-lg-3 border-end-gold">
                                <label class="text-muted x-small text-uppercase fw-bold mb-2 d-block">Service Professional</label>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars($pImg) ?>" alt="Provider" class="provider-mini-avatar">
                                    <div>
                                        <p class="mb-0 text-white small fw-bold"><?= htmlspecialchars($item['provider_name']) ?></p>
                                        <p class="mb-0 x-small" style="color: #D4AF37;">
                                            <i class="fa-solid fa-shield-halved me-1"></i> <?= htmlspecialchars($item['experience']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. PAYMENT HISTORY -->
                            <div class="col-lg-3 border-end-gold">
                                <label class="text-muted x-small text-uppercase fw-bold mb-2 d-block">Payment Info</label>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fa-regular fa-credit-card me-2" style="color: #D4AF37;"></i>
                                    <span class="fw-bold text-white fs-5">$<?= number_format($item['grand_total'], 2) ?></span>
                                </div>
                                <span class="<?= $badgeClass ?>">
                                    <i class="fa-solid <?= $isPaid ? 'fa-circle-check' : 'fa-clock' ?> me-1"></i> <?= htmlspecialchars($paymentText) ?>
                                </span>
                            </div>

                            <!-- 4. FINAL STATUS & ACTION -->
                            <div class="col-lg-2 text-lg-center">
                                <label class="text-muted x-small text-uppercase fw-bold mb-2 d-block">Order Status</label>
                                <div class="status-indicator mb-3">
                                    <?php if ($item['booking_status'] !== 'Cancelled' && $item['booking_status'] !== 'Rejected'): ?>
                                        <span class="dot-blink"></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($item['booking_status']) ?>
                                </div>

                                <?php if ($isPaid || $item['booking_status'] === 'Completed'): ?>
                                    <a href="invoice.php?id=<?= $item['booking_id'] ?>" class="btn-rebook-link">
                                        View Invoice <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <!-- Provide link back to payment or details if pending/cancelled -->
                                    <a href="my-bookings.php" class="btn-rebook-link" style="opacity: 0.7;">
                                        Manage Booking
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php
                    $delay += 0.05; // Staggered animation delay
                endforeach;
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>