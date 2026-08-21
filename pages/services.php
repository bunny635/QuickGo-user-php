<?php
// pages/services.php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// 1. Fetch Dynamic Counts (Customers & Providers)
$customerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$providerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'provider'")->fetchColumn();

// 2. Handle Search and Filtering
$selectedCategory = isset($_GET['category']) && $_GET['category'] !== 'All' ? $_GET['category'] : 'All';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// 3. Build Dynamic SQL Query
$sql = "
    SELECT s.*, p.name AS provider_name, p.profile_image, p.availability AS provider_availability, p.experience 
    FROM services s 
    JOIN providers p ON s.provider_id = p.id 
    WHERE s.is_active = 1
";
$params = [];

if ($selectedCategory !== 'All') {
    $sql .= " AND s.category = ?";
    $params[] = $selectedCategory;
}

if ($searchQuery !== '') {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ? OR p.name LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY s.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
    $error = null;
} catch (PDOException $e) {
    $services = [];
    $error = "Unable to connect to service catalog. Please check backend server.";
}

$categories = ['All', 'Home Cleaning', 'Garden Care', 'Electrician'];
?>

<style>
    /* Inherited from Services.css */
    .services-page {
        background-color: #0F1115;
        min-height: 100vh;
    }

    .services-header {
        padding: 100px 0 60px;
        background: linear-gradient(rgba(11, 11, 11, 0.85), rgba(11, 11, 11, 0.95)), url('../assets/images/bg.jpg') center/cover;
        border-bottom: 1px solid rgba(197, 160, 89, 0.15);
    }

    .main-service-card {
        background: #16181D;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        transition: 0.4s ease;
    }

    .main-service-card:hover {
        border-color: #D4AF37;
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
    }

    .card-img-wrapper {
        position: relative;
        height: 260px;
        overflow: hidden;
    }

    .service-main-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.6s ease;
    }

    .main-service-card:hover .service-main-img {
        transform: scale(1.08);
    }

    .category-tag {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.85);
        color: #D4AF37;
        padding: 8px 18px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 13px;
        text-transform: uppercase;
        border: 1px solid rgba(197, 160, 89, 0.3);
        backdrop-filter: blur(10px);
        z-index: 2;
    }

    .main-card-body {
        padding: 30px;
        text-align: left;
    }

    .btn-gold-outline {
        background: transparent;
        border: 1px solid #D4AF37;
        color: white;
        padding: 10px 22px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.3s ease;
    }

    .btn-gold-active {
        background: #D4AF37;
        border: 1px solid #D4AF37;
        color: black !important;
        box-shadow: 0 5px 15px rgba(197, 160, 89, 0.4);
    }

    .btn-action-premium {
        padding: 12px 25px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 800;
        border: none;
        transition: 0.3s ease;
    }

    .btn-action-premium.gold {
        background: #D4AF37;
        color: black;
    }

    .unavailable-btn {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Custom Toast UI */
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

    .toast-show {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }
</style>

<div class="services-page pb-5">

    <!-- Custom Vanilla JS Toast Container -->
    <div id="toast" class="custom-toast"></div>

    <!-- HEADER & FILTERS -->
    <section class="services-header text-center">
        <div class="container">
            <h1 class="text-white fw-bold display-4 mb-3" style="animation: fadeIn 0.8s ease;">
                QuickGo <span>Services Catalog</span>
            </h1>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                Book vetted, top-tier professionals for luxury residential maintenance and lifestyle services.
            </p>

            <!-- Dynamic Counts -->
            <div class="d-flex justify-content-center gap-4 mt-3">
                <span class="badge bg-dark border border-secondary text-gold px-3 py-2 fs-6">
                    <i class="fa-solid fa-users me-2"></i> <?= number_format($customerCount) ?> Happy Customers
                </span>
                <span class="badge bg-dark border border-secondary text-gold px-3 py-2 fs-6">
                    <i class="fa-solid fa-user-tie me-2"></i> <?= number_format($providerCount) ?> Expert Providers
                </span>
            </div>

            <!-- Form for Filtering and Searching -->
            <form id="filterForm" action="services.php" method="GET" class="mt-4">
                <input type="hidden" name="category" id="categoryInput" value="<?= htmlspecialchars($selectedCategory) ?>">

                <!-- Search Box -->
                <div class="search-box-wrapper mx-auto" style="max-width: 540px;">
                    <div class="d-flex align-items-center bg-dark border border-secondary rounded-pill px-3 py-2">
                        <i class="fa-solid fa-magnifying-glass text-gold me-2 fs-5"></i>
                        <input type="text" name="search" placeholder="Search services, providers, or locations..."
                            class="bg-transparent border-0 text-white w-100 shadow-none"
                            style="outline: none;" value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="btn btn-sm btn-gold rounded-pill ms-2">Search</button>
                    </div>
                </div>

                <!-- Category Pills -->
                <div class="category-pills-row d-flex flex-wrap justify-content-center gap-2 mt-4">
                    <?php foreach ($CATEGORIES as $cat): ?>
                        <button type="button"
                            class="btn btn-sm <?= $selectedCategory === $cat ? 'btn-gold-active' : 'btn-gold-outline' ?>"
                            style="border-radius: 20px; padding: 6px 16px; font-size: 13px; font-weight: 600;"
                            onclick="setCategory('<?= htmlspecialchars($cat) ?>')">
                            <?= htmlspecialchars($cat) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </section>

    <!-- SERVICES GRID -->
    <section class="py-5">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger bg-transparent text-danger border border-danger text-center my-4 py-4 rounded-4">
                    <i class="fa-solid fa-circle-exclamation fs-3 mb-2"></i>
                    <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php elseif (empty($services)): ?>
                <div class="text-center py-5 rounded-4 border border-secondary" style="background: rgba(255,255,255,0.02); padding: 60px 20px;">
                    <h3 class="text-white mb-2">No Active Services Found</h3>
                    <p class="text-muted mb-0">
                        <?= $selectedCategory !== 'All' ? "No verified services currently available in \"$selectedCategory\"." : "Check back soon as providers publish new offerings." ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php
                    foreach ($services as $service):
                        // Determine Availability
                        $actualAvailability = $service['provider_availability'] ?? ($service['availability'] ?? 'Online');
                        $isOnline = $actualAvailability === 'Online';
                        $isBusy = $actualAvailability === 'Busy';
                        $isOffline = $actualAvailability === 'Offline';

                        // Parse Image
                        $images = json_decode($service['images'], true);
                        $serviceImage = !empty($images) ? $images[0] : '../assets/images/fallback.jpg';
                        $price = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="main-service-card d-flex flex-column h-100"
                                style="opacity: <?= $isOffline ? '0.65' : '1' ?>; filter: <?= $isOffline ? 'grayscale(50%)' : 'none' ?>;">

                                <div class="card-img-wrapper">
                                    <img src="<?= htmlspecialchars($serviceImage) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-main-img">

                                    <?php if ($isOffline): ?>
                                        <div style="position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.4); z-index: 1;"></div>
                                    <?php endif; ?>

                                    <div class="category-tag">$<?= number_format($price, 2) ?> / hr</div>

                                    <?php
                                    // Status Colors
                                    $bg = $isOnline ? 'rgba(16, 185, 129, 0.9)' : ($isBusy ? 'rgba(245, 158, 11, 0.9)' : 'rgba(239, 68, 68, 0.9)');
                                    ?>
                                    <div style="position: absolute; top: 12px; left: 12px; background: <?= $bg ?>; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 15px; text-transform: uppercase; z-index: 2;">
                                        ● <?= htmlspecialchars($actualAvailability) ?>
                                    </div>
                                </div>

                                <div class="main-card-body flex-grow-1 d-flex flex-column justify-content-between p-4">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-gold small fw-bold"><i class="fa-solid fa-tag me-1"></i> <?= htmlspecialchars($service['category']) ?></span>
                                            <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($service['duration']) ?></span>
                                        </div>

                                        <h4 class="text-white fw-bold mb-2"><?= htmlspecialchars($service['title']) ?></h4>
                                        <p class="text-muted small mb-3" style="min-height: 38px;">
                                            <?= htmlspecialchars(strlen($service['description']) > 90 ? substr($service['description'], 0, 90) . '...' : ($service['description'] ?: 'Professional on-demand service delivered by verified QuickGo partner.')) ?>
                                        </p>

                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary">
                                            <div>
                                                <span class="text-white small fw-bold d-block"><?= htmlspecialchars($service['provider_name']) ?></span>
                                                <span class="text-muted small"><i class="fa-solid fa-location-dot me-1 text-gold"></i> <?= htmlspecialchars($service['location'] ?: 'Pan-City') ?></span>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-gold small fw-bold">
                                                    <i class="fa-solid fa-star me-1"></i> <?= number_format($service['rating'], 1) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 pt-3 mt-3 border-top border-secondary">
                                        <a href="service-details.php?id=<?= $service['id'] ?>" class="btn-gold-outline flex-fill text-center text-decoration-none" style="font-size: 13px; padding: 8px 12px;">Details</a>
                                        <button type="button"
                                            class="btn-action-premium gold flex-fill m-0 <?= !$isOnline ? 'unavailable-btn' : '' ?>"
                                            style="font-size: 13px; padding: 8px 12px;"
                                            onclick="handleBookNow(<?= $service['id'] ?>, '<?= $actualAvailability ?>')">
                                            Book Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    // Handle Category Pill Selection
    function setCategory(category) {
        document.getElementById('categoryInput').value = category;
        document.getElementById('filterForm').submit();
    }

    // Custom Toast Notification function
    function showToast(message, type = 'error') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'custom-toast toast-show';

        if (type === 'error') {
            toast.classList.add('toast-error');
        }

        setTimeout(() => {
            toast.classList.remove('toast-show');
        }, 4000);
    }

    // Booking Validation matching React's handleBookNow
    function handleBookNow(serviceId, availability) {
        if (availability === 'Busy') {
            showToast("Provider is currently busy serving another customer. Please try again later.", "error");
            return;
        }
        if (availability === 'Offline') {
            showToast("Provider is currently offline. Please try again later.", "error");
            return;
        }

        // If online, proceed to booking
        window.location.href = `book-service.php?service_id=${serviceId}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>