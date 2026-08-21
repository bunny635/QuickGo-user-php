<?php
// pages/invoice.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch invoice data securely from database with normalization JOINs
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        s.title AS service_name, 
        s.category, 
        pu.name AS provider_name, 
        pu.profile_image AS provider_image, 
        u.name AS customer_name, 
        pay.transaction_reference, 
        pay.payment_status, 
        pay.method, 
        pm.last4, 
        pm.card_brand
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN providers p ON b.provider_id = p.id
    JOIN users pu ON p.user_id = pu.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    LEFT JOIN payment_methods pm ON pay.payment_method_id = pm.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$bill = $stmt->fetch();

if (!$bill) {
    echo "<div class='container py-5 text-center text-dark'><h3>Invoice not found or unauthorized access.</h3><a href='my-bookings.php' class='btn btn-dark mt-3'>Back to Bookings</a></div>";
    exit;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<style>
    /* ==========================================
   INVOICE PAGE STYLES (From Invoice.css)
========================================== */
    .invoice-page-container {
        --primary-white: #ffffff;
        --text-dark: #000000;
        --text-muted: #555555;
        --gold-accent: #000000;
        --brand-gold: #c5a059;
        --gold-light: rgba(0, 0, 0, 0.05);
        --border-light: rgba(0, 0, 0, 0.15);
        --success-green: #28a745;
        background-color: #f5f7f9;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding: 50px 0;
    }

    .invoice-premium-card {
        background: var(--primary-white);
        max-width: 800px;
        border-radius: 12px;
        padding: 40px;
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        color: var(--text-dark);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin: 0 auto;
    }

    .bill-watermark {
        position: absolute;
        top: 50%;
        right: 5%;
        transform: translateY(-50%);
        font-size: 300px;
        font-weight: 900;
        color: var(--brand-gold);
        opacity: 0.05;
        pointer-events: none;
        z-index: 0;
        user-select: none;
    }

    .invoice-header,
    .info-box,
    .provider-box,
    .invoice-table,
    .total-box,
    .invoice-footer,
    .tagline {
        position: relative;
        z-index: 2;
    }

    .invoice-logo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid var(--brand-gold);
        object-fit: cover;
    }

    .invoice-brand-title {
        color: var(--text-dark);
        font-weight: 800;
        font-size: 2rem;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .invoice-brand-title span {
        color: var(--brand-gold) !important;
    }

    .invoice-title {
        color: var(--gold-accent);
        font-weight: 800;
        font-size: 1.8rem;
        letter-spacing: 1px;
    }

    .issue-date {
        color: var(--text-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .status-pill-paid {
        display: inline-flex;
        align-items: center;
        background: var(--primary-white);
        color: var(--success-green);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid var(--success-green);
    }

    .gold-label {
        color: var(--brand-gold);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: block;
    }

    .info-box,
    .provider-box,
    .footer-box {
        border: 1px solid var(--border-light);
        border-radius: 8px;
        padding: 16px;
        background: var(--primary-white);
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--gold-light);
        color: var(--brand-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .customer-name {
        font-weight: 700;
        font-size: 16px;
        color: var(--text-dark);
    }

    .customer-address {
        font-size: 13px;
        color: var(--text-muted);
    }

    .info-text-label {
        font-size: 13px;
        color: var(--text-dark);
        font-weight: 600;
    }

    .info-text-value {
        font-size: 13px;
        color: var(--text-dark);
        text-align: right;
    }

    .provider-box {
        border-left: 4px solid var(--brand-gold);
    }

    .prov-inv-img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--brand-gold);
    }

    .provider-name {
        font-weight: 700;
        font-size: 15px;
        color: var(--text-dark);
    }

    .invoice-table {
        width: 100%;
        margin-top: 10px;
        border-collapse: collapse;
    }

    .invoice-table thead th {
        background: var(--gold-light);
        color: var(--text-dark);
        padding: 12px 16px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
    }

    .invoice-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        color: #333;
    }

    .service-item-title {
        font-size: 15px;
        color: var(--text-dark);
    }

    .service-item-price {
        font-weight: 700;
        font-size: 15px;
        color: var(--text-dark);
    }

    .total-box {
        border: 1px solid var(--brand-gold);
        border-radius: 8px;
        background: var(--gold-light);
    }

    .total-amount {
        font-size: 24px;
        font-weight: 800;
        color: var(--brand-gold);
    }

    .footer-box {
        border: 1px solid rgba(0, 0, 0, 0.05);
        background: rgba(0, 0, 0, 0.02);
    }

    .tagline {
        font-size: 12px;
        color: var(--brand-gold);
        font-weight: 600;
        border-top: 1px solid var(--border-light);
    }

    .btn-luxury-print {
        background: var(--primary-white);
        color: var(--text-dark);
        border: 1px solid var(--text-dark);
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
    }

    .btn-luxury-print:hover {
        background: #f0f0f0;
        color: #000;
    }

    .btn-luxury-print.outline {
        background: transparent;
        color: var(--text-dark);
        border-color: #ccc;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body,
        html,
        .invoice-page-container {
            background: #fff !important;
            padding: 0 !important;
        }

        nav,
        footer,
        .no-print {
            display: none !important;
        }

        .invoice-premium-card {
            border: none !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="invoice-page-container">
    <div class="container">
        <div class="invoice-premium-card shadow-sm">

            <!-- Watermark -->
            <div class="bill-watermark">QG</div>

            <!-- Header -->
            <div class="invoice-header d-flex justify-content-between align-items-start mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="../assets/images/weblogo.jpg" alt="Logo" class="invoice-logo" onerror="this.src='https://via.placeholder.com/50'">
                    <div>
                        <h1 class="invoice-brand-title mb-0">QUICK<span>GO</span></h1>
                        <p class="text-muted small mb-0">Premium Home Services</p>
                    </div>
                </div>
                <div class="text-end">
                    <h2 class="invoice-title mb-1">TAX INVOICE</h2>
                    <p class="issue-date m-0">ISSUE DATE: <?= date('M d, Y', strtotime($bill['created_at'])) ?></p>
                    <div class="status-pill-paid mt-3">
                        <i class="fa-solid fa-circle-check me-1"></i> PAID - <?= $bill['card_brand'] ? htmlspecialchars($bill['card_brand'] . " •••• " . $bill['last4']) : htmlspecialchars($bill['payment_method'] ?? 'ONLINE') ?>
                    </div>
                </div>
            </div>

            <!-- Section 1: Customer & Invoice Info -->
            <div class="row mb-4 g-3">
                <div class="col-md-6">
                    <div class="info-box d-flex gap-3 h-100">
                        <div class="info-icon"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <label class="gold-label">BILLED TO</label>
                            <h6 class="customer-name mb-1"><?= htmlspecialchars($bill['customer_name']) ?></h6>
                            <p class="customer-address mb-0"><i class="fa-solid fa-location-dot me-1 text-gold"></i> <?= htmlspecialchars($bill['address']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box h-100">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="info-text-label"><i class="fa-regular fa-file-lines me-2"></i>INVOICE REF</span>
                            <span class="info-text-value"><?= htmlspecialchars($bill['booking_ref']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="info-text-label"><i class="fa-regular fa-calendar me-2"></i>BOOKING ID</span>
                            <span class="info-text-value">#<?= $bill['id'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="info-text-label"><i class="fa-regular fa-calendar me-2"></i>DATE</span>
                            <span class="info-text-value"><?= date('M d, Y', strtotime($bill['booking_date'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="info-text-label"><i class="fa-regular fa-clock me-2"></i>TIME</span>
                            <span class="info-text-value"><?= htmlspecialchars($bill['booking_time']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Professional -->
            <div class="provider-box mb-4 p-3 d-flex align-items-center gap-3">
                <div class="info-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="flex-grow-1">
                    <label class="gold-label mb-2">ASSIGNED SERVICE PROFESSIONAL</label>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= htmlspecialchars($bill['provider_image'] ?: '../assets/images/default-avatar.png') ?>" alt="Provider" class="prov-inv-img" onerror="this.src='../assets/images/default-avatar.png'">
                        <div>
                            <h5 class="provider-name mb-1"><?= htmlspecialchars($bill['provider_name']) ?></h5>
                            <p class="text-muted small mb-0">QuickGo Verified Partner &bull; ETA: <?= htmlspecialchars($bill['estimated_arrival']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Table -->
            <table class="table invoice-table mb-4">
                <thead>
                    <tr>
                        <th class="w-75">SERVICE DESCRIPTION</th>
                        <th class="text-end w-25">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong class="d-block service-item-title mb-1"><?= htmlspecialchars($bill['service_name']) ?></strong>
                            <small class="text-muted">Fulfilled by <?= htmlspecialchars($bill['provider_name']) ?></small>
                        </td>
                        <td class="text-end align-middle service-item-price">$<?= number_format($bill['provider_fee'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Hourly Rate</td>
                        <td class="text-end text-muted py-2">$<?= number_format($bill['hourly_pay'], 2) ?> / hr</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Booked Duration</td>
                        <td class="text-end text-muted py-2"><?= $bill['booked_hours'] ?> hrs</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">GST (18%)</td>
                        <td class="text-end text-muted py-2">$<?= number_format($bill['gst'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Platform Fee</td>
                        <td class="text-end text-muted py-2">$<?= number_format($bill['platform_fee'], 2) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Section 4: Total -->
            <div class="total-box mb-4 d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center gap-2" style="color: #c5a059;">
                    <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
                    <span class="fw-bold fs-5">TOTAL AMOUNT</span>
                </div>
                <div class="total-amount">$<?= number_format($bill['grand_total'], 2) ?></div>
            </div>

            <!-- Footer Notes -->
            <div class="row g-3 invoice-footer no-print">
                <div class="col-md-6">
                    <div class="footer-box text-center h-100 p-3">
                        <i class="fa-solid fa-heart text-gold mb-2" style="color: #c5a059; font-size: 20px;"></i>
                        <h6 class="fw-bold text-dark mb-1">THANK YOU!</h6>
                        <p class="small text-muted mb-0">Thank you for choosing QuickGo.<br>We appreciate your trust in our services.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="footer-box d-flex align-items-center gap-3 h-100 p-3">
                        <div class="info-icon" style="background: transparent; border: 1px solid #c5a059; color: #c5a059;">
                            <i class="fa-solid fa-headphones"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-2">NEED HELP?</h6>
                            <p class="small text-muted mb-1"><i class="fa-regular fa-envelope me-2"></i> support@quickgo.com</p>
                            <p class="small text-muted mb-0"><i class="fa-solid fa-phone me-2"></i> +91 98765 43210</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tagline text-center mt-3 pt-2 no-print">
                Quality Services. Trusted Professionals. At Your Doorstep.
            </div>

        </div>

        <div class="text-center mt-4 mb-5 no-print d-flex justify-content-center gap-3">
            <a href="my-bookings.php" class="btn-luxury-print outline"><i class="fa-solid fa-arrow-left me-2"></i> Go to Bookings</a>
            <button class="btn-luxury-print" onclick="window.print()"><i class="fa-solid fa-print me-2"></i> Print / Save PDF</button>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>