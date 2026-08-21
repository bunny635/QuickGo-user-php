<?php
// pages/profile.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// 1. Fetch User Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Fetch Booking Statistics (Total, Pending, Completed, Cancelled, and Total Spent)
$statStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN booking_status IN ('Pending', 'Confirmed', 'In Progress') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN booking_status IN ('Cancelled', 'Rejected') THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN payment_status IN ('Paid', 'Success') THEN grand_total ELSE 0 END) as total_spent
    FROM bookings 
    WHERE user_id = ?
");
$statStmt->execute([$user_id]);
$stats = $statStmt->fetch();

// Ensure null sums default to 0
$totalSpent = $stats['total_spent'] ?? 0;
$pending = $stats['pending'] ?? 0;
$completed = $stats['completed'] ?? 0;
$cancelled = $stats['cancelled'] ?? 0;
$totalBookings = $stats['total_bookings'] ?? 0;

// 3. Fetch Top 5 Recent Bookings for History Tab
$historyStmt = $pdo->prepare("
    SELECT b.*, s.title AS service_name 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$historyStmt->execute([$user_id]);
$recentBookings = $historyStmt->fetchAll();

// Format Data
$profilePic = !empty($user['profile_image']) ? $user['profile_image'] : '../assets/images/default-avatar.png';
$memberSince = date('M Y', strtotime($user['created_at']));

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Flash Messages
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<style>
    /* ==========================================
   QUICKGO PREMIUM PROFILE - MASTER CSS
========================================== */
    .profile-page-wrapper {
        background-color: #0F1115;
        color: white;
        min-height: 100vh;
    }

    .profile-hero-banner {
        padding: 60px 0 40px;
        background: linear-gradient(rgba(15, 17, 21, 0.85), rgba(15, 17, 21, 1)), url('../assets/images/bg.jpg') center/cover;
        border-bottom: 1px solid rgba(197, 160, 89, 0.15);
    }

    .banner-flex {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .profile-avatar-container {
        position: relative;
        width: 140px;
        height: 140px;
        cursor: pointer;
        transition: 0.4s ease;
        flex-shrink: 0;
    }

    .profile-avatar-container:hover .avatar-main {
        filter: brightness(0.6);
        transform: scale(1.02);
    }

    .avatar-main {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 4px solid #D4AF37;
        object-fit: cover;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.7);
    }

    .camera-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #D4AF37;
        color: black;
        padding: 10px;
        border-radius: 50%;
        display: flex;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        z-index: 5;
        transition: 0.3s;
    }

    .profile-avatar-container:hover .camera-badge {
        transform: scale(1.1);
    }

    .v-badge {
        font-size: 11px;
        background: rgba(40, 167, 69, 0.15);
        color: #28a745;
        padding: 4px 14px;
        border-radius: 50px;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid rgba(40, 167, 69, 0.3);
        vertical-align: middle;
    }

    .btn-edit-header {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-edit-header:hover {
        border-color: #D4AF37;
        color: #D4AF37;
        background: rgba(197, 160, 89, 0.05);
    }

    .btn-logout-header {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff4d4d;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-logout-header:hover {
        background: rgba(220, 53, 69, 0.2);
        color: #ff6b6b;
    }

    /* Content Cards */
    .profile-glass-card,
    .p-billing-box {
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
    }

    .gold-heading {
        color: #D4AF37;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        font-size: 0.85rem;
    }

    .p-form-group-new {
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .p-form-group-new label {
        font-size: 10px;
        color: #777;
        text-transform: uppercase;
        font-weight: 800;
        margin-bottom: 5px;
        display: block;
        letter-spacing: 1px;
    }

    .p-form-group-new p {
        margin: 0;
        font-size: 15px;
        color: #eee;
    }

    .p-edit-input {
        width: 100%;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(197, 160, 89, 0.5);
        color: white;
        padding: 12px;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: 0.3s;
    }

    .p-edit-input:focus {
        border-color: #D4AF37;
        box-shadow: 0 0 10px rgba(197, 160, 89, 0.1);
    }

    /* Stat Cards */
    .p-stat-card-luxury {
        background: rgba(255, 255, 255, 0.02);
        padding: 25px 15px;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
        transition: 0.3s;
    }

    .p-stat-card-luxury:hover {
        border-color: rgba(197, 160, 89, 0.3);
        background: rgba(197, 160, 89, 0.02);
        transform: translateY(-3px);
    }

    .p-stat-icon-circ {
        width: 45px;
        height: 45px;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .p-stat-card-luxury h4 {
        font-weight: 800;
        margin: 0;
        font-size: 1.5rem;
    }

    .p-stat-card-luxury p {
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        font-weight: 700;
        margin-top: 5px;
        letter-spacing: 1px;
    }

    /* History Card */
    .p-history-card-detailed {
        background: rgba(255, 255, 255, 0.02);
        padding: 25px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: 0.3s;
    }

    .p-history-card-detailed:hover {
        border-color: rgba(197, 160, 89, 0.3);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
    }

    .h-card-top-bar {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding-bottom: 15px;
    }

    .h-ref-text {
        font-family: monospace;
        color: #888;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .h-status-pill {
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .h-status-pill.completed {
        background: rgba(197, 160, 89, 0.15);
        color: #D4AF37;
        border: 1px solid rgba(197, 160, 89, 0.3);
    }

    .h-status-pill.pending,
    .h-status-pill.confirmed {
        background: rgba(255, 165, 0, 0.1);
        color: #FFA500;
        border: 1px solid rgba(255, 165, 0, 0.3);
    }

    .h-status-pill.cancelled {
        background: rgba(220, 53, 69, 0.1);
        color: #DC3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    /* Action Small Buttons */
    .btn-history-action {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #ccc;
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        transition: 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-history-action:hover {
        border-color: #D4AF37;
        color: #D4AF37;
    }

    .btn-history-action.gold-bg {
        background: #D4AF37;
        color: black;
        border: none;
    }

    .btn-history-action.gold-bg:hover {
        background: #E6C25A;
        box-shadow: 0 5px 15px rgba(197, 160, 89, 0.2);
    }

    /* Profile Menu */
    .profile-sidebar-menu {
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 10px 0;
        overflow: hidden;
    }

    .profile-menu-item {
        display: flex;
        align-items: center;
        padding: 15px 25px;
        cursor: pointer;
        transition: 0.3s;
        color: #ccc;
        font-size: 15px;
        font-weight: 600;
        border-right: 3px solid transparent;
    }

    .profile-menu-item:hover {
        background: rgba(0, 0, 0, 0.3);
        color: white;
    }

    .profile-menu-item.active {
        background: rgba(197, 160, 89, 0.1);
        color: #D4AF37;
        border-right-color: #D4AF37;
    }

    .p-menu-icon {
        font-size: 18px;
        margin-right: 15px;
    }

    /* Tab Animation (Replaces Framer Motion) */
    .tab-pane {
        display: none;
        animation: fadeInRight 0.3s ease forwards;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Toast */
    .custom-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        min-width: 250px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.4s ease;
        visibility: hidden;
    }

    .toast-error {
        background: #e74c3c;
        border-left: 5px solid #c0392b;
    }

    .toast-success {
        background: #2ecc71;
        border-left: 5px solid #27ae60;
    }

    .toast-show {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }

    @media (max-width: 991px) {
        .banner-flex {
            flex-direction: column;
            text-align: center;
        }

        .profile-sidebar-menu {
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            padding: 0;
            white-space: nowrap;
            border-radius: 15px;
        }

        .profile-sidebar-menu::-webkit-scrollbar {
            height: 4px;
        }

        .profile-sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(197, 160, 89, 0.3);
            border-radius: 10px;
        }

        .profile-menu-item {
            border-right: none;
            border-bottom: 3px solid transparent;
            padding: 15px 20px;
        }

        .profile-menu-item.active {
            border-right-color: transparent;
            border-bottom-color: #D4AF37;
        }

        .p-menu-icon {
            margin-right: 8px;
        }
    }
</style>

<div class="profile-page-wrapper pb-5">
    <!-- Toast Notification -->
    <div id="toast" class="custom-toast <?= $error ? 'toast-error toast-show' : ($success ? 'toast-success toast-show' : '') ?>">
        <?= htmlspecialchars($error ?? $success ?? '') ?>
    </div>

    <!-- Hidden Avatar Upload Form -->
    <form id="avatarForm" action="../actions/upload_avatar.php" method="POST" enctype="multipart/form-data" style="display: none;">
        <input type="file" name="profile_image" id="avatarInput" accept="image/*" onchange="document.getElementById('avatarForm').submit();">
    </form>

    <!-- 1. PROFILE HEADER -->
    <section class="profile-hero-banner">
        <div class="container" style="max-width: 1000px;">
            <div class="banner-flex">
                <div class="profile-avatar-container" onclick="document.getElementById('avatarInput').click();">
                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="avatar-main">
                    <div class="camera-badge"><i class="fa-solid fa-camera"></i></div>
                </div>
                <div class="banner-details text-white flex-grow-1">
                    <h2 class="fw-bold mb-2 d-flex align-items-center flex-wrap gap-2">
                        <?= htmlspecialchars($user['name']) ?> <span class="v-badge"><i class="fa-solid fa-shield-halved"></i> Verified User</span>
                    </h2>
                    <div class="text-muted d-flex gap-3 flex-wrap mb-2">
                        <span><i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($user['email']) ?></span>
                        <span><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($user['phone'] ?: 'Not Added') ?></span>
                    </div>
                    <p class="member-since mb-0">Member Since: <?= $memberSince ?></p>
                </div>
                <div class="banner-actions text-md-end mt-3 mt-md-0 d-flex flex-column gap-2">
                    <button class="btn-edit-header w-100" onclick="switchTab('personal', true)">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                    </button>
                    <a href="../auth/logout.php" class="btn-logout-header w-100 text-decoration-none d-inline-block text-center">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. ACCOUNT CENTER GRID -->
    <div class="container mt-4" style="max-width: 1000px;">
        <div class="row">
            <!-- PROFILE MENU (Left) -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-sidebar-menu">
                    <div class="profile-menu-item active" onclick="switchTab('overview')" id="nav-overview">
                        <i class="fa-solid fa-chart-line p-menu-icon"></i> <span class="p-menu-label">Overview</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('personal')" id="nav-personal">
                        <i class="fa-regular fa-user p-menu-icon"></i> <span class="p-menu-label">Personal Information</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('security')" id="nav-security">
                        <i class="fa-solid fa-shield-halved p-menu-icon"></i> <span class="p-menu-label">Security</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('history')" id="nav-history">
                        <i class="fa-solid fa-list p-menu-icon"></i> <span class="p-menu-label">Booking History</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('address')" id="nav-address">
                        <i class="fa-solid fa-location-dot p-menu-icon"></i> <span class="p-menu-label">Saved Addresses</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('billing')" id="nav-billing">
                        <i class="fa-regular fa-credit-card p-menu-icon"></i> <span class="p-menu-label">Billing</span>
                    </div>
                    <div class="profile-menu-item" onclick="switchTab('support')" id="nav-support">
                        <i class="fa-regular fa-comment-dots p-menu-icon"></i> <span class="p-menu-label">Help & Support</span>
                    </div>
                </div>
            </div>

            <!-- ACTIVE CONTENT (Right) -->
            <div class="col-lg-9 col-md-8">

                <!-- OVERVIEW TAB -->
                <div id="tab-overview" class="tab-pane active">
                    <h5 class="gold-heading mb-4">Account Snapshot</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-6">
                            <div class="p-stat-card-luxury">
                                <div class="p-stat-icon-circ" style="color: #D4AF37; border-color: #D4AF37;"><i class="fa-solid fa-chart-line"></i></div>
                                <h4 class="text-white"><?= $totalBookings ?></h4>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="p-stat-card-luxury">
                                <div class="p-stat-icon-circ" style="color: #FFA500; border-color: #FFA500;"><i class="fa-regular fa-clock"></i></div>
                                <h4 class="text-white"><?= $pending ?></h4>
                                <p>Pending</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="p-stat-card-luxury">
                                <div class="p-stat-icon-circ" style="color: #28A745; border-color: #28A745;"><i class="fa-regular fa-circle-check"></i></div>
                                <h4 class="text-white"><?= $completed ?></h4>
                                <p>Completed</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="p-stat-card-luxury">
                                <div class="p-stat-icon-circ" style="color: #DC3545; border-color: #DC3545;"><i class="fa-regular fa-circle-xmark"></i></div>
                                <h4 class="text-white"><?= $cancelled ?></h4>
                                <p>Cancelled</p>
                            </div>
                        </div>
                    </div>
                    <div class="profile-glass-card p-4">
                        <h5 class="gold-heading mb-3">Membership Status</h5>
                        <p class="mb-1 text-white">Member Since: <span class="text-muted"><?= $memberSince ?></span></p>
                        <p class="mb-0 text-white">Account Status: <span class="text-success fw-bold">Verified User</span></p>
                    </div>
                </div>

                <!-- PERSONAL INFO TAB -->
                <div id="tab-personal" class="tab-pane profile-glass-card p-4">
                    <h5 class="gold-heading mb-4 d-flex justify-content-between align-items-center">
                        Personal Information
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="toggleEditMode()" id="editToggleBtn" style="border-color: #D4AF37; color: #D4AF37; font-size: 12px; font-weight: bold;">
                            Edit Profile
                        </button>
                    </h5>
                    <form action="../actions/profile_action.php" method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-form-group-new border-0 pb-0">
                                    <label>Full Name</label>
                                    <p class="fw-bold view-mode m-0 text-white"><?= htmlspecialchars($user['name']) ?></p>
                                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="p-edit-input edit-mode d-none" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-form-group-new border-0 pb-0">
                                    <label>Email Address</label>
                                    <p class="fw-bold m-0 text-white"><?= htmlspecialchars($user['email']) ?> <i class="fa-solid fa-lock text-muted ms-2" title="Cannot be changed"></i></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-form-group-new border-0 pb-0 mb-0">
                                    <label>Mobile Number</label>
                                    <p class="fw-bold view-mode m-0 text-white"><?= htmlspecialchars($user['phone'] ?: 'Not Provided') ?></p>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="p-edit-input edit-mode d-none">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-form-group-new border-0 pb-0 mb-0">
                                    <label>Location / Default Address</label>
                                    <p class="fw-bold view-mode m-0 text-white"><?= htmlspecialchars($user['address'] ?: 'Not Provided') ?></p>
                                    <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" class="p-edit-input edit-mode d-none">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top edit-mode d-none" style="border-color: rgba(255,255,255,0.1) !important;">
                            <button type="submit" class="btn btn-gold px-4 rounded-pill fw-bold">Save Profile Changes</button>
                        </div>
                    </form>
                </div>

                <!-- SECURITY TAB -->
                <div id="tab-security" class="tab-pane profile-glass-card p-4">
                    <h5 class="gold-heading mb-1">Security</h5>
                    <p class="text-muted small mb-4">Manage your account password securely.</p>
                    <form action="../actions/profile_action.php" method="POST">
                        <input type="hidden" name="update_password" value="1">
                        <div class="mb-3">
                            <label class="text-muted small fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="p-edit-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="p-edit-input" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-muted small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="p-edit-input" required>
                        </div>
                        <button type="submit" class="btn btn-gold rounded-pill px-4 fw-bold">Update Password</button>
                    </form>
                </div>

                <!-- HISTORY TAB -->
                <div id="tab-history" class="tab-pane">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gold-heading mb-0">Recent Bookings</h5>
                        <a href="my-bookings.php" class="btn btn-sm text-gold text-decoration-none" style="font-size: 13px; font-weight: bold;">View All Bookings <i class="fa-solid fa-chevron-right"></i></a>
                    </div>

                    <?php if (empty($recentBookings)): ?>
                        <div class="empty-state-placeholder text-center p-5" style="background: rgba(0,0,0,0.4); border-radius: 20px; border: 1px dashed #333;">
                            <i class="fa-solid fa-file-lines text-muted mb-3" style="font-size: 40px;"></i>
                            <h6 class="text-white">No Bookings Found</h6>
                            <a href="services.php" class="btn btn-gold mt-3 px-4 rounded-pill">Explore Services</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $bk): ?>
                            <div class="p-history-card-detailed mb-3 p-3">
                                <div class="h-card-top-bar pb-2 mb-2">
                                    <span class="h-ref-text">REF: <?= htmlspecialchars($bk['booking_ref']) ?></span>
                                    <span class="h-status-pill <?= strtolower($bk['booking_status']) ?>"><?= htmlspecialchars($bk['booking_status']) ?></span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($bk['service_name']) ?></h6>
                                        <p class="small text-muted mb-0">
                                            <i class="fa-regular fa-calendar me-1"></i> <?= date('M d, Y', strtotime($bk['booking_date'])) ?> | <i class="fa-regular fa-clock me-1"></i> <?= $bk['booking_time'] ?>
                                        </p>
                                    </div>
                                    <div class="col-md-5 text-md-end mt-2 mt-md-0">
                                        <span class="text-gold fw-bold d-block mb-1">₹<?= number_format($bk['grand_total'], 2) ?></span>
                                        <?php if ($bk['booking_status'] === 'Completed'): ?>
                                            <a href="invoice.php?id=<?= $bk['id'] ?>" class="btn-history-action py-1 px-3 mt-1" style="font-size: 11px;">
                                                <i class="fa-solid fa-download me-1"></i> Invoice
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- ADDRESS TAB -->
                <div id="tab-address" class="tab-pane profile-glass-card p-4">
                    <h5 class="gold-heading mb-4">Saved Addresses</h5>
                    <div class="address-box p-3 mb-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(197, 160, 89, 0.3);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-gold mb-0"><i class="fa-solid fa-location-dot me-2"></i>Home</h6>
                            <span class="badge bg-gold text-dark" style="font-size: 10px;">DEFAULT</span>
                        </div>
                        <p class="text-white mb-0 small ps-4"><?= htmlspecialchars($user['address'] ?: 'No address saved.') ?></p>
                    </div>
                    <button class="btn-edit-header mt-2 text-gold w-100" onclick="switchTab('personal', true)"><i class="fa-solid fa-map-pin me-1"></i> Update Address in Profile</button>
                </div>

                <!-- BILLING TAB -->
                <div id="tab-billing" class="tab-pane">
                    <div class="p-billing-box p-4 mb-4 text-center">
                        <h5 class="gold-heading mb-2">Total Billed</h5>
                        <h2 class="text-white fw-bold mb-1">$<?= number_format($totalSpent, 2) ?></h2>
                        <span class="text-muted small">Lifetime investment in premium services</span>
                    </div>
                    <h5 class="gold-heading mb-3">Payment Methods</h5>
                    <a href="digital-card.php" class="text-decoration-none">
                        <div class="profile-glass-card p-3 d-flex align-items-center justify-content-between mb-3" style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <i class="fa-regular fa-credit-card text-gold fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold text-white">Digital Cards</h6>
                                    <p class="mb-0 text-muted small">Manage your saved credit/debit cards</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-gold"></i>
                        </div>
                    </a>
                </div>

                <!-- SUPPORT TAB -->
                <div id="tab-support" class="tab-pane profile-glass-card p-4 text-center">
                    <i class="fa-regular fa-comments text-gold mb-3" style="font-size: 40px;"></i>
                    <h5 class="gold-heading mb-3">Help & Support</h5>
                    <p class="text-muted small mb-4">Our premium support team is available 24/7 to assist you with bookings, payments, and account inquiries.</p>
                    <a href="contact.php" class="btn-history-action gold-bg w-100">Contact Support</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Tab Switching Logic (Replaces React State)
    function switchTab(tabId, triggerEdit = false) {
        // Hide all tabs
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        // Remove active class from menu items
        document.querySelectorAll('.profile-menu-item').forEach(el => el.classList.remove('active'));

        // Show target tab
        document.getElementById('tab-' + tabId).classList.add('active');
        // Highlight target menu item
        const navItem = document.getElementById('nav-' + tabId);
        if (navItem) navItem.classList.add('active');

        if (triggerEdit && tabId === 'personal') {
            isEditing = false; // Reset to force true in toggle
            toggleEditMode();
        }
    }

    // Edit Profile Form Toggle (Replaces React State)
    let isEditing = false;

    function toggleEditMode() {
        isEditing = !isEditing;
        const viewElements = document.querySelectorAll('.view-mode');
        const editElements = document.querySelectorAll('.edit-mode');
        const btn = document.getElementById('editToggleBtn');

        if (isEditing) {
            viewElements.forEach(el => el.classList.add('d-none'));
            editElements.forEach(el => el.classList.remove('d-none'));
            btn.innerText = 'Cancel Edit';
        } else {
            viewElements.forEach(el => el.classList.remove('d-none'));
            editElements.forEach(el => el.classList.add('d-none'));
            btn.innerText = 'Edit Profile';
        }
    }

    // Auto-hide toast
    const toast = document.getElementById('toast');
    if (toast.classList.contains('toast-show')) {
        setTimeout(() => {
            toast.classList.remove('toast-show');
        }, 4000);
    }
</script>

<?php require_once '../includes/footer.php'; ?>