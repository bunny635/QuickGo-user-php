<?php
// pages/contact.php
require_once '../config/database.php';

$success = null;
$error = null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = "Message Sent! Our team will get back to you within 24 hours.";
        } catch (PDOException $e) {
            $error = "Failed to send inquiry. Please try again.";
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<style>
    /* Contact.css */
    .contact-page-wrapper {
        background-color: var(--primary-black);
        min-height: 100vh;
    }

    .info-card,
    .form-card {
        background: var(--secondary-black);
        padding: 40px;
        border-radius: 20px;
        border: 1px solid #222;
        height: 100%;
    }

    .info-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }

    .info-icon {
        font-size: 24px;
        color: var(--gold-accent);
        margin-top: 5px;
    }

    .info-row h6 {
        color: white;
        margin-bottom: 5px;
    }

    .info-row p {
        color: var(--text-muted);
        font-size: 14px;
        margin: 0;
    }

    .contact-social-links {
        display: flex;
        gap: 15px;
    }

    .contact-social-links a {
        width: 40px;
        height: 40px;
        border: 1px solid #333;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--gold-accent);
        transition: 0.3s;
        text-decoration: none;
    }

    .contact-social-links a:hover {
        background: var(--gold-accent);
        color: black;
        border-color: var(--gold-accent);
    }

    .form-label-gold {
        color: var(--gold-accent);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    .contact-input {
        width: 100%;
        background: #000;
        border: 1px solid #333;
        padding: 12px 15px;
        border-radius: 8px;
        color: white;
        outline: none;
        transition: 0.3s;
    }

    .contact-input:focus {
        border-color: var(--gold-accent);
    }

    /* FAQ Accordion */
    .faq-box {
        background: var(--secondary-black);
        border: 1px solid #222;
        border-radius: 10px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: 0.3s;
    }

    .faq-box.faq-active {
        border-color: var(--gold-accent);
    }

    .faq-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        font-weight: 600;
    }

    .faq-body {
        padding: 0 20px;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.6;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .faq-box.faq-active .faq-body {
        max-height: 200px;
        padding-bottom: 20px;
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

<div class="contact-page-wrapper py-5">
    <div id="toast" class="custom-toast <?= $error ? 'toast-error toast-show' : ($success ? 'toast-success toast-show' : '') ?>">
        <?= htmlspecialchars($error ?? $success ?? '') ?>
    </div>

    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Connect With <span>QuickGo</span></h2>
            <p class="text-muted">Your luxury home care experience starts with a conversation.</p>
        </div>

        <div class="row g-5">
            <!-- LEFT COLUMN -->
            <div class="col-lg-5">
                <div class="info-card shadow-lg">
                    <h4 class="text-gold mb-4">Contact Details</h4>

                    <div class="info-row">
                        <i class="fa-regular fa-envelope info-icon"></i>
                        <div>
                            <h6>Email Support</h6>
                            <p>concierge@quickgo.com</p>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fa-solid fa-phone info-icon"></i>
                        <div>
                            <h6>Customer Hotline</h6>
                            <p>+91 98765 43210</p>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fa-solid fa-location-dot info-icon"></i>
                        <div>
                            <h6>Registered Office</h6>
                            <p>Vivekanand College, Olpad Rd, Jahangir Pura, Surat, Gujarat 395005</p>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fa-regular fa-clock info-icon"></i>
                        <div>
                            <h6>Working Hours</h6>
                            <p>Mon - Sat: 09:00 AM - 08:00 PM</p>
                        </div>
                    </div>

                    <hr class="my-4 border-secondary" />

                    <h5 class="text-white mb-3">Join our Community</h5>
                    <div class="contact-social-links">
                        <a href="#" onclick="alert('Our social media pages are coming soon!'); return false;"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" onclick="alert('Our social media pages are coming soon!'); return false;"><i class="fa-brands fa-facebook-f"></i></a>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-lg-7">
                <div class="form-card shadow-lg">
                    <h4 class="text-gold mb-4">Send an Inquiry</h4>
                    <form action="contact.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-gold">Your Name</label>
                                <input type="text" name="name" class="contact-input" placeholder="e.g. Smit Ghoghari" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-gold">Email Address</label>
                                <input type="email" name="email" class="contact-input" placeholder="smit@example.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-gold">Subject</label>
                            <input type="text" name="subject" class="contact-input" placeholder="How can we help?" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-gold">Detailed Message</label>
                            <textarea name="message" class="contact-input" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 py-3 fw-bold">
                            <i class="fa-regular fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FAQ SECTION (Vanilla JS replaces React State) -->
        <div class="faq-area mt-5 pt-5">
            <h3 class="text-center text-gold mb-5">Frequently Asked Questions</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- FAQ 1 -->
                    <div class="faq-box" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span>How quickly can I book a service?</span>
                            <i class="fa-solid fa-plus text-gold faq-icon"></i>
                        </div>
                        <div class="faq-body">With QuickGo, you can book a verified home expert in under 60 seconds through our streamlined booking flow.</div>
                    </div>
                    <!-- FAQ 2 -->
                    <div class="faq-box" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span>Are the experts background checked?</span>
                            <i class="fa-solid fa-plus text-gold faq-icon"></i>
                        </div>
                        <div class="faq-body">Absolutely. Every professional on our platform undergoes a rigorous 3-step background and skills verification process.</div>
                    </div>
                    <!-- FAQ 3 -->
                    <div class="faq-box" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span>What happens if I'm not satisfied?</span>
                            <i class="fa-solid fa-plus text-gold faq-icon"></i>
                        </div>
                        <div class="faq-body">We offer a 7-day service warranty. If the job isn't done perfectly, we will send an expert back to fix it for free.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Mimics the React activeFaq state using Vanilla JS
    function toggleFaq(element) {
        // Close all others
        document.querySelectorAll('.faq-box').forEach(box => {
            if (box !== element) {
                box.classList.remove('faq-active');
                box.querySelector('.faq-icon').classList.remove('fa-minus');
                box.querySelector('.faq-icon').classList.add('fa-plus');
            }
        });

        // Toggle current
        element.classList.toggle('faq-active');
        const icon = element.querySelector('.faq-icon');
        if (element.classList.contains('faq-active')) {
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-minus');
        } else {
            icon.classList.remove('fa-minus');
            icon.classList.add('fa-plus');
        }
    }

    // Auto-hide toast
    const toast = document.getElementById('toast');
    if (toast.classList.contains('toast-show')) {
        setTimeout(() => {
            toast.classList.remove('toast-show');
        }, 4000);
    }
</script>

<?php require_once '../includes/footer.php'; ?>