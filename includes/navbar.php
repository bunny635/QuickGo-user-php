<?php
// includes/navbar.php
$isLoggedIn = isset($_SESSION['user_id']);
// Mock user data for display
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Customer";
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar-custom">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="../pages/home.php" class="navbar-brand-custom">
            <img src="../assets/images/weblogo.jpg" alt="QuickGo" class="nav-logo" onerror="this.src='https://via.placeholder.com/42'">
            <h4 class="brand-text">Quick<span>Go</span></h4>
        </a>

        <div class="menu-icon" id="mobileMenuBtn">
            <i class="fa-solid fa-bars" id="menuIcon"></i>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="../pages/home.php" class="<?= $currentPage == 'home.php' ? 'active-link' : '' ?>">Home</a></li>
            <li><a href="../pages/about.php" class="<?= $currentPage == 'about.php' ? 'active-link' : '' ?>">About</a></li>
            <li><a href="../pages/services.php" class="<?= $currentPage == 'services.php' ? 'active-link' : '' ?>">Services</a></li>

            <?php if ($isLoggedIn): ?>
                <li><a href="../pages/my-bookings.php" class="<?= $currentPage == 'my-bookings.php' ? 'active-link' : '' ?>">My Bookings</a></li>
            <?php endif; ?>

            <li><a href="../pages/contact.php" class="<?= $currentPage == 'contact.php' ? 'active-link' : '' ?>">Contact</a></li>

            <?php if ($isLoggedIn): ?>
                <li class="user-nav-wrapper">
                    <div style="display: flex; align-items: center;">
                        <div class="user-profile-pill">
                            <a href="../pages/profile.php" class="user-name-text">
                                <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($userName) ?>
                            </a>
                            <a href="../auth/logout.php" class="nav-logout-btn" title="Log Out">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </a>
                        </div>
                    </div>
                </li>
            <?php else: ?>
                <li class="auth-links">
                    <a href="../auth/login.php" class="login-link text-white text-decoration-none">Login</a>
                    <span class="divider">|</span>
                    <a href="../auth/register.php" class="register-link text-white text-decoration-none">Register</a>
                </li>
            <?php endif; ?>

            <li>
                <a href="../pages/services.php" class="btn-gold">Book Now</a>
            </li>
        </ul>
    </div>
</nav>

<script>
    // Vanilla JS replacement for React setIsOpen state
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        const navLinks = document.getElementById('navLinks');
        const icon = document.getElementById('menuIcon');

        navLinks.classList.toggle('open');

        if (navLinks.classList.contains('open')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
</script>