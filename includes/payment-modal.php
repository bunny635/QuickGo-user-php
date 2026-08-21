<?php
// includes/payment-modal.php
// Fetch user's saved cards for the modal
$savedCards = [];
if (isset($_SESSION['user_id'])) {
    $cardStmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $cardStmt->execute([$_SESSION['user_id']]);
    $savedCards = $cardStmt->fetchAll();
}
?>
<style>
    /* Inline critical modal styles mapped from PaymentModal.css */
    .gateway-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.85);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        backdrop-filter: blur(15px);
    }

    .gateway-overlay.active {
        display: flex;
    }

    .gateway-card {
        background: rgba(15, 17, 21, 0.95);
        width: 95%;
        max-width: 440px;
        border-radius: 20px;
        border: 1px solid rgba(197, 160, 89, 0.25);
        overflow: hidden;
        box-shadow: 0 40px 80px rgba(0, 0, 0, 0.9);
        transition: 0.3s;
    }

    .gateway-head {
        background: rgba(0, 0, 0, 0.8);
        padding: 18px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(197, 160, 89, 0.15);
    }

    .g-logo {
        height: 40px;
        border-radius: 50%;
        border: 1px solid var(--gold-accent);
    }

    .g-close {
        background: none;
        border: none;
        color: #ccc;
        font-size: 26px;
        cursor: pointer;
    }

    .gold-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--gold-accent);
        font-weight: 800;
        margin-bottom: 12px;
    }

    .summary-list {
        background: rgba(0, 0, 0, 0.4);
        padding: 18px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .g-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin-bottom: 8px;
        color: #aaa;
    }

    .g-input {
        width: 100%;
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid #333;
        color: white;
        padding: 14px 16px;
        border-radius: 10px;
        outline: none;
    }

    .g-input:focus {
        border-color: var(--gold-accent);
    }

    .btn-pay-now {
        width: 100%;
        background: var(--gold-accent);
        color: black;
        border: none;
        padding: 16px;
        border-radius: 12px;
        font-weight: 800;
    }

    .saved-card-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .saved-card-item.selected {
        border-color: var(--gold-accent);
        background: rgba(212, 175, 55, 0.1);
    }

    .step-container {
        display: none;
    }

    .step-container.active {
        display: block;
        animation: fadeSlideIn 0.4s ease forwards;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .spin-icon {
        animation: spin 1.5s linear infinite;
    }
</style>

<div class="gateway-overlay" id="customPaymentModal">
    <div class="gateway-card">
        <div class="gateway-head">
            <div class="d-flex align-items-center gap-2">
                <img src="../assets/images/weblogo.jpg" alt="Logo" class="g-logo" onerror="this.src='https://via.placeholder.com/40'">
                <div>
                    <h6 class="mb-0 text-white">QuickGo Secure Pay</h6><small class="text-muted text-warning">DEMO MODE</small>
                </div>
            </div>
            <button class="g-close" onclick="closePaymentModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- STEP 1: SUMMARY -->
        <div class="p-4 step-container active" id="step-summary">
            <h5 class="gold-label">Price Breakdown</h5>
            <div class="summary-list mb-4">
                <div class="g-row"><span>Provider Fee</span><span>$<span id="pm-prov-fee">0.00</span></span></div>
                <div class="g-row"><span>GST (18%)</span><span>$<span id="pm-gst">0.00</span></span></div>
                <div class="g-row"><span>Platform Fee</span><span>$49.00</span></div>
                <hr class="border-secondary my-2" />
                <div class="g-row mt-2"><span class="text-white fw-bold">Grand Total</span><strong class="text-gold fs-5">$<span id="pm-total">0.00</span></strong></div>
            </div>
            <button class="btn-pay-now mt-2" onclick="setPaymentStep('method')">Proceed to Payment <i class="fa-solid fa-arrow-right ms-2"></i></button>
        </div>

        <!-- STEP 2: METHOD & CARD INPUT -->
        <div class="p-4 step-container" id="step-method" style="max-height: 500px; overflow-y: auto;">
            <input type="hidden" id="pm-booking-id">

            <?php if (count($savedCards) > 0): ?>
                <h6 class="text-white mb-2 small text-uppercase" style="opacity: 0.7;">Saved Digital Cards</h6>
                <?php foreach ($savedCards as $card): ?>
                    <div class="saved-card-item" onclick="selectCard(<?= $card['id'] ?>)" id="card-<?= $card['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><i class="fa-regular fa-credit-card me-2 text-gold"></i> <strong class="text-white"><?= $card['card_brand'] ?> •••• <?= $card['last4'] ?></strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <hr class="border-secondary my-3">
            <?php endif; ?>

            <div class="saved-card-item selected" onclick="selectCard(0)" id="card-0">
                <strong class="text-white"><i class="fa-regular fa-credit-card me-2"></i> Use New Card</strong>
            </div>

            <div id="new-card-form" class="mt-3 p-3 rounded" style="background: rgba(0,0,0,0.2); border: 1px solid #333;">
                <div class="mb-3">
                    <label class="text-muted small mb-1">Card Number</label>
                    <input type="text" id="pm-cc-num" class="form-control g-input" placeholder="0000 0000 0000 0000" oninput="formatCard(this)">
                </div>
                <div class="mb-3">
                    <label class="text-muted small mb-1">Cardholder Name</label>
                    <input type="text" id="pm-cc-name" class="form-control g-input" placeholder="Name on card">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-muted small mb-1">Expiry (MM/YY)</label>
                        <input type="text" id="pm-cc-exp" class="form-control g-input" placeholder="MM/YY" oninput="formatExpiry(this)">
                    </div>
                    <div class="col-6">
                        <label class="text-muted small mb-1">CVV</label>
                        <input type="password" id="pm-cc-cvv" class="form-control g-input" placeholder="***" maxlength="3">
                    </div>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="pm-save-card" style="accent-color: #D4AF37;">
                    <label class="form-check-label text-muted small" for="pm-save-card">Save this card for future payments</label>
                </div>
            </div>

            <div id="pm-error" class="alert alert-danger mt-3 d-none small py-2"></div>
            <button class="btn-pay-now mt-3" onclick="processPayment()">Secure Pay $<span id="pm-btn-total">0.00</span></button>
        </div>

        <!-- STEP 3: PROCESSING -->
        <div class="p-5 text-center step-container" id="step-processing">
            <div class="text-gold mb-4 d-inline-block spin-icon"><i class="fa-solid fa-spinner fs-1"></i></div>
            <h4 class="text-white mb-2" id="process-text">Initiating Secure Connection...</h4>
            <p class="text-muted small">Please do not close this window.</p>
        </div>

        <!-- STEP 4: SUCCESS -->
        <div class="p-5 text-center step-container" id="step-success">
            <i class="fa-solid fa-circle-check text-success mb-4" style="font-size: 70px;"></i>
            <h3 class="text-white mb-1">Payment Successful!</h3>
            <p class="text-muted small mb-4">Transaction ID: <span id="success-txn-id"></span></p>
            <div class="d-grid gap-3">
                <a id="invoice-link" href="#" class="btn-pay-now text-decoration-none"><i class="fa-solid fa-download me-2"></i> Download Invoice</a>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedCardId = 0;

    function openPaymentModal(bookingId, total, providerFee, gst) {
        document.getElementById('pm-booking-id').value = bookingId;
        document.getElementById('pm-total').innerText = total;
        document.getElementById('pm-btn-total').innerText = total;
        document.getElementById('pm-prov-fee').innerText = providerFee;
        document.getElementById('pm-gst').innerText = gst;
        document.getElementById('customPaymentModal').classList.add('active');
        setPaymentStep('summary');
    }

    function closePaymentModal() {
        document.getElementById('customPaymentModal').classList.remove('active');
    }

    function setPaymentStep(step) {
        document.querySelectorAll('.step-container').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
    }

    function selectCard(id) {
        selectedCardId = id;
        document.querySelectorAll('.saved-card-item').forEach(el => el.classList.remove('selected'));
        document.getElementById('card-' + id).classList.add('selected');
        document.getElementById('new-card-form').style.display = (id === 0) ? 'block' : 'none';
    }

    function formatCard(input) {
        let val = input.value.replace(/\D/g, '');
        input.value = val.replace(/(.{4})/g, '$1 ').trim().substring(0, 19);
    }

    function formatExpiry(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 2) val = val.substring(0, 2) + '/' + val.substring(2, 4);
        input.value = val;
    }

    async function processPayment() {
        const errorDiv = document.getElementById('pm-error');
        errorDiv.classList.add('d-none');

        const payload = {
            booking_id: document.getElementById('pm-booking-id').value,
            saved_card_id: selectedCardId,
            card_number: document.getElementById('pm-cc-num').value,
            card_name: document.getElementById('pm-cc-name').value,
            expiry: document.getElementById('pm-cc-exp').value,
            cvv: document.getElementById('pm-cc-cvv').value,
            save_card: document.getElementById('pm-save-card').checked
        };

        setPaymentStep('processing');

        // Simulate UI steps
        setTimeout(() => document.getElementById('process-text').innerText = "Verifying Payment Details...", 800);

        try {
            const response = await fetch('../actions/payment_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                document.getElementById('success-txn-id').innerText = data.transaction_id;
                document.getElementById('invoice-link').href = `invoice.php?id=${data.booking_id}`;
                setPaymentStep('success');
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            errorDiv.innerText = err.message || "Payment Failed. Try again.";
            errorDiv.classList.remove('d-none');
            setPaymentStep('method');
        }
    }
</script>