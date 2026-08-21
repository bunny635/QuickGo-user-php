<?php
// includes/service-card.php
// Replaces ServiceCard.jsx. Ensure $service exists before rendering.
if (!isset($service)) return;
?>
<div class="col-md-4 mb-4">
    <div class="service-card h-100 d-flex flex-column">
        <img src="<?= htmlspecialchars($service['img'] ?? '../assets/images/fallback.jpg') ?>"
            alt="<?= htmlspecialchars($service['title']) ?>"
            class="card-img" />

        <div class="card-content d-flex flex-column flex-grow-1">
            <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                <h4 class="mb-0"><?= htmlspecialchars($service['title']) ?></h4>
                <span class="text-gold fw-bold">$<?= number_format($service['base_price'] ?? 0, 2) ?></span>
            </div>

            <p class="flex-grow-1"><?= htmlspecialchars($service['desc']) ?></p>

            <!-- Link replaces React Router useNavigate or <Link> -->
            <a href="../pages/service-details.php?id=<?= urlencode($service['id']) ?>" class="card-link text-decoration-none mt-auto">
                Book Now <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>