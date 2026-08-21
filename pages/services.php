<?php
// pages/services.php
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Mock data (In production, you would fetch this from MySQL via PDO)
$services = [
    [
        'id' => 1,
        'name' => 'Deep Home Cleaning',
        'category' => 'Cleaning',
        'rating' => 4.8,
        'base_price' => 120.00,
        'description' => 'Complete deep cleaning for your entire home.',
        'image_url' => '../assets/homecleaning.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Electrical Repair',
        'category' => 'Handyman',
        'rating' => 4.9,
        'base_price' => 85.00,
        'description' => 'Professional electrical fixes and installations.',
        'image_url' => '../assets/ElectricianService.jpg'
    ]
];

$error = null; // Set this to a string to test the error state
?>

<!-- flex-grow-1 pushes the footer to the bottom -->
<div class="flex-grow-1">
    <div class="bg-primary bg-opacity-10 py-5 mb-5">
        <div class="container text-center py-4">
            <h1 class="fw-bold display-5 text-dark">Our Services</h1>
            <p class="text-muted lead mb-0">Choose from our wide range of professional, vetted services.</p>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="row">
                <?php
                if (count($services) > 0) {
                    foreach ($services as $service) {
                        include '../includes/service-card.php';
                    }
                } else {
                    echo "
                    <div class='col-12 text-center py-5'>
                        <i class='fa-solid fa-box-open fs-1 text-muted mb-3'></i>
                        <h4 class='text-muted'>No services available at the moment.</h4>
                    </div>";
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>