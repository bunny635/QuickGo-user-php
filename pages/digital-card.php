<?php
// pages/digital-card.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch saved cards
$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <?php require_once '../includes/profile-sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h3 class="text-white fw-bold mb-4">Saved Digital Cards</h3>

            <?php if (empty($cards)): ?>
                <div class="alert alert-secondary bg-dark text-white border-secondary">You have no saved payment methods.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($cards as $card): ?>
                        <div class="col-md-6 mb-4">
                            <!-- Digital Card Preview matched to CSS -->
                            <div class="digital-card-preview" style="background: linear-gradient(135deg, #2a2d34 0%, #101216 100%); border: 1px solid rgba(197, 160, 89, 0.4); border-radius: 16px; padding: 22px; color: white; box-shadow: 0 15px 35px rgba(0,0,0,0.5);">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div style="width: 42px; height: 32px; background: linear-gradient(135deg, #F4D35E, #B99335); border-radius: 6px;"></div>
                                    <h5 class="m-0 fw-bold" style="color: rgba(255,255,255,0.5);"><?= htmlspecialchars($card['card_brand']) ?></h5>
                                </div>

                                <div style="font-family: monospace; font-size: 1.45rem; letter-spacing: 3px; margin-bottom: 15px;">
                                    •••• •••• •••• <?= htmlspecialchars($card['last4']) ?>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small style="font-size: 9px; color: #D4AF37; text-transform: uppercase;">Card Holder</small>
                                        <div style="font-size: 14px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;"><?= htmlspecialchars($card['card_holder_name']) ?></div>
                                    </div>
                                    <div>
                                        <small style="font-size: 9px; color: #D4AF37; text-transform: uppercase;">Expires</small>
                                        <div style="font-size: 14px; font-weight: 600; letter-spacing: 1px;"><?= sprintf("%02d", $card['expiry_month']) ?>/<?= substr($card['expiry_year'], -2) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>