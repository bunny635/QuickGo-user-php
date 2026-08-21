<?php
// includes/payment-modal.php
?>
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Styled to match var(--secondary-black) -->
        <div class="modal-content shadow-lg" style="background: var(--secondary-black); border: 1px solid var(--gold-accent); border-radius: 12px;">
            <form action="../actions/payment_action.php" method="POST" id="paymentForm">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title text-white fw-bold" id="paymentModalLabel">Complete Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 text-white">
                    <input type="hidden" name="booking_id" id="modalBookingId" value="">

                    <div class="text-center mb-4 p-3 rounded-3" style="background: var(--primary-black); border: 1px solid #333;">
                        <p class="text-muted mb-1 fs-6">Amount to Pay</p>
                        <h2 class="fw-bold mb-0" style="color: var(--gold-accent);" id="modalAmountDisplay">$0.00</h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Card Number</label>
                        <div class="input-group">
                            <span class="input-group-text border-dark text-muted" style="background: var(--primary-black);"><i class="fa-regular fa-credit-card"></i></span>
                            <input type="text" class="form-control border-dark text-white shadow-none" style="background: var(--primary-black);" name="card_number" placeholder="0000 0000 0000 0000" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">Expiry Date</label>
                            <input type="text" class="form-control border-dark text-white shadow-none" style="background: var(--primary-black);" name="expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">CVV</label>
                            <input type="password" class="form-control border-dark text-white shadow-none" style="background: var(--primary-black);" name="cvv" placeholder="123" required maxlength="4">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4" id="paySubmitBtn">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Vanilla JS replaces React State for Modal Data
    document.addEventListener('DOMContentLoaded', function() {
        var paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var bookingId = button.getAttribute('data-booking-id');
                var amount = button.getAttribute('data-amount');

                document.getElementById('modalBookingId').value = bookingId;
                document.getElementById('modalAmountDisplay').textContent = '$' + parseFloat(amount).toFixed(2);
            });
        }
    });
</script>