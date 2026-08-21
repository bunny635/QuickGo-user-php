<?php
// pages/book-service.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

$stmt = $pdo->prepare("SELECT s.*, p.name AS provider_name, p.profile_image FROM services s JOIN providers p ON s.provider_id = p.id WHERE s.id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    echo "<script>window.location.href = 'services.php';</script>";
    exit;
}

$hourlyRate = $service['hourly_pay'] > 0 ? $service['hourly_pay'] : $service['price'];
$providerImage = !empty($service['profile_image']) ? $service['profile_image'] : '../assets/images/default-avatar.png';
?>

<style>
    /* Booking Flow CSS matching React */
    .booking-flow-container {
        background-color: #0F1115;
        min-height: 100vh;
        padding-bottom: 50px;
    }

    .booking-form-card {
        background: rgba(22, 22, 22, 0.6);
        backdrop-filter: blur(15px);
        padding: 40px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .form-label-custom {
        display: block;
        color: #D4AF37;
        font-weight: 700;
        margin-bottom: 10px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .booking-input {
        width: 100%;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(197, 160, 89, 0.3);
        padding: 14px 16px;
        border-radius: 10px;
        color: white;
        outline: none;
        transition: 0.3s;
    }

    .booking-input:focus {
        border-color: #D4AF37;
        box-shadow: 0 0 12px rgba(197, 160, 89, 0.2);
    }

    .booking-prov-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #D4AF37;
        object-fit: cover;
    }

    .btn-back-link {
        font-size: 15px;
        font-weight: 700;
        color: #D4AF37;
        text-decoration: none;
    }

    .calendar-legend-box {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        background: rgba(0, 0, 0, 0.4);
        padding: 15px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #ccc;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .legend-dot.green {
        background: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
    }

    .legend-dot.yellow {
        background: #ffc107;
        box-shadow: 0 0 8px rgba(255, 193, 7, 0.4);
    }

    .legend-dot.red {
        background: #dc3545;
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.4);
    }

    .legend-dot.grey {
        background: #6c757d;
    }

    .custom-calendar-wrapper {
        background: rgba(0, 0, 0, 0.3);
        padding: 20px;
        border-radius: 15px;
        border: 1px solid rgba(197, 160, 89, 0.15);
    }

    .cal-nav-btn {
        background: rgba(197, 160, 89, 0.1);
        border: 1px solid rgba(197, 160, 89, 0.3);
        color: #D4AF37;
        border-radius: 8px;
        padding: 8px 12px;
        transition: 0.3s;
    }

    .calendar-days-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        margin-bottom: 10px;
    }

    .cal-day-name {
        font-size: 11px;
        color: #888;
        font-weight: 800;
        text-transform: uppercase;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    .cal-cell {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        border: 1px solid transparent;
        color: white;
    }

    .cal-cell.empty {
        background: transparent;
        cursor: default;
    }

    .cal-cell.past-date {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }

    .cal-cell.status-green {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border-color: rgba(40, 167, 69, 0.2);
    }

    .cal-cell.status-red {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border-color: rgba(220, 53, 69, 0.2);
    }

    .cal-cell.status-yellow {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border-color: rgba(255, 193, 7, 0.2);
    }

    .cal-cell.selected {
        background: #D4AF37 !important;
        color: black !important;
        transform: scale(1.05);
    }

    .time-slots-wrapper {
        padding-top: 10px;
        border-top: 1px dashed rgba(255, 255, 255, 0.1);
    }

    .slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
    }

    .time-slot-btn {
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(197, 160, 89, 0.3);
        color: white;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .time-slot-btn.active {
        background: #D4AF37;
        color: black;
        border-color: #D4AF37;
    }

    .summary-card {
        background: rgba(22, 22, 22, 0.8);
        padding: 30px;
        border-radius: 20px;
        border: 1px solid rgba(197, 160, 89, 0.4);
        position: sticky;
        top: 110px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 14px;
        color: #888;
    }

    .total-row {
        font-size: 20px;
        letter-spacing: 0.5px;
    }
</style>

<div class="booking-flow-container py-5">
    <div class="container pt-4">

        <a href="service-details.php?id=<?= $service['id'] ?>" class="btn-back-link mb-4 d-inline-block">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Provider Selection
        </a>

        <h2 class="section-title text-center mb-5">Select Schedule & <span>Book</span></h2>

        <div class="row g-4">
            <!-- LEFT FORM -->
            <div class="col-lg-8">
                <div class="booking-form-card">
                    <!-- Provider Info -->
                    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom border-secondary">
                        <img src="<?= htmlspecialchars($providerImage) ?>" alt="Provider" class="booking-prov-img">
                        <div>
                            <h5 class="text-white mb-0"><?= htmlspecialchars($service['provider_name']) ?></h5>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($service['title']) ?></p>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="calendar-legend-box mb-4">
                        <div class="legend-item"><span class="legend-dot green"></span> Available</div>
                        <div class="legend-item"><span class="legend-dot yellow"></span> Half Day</div>
                        <div class="legend-item"><span class="legend-dot red"></span> Unavailable</div>
                    </div>

                    <!-- Vanilla JS Calendar -->
                    <div class="custom-calendar-wrapper mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="cal-nav-btn" id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
                            <h5 class="text-white m-0 fw-bold" id="monthDisplay">Month YYYY</h5>
                            <button type="button" class="cal-nav-btn" id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-days-header">
                            <div class="cal-day-name">Sun</div>
                            <div class="cal-day-name">Mon</div>
                            <div class="cal-day-name">Tue</div>
                            <div class="cal-day-name">Wed</div>
                            <div class="cal-day-name">Thu</div>
                            <div class="cal-day-name">Fri</div>
                            <div class="cal-day-name">Sat</div>
                        </div>
                        <div class="calendar-grid" id="calendarGrid"></div>
                    </div>

                    <!-- Time Slots -->
                    <div class="time-slots-wrapper mb-4 d-none" id="timeSlotsWrapper">
                        <h6 class="text-white mb-3"><i class="fa-regular fa-clock text-gold me-2"></i> Available Time Slots</h6>
                        <div class="slots-grid" id="slotsGrid"></div>
                    </div>

                    <!-- Booking Form Data -->
                    <form id="bookingForm" action="../actions/booking_action.php" method="POST">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                        <input type="hidden" name="booking_date" id="inputDate" required>
                        <input type="hidden" name="booking_time" id="inputTime" required>

                        <div class="mb-4">
                            <label class="form-label-custom"><i class="fa-regular fa-clock me-2"></i>Booked Duration (Hours)</label>
                            <select class="booking-input" name="booked_hours" id="inputHours" onchange="calculateTotals()">
                                <?php for ($h = 1; $h <= 8; $h++): ?>
                                    <option value="<?= $h ?>"><?= $h ?> Hour<?= $h > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label-custom"><i class="fa-solid fa-location-dot me-2"></i>Service Address</label>
                            <textarea class="booking-input" name="address" id="inputAddress" rows="3" placeholder="Enter full address..." required></textarea>
                        </div>
                    </form>

                </div>
            </div>

            <!-- RIGHT SUMMARY -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="text-gold text-center mb-4">Booking Summary</h5>

                    <div class="summary-item"><span>Hourly Rate</span><span class="text-white">$<span id="summRate"><?= number_format($hourlyRate, 2) ?></span></span></div>
                    <div class="summary-item"><span>Booked Duration</span><span class="text-white"><span id="summHours">1</span> hr</span></div>
                    <div class="summary-item"><span>Provider Fee</span><span class="text-white">$<span id="summFee">0.00</span></span></div>
                    <div class="summary-item"><span>GST (18%)</span><span class="text-white">$<span id="summGST">0.00</span></span></div>
                    <div class="summary-item"><span>Platform Fee</span><span class="text-white">$49.00</span></div>

                    <hr class="border-secondary my-3" />

                    <div class="summary-item total-row">
                        <span class="fw-bold text-white">Grand Total</span>
                        <span class="text-gold fw-bold">$<span id="summTotal">0.00</span></span>
                    </div>

                    <div class="mt-4 d-grid">
                        <button type="button" class="btn btn-gold py-3 fw-bold rounded-3" id="proceedBtn">Proceed to Payment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incorporate the Payment Modal -->
<?php require_once '../includes/payment-modal.php'; ?>

<script>
    // --- CLIENT SIDE CALENDAR & CALCULATIONS (MIMICS REACT BEHAVIOR) ---
    const hourlyRate = <?= $hourlyRate ?>;
    const platformFee = 49;
    let selectedDate = null;
    let selectedTime = null;
    let currentDate = new Date();

    function calculateTotals() {
        const hours = parseInt(document.getElementById('inputHours').value) || 1;
        const providerFee = hourlyRate * hours;
        const gst = Math.round(providerFee * 0.18 * 100) / 100;
        const total = providerFee + platformFee + gst;

        document.getElementById('summHours').textContent = hours;
        document.getElementById('summFee').textContent = providerFee.toFixed(2);
        document.getElementById('summGST').textContent = gst.toFixed(2);
        document.getElementById('summTotal').textContent = total.toFixed(2);
    }

    function generateCalendar() {
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        document.getElementById('monthDisplay').textContent = `${monthNames[month]} ${year}`;

        // Empty cells
        for (let i = 0; i < firstDay; i++) {
            grid.innerHTML += `<div class="cal-cell empty"></div>`;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Days
        for (let i = 1; i <= daysInMonth; i++) {
            const cellDate = new Date(year, month, i);
            const dateStr = cellDate.toISOString().split('T')[0];
            const isPast = cellDate < today;
            const dayOfWeek = cellDate.getDay();

            // Demo Status Logic
            let status = 'green';
            let slots = ['09:00 AM', '10:30 AM', '12:00 PM', '02:00 PM'];
            if (dayOfWeek === 0) {
                status = 'grey';
                slots = [];
            } else if (dayOfWeek === 4) {
                status = 'red';
                slots = [];
            } else if (dayOfWeek === 2 || dayOfWeek === 6) {
                status = 'yellow';
                slots = ['09:00 AM', '02:00 PM'];
            }

            if (isPast) status = 'grey';

            const cell = document.createElement('div');
            cell.className = `cal-cell status-${status} ${isPast ? 'past-date' : ''} ${selectedDate === dateStr ? 'selected' : ''}`;
            cell.textContent = i;

            if (!isPast && status !== 'grey' && status !== 'red') {
                cell.onclick = () => selectDate(dateStr, slots);
            }
            grid.appendChild(cell);
        }
    }

    function selectDate(dateStr, slots) {
        selectedDate = dateStr;
        selectedTime = null;
        document.getElementById('inputDate').value = dateStr;
        document.getElementById('inputTime').value = '';

        const wrapper = document.getElementById('timeSlotsWrapper');
        const slotsGrid = document.getElementById('slotsGrid');

        wrapper.classList.remove('d-none');
        slotsGrid.innerHTML = '';

        slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-slot-btn';
            btn.textContent = slot;
            btn.onclick = (e) => {
                document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedTime = slot;
                document.getElementById('inputTime').value = slot;
            };
            slotsGrid.appendChild(btn);
        });

        generateCalendar(); // Re-render to show selected state
    }

    document.getElementById('prevMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        generateCalendar();
    };
    document.getElementById('nextMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        generateCalendar();
    };

    // INITIALIZE
    calculateTotals();
    generateCalendar();

    // VALIDATE AND TRIGGER MODAL
    document.getElementById('proceedBtn').onclick = () => {
        const address = document.getElementById('inputAddress').value.trim();
        if (!selectedDate || !selectedTime || !address) {
            alert("Please select a date, time, and enter your address.");
            return;
        }
        // Inject totals into the modal dynamically before opening
        const total = document.getElementById('summTotal').textContent;
        document.getElementById('modalAmountDisplay').textContent = '$' + total;

        // Open Bootstrap Modal
        var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        myModal.show();
    };

    // INTERCEPT MODAL SUBMIT TO SUBMIT THE ENTIRE BOOKING FORM
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Create hidden inputs for the CC data inside the main booking form
        const bookingForm = document.getElementById('bookingForm');

        const cardInput = document.createElement('input');
        cardInput.type = 'hidden';
        cardInput.name = 'card_number';
        cardInput.value = this.querySelector('[name="card_number"]').value;

        bookingForm.appendChild(cardInput);

        // Submit the real booking form
        bookingForm.submit();
    });
</script>

<?php require_once '../includes/footer.php'; ?>