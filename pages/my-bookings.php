<?php
// pages/my-bookings.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Fetch all bookings for the logged-in user
// Fetch all bookings for the logged-in user
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        s.title AS service_name, 
        s.images AS service_images,
        pu.name AS provider_name, 
        pu.profile_image AS provider_image, 
        p.experience,
        pay.method AS payment_method_name,
        pm.card_brand,
        pm.last4
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN providers p ON b.provider_id = p.id
    JOIN users pu ON p.user_id = pu.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    LEFT JOIN payment_methods pm ON pay.payment_method_id = pm.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();


// Fetch user's reviews to map against bookings
$revStmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ?");
$revStmt->execute([$user_id]);
$reviews = $revStmt->fetchAll();
$reviewMap = [];
foreach ($reviews as $r) {
    $reviewMap[$r['booking_id']] = $r;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<style>
    /* ==========================================
   MY BOOKINGS CSS (From MyBookings.css)
========================================== */
    .my-bookings-container {
        background-color: var(--primary-black, #0F1115);
        min-height: 100vh;
    }

    .booking-filters-wrapper {
        display: flex;
        gap: 15px;
    }

    .filter-search,
    .filter-dropdown {
        background: #111;
        border: 1px solid #222;
        border-radius: 10px;
        padding: 0 15px;
        display: flex;
        align-items: center;
        flex: 1;
    }

    .filter-search input,
    .filter-dropdown select {
        background: transparent;
        border: none;
        color: white;
        padding: 12px;
        width: 100%;
        outline: none;
        font-size: 14px;
    }

    .filter-dropdown select option {
        background: #111;
        color: white;
    }

    .icon-gold {
        color: var(--gold-accent, #D4AF37);
    }

    .booking-glass-card {
        background: rgba(22, 22, 22, 0.7);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(197, 160, 89, 0.15);
        border-radius: 20px;
        padding: 30px;
        transition: 0.4s ease;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    .booking-glass-card:hover {
        border-color: var(--gold-accent, #D4AF37);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transform: translateY(-5px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .border-end-premium {
        border-right: 1px solid #222;
    }

    .x-small {
        font-size: 10px;
        letter-spacing: 1px;
    }

    /* Status Badges */
    .badge-status {
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
    }

    .badge-status.confirmed,
    .badge-status.completed {
        background: rgba(197, 160, 89, 0.2);
        color: var(--gold-accent, #D4AF37);
        border: 1px solid rgba(197, 160, 89, 0.3);
    }

    .badge-status.pending {
        background: rgba(100, 100, 100, 0.2);
        color: #ccc;
        border: 1px solid #555;
    }

    .badge-status.cancelled,
    .badge-status.rejected {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    /* Buttons */
    .btn-action-premium {
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        transition: 0.3s;
        border: 1px solid transparent;
        text-decoration: none;
        text-align: center;
    }

    .btn-action-premium.gold {
        background: var(--gold-accent, #D4AF37);
        color: black;
    }

    .btn-action-premium.gold:hover {
        background: #e6ca65;
        box-shadow: 0 5px 15px rgba(197, 160, 89, 0.3);
    }

    /* Review Modal specific */
    .star-rating i {
        cursor: pointer;
        transition: 0.2s;
    }

    .star-rating i:hover {
        transform: scale(1.2);
    }

    /* Toast */
    .custom-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10500;
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
        .booking-filters-wrapper {
            flex-direction: column;
        }

        .border-end-premium {
            border-right: none;
            border-bottom: 1px solid #222;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
    }
</style>

<div class="my-bookings-container py-5">
    <div id="toast" class="custom-toast"></div>

    <div class="container pt-4">

        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="text-white fw-bold display-5" style="font-family: 'Playfair Display', serif;">My <span style="color: #D4AF37;">Bookings</span></h2>
                <p class="text-muted">Manage your appointments, status updates, and premium service invoices.</p>
            </div>
            <div class="col-lg-6">
                <div class="booking-filters-wrapper">
                    <div class="filter-search">
                        <i class="fa-solid fa-magnifying-glass icon-gold"></i>
                        <input type="text" id="searchInput" placeholder="Search ID or Service..." onkeyup="filterBookings()">
                    </div>
                    <div class="filter-dropdown">
                        <i class="fa-solid fa-filter icon-gold"></i>
                        <select id="statusFilter" onchange="filterBookings()">
                            <option value="All">All Status</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="bookingsContainer">
            <?php if (empty($bookings)): ?>
                <div class="col-12 text-center py-5 border border-secondary rounded-4" style="background: rgba(0,0,0,0.4)">
                    <i class="fa-regular fa-file-lines text-muted mb-3" style="font-size: 50px;"></i>
                    <h4 class="text-white">No Bookings Found</h4>
                    <p class="text-muted">You haven't scheduled any services yet.</p>
                </div>
            <?php else: ?>
                <?php
                $delay = 0;
                foreach ($bookings as $item):
                    $bStatus = $item['booking_status'];
                    $bId = $item['id'];
                    $ref = $item['booking_ref'];
                    $serviceName = $item['service_name'];

                    // JSON decode images
                    $images = json_decode($item['service_images'], true);
                    $sImg = !empty($images) ? $images[0] : '../assets/images/fallback.jpg';
                    $pImg = !empty($item['provider_image']) ? $item['provider_image'] : '../assets/images/default-avatar.png';

                    $isCancellable = !in_array($bStatus, ['Completed', 'Cancelled', 'Rejected']);
                    $hasReview = isset($reviewMap[$bId]);
                    $reviewData = $hasReview ? $reviewMap[$bId] : null;

                    // Match React CSS classes
                    $badgeClass = 'badge-status ' . strtolower(str_replace(' ', '-', $bStatus));
                    $iconClass = in_array($bStatus, ['Confirmed', 'Completed']) ? 'fa-circle-check' : 'fa-clock';
                ?>

                    <div class="col-12 mb-4 booking-item" data-search="<?= strtolower($ref . ' ' . $serviceName) ?>" data-status="<?= $bStatus ?>">
                        <div class="booking-glass-card" style="animation-delay: <?= $delay ?>s;">
                            <div class="row align-items-center g-4">

                                <div class="col-lg-4 border-end-premium">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="<?= htmlspecialchars($sImg) ?>" alt="Service" style="width: 60px; height: 60px; border-radius: 12px; object-fit: cover;">
                                        <div>
                                            <span class="text-muted x-small fw-bold">ID: <?= htmlspecialchars($ref) ?></span>
                                            <h5 class="text-white mb-0 mt-1"><?= htmlspecialchars($serviceName) ?></h5>
                                        </div>
                                    </div>
                                    <span class="<?= $badgeClass ?>">
                                        <i class="fa-solid <?= $iconClass ?> me-1"></i>
                                        <?= htmlspecialchars($bStatus) ?> • <?= date('M d, Y', strtotime($item['booking_date'])) ?> <?= htmlspecialchars($item['booking_time']) ?>
                                    </span>
                                </div>

                                <div class="col-lg-3 border-end-premium">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($pImg) ?>" alt="Provider" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #D4AF37; object-fit: cover;">
                                        <div>
                                            <label class="text-muted x-small text-uppercase fw-bold mb-0">Assigned Provider</label>
                                            <h6 class="text-white mb-0 mt-1" style="font-size: 14px;"><?= htmlspecialchars($item['provider_name']) ?></h6>
                                            <span class="text-muted small" style="font-size: 11px;"><i class="fa-solid fa-location-dot" style="color: #D4AF37;"></i> <?= htmlspecialchars($item['address']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 border-end-premium">
                                    <label class="text-muted x-small text-uppercase fw-bold mb-2 d-block">Transaction Details</label>
                                    <h4 class="mb-1" style="color: #D4AF37;">$<?= number_format($item['grand_total'], 2) ?></h4>
                                    <div class="text-muted small mb-1">
                                        <?= $item['booked_hours'] ?> hrs × $<?= number_format($item['hourly_pay'], 2) ?>/hr
                                    </div>

                                    <?php if (in_array($item['payment_status'], ['Paid', 'Success'])): ?>
                                        <span class="text-success small fw-bold"><i class="fa-solid fa-circle-check me-1"></i> PAID VIA <?= $item['card_brand'] ? htmlspecialchars($item['card_brand'] . " •••• " . $item['last4']) : htmlspecialchars($item['payment_method_name']) ?></span>
                                    <?php elseif ($item['payment_status'] === 'Refunded'): ?>
                                        <span class="text-info small fw-bold"><i class="fa-solid fa-circle-check me-1"></i> REFUND PROCESSED</span>
                                    <?php else: ?>
                                        <span class="text-warning small fw-bold">PAYMENT PENDING</span>
                                    <?php endif; ?>

                                    <div class="mt-1 x-small text-muted">ETA: <?= htmlspecialchars($item['estimated_arrival']) ?></div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="d-grid gap-2">
                                        <a href="invoice.php?id=<?= $bId ?>" class="btn-action-premium gold">
                                            <i class="fa-regular fa-file-lines me-1"></i> Invoice
                                        </a>

                                        <?php if ($isCancellable): ?>
                                            <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 12px;" onclick="cancelBooking(<?= $bId ?>)" id="cancel-btn-<?= $bId ?>">
                                                Cancel Booking
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($bStatus === 'Completed'): ?>
                                            <button class="btn btn-sm btn-outline-warning" style="border-radius: 8px; font-size: 12px; border-color: #D4AF37; color: #D4AF37;"
                                                onclick="openReviewModal(<?= $bId ?>, <?= $item['provider_id'] ?>, <?= $item['service_id'] ?>, <?= $hasReview ? $reviewData['rating'] : 0 ?>, '<?= htmlspecialchars($hasReview ? $reviewData['comment'] : '') ?>', <?= $hasReview ? $reviewData['id'] : 0 ?>)">
                                                <i class="fa-solid fa-star me-1"></i> <?= $hasReview ? 'Edit Review' : 'Write Review' ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php
                    $delay += 0.05;
                endforeach;
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- REVIEW MODAL -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg" style="background: var(--secondary-black); border: 1px solid var(--gold-accent); border-radius: 12px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title text-white fw-bold"><i class="fa-solid fa-star me-2" style="color: #D4AF37;"></i> <span id="reviewModalTitle">Write a Review</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-white">
                <form id="reviewForm" onsubmit="submitReview(event)">
                    <input type="hidden" id="rev_booking_id">
                    <input type="hidden" id="rev_provider_id">
                    <input type="hidden" id="rev_service_id">
                    <input type="hidden" id="rev_id" value="0">
                    <input type="hidden" id="rev_rating" value="0">

                    <div class="text-center mb-4">
                        <p class="text-muted mb-2">How would you rate this service?</p>
                        <div class="star-rating fs-2 text-muted" id="starContainer">
                            <i class="fa-solid fa-star" data-val="1" onclick="setRating(1)"></i>
                            <i class="fa-solid fa-star" data-val="2" onclick="setRating(2)"></i>
                            <i class="fa-solid fa-star" data-val="3" onclick="setRating(3)"></i>
                            <i class="fa-solid fa-star" data-val="4" onclick="setRating(4)"></i>
                            <i class="fa-solid fa-star" data-val="5" onclick="setRating(5)"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Share your experience</label>
                        <textarea id="rev_comment" class="form-control text-white" style="background: rgba(0,0,0,0.5); border: 1px solid #333; outline: none;" rows="4" placeholder="The professional was great..." required minlength="10" maxlength="500"></textarea>
                    </div>

                    <button type="submit" class="btn-action-premium gold w-100 mt-2" id="revSubmitBtn">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // --- CLIENT-SIDE INSTANT FILTERING (Replaces React State Filtering) ---
    function filterBookings() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const items = document.querySelectorAll('.booking-item');
        let visibleCount = 0;

        items.forEach(item => {
            const itemSearch = item.getAttribute('data-search');
            const itemStatus = item.getAttribute('data-status');

            const matchesSearch = itemSearch.includes(search);
            const matchesStatus = (status === 'All' || itemStatus === status);

            if (matchesSearch && matchesStatus) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Handle empty state if no filters match
        let emptyState = document.getElementById('filterEmptyState');
        if (visibleCount === 0 && items.length > 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.id = 'filterEmptyState';
                emptyState.className = 'col-12 text-center py-5 border border-secondary rounded-4';
                emptyState.style.background = 'rgba(0,0,0,0.4)';
                emptyState.innerHTML = '<h4 class="text-white">No matches found</h4><p class="text-muted">Try adjusting your filters.</p>';
                document.getElementById('bookingsContainer').appendChild(emptyState);
            }
            emptyState.style.display = 'block';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    // --- CANCELLATION LOGIC ---
    async function cancelBooking(bookingId) {
        if (!confirm("Are you sure you want to cancel this booking?")) return;

        const btn = document.getElementById('cancel-btn-' + bookingId);
        const originalText = btn.innerText;
        btn.innerText = "Cancelling...";
        btn.disabled = true;

        try {
            const res = await fetch('../actions/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `booking_id=${bookingId}`
            });
            const data = await res.json();

            if (data.success) {
                showToast("Booking cancelled successfully.", "success");
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showToast(err.message || "Failed to cancel booking.", "error");
            btn.innerText = originalText;
            btn.disabled = false;
        }
    }

    // --- REVIEW MODAL LOGIC ---
    function openReviewModal(bookingId, providerId, serviceId, currentRating, currentComment, reviewId) {
        document.getElementById('rev_booking_id').value = bookingId;
        document.getElementById('rev_provider_id').value = providerId;
        document.getElementById('rev_service_id').value = serviceId;
        document.getElementById('rev_id').value = reviewId;
        document.getElementById('rev_comment').value = currentComment;
        document.getElementById('reviewModalTitle').innerText = reviewId > 0 ? "Edit Review" : "Write a Review";

        setRating(currentRating || 0);

        var reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        reviewModal.show();
    }

    function setRating(rating) {
        document.getElementById('rev_rating').value = rating;
        const stars = document.querySelectorAll('#starContainer i');
        stars.forEach(star => {
            if (parseInt(star.getAttribute('data-val')) <= rating) {
                star.style.color = '#D4AF37'; // Gold
            } else {
                star.style.color = '#6c757d'; // Muted
            }
        });
    }

    async function submitReview(e) {
        e.preventDefault();
        const rating = document.getElementById('rev_rating').value;
        if (rating == 0) {
            showToast("Please select a star rating.", "error");
            return;
        }

        const btn = document.getElementById('revSubmitBtn');
        btn.innerText = "Submitting...";
        btn.disabled = true;

        const payload = {
            review_id: document.getElementById('rev_id').value,
            booking_id: document.getElementById('rev_booking_id').value,
            provider_id: document.getElementById('rev_provider_id').value,
            service_id: document.getElementById('rev_service_id').value,
            rating: rating,
            comment: document.getElementById('rev_comment').value
        };

        try {
            const res = await fetch('../actions/review_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, "success");
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showToast(err.message || "Failed to submit review.", "error");
            btn.innerText = "Submit Review";
            btn.disabled = false;
        }
    }

    // Toast helper
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'custom-toast toast-show ' + (type === 'error' ? 'toast-error' : 'toast-success');
        setTimeout(() => toast.classList.remove('toast-show'), 3000);
    }
</script>

<?php require_once '../includes/footer.php'; ?>