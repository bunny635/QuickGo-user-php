<?php
// pages/digital-card.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch saved cards for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll();
?>

<style>
    /* ==========================================
   DIGITAL CARD 3D STYLES (DigitalCard.css)
========================================== */
    .digital-card-container {
        perspective: 1000px;
        width: 100%;
        max-width: 400px;
        aspect-ratio: 1.586;
        margin: 0 auto;
        cursor: pointer;
        position: relative;
    }

    .digital-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: left;
        transition: transform 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
        transform-style: preserve-3d;
    }

    .digital-card-inner.is-flipped {
        transform: rotateY(180deg);
    }

    .digital-card-container:hover .digital-card-inner:not(.is-flipped) {
        transform: translateY(-5px) rotateX(4deg) rotateY(-4deg);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 0 10px 25px rgba(212, 175, 55, 0.15);
    }

    .digital-card-front,
    .digital-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
        border: 1px solid rgba(212, 175, 55, 0.2);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.05);
        color: #fff;
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
    }

    .card-bg-decoration {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
        border-radius: 18px;
        justify-content: center;
    }

    .bg-glow {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.03) 0%, transparent 50%);
    }

    .bg-circle {
        position: absolute;
        top: -30px;
        right: -60px;
        width: 200px;
        height: 200px;
        border: 1px solid rgba(255, 255, 255, 0.02);
        border-radius: 50%;
    }

    .digital-card-front>* {
        position: relative;
        z-index: 1;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .card-brand-logo {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .logo-img {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(212, 175, 55, 0.4);
    }

    .logo-text {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .logo-text-accent {
        color: #D4AF37;
    }

    .card-network {
        font-size: 20px;
        font-weight: 800;
        font-style: italic;
        color: #fff;
        letter-spacing: 1px;
    }

    .card-middle {
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1;
    }

    .card-chip-contactless {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
    }

    .card-chip {
        width: 44px;
        height: 32px;
        background: linear-gradient(135deg, #f0c563 0%, #c49a37 50%, #9e7b2b 100%);
        border-radius: 6px;
        position: relative;
        box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.4), 0 2px 4px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.3);
    }

    .card-chip::before {
        content: '';
        position: absolute;
        top: 15%;
        left: 15%;
        right: 15%;
        bottom: 15%;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 4px;
    }

    .chip-lines {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .chip-line {
        position: absolute;
        background: rgba(0, 0, 0, 0.2);
    }

    .chip-line:nth-child(1) {
        top: 35%;
        left: 0;
        width: 100%;
        height: 1px;
    }

    .chip-line:nth-child(2) {
        top: 65%;
        left: 0;
        width: 100%;
        height: 1px;
    }

    .chip-line:nth-child(3) {
        top: 0;
        left: 35%;
        width: 1px;
        height: 100%;
    }

    .chip-line:nth-child(4) {
        top: 0;
        left: 65%;
        width: 1px;
        height: 100%;
    }

    .contactless-icon {
        font-size: 24px;
        color: rgba(255, 255, 255, 0.8);
        transform: rotate(90deg);
    }

    .card-number-display {
        font-family: 'Courier New', Courier, monospace;
        font-size: 22px;
        letter-spacing: 3px;
        margin-top: 16px;
        margin-bottom: 8px;
        font-weight: 600;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .card-holder,
    .card-expiry {
        display: flex;
        flex-direction: column;
    }

    .card-expiry {
        align-items: flex-end;
        text-align: right;
    }

    .card-label {
        font-size: 9px;
        text-transform: uppercase;
        color: #aaa;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .card-name,
    .card-date {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
        color: #fff;
        margin: 0;
    }

    .digital-card-back {
        transform: rotateY(180deg);
        background: #111;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    .magnetic-strip {
        width: 100%;
        height: 45px;
        background: #000;
        margin-top: 25px;
    }

    .back-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .signature-box {
        height: 40px;
        background: #fff;
        display: flex;
        align-items: center;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .signature-pattern {
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 80%;
        background-image: repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 2px, #fff 2px, #fff 6px);
    }

    .cvv-mask {
        position: absolute;
        right: 15px;
        font-family: 'Courier New', Courier, monospace;
        color: #333;
        font-weight: 700;
        letter-spacing: 2px;
    }

    .back-text {
        text-align: center;
        color: #888;
    }

    .back-text p {
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1px;
        color: #ccc;
    }

    .back-text small {
        font-size: 9px;
    }

    /* ==========================================
   ADD CARD MODAL STYLES (AddCardModal.css)
========================================== */
    .modal-overlay-custom {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-overlay-custom.active {
        display: flex;
        animation: fadeIn 0.3s ease;
    }

    .add-card-modal {
        background: #1e1e1e;
        border-radius: 12px;
        width: 100%;
        max-width: 450px;
        padding: 30px;
        border: 1px solid rgba(212, 175, 55, 0.2);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        color: #fff;
        transform: translateY(50px);
        transition: 0.4s ease;
    }

    .modal-overlay-custom.active .add-card-modal {
        transform: translateY(0);
    }

    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }

    .modal-header-custom h2 {
        font-size: 22px;
        color: #D4AF37;
        margin: 0;
        font-weight: 600;
    }

    .close-btn {
        background: transparent;
        border: none;
        color: #aaa;
        font-size: 24px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .close-btn:hover {
        color: #fff;
    }

    .add-card-form .form-group {
        margin-bottom: 20px;
    }

    .add-card-form label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #ccc;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .add-card-form input[type="text"],
    .add-card-form input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        background: #2a2a2a;
        border: 1px solid #444;
        border-radius: 8px;
        color: #fff;
        font-size: 16px;
        transition: border-color 0.3s;
        outline: none;
    }

    .add-card-form input:focus {
        border-color: #D4AF37;
    }

    .add-card-form .form-row {
        display: flex;
        gap: 15px;
    }

    .add-card-form .form-group.half {
        flex: 1;
    }

    .expiry-inputs {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .expiry-inputs span {
        font-size: 20px;
        color: #777;
    }

    .checkbox-custom-box {
        width: 20px;
        height: 20px;
        border: 2px solid #D4AF37;
        border-radius: 4px;
        margin-right: 10px;
        position: relative;
        transition: background 0.2s;
        display: inline-block;
        vertical-align: middle;
    }

    .add-card-form input[type="checkbox"] {
        display: none;
    }

    .add-card-form input[type="checkbox"]:checked+.checkbox-custom-box {
        background: #D4AF37;
    }

    .add-card-form input[type="checkbox"]:checked+.checkbox-custom-box::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 6px;
        width: 5px;
        height: 10px;
        border: solid #000;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .submit-card-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #D4AF37 0%, #AA8C2C 100%);
        color: #000;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 20px;
    }

    .submit-card-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
    }

    /* Custom Toast UI */
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
</style>

<div class="profile-page-wrapper pb-5">
    <div id="toast" class="custom-toast"></div>

    <!-- Header Banner -->
    <section class="profile-hero-banner">
        <div class="container text-center text-white">
            <h2 class="fw-bold"><i class="fa-regular fa-credit-card text-gold me-2"></i> Payment Methods</h2>
            <p class="text-muted">Manage your secure digital cards for faster checkout.</p>
        </div>
    </section>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="profile.php" class="btn btn-outline-light rounded-pill"><i class="fa-solid fa-arrow-left me-2"></i> Back to Profile</a>
            <button class="btn btn-gold rounded-pill" onclick="openAddCardModal()"><i class="fa-solid fa-plus me-2"></i> Add New Card</button>
        </div>

        <div class="row g-4">
            <?php if (empty($cards)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fa-regular fa-credit-card fs-1 text-muted mb-3"></i>
                    <h5 class="text-white">No Payment Methods Found</h5>
                    <p class="text-muted">Add a card to enable secure, 1-click checkout.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cards as $card): ?>
                    <div class="col-md-6 col-lg-4">
                        <!-- Digital Card 3D Component -->
                        <div class="digital-card-container" onclick="this.querySelector('.digital-card-inner').classList.toggle('is-flipped')">
                            <div class="digital-card-inner">
                                <!-- FRONT -->
                                <div class="digital-card-front">
                                    <div class="card-bg-decoration">
                                        <div class="bg-glow"></div>
                                        <div class="bg-circle"></div>
                                    </div>
                                    <div class="card-header">
                                        <div class="card-brand-logo">
                                            <img src="../assets/images/weblogo.jpg" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/26'">
                                            <p class="logo-text">Quick<span class="logo-text-accent">Go</span></p>
                                        </div>
                                        <div class="card-network"><?= htmlspecialchars(strtoupper($card['card_brand'])) ?></div>
                                    </div>
                                    <div class="card-middle">
                                        <div class="card-chip-contactless">
                                            <div class="card-chip">
                                                <div class="chip-lines">
                                                    <div class="chip-line"></div>
                                                    <div class="chip-line"></div>
                                                    <div class="chip-line"></div>
                                                    <div class="chip-line"></div>
                                                </div>
                                            </div>
                                            <i class="fa-solid fa-wifi contactless-icon"></i>
                                        </div>
                                        <div class="card-number-display">•••• •••• •••• <?= htmlspecialchars($card['last4']) ?></div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="card-holder">
                                            <span class="card-label">CARD HOLDER</span>
                                            <p class="card-name"><?= htmlspecialchars($card['card_holder_name']) ?></p>
                                        </div>
                                        <div class="card-expiry">
                                            <span class="card-label">EXPIRES</span>
                                            <p class="card-date"><?= sprintf("%02d", $card['expiry_month']) ?>/<?= substr($card['expiry_year'], -2) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- BACK -->
                                <div class="digital-card-back">
                                    <div class="magnetic-strip"></div>
                                    <div class="back-content">
                                        <div class="signature-box">
                                            <div class="signature-pattern"></div>
                                            <div class="cvv-mask">•••</div>
                                        </div>
                                        <div class="back-text">
                                            <div class="mb-2 text-muted fw-bold">QUICKGO</div>
                                            <p>SECURE DIGITAL CARD</p>
                                            <small>For secure platform payments only. Do not share your card details.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($card['is_default']): ?>
                            <div class="text-center mt-2"><span class="badge bg-success">Default Card</span></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Card Modal -->
<div class="modal-overlay-custom" id="addCardModalOverlay">
    <div class="add-card-modal">
        <div class="modal-header-custom">
            <h2>Add New Card</h2>
            <button class="close-btn" onclick="closeAddCardModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="addCardForm" class="add-card-form" onsubmit="submitNewCard(event)">
            <div class="form-group">
                <label><i class="fa-regular fa-user"></i> Cardholder Name</label>
                <input type="text" id="acName" placeholder="JOHN DOE" required>
            </div>

            <div class="form-group">
                <label><i class="fa-regular fa-credit-card"></i> Card Number</label>
                <input type="text" id="acNumber" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required oninput="formatCardInput(this)">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label><i class="fa-regular fa-calendar"></i> Expiry Date</label>
                    <div class="expiry-inputs">
                        <input type="text" id="acExpMonth" placeholder="MM" maxlength="2" required oninput="this.value=this.value.replace(/\D/g,'')">
                        <span>/</span>
                        <input type="text" id="acExpYear" placeholder="YYYY" maxlength="4" required oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                </div>

                <div class="form-group half">
                    <label><i class="fa-solid fa-lock"></i> CVV</label>
                    <!-- Type is password for security, never stored in DB -->
                    <input type="password" id="acCvv" placeholder="***" maxlength="4" required oninput="this.value=this.value.replace(/\D/g,'')">
                </div>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label" for="acDefault">
                    <input type="checkbox" id="acDefault">
                    <span class="checkbox-custom-box"></span>
                    Set as Default Payment Method
                </label>
            </div>

            <button type="submit" class="submit-card-btn" id="acSubmitBtn">
                <i class="fa-regular fa-circle-check"></i> Save Card
            </button>
        </form>
    </div>
</div>

<script>
    // Format Card Number (Add spaces)
    function formatCardInput(input) {
        let val = input.value.replace(/\D/g, '');
        input.value = val.replace(/(.{4})/g, '$1 ').trim().substring(0, 19);
    }

    // Modal Toggles
    function openAddCardModal() {
        document.getElementById('addCardModalOverlay').classList.add('active');
    }

    function closeAddCardModal() {
        document.getElementById('addCardModalOverlay').classList.remove('active');
        document.getElementById('addCardForm').reset();
    }

    // Show Toast
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'custom-toast toast-show ' + (type === 'error' ? 'toast-error' : 'toast-success');
        setTimeout(() => toast.classList.remove('toast-show'), 3000);
    }

    // Submit via AJAX
    async function submitNewCard(e) {
        e.preventDefault();
        const btn = document.getElementById('acSubmitBtn');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const payload = {
            cardHolderName: document.getElementById('acName').value,
            cardNumber: document.getElementById('acNumber').value,
            expiryMonth: document.getElementById('acExpMonth').value,
            expiryYear: document.getElementById('acExpYear').value,
            cvv: document.getElementById('acCvv').value, // Used for validation only, not stored
            isDefault: document.getElementById('acDefault').checked
        };

        try {
            const response = await fetch('../actions/add_card_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                showToast('Card added successfully!');
                setTimeout(() => window.location.reload(), 1500); // Reload to show new card
            } else {
                showToast(data.message || 'Failed to add card.', 'error');
            }
        } catch (err) {
            showToast('A network error occurred.', 'error');
        } finally {
            btn.innerHTML = '<i class="fa-regular fa-circle-check"></i> Save Card';
            btn.disabled = false;
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>