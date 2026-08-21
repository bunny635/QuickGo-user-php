<?php
// includes/profile-sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
$userName = $_SESSION['user_name'] ?? 'Customer';
?>
<div class="card shadow-sm mb-4" style="background: var(--secondary-black); border: 1px solid #333; border-radius: 12px;">
    <div class="card-body text-center p-4">
        <div class="position-relative d-inline-block mb-3">
            <img src="../assets/images/default-avatar.png" alt="Profile" class="rounded-circle" width="100" height="100" style="border: 2px solid var(--gold-accent); padding: 3px;">
        </div>
        <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($userName) ?></h5>
        <p class="text-muted small mb-0">QuickGo Member</p>
    </div>

    <div class="list-group list-group-flush border-top border-dark" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <a href="profile.php" class="list-group-item list-group-item-action bg-transparent text-white py-3 <?= $currentPage === 'profile.php' ? 'fw-bold' : '' ?>" <?= $currentPage === 'profile.php' ? 'style="color: var(--gold-accent) !important;"' : '' ?>>
            <i class="fa-solid fa-user me-3 w-20px text-center"></i> Personal Details
        </a>
        <a href="my-bookings.php" class="list-group-item list-group-item-action bg-transparent text-white py-3 <?= $currentPage === 'my-bookings.php' ? 'fw-bold' : '' ?>" <?= $currentPage === 'my-bookings.php' ? 'style="color: var(--gold-accent) !important;"' : '' ?>>
            <i class="fa-solid fa-calendar-check me-3 w-20px text-center"></i> My Bookings
        </a>
        <a href="payment-history.php" class="list-group-item list-group-item-action bg-transparent text-white py-3 <?= $currentPage === 'payment-history.php' ? 'fw-bold' : '' ?>" <?= $currentPage === 'payment-history.php' ? 'style="color: var(--gold-accent) !important;"' : '' ?>>
            <i class="fa-solid fa-receipt me-3 w-20px text-center"></i> Payment History
        </a>
        <a href="digital-card.php" class="list-group-item list-group-item-action bg-transparent text-white py-3 <?= $currentPage === 'digital-card.php' ? 'fw-bold' : '' ?>" <?= $currentPage === 'digital-card.php' ? 'style="color: var(--gold-accent) !important;"' : '' ?>>
            <i class="fa-solid fa-id-card me-3 w-20px text-center"></i> Digital Card
        </a>
        <a href="../auth/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger py-3">
            <i class="fa-solid fa-arrow-right-from-bracket me-3 w-20px text-center"></i> Logout
        </a>
    </div>
</div>