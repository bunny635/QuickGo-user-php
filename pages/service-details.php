<?php
// pages/service-details.php
require_once '../includes/auth_check.php'; // Optional: remove if public can view details
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($service_id === 0) {
    echo "<script>window.location.href = 'services.php';</script>";
    exit;
}

// Fetch Service and Provider Details
$stmt = $pdo->prepare("
    SELECT s.*, p.name AS provider_name, p.profile_image, p.experience, p.availability AS provider_availability 
    FROM services s 
    JOIN providers p ON s.provider_id = p.id 
    WHERE s.id = ? AND s.is_active = 1
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    echo "<div class='container py-5 text-center text-white min-vh-100'>
            <h2>Service Not Found</h2>
            <p class='text-muted'>The requested service is no longer available.</p>
            <a href='services.php' class='btn btn-outline-light mt-3'>Back to Services</a>
          </div>";
    require_once '../includes/footer.php';
    exit;
}

// Fetch Reviews
$revStmt = $pdo->prepare("
    SELECT r.*, u.name AS user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.service_id = ? AND r.status = 'Published' 
    ORDER BY r.created_at DESC
");
$revStmt->execute([$service_id]);
$reviews = $revStmt->fetchAll();

// Determine Availability
$actualAvailability = $service['provider_availability'] ?? 'Online';
$isOnline = $actualAvailability === 'Online';
$isBusy = $actualAvailability === 'Busy';
$isOffline = $actualAvailability === 'Offline';

$serviceImage = !empty($service['images']) ? json_decode($service['images'], true)[0] : '../assets/images/fallback.jpg';
$providerImage = !empty($service['profile_image']) ? $service['profile_image'] : '../assets/images/default-avatar.png';
$hourlyPay = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
?>

<style>
    /* Service Details Styles matching React CSS */
    .service-details-page-wrapper {
        background-color: #0F1115;
        min-height: 100vh;
        padding-bottom: 50px;
    }

    .btn-back-link {
        background: none;
        border: none;
        color: #D4AF37;
        font-weight: 700;
        font-size: 15px;
        transition: 0.3s;
        padding: 0;
        text-decoration: none;
    }

    .btn-back-link:hover {
        letter-spacing: 1px;
        color: #e6ca65;
    }

    .main-image-frame {
        position: relative;
        width: 100%;
        height: 450px;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .details-main-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rating-tag-float {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: #D4AF37;
        padding: 5px 15px;
        border-radius: 50px;
        font-weight: 800;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(197, 160, 89, 0.3);
    }

    .service-title-text {
        color: white;
        font-weight: 800;
        font-size: 2.8rem;
        letter-spacing: -1px;
        font-family: 'Playfair Display', serif;
    }

    .service-full-desc {
        color: #ccc;
        font-size: 1.1rem;
        line-height: 1.8;
    }

    .gold-title {
        color: #D4AF37;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .check-item-card {
        background: rgba(22, 22, 22, 0.6);
        padding: 15px;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
    }

    .divider-gold-thin {
        border-top: 1px solid rgba(197, 160, 89, 0.2);
        margin: 25px 0;
        opacity: 1;
    }

    .booking-side-card {
        background: linear-gradient(145deg, rgba(26, 29, 36, 0.9) 0%, rgba(15, 17, 21, 0.9) 100%);
        backdrop-filter: blur(15px);
        padding: 35px;
        border-radius: 25px;
        border: 1px solid rgba(197, 160, 89, 0.2);
        position: sticky;
        top: 100px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
    }

    .pricing-box h2 {
        color: #D4AF37;
        font-weight: 800;
        font-size: 2.5rem;
        margin-top: 5px;
    }

    .pricing-box h2 span {
        font-size: 1rem;
        color: #777;
        font-weight: 600;
    }

    .info-row-item {
        display: flex;
        gap: 15px;
        margin-bottom: 22px;
    }

    .row-icon {
        color: #D4AF37;
        font-size: 24px;
        margin-top: 5px;
    }

    .row-label {
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        font-weight: 800;
        margin: 0;
        letter-spacing: 1px;
    }

    .row-val {
        color: white;
        font-weight: 600;
        margin: 0;
        font-size: 15px;
    }

    .help-box {
        text-align: center;
        font-size: 13px;
        color: #888;
        font-weight: 600;
    }

    .review-card {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(212, 175, 55, 0.1);
    }
</style>

<div class="service-details-page-wrapper">
    <div class="container py-5">
        <a href="services.php" class="btn-back-link mb-4 d-inline-block">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Services
        </a>

        <div class="row g-5">
            <!-- LEFT CONTENT -->
            <div class="col-lg-8">
                <div class="main-image-frame">
                    <img src="<?= htmlspecialchars($serviceImage) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="details-main-img">
                    <div class="rating-tag-float">
                        <i class="fa-solid fa-star me-1 mb-1"></i> <?= number_format($service['rating'], 1) ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <span class="badge" style="background: rgba(212, 175, 55, 0.15); color: #D4AF37; border: 1px solid rgba(212, 175, 55, 0.3); padding: 6px 14px; border-radius: 20px;">
                        <i class="fa-solid fa-tag me-1"></i> <?= htmlspecialchars($service['category']) ?>
                    </span>

                    <?php
                    $statusBg = $isOnline ? 'rgba(16, 185, 129, 0.15)' : ($isBusy ? 'rgba(245, 158, 11, 0.15)' : 'rgba(239, 68, 68, 0.15)');
                    $statusColor = $isOnline ? '#10b981' : ($isBusy ? '#f59e0b' : '#ef4444');
                    $statusBorder = $isOnline ? 'rgba(16, 185, 129, 0.3)' : ($isBusy ? 'rgba(245, 158, 11, 0.3)' : 'rgba(239, 68, 68, 0.3)');
                    ?>
                    <span style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusBorder ?>; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                        ● Provider Status: <?= htmlspecialchars($actualAvailability) ?>
                    </span>
                </div>

                <h1 class="service-title-text mt-3"><?= htmlspecialchars($service['title']) ?></h1>
                <p class="service-full-desc mt-3">
                    <?= htmlspecialchars($service['description'] ?: 'Professional and reliable service performed with premium grade tools and adherence to QuickGo quality and safety standards.') ?>
                </p>

                <!-- PROVIDER OVERVIEW CARD -->
                <div class="mt-4 p-4 rounded-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <h5 class="text-gold mb-3 d-flex align-items-center">
                        <i class="fa-solid fa-user-check me-2"></i> Verified Provider Overview
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= htmlspecialchars($providerImage) ?>" alt="Provider" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #D4AF37;">
                        <div>
                            <h5 class="text-white mb-1"><?= htmlspecialchars($service['provider_name']) ?></h5>
                            <p class="text-muted small mb-0">Experience: <?= htmlspecialchars($service['experience']) ?> • Verified Partner</p>
                        </div>
                    </div>
                </div>

                <!-- FEATURES GRID -->
                <div class="service-features-section mt-5 mb-5">
                    <h4 class="gold-title mb-4"><i class="fa-solid fa-toolbox me-2"></i> QuickGo Premium Guarantee</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="check-item-card"><i class="fa-solid fa-circle-check text-gold me-2"></i> Certified & Background-Checked</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="check-item-card"><i class="fa-solid fa-circle-check text-gold me-2"></i> Transparent Hourly & Flat Rates</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="check-item-card"><i class="fa-solid fa-circle-check text-gold me-2"></i> Contactless Secure Payment</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="check-item-card"><i class="fa-solid fa-circle-check text-gold me-2"></i> 100% Satisfaction Warranty</div>
                        </div>
                    </div>
                </div>

                <!-- REVIEWS -->
                <div class="service-reviews-section mt-5 mb-5">
                    <h4 class="gold-title mb-4"><i class="fa-solid fa-star me-2"></i> Reviews & Ratings</h4>

                    <?php if (count($reviews) > 0): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="review-card mb-3 p-4 rounded-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="text-white mb-0"><?= htmlspecialchars($rev['user_name']) ?></h6>
                                        <span class="text-muted small"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                    </div>
                                    <div class="text-gold mb-2" style="font-size: 14px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-solid fa-star" style="color: <?= $i <= $rev['rating'] ? '#D4AF37' : '#444' ?>;"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-light small mb-0" style="line-height: 1.6;"><?= htmlspecialchars($rev['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4 rounded-4" style="background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1);">
                            <p class="text-muted mb-0">No reviews yet for this service.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="col-lg-4">
                <div class="booking-side-card">
                    <div class="pricing-box">
                        <span class="small text-muted text-uppercase fw-bold">Hourly Rate</span>
                        <h2>$<?= number_format($hourlyPay, 2) ?> <span>/ hr</span></h2>
                    </div>

                    <hr class="divider-gold-thin" />

                    <div class="info-rows">
                        <div class="info-row-item">
                            <i class="fa-regular fa-clock row-icon"></i>
                            <div>
                                <p class="row-label">Estimated Duration</p>
                                <p class="row-val"><?= htmlspecialchars($service['duration']) ?></p>
                            </div>
                        </div>
                        <div class="info-row-item">
                            <i class="fa-solid fa-location-dot row-icon"></i>
                            <div>
                                <p class="row-label">Service Area</p>
                                <p class="row-val"><?= htmlspecialchars($service['location'] ?: 'Available Pan-City') ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($isBusy): ?>
                        <div class="mt-3 p-3 rounded-3" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b; font-size: 12.5px;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>High Demand:</strong> Provider is busy.
                        </div>
                    <?php elseif ($isOffline): ?>
                        <div class="mt-3 p-3 rounded-3" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 12.5px;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Offline:</strong> Provider is not accepting requests.
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 d-grid">
                        <?php if ($isOnline): ?>
                            <a href="book-service.php?service_id=<?= $service['id'] ?>" class="btn btn-gold py-3 fw-bold rounded-3">Proceed to Schedule & Book</a>
                        <?php else: ?>
                            <button class="btn btn-secondary py-3 fw-bold rounded-3" disabled>Provider Unavailable</button>
                        <?php endif; ?>
                    </div>

                    <div class="help-box mt-4">
                        <i class="fa-solid fa-phone me-2"></i> Need help? <span class="text-gold">Contact Concierge</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>