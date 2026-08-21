<?php
// pages/home.php
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="home-container">
    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div>
                <h1 class="hero-title">Your Home, Our <br> <span>Expert Care</span></h1>
                <p class="hero-tagline">Your Time, Our Priority. Luxury Home Services in one click.</p>
                <div class="hero-btns">
                    <a href="services.php" class="btn-gold">Book a Service</a>
                    <a href="about.php" class="btn-outline-gold ms-3">Our Story</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED HOME SERVICES -->
    <section class="section-padding">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Home Service <span>Essentials</span></h2>
                <p class="text-muted">High-quality solutions for your everyday living.</p>
            </div>
            <div class="row">
                <!-- Card 1 -->
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <img src="../assets/images/homecleaning.jpg" alt="Home Cleaning" class="card-img" onerror="this.src='https://via.placeholder.com/400x200?text=Home+Cleaning'">
                        <div class="card-content">
                            <h4>Home Cleaning</h4>
                            <p>Premium deep cleaning for high-end residential interiors.</p>
                            <a href="services.php" class="card-link">Browse Services <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <img src="../assets/images/GardenCleaning.jpg" alt="Garden Care" class="card-img" onerror="this.src='https://via.placeholder.com/400x200?text=Garden+Care'">
                        <div class="card-content">
                            <h4>Garden Care</h4>
                            <p>Professional landscaping and yard maintenance services.</p>
                            <a href="services.php" class="card-link">Browse Services <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <img src="../assets/images/elo.jpg" alt="Electrician" class="card-img" onerror="this.src='https://via.placeholder.com/400x200?text=Electrician'">
                        <div class="card-content">
                            <h4>Electrician</h4>
                            <p>Certified experts for all your electrical repairs.</p>
                            <a href="services.php" class="card-link">Browse Services <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE QUICKGO -->
    <section class="section-padding bg-black-light">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="feature-icon"><i class="fa-regular fa-clock"></i></div>
                    <h5 class="mt-3 text-white">On-Time Arrival</h5>
                    <p class="text-muted small">We value your schedule above all.</p>
                </div>
                <div class="col-md-3">
                    <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h5 class="mt-3 text-white">Trusted Experts</h5>
                    <p class="text-muted small">Strict background-verified professionals.</p>
                </div>
                <div class="col-md-3">
                    <div class="feature-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                    <h5 class="mt-3 text-white">Transparent Pricing</h5>
                    <p class="text-muted small">Premium services at honest rates.</p>
                </div>
                <div class="col-md-3">
                    <div class="feature-icon"><i class="fa-solid fa-headphones"></i></div>
                    <h5 class="mt-3 text-white">24/7 Concierge</h5>
                    <p class="text-muted small">Support whenever you need assistance.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
require_once '../includes/footer.php';
?>