<?php
// auth/login.php
session_start();
require_once '../config/database.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/home.php");
    exit;
}

$error = null;
$redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '../pages/home.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $redirect_url = $_POST['redirect_url'];

    if (empty($email) || empty($password)) {
        $error = "Please provide both email and password.";
    } else {
        // Fetch User
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify Password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Establish Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect
            header("Location: " . $redirect_url);
            exit;
        } else {
            $error = "Invalid Credentials! Please check your email and password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuickGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Inherit the exact same CSS as register.php (skipping duplication here for brevity, 
           but in production, these should live in auth.css or style.css) */
        .auth-page-container {
            background: url('../assets/images/bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
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
        }

        .auth-wrapper {
            background: transparent;
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
            background: rgba(15, 17, 21, 0.82);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(197, 160, 89, 0.25);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85);
            animation: fadeInScale 0.8s ease forwards;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
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
            background: rgba(0, 0, 0, 0.45);
            border: 1px solid #333;
            padding: 12px 15px 12px 45px;
            border-radius: 10px;
            color: white;
            outline: none;
            font-size: 14px;
        }

        .auth-input:focus {
            border-color: var(--gold-accent);
            background: rgba(0, 0, 0, 0.65);
        }

        .auth-link-gold {
            color: var(--gold-accent);
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }

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
</head>

<body>

    <div class="auth-page-container">
        <div id="toast" class="custom-toast <?= $error ? 'toast-error toast-show' : '' ?>">
            <?= htmlspecialchars($error ?? '') ?>
        </div>

        <div class="auth-wrapper">
            <div class="glass-card">
                <div class="text-center mb-3">
                    <img src="../assets/images/weblogo.jpg" alt="QuickGo" class="auth-logo" onerror="this.src='https://via.placeholder.com/65'">
                    <h2 class="auth-title mt-1">Quick<span>Go</span> Access</h2>
                    <p class="text-muted small">Sign in to your QuickGo account</p>
                </div>

                <form action="login.php" method="POST">
                    <!-- Keep track of redirect URL -->
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirect) ?>">

                    <div class="input-group-custom mb-3">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Email ID" class="auth-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="input-group-custom mb-4">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="loginPwd" placeholder="Password" class="auth-input" style="padding-right: 45px;" required>
                        <button type="button" onclick="togglePwd('loginPwd', 'eyeIcon')" style="position: absolute; right: 15px; background: none; border: none; color: #D4AF37; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-gold w-100 py-3 d-flex justify-content-center align-items-center">
                        SIGN IN <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small">
                        Don't have an account? <a href="register.php" class="auth-link-gold ms-1">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        const toast = document.getElementById('toast');
        if (toast.classList.contains('toast-show')) {
            setTimeout(() => toast.classList.remove('toast-show'), 4000);
        }
    </script>
</body>

</html>