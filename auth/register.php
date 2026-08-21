<?php
// auth/register.php
session_start();
require_once '../config/database.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/home.php");
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $agreeTerms = isset($_POST['agreeTerms']) ? true : false;

    // Validation Rules (Matching React Logic)
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!$agreeTerms) {
        $error = "Please agree to the Terms & Conditions.";
    } else {
        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Password Hashing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer'; // Default role

            // Insert User
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");

            try {
                $insertStmt->execute([$name, $email, $phone, $hashedPassword, $role]);
                $success = "Account created successfully! Redirecting to login...";
                // Auto-redirect via JS after success toast shows
            } catch (PDOException $e) {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QuickGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Auth Specific Styles from Auth.css */
        .auth-page-container {
            background: url('../assets/images/bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        .auth-page-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            /* Cinematic overlay replacement */
        }

        .auth-wrapper {
            background: transparent !important;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 10;
            padding: 30px 20px;
        }

        .glass-card {
            width: 100%;
            max-width: 440px;
            background: rgba(15, 17, 21, 0.82) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(197, 160, 89, 0.25);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85);
            animation: fadeInScale 0.8s ease forwards;
        }

        .register-card {
            max-width: 490px;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.9) translateY(30px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .auth-logo {
            height: 65px;
            border-radius: 50%;
            border: 2px solid var(--gold-accent);
            margin-bottom: 12px;
        }

        .auth-title {
            color: white;
            font-weight: 800;
            font-family: 'Playfair Display', serif;
        }

        .auth-title span {
            color: var(--gold-accent);
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: var(--gold-accent);
            font-size: 18px;
            z-index: 5;
        }

        .auth-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.45) !important;
            border: 1px solid #333;
            padding: 12px 15px 12px 45px;
            border-radius: 10px;
            color: white !important;
            outline: none;
            transition: 0.3s;
            font-size: 14px;
        }

        .auth-input:focus {
            border-color: var(--gold-accent);
            background: rgba(0, 0, 0, 0.65) !important;
        }

        .auth-link-gold {
            color: var(--gold-accent);
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }

        .auth-link-gold:hover {
            text-decoration: underline !important;
            color: #e6ca65;
        }

        .custom-check:checked {
            background-color: var(--gold-accent);
            border-color: var(--gold-accent);
        }

        /* Toastify Mimic */
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
    </style>
</head>

<body>

    <div class="auth-page-container">
        <!-- Custom Toast Notification -->
        <div id="toast" class="custom-toast <?= $error ? 'toast-error toast-show' : ($success ? 'toast-success toast-show' : '') ?>">
            <?= htmlspecialchars($error ?? $success ?? '') ?>
        </div>

        <div class="auth-wrapper">
            <div class="glass-card register-card">
                <div class="text-center mb-3">
                    <img src="../assets/images/weblogo.jpg" alt="QuickGo" class="auth-logo" onerror="this.src='https://via.placeholder.com/65'">
                    <h2 class="auth-title mt-1">Join <span>QuickGo</span></h2>
                    <p class="text-muted small">Create your customer account</p>
                </div>

                <form action="register.php" method="POST">
                    <div class="input-group-custom">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" name="name" placeholder="Full Name" class="auth-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Email Address" class="auth-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-phone input-icon"></i>
                        <input type="tel" name="phone" placeholder="Phone Number" class="auth-input" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="regPassword" placeholder="Create Password (min 6 chars)" class="auth-input" style="padding-right: 45px;" required>
                        <button type="button" onclick="togglePwd('regPassword', 'eyeIcon1')" style="position: absolute; right: 15px; background: none; border: none; color: #D4AF37; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-circle-check input-icon"></i>
                        <input type="password" name="confirmPassword" id="regConfirm" placeholder="Confirm Password" class="auth-input" style="padding-right: 45px;" required>
                        <button type="button" onclick="togglePwd('regConfirm', 'eyeIcon2')" style="position: absolute; right: 15px; background: none; border: none; color: #D4AF37; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>

                    <div class="form-check mb-4 px-1 text-start">
                        <input type="checkbox" name="agreeTerms" class="form-check-input custom-check ms-0 me-2" id="terms" required>
                        <label class="form-check-label text-muted small" for="terms">
                            I agree to the <span class="text-gold">Terms & Conditions</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-gold w-100 py-3 d-flex justify-content-center align-items-center">
                        CREATE CUSTOMER ACCOUNT <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted small mb-0">
                        Already have an account? <a href="login.php" class="auth-link-gold fs-6">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vanilla JS Password Toggle
        function togglePwd(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Auto-hide toast & redirect logic
        const toast = document.getElementById('toast');
        if (toast.classList.contains('toast-show')) {
            setTimeout(() => {
                toast.classList.remove('toast-show');
            }, 4000);

            <?php if ($success): ?>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            <?php endif; ?>
        }
    </script>
</body>

</html>