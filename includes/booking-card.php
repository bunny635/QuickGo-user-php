<?php
// includes/booking-card.php
if (!isset($booking)) return;

// Determine badge color based on booking status
$statusClass = match (strtolower($booking['status'])) {
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-success text-white',
    'completed' => 'bg-primary text-white',
    'cancelled' => 'bg-danger text-white',
    default => 'bg-secondary text-white'
};
?>
<div class="card mb-4 shadow-sm" style="background: var(--secondary-black); border: 1px solid #333; border-radius: 12px;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <!-- Date & Time -->
            <div class="col-md-2 text-center mb-3 mb-md-0 border-end border-dark">
                <h4 style="color: var(--gold-accent);" class="fw-bold mb-0">
                    <?= date('M d', strtotime($booking['booking_date'])) ?>
                </h4>
                <small class="text-muted"><?= date('h:i A', strtotime($booking['booking_time'])) ?></small>
            </div>

            <!-- Booking Details -->
            <div class="col-md-6 mb-3 mb-md-0 ps-md-4">
                <h5 class="text-white fw-bold mb-2"><?= htmlspecialchars($booking['service_name']) ?></h5>
                <p class="text-muted mb-2 small">
                    <i class="fa-solid fa-location-dot me-2" style="color: var(--gold-accent);"></i>
                    <?= htmlspecialchars($booking['address']) ?>
                </p>
                <span class="badge <?= $statusClass ?> rounded-pill px-3 py-2 fw-medium">
                    <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                </span>
            </div>

            <!-- Actions & Price -->
            <div class="col-md-4 text-md-end">
                <h4 class="fw-bold mb-3" style="color: var(--gold-accent);">
                    $<?= number_format($booking['total_amount'], 2) ?>
                </h4>

                <?php if (strtolower($booking['status']) === 'pending'): ?>
                    <button type="button" class="btn btn-gold btn-sm px-4 rounded-pill"
                        data-bs-toggle="modal"
                        data-bs-target="#paymentModal"
                        data-booking-id="<?= $booking['id'] ?>"
                        data-amount="<?= $booking['total_amount'] ?>">
                        Pay Now
                    </button>

                <?php elseif (strtolower($booking['status']) === 'completed'): ?>
                    <a href="../pages/invoice.php?id=<?= $booking['id'] ?>" class="btn btn-outline-gold btn-sm px-3 rounded-pill">
                        <i class="fa-solid fa-download me-1"></i> Invoice
                    </a>
                    <a href="../pages/reviews.php?service_id=<?= $booking['service_id'] ?>" class="btn btn-gold btn-sm px-3 rounded-pill ms-2">
                        Rate
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>