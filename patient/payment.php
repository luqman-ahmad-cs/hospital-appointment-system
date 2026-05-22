<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$appointment_id = $_GET['id'];
$patient_id = $_SESSION['user_id'];

// Appointment details fetch karo
$apt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, u.fullname as doctor_name, d.specialization
     FROM appointments a
     JOIN users u ON a.doctor_id = u.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE a.id = '$appointment_id'
     AND a.patient_id = '$patient_id'"));

if (!$apt) {
    echo "<script>alert('Invalid appointment!');
          window.location='my_appointments.php';</script>";
    exit();
}

// Already paid check
$paid = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id FROM payments 
     WHERE appointment_id='$appointment_id' 
     AND status='completed'"));

if ($paid) {
    echo "<script>alert('Payment already done for this appointment!');
          window.location='my_appointments.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4ff; }

        .sidebar {
            width: 260px; height: 100vh;
            background: linear-gradient(180deg, #0d6efd, #0a58ca);
            position: fixed; left: 0; top: 0;
            padding: 30px 0; z-index: 100;
        }
        .sidebar-logo {
            text-align: center; color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-logo i { font-size: 40px; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; color: rgba(255,255,255,0.85);
            text-decoration: none; padding: 13px 25px;
            font-size: 15px; transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white; padding-left: 35px;
        }
        .sidebar-menu a i { margin-right: 10px; width: 20px; }
        .sidebar-logout {
            position: absolute; bottom: 30px;
            width: 100%; padding: 0 20px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2); color: white;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 10px; padding: 10px;
            width: 100%; font-weight: 600;
        }
        .btn-logout:hover { background: white; color: #0d6efd; }
        .main-content { margin-left: 260px; padding: 30px; }
        .topbar {
            background: white; border-radius: 15px;
            padding: 20px 25px;
            display: flex; justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }
        .topbar h5 { margin: 0; font-weight: 700; color: #333; }
        .user-badge {
            background: #f0f4ff; color: #0d6efd;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 600; font-size: 14px;
        }

        /* Appointment Summary */
        .apt-summary {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
            border-left: 5px solid #0d6efd;
        }
        .apt-summary h5 { font-weight: 700; color: #0d6efd; margin-bottom: 15px; }

        /* Payment Methods */
        .payment-card {
            background: white; border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .payment-card h5 { font-weight: 700; margin-bottom: 20px; }

        .method-card {
            border: 3px solid #e0e0e0;
            border-radius: 12px; padding: 15px 20px;
            cursor: pointer; transition: all 0.3s;
            margin-bottom: 15px; display: flex;
            align-items: center; gap: 15px;
        }
        .method-card:hover { border-color: #0d6efd; background: #f0f4ff; }
        .method-card.selected { border-color: #0d6efd; background: #f0f4ff; }
        .method-icon { width: 55px; height: 55px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 700; color: white; flex-shrink: 0;
        }
        .method-name { font-weight: 700; font-size: 16px; color: #333; }
        .method-desc { font-size: 12px; color: #888; }

        /* Payment Forms */
        .payment-form { display: none; margin-top: 20px; }
        .payment-form.active { display: block; }

        .form-label { font-weight: 600; color: #333; }
        .form-control {
            border-radius: 10px; padding: 12px 15px;
            border: 2px solid #e0e0e0; font-size: 15px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .btn-pay {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white; border: none; border-radius: 10px;
            padding: 14px 40px; font-size: 16px;
            font-weight: 700; width: 100%; margin-top: 15px;
            transition: opacity 0.3s;
        }
        .btn-pay:hover { opacity: 0.9; color: white; }

        .amount-badge {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white; border-radius: 10px;
            padding: 15px 25px; text-align: center;
            margin-bottom: 20px;
        }
        .amount-badge h3 { margin: 0; font-weight: 700; font-size: 28px; }
        .amount-badge p { margin: 0; opacity: 0.85; font-size: 14px; }

        /* Card number formatting */
        .card-input { letter-spacing: 2px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-hospital-alt"></i>
        <h4>MediCare</h4>
        <small style="opacity:0.7">Patient Portal</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        <a href="my_appointments.php" class="active"><i class="fas fa-calendar-check"></i> My Appointments</a>
        <a href="video_call.php"><i class="fas fa-video"></i> Video Call</a>
        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
    </div>
    <div class="sidebar-logout">
        <a href="../logout.php">
            <button class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="topbar">
        <h5><i class="fas fa-credit-card"></i> Payment</h5>
        <div class="user-badge">
            <i class="fas fa-user"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <!-- Appointment Summary -->
    <div class="apt-summary">
        <h5><i class="fas fa-calendar-check"></i> Appointment Summary</h5>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted" style="font-size:13px">Doctor</p>
                <p class="fw-bold mb-0">Dr. <?php echo $apt['doctor_name']; ?></p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted" style="font-size:13px">Specialization</p>
                <p class="fw-bold mb-0"><?php echo $apt['specialization']; ?></p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted" style="font-size:13px">Date & Time</p>
                <p class="fw-bold mb-0">
                    <?php echo date('d M Y', strtotime($apt['appointment_date'])); ?>
                    — <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted" style="font-size:13px">Type</p>
                <p class="fw-bold mb-0">
                    <?php echo $apt['type'] == 'video-call' ? '🎥 Video Call' : '🏥 In-Person'; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="payment-card">
                <h5><i class="fas fa-credit-card text-primary"></i> Select Payment Method</h5>

                <!-- Amount -->
                <div class="amount-badge">
                    <p>Consultation Fee</p>
                    <h3>Rs. 500</h3>
                </div>

                <!-- JazzCash -->
                <div class="method-card" id="card_jazzcash"
                     onclick="selectMethod('jazzcash')">
                    <div class="method-icon" style="background:#EF3737">💳</div>
                    <div>
                        <div class="method-name">JazzCash</div>
                        <div class="method-desc">Pay via JazzCash Mobile Account</div>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>

                <!-- JazzCash Form -->
                <div class="payment-form" id="form_jazzcash">
                    <div class="p-3" style="background:#FFF5F5;border-radius:10px;border:1px solid #FFCCCC">
                        <p class="mb-2" style="color:#EF3737;font-weight:600">
                            <i class="fas fa-mobile-alt"></i> JazzCash Mobile Account
                        </p>
                        <div class="mb-3">
                            <label class="form-label">JazzCash Number</label>
                            <input type="text" class="form-control"
                                   placeholder="03XX-XXXXXXX" maxlength="11"
                                   id="jazzcash_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MPIN</label>
                            <input type="password" class="form-control"
                                   placeholder="Enter 4-digit MPIN" maxlength="4"
                                   id="jazzcash_mpin">
                        </div>
                        <button class="btn-pay" onclick="processPayment('jazzcash')">
                            <i class="fas fa-lock"></i> Pay Rs. 500 via JazzCash
                        </button>
                    </div>
                </div>

                <!-- Easypaisa -->
                <div class="method-card" id="card_easypaisa"
                     onclick="selectMethod('easypaisa')">
                    <div class="method-icon" style="background:#4CAF50">💚</div>
                    <div>
                        <div class="method-name">Easypaisa</div>
                        <div class="method-desc">Pay via Easypaisa Mobile Account</div>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>

                <!-- Easypaisa Form -->
                <div class="payment-form" id="form_easypaisa">
                    <div class="p-3" style="background:#F5FFF5;border-radius:10px;border:1px solid #CCFFCC">
                        <p class="mb-2" style="color:#4CAF50;font-weight:600">
                            <i class="fas fa-mobile-alt"></i> Easypaisa Mobile Account
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Easypaisa Number</label>
                            <input type="text" class="form-control"
                                   placeholder="03XX-XXXXXXX" maxlength="11"
                                   id="easypaisa_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PIN</label>
                            <input type="password" class="form-control"
                                   placeholder="Enter 4-digit PIN" maxlength="4"
                                   id="easypaisa_pin">
                        </div>
                        <button class="btn-pay" onclick="processPayment('easypaisa')"
                                style="background:linear-gradient(135deg,#4CAF50,#388E3C)">
                            <i class="fas fa-lock"></i> Pay Rs. 500 via Easypaisa
                        </button>
                    </div>
                </div>

                <!-- Debit/Credit Card -->
                <div class="method-card" id="card_card"
                     onclick="selectMethod('card')">
                    <div class="method-icon" style="background:#1565C0">💳</div>
                    <div>
                        <div class="method-name">Debit / Credit Card</div>
                        <div class="method-desc">Visa, Mastercard, UnionPay</div>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>

                <!-- Card Form -->
                <div class="payment-form" id="form_card">
                    <div class="p-3" style="background:#F0F4FF;border-radius:10px;border:1px solid #C5D8FF">
                        <p class="mb-2" style="color:#1565C0;font-weight:600">
                            <i class="fas fa-credit-card"></i> Card Details
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control card-input"
                                   placeholder="XXXX XXXX XXXX XXXX" maxlength="19"
                                   id="card_number" oninput="formatCard(this)">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control"
                                       placeholder="MM/YY" maxlength="5"
                                       id="card_expiry">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="password" class="form-control"
                                       placeholder="XXX" maxlength="3"
                                       id="card_cvv">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cardholder Name</label>
                            <input type="text" class="form-control"
                                   placeholder="Name on card"
                                   id="card_name">
                        </div>
                        <button class="btn-pay" onclick="processPayment('card')"
                                style="background:linear-gradient(135deg,#1565C0,#0D47A1)">
                            <i class="fas fa-lock"></i> Pay Rs. 500 via Card
                        </button>
                    </div>
                </div>

                <!-- Bank Transfer -->
                <div class="method-card" id="card_bank"
                     onclick="selectMethod('bank')">
                    <div class="method-icon" style="background:#6A1B9A">🏦</div>
                    <div>
                        <div class="method-name">Bank Transfer</div>
                        <div class="method-desc">HBL, MCB, UBL, Meezan Bank</div>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>

                <!-- Bank Form -->
                <div class="payment-form" id="form_bank">
                    <div class="p-3" style="background:#F8F0FF;border-radius:10px;border:1px solid #E0C8FF">
                        <p class="mb-2" style="color:#6A1B9A;font-weight:600">
                            <i class="fas fa-university"></i> Bank Transfer Details
                        </p>
                        <div class="mb-3" style="background:white;border-radius:8px;padding:15px;border:1px solid #E0C8FF">
                            <p class="mb-1"><strong>Bank:</strong> HBL — Hayatabad Branch</p>
                            <p class="mb-1"><strong>Account Title:</strong> MediCare Hospital</p>
                            <p class="mb-1"><strong>Account No:</strong> 1234-5678-9012</p>
                            <p class="mb-0"><strong>Amount:</strong> Rs. 500</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction Reference No.</label>
                            <input type="text" class="form-control"
                                   placeholder="Enter bank transaction reference"
                                   id="bank_ref">
                        </div>
                        <button class="btn-pay" onclick="processPayment('bank')"
                                style="background:linear-gradient(135deg,#6A1B9A,#4A148C)">
                            <i class="fas fa-check"></i> Confirm Bank Transfer
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Side Info -->
        <div class="col-md-4">
            <div style="background:white;border-radius:15px;padding:25px;box-shadow:0 2px 15px rgba(0,0,0,0.06);">
                <h6 class="fw-bold mb-3">🔐 Secure Payment</h6>
                <p style="font-size:13px;color:#888">
                    Your payment is 100% secure and encrypted.
                </p>
                <hr>
                <h6 class="fw-bold mb-3">📋 Payment Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:#888">Consultation Fee</span>
                    <span style="font-size:13px;font-weight:600">Rs. 500</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:#888">Service Charges</span>
                    <span style="font-size:13px;font-weight:600">Rs. 0</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span style="font-weight:700">Total</span>
                    <span style="font-weight:700;color:#0d6efd">Rs. 500</span>
                </div>
                <hr>
                <p style="font-size:12px;color:#888;text-align:center">
                    <i class="fas fa-shield-alt text-success"></i>
                    SSL Secured Payment
                </p>
                <!-- Payment Methods Icons -->
                <div class="text-center mt-2">
                    <span style="font-size:22px;margin:0 5px">💳</span>
                    <span style="font-size:22px;margin:0 5px">💚</span>
                    <span style="font-size:22px;margin:0 5px">🏦</span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:15px;text-align:center;padding:30px">
            <div class="spinner-border text-primary mb-3" style="width:50px;height:50px;margin:0 auto"></div>
            <h5 class="fw-bold">Processing Payment...</h5>
            <p class="text-muted">Please wait, do not close this window.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let selectedMethod = '';

function selectMethod(method) {
    document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.payment-form').forEach(f => f.classList.remove('active'));
    document.getElementById('card_' + method).classList.add('selected');
    document.getElementById('form_' + method).classList.add('active');
    selectedMethod = method;
    document.getElementById('form_' + method).scrollIntoView({ behavior: 'smooth' });
}

function formatCard(input) {
    let value = input.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let matches = value.match(/\d{4,16}/g);
    let match = matches && matches[0] || '';
    let parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
        parts.push(match.substring(i, i + 4));
    }
    input.value = parts.length ? parts.join(' ') : value;
}

function validatePakistaniNumber(num) {
    // 03XXXXXXXXX format validate karo
    let clean = num.replace(/[-\s]/g, '');
    return /^03[0-9]{9}$/.test(clean);
}

function processPayment(method) {

    if (method === 'jazzcash') {
        let num = document.getElementById('jazzcash_number').value;
        let mpin = document.getElementById('jazzcash_mpin').value;

        if (!num || !mpin) {
            alert('❌ Please fill all JazzCash details!');
            return;
        }
        if (!validatePakistaniNumber(num)) {
            alert('❌ Invalid JazzCash number!\nFormat: 03XX-XXXXXXX');
            return;
        }
        if (mpin.length !== 4 || !/^\d{4}$/.test(mpin)) {
            alert('❌ MPIN must be exactly 4 digits!');
            return;
        }

    } else if (method === 'easypaisa') {
        let num = document.getElementById('easypaisa_number').value;
        let pin = document.getElementById('easypaisa_pin').value;

        if (!num || !pin) {
            alert('❌ Please fill all Easypaisa details!');
            return;
        }
        if (!validatePakistaniNumber(num)) {
            alert('❌ Invalid Easypaisa number!\nFormat: 03XX-XXXXXXX');
            return;
        }
        if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
            alert('❌ PIN must be exactly 4 digits!');
            return;
        }

    } else if (method === 'card') {
        let cardNum = document.getElementById('card_number').value.replace(/\s/g,'');
        let expiry  = document.getElementById('card_expiry').value;
        let cvv     = document.getElementById('card_cvv').value;
        let name    = document.getElementById('card_name').value;

        if (!cardNum || !expiry || !cvv || !name) {
            alert('❌ Please fill all card details!');
            return;
        }
        if (cardNum.length !== 16 || !/^\d{16}$/.test(cardNum)) {
            alert('❌ Card number must be 16 digits!');
            return;
        }
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            alert('❌ Invalid expiry format! Use MM/YY');
            return;
        }
        if (cvv.length !== 3 || !/^\d{3}$/.test(cvv)) {
            alert('❌ CVV must be exactly 3 digits!');
            return;
        }

    } else if (method === 'bank') {
        let ref = document.getElementById('bank_ref').value;
        if (!ref || ref.length < 5) {
            alert('❌ Please enter valid bank transaction reference!');
            return;
        }
    } else {
        alert('❌ Please select a payment method first!');
        return;
    }

    // Processing modal
    var modal = new bootstrap.Modal(
        document.getElementById('processingModal'));
    modal.show();

    // 3 second delay — real feel
    setTimeout(function() {
        window.location.href =
            'payment_process.php?id=<?php echo $appointment_id; ?>&method=' + method;
    }, 3000);
}
</script>

</body>
</html>