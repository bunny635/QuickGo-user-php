<?php
// pages/about.php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<style>
    /* About.css */
    .about-hero {
        padding: 100px 0 60px;
        background: linear-gradient(rgba(11, 11, 11, 0.8), rgba(11, 11, 11, 0.8)), url('../assets/images/lobby.jpg') center/cover;
    }

    .about-hero h1 span {
        color: var(--gold-accent);
    }

    .shadow-gold {
        box-shadow: 0 10px 30px rgba(197, 160, 89, 0.2);
    }

    .mv-card {
        background: var(--primary-black);
        padding: 40px;
        border: 1px solid #333;
        border-radius: 15px;
        height: 100%;
        transition: 0.3s;
    }

    .mv-card:hover {
        border-color: var(--gold-accent);
    }

    .mv-icon {
        font-size: 40px;
        color: var(--gold-accent);
        margin-bottom: 20px;
    }

    .trust-card {
        padding: 20px;
    }

    .trust-icon {
        font-size: 35px;
        color: var(--gold-accent);
    }

    .process-timeline {
        max-width: 800px;
        margin: 0 auto;
    }

    .process-step {
        display: flex;
        align-items: flex-start;
        gap: 30px;
        margin-bottom: 40px;
        padding: 20px;
        background: var(--primary-black);
        border-radius: 12px;
    }

    .step-num {
        font-size: 24px;
        font-weight: 800;
        color: var(--gold-accent);
        border-bottom: 2px solid var(--gold-accent);
    }

    .team-card {
        background: var(--secondary-black);
        padding: 30px;
        border-radius: 15px;
        border: 1px solid #222;
        transition: transform 0.3s ease;
    }

    .team-card:hover {
        transform: scale(1.05);
    }

    .team-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--gold-accent);
    }

    .text-gold {
        color: var(--gold-accent);
    }

    .fade-in-up {
        animation: fadeInUp 0.8s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="about-container bg-primary-black">
    <!-- 1. Header Section -->
    <section class="about-hero text-center">
        <div class="container fade-in-up">
            <h1 class="display-4 fw-bold text-white">Redefining <span>Home Services</span></h1>
            <p class="lead text-muted">Your Time, Our Priority. The QuickGo Story.</p>
        </div>
    </section>

    <!-- 2. Company Story -->
    <section class="section-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0 fade-in-up">
                    <img src="../assets/images/companymeet.jpg" alt="Our Story" class="img-fluid rounded-4 shadow-gold" onerror="this.src='https://via.placeholder.com/600x400'">
                </div>
                <div class="col-md-6 ps-md-5 fade-in-up" style="animation-delay: 0.2s;">
                    <h2 class="section-title text-start">Our <span>Story</span></h2>
                    <p class="text-muted mt-3">Founded in 2026, QuickGo was born out of a simple observation: finding reliable, premium home services was too difficult. We set out to create a platform that values the customer's time above all else.</p>
                    <p class="text-muted">Today, QuickGo stands as a symbol of luxury and trust, connecting thousands of households with background-verified experts who treat every home like their own.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Mission & Vision -->
    <section class="section-padding bg-black-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 fade-in-up">
                    <div class="mv-card">
                        <i class="fa-solid fa-bullseye mv-icon"></i>
                        <h3 class="text-white">Our Mission</h3>
                        <p class="text-muted">To provide seamless, high-quality home solutions that simplify life and return valuable time back to our clients.</p>
                    </div>
                </div>
                <div class="col-md-6 fade-in-up" style="animation-delay: 0.2s;">
                    <div class="mv-card">
                        <i class="fa-regular fa-eye mv-icon"></i>
                        <h3 class="text-white">Our Vision</h3>
                        <p class="text-muted">To be the global gold standard for home maintenance and lifestyle services, known for excellence and punctuality.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Why Choose Us -->
    <section class="section-padding">
        <div class="container text-center">
            <h2 class="section-title mb-5">Why People <span>Trust Us</span></h2>
            <div class="row fade-in-up">
                <div class="col-md-4 mb-4">
                    <div class="trust-card">
                        <i class="fa-solid fa-award trust-icon"></i>
                        <h5 class="mt-3 text-white">Certified Excellence</h5>
                        <p class="text-muted small">Only the top 5% of applicants make it to our team.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="trust-card">
                        <i class="fa-solid fa-users trust-icon"></i>
                        <h5 class="mt-3 text-white">Customer Centric</h5>
                        <p class="text-muted small">Dedicated support managers for every service.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="trust-card">
                        <i class="fa-regular fa-circle-check trust-icon"></i>
                        <h5 class="mt-3 text-white">Guaranteed Satisfaction</h5>
                        <p class="text-muted small">If you aren't happy, we'll make it right at no cost.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Our Process -->
    <section class="section-padding bg-black-light">
        <div class="container fade-in-up">
            <h2 class="section-title text-center mb-5">Our <span>Process</span></h2>
            <div class="process-timeline">
                <div class="process-step">
                    <span class="step-num">01</span>
                    <div>
                        <h5 class="text-white">Rigorous Screening</h5>
                        <p class="text-muted">Background checks and skills assessment of all partners.</p>
                    </div>
                </div>
                <div class="process-step">
                    <span class="step-num">02</span>
                    <div>
                        <h5 class="text-white">Smart Matching</h5>
                        <p class="text-muted">Assigning the best-suited expert based on your specific requirements.</p>
                    </div>
                </div>
                <div class="process-step">
                    <span class="step-num">03</span>
                    <div>
                        <h5 class="text-white">Service Execution</h5>
                        <p class="text-muted">Top-tier service delivered with premium equipment and care.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Professional Team -->
    <section class="section-padding">
        <div class="container text-center fade-in-up">
            <h2 class="section-title mb-5">The Leadership Team </h2>
            <div class="row justify-content-center">
                <div class="col-md-3 mb-4">
                    <div class="team-card">
                        <img src="../assets/images/avatar3.jpg" alt="Vinay Dharaiya" class="team-img" onerror="this.src='https://via.placeholder.com/120'">
                        <h5 class="mt-3 mb-1 text-gold">Vinay Dharaiya</h5>
                        <p class="text-gold small fw-bold">Founder & CEO</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="team-card">
                        <img src="../assets/images/avatar1.jpg" alt="Kavya Desai" class="team-img" onerror="this.src='https://via.placeholder.com/120'">
                        <h5 class="mt-3 mb-1 text-gold">Kavya Desai</h5>
                        <p class="text-gold small fw-bold">Operations Head</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="team-card">
                        <img src="../assets/images/avatar2.jpg" alt="Smit Ghoghari" class="team-img" onerror="this.src='https://via.placeholder.com/120'">
                        <h5 class="mt-3 mb-1 text-gold">Smit Ghoghari</h5>
                        <p class="text-gold small fw-bold">Service Director</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>