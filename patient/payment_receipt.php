<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$txn_id = $_GET['txn'];
$apt_id = $_GET['apt'];

// Payment details fetch karo
$payment = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT p.*, a.appointment_date, a.appointment_time,
            a.type, u.fullname as doctor_name,
            pat.fullname as patient_name,
            d.specialization
     FROM payments p
     JOIN appointments a ON p.appointment_id = a.id
     JOIN users u ON a.doctor_id = u.id
     JOIN users pat ON p.patient_id = pat.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE p.transaction_id = '$txn_id'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4ff; display: flex; align-items: center;
               justify-content: center; min-height: 100vh; padding: 30px; }

        .receipt-card {
            background: white; border-radius: 20px;
            padding: 0; max-width: 550px; width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            padding: 30px; text-align: center; color: white;
        }

        .receipt-header .check-icon {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 40px; margin: 0 auto 15px;
        }

        .receipt-header h3 { font-weight: 700; margin: 0; }
        .receipt-header p { opacity: 0.85; margin: 5px 0 0; }

        .receipt-body { padding: 30px; }

        .txn-id {
            background: #f0f4ff; border-radius: 10px;
            padding: 12px 20px; text-align: center;
            margin-bottom: 20px; border: 2px dashed #0d6efd;
        }

        .txn-id p { margin: 0; color: #888; font-size: 12px; }
        .txn-id h5 { margin: 5px 0 0; color: #0d6efd; font-weight: 700; }

        .receipt-row {
            display: flex; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid #f0f0f0;
        }

        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .label { color: #888; font-size: 14px; }
        .receipt-row .value { font-weight: 600; font-size: 14px; color: #333; }

        .total-row {
            background: #f0f4ff; border-radius: 10px;
            padding: 15px 20px; margin: 15px 0;
            display: flex; justify-content: space-between;
            align-items: center;
        }

        .total-row .label { font-weight: 700; font-size: 16px; }
        .total-row .value { font-weight: 700; font-size: 22px; color: #28a745; }

        .status-badge {
            background: #d1e7dd; color: #0f5132;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 700; font-size: 14px;
            display: inline-block; margin-bottom: 20px;
        }

        .btn-dashboard {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white; border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 600;
            width: 100%; margin-bottom: 10px; cursor: pointer;
        }

        .btn-print {
            background: #6c757d; color: white;
            border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 600;
            width: 100%; cursor: pointer;
        }

        .receipt-footer {
            background: #f8f9fa; padding: 15px;
            text-align: center; border-top: 1px solid #e0e0e0;
        }

        .receipt-footer p {
            margin: 0; font-size: 11px; color: #888;
        }
    </style>
</head>
<body>

<div class="receipt-card">

    <!-- Header -->
    <div class="receipt-header">
        <div class="check-icon">✅</div>
        <h3>Payment Successful!</h3>
        <p>Your payment has been processed successfully</p>
    </div>

    <!-- Body -->
    <div class="receipt-body">

        <!-- Transaction ID -->
        <div class="txn-id">
            <p>Transaction ID</p>
            <h5><?php echo $payment['transaction_id']; ?></h5>
        </div>

        <!-- Status -->
        <div class="text-center">
            <span class="status-badge">✅ PAID</span>
        </div>

        <!-- Details -->
        <div class="receipt-row">
            <span class="label">Patient Name</span>
            <span class="value"><?php echo $payment['patient_name']; ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Doctor</span>
            <span class="value">Dr. <?php echo $payment['doctor_name']; ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Specialization</span>
            <span class="value"><?php echo $payment['specialization']; ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Appointment Date</span>
            <span class="value">
                <?php echo date('d M Y', strtotime($payment['appointment_date'])); ?>
            </span>
        </div>
        <div class="receipt-row">
            <span class="label">Appointment Time</span>
            <span class="value">
                <?php echo date('h:i A', strtotime($payment['appointment_time'])); ?>
            </span>
        </div>
        <div class="receipt-row">
            <span class="label">Payment Method</span>
            <span class="value"><?php echo ucfirst($payment['payment_method']); ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Payment Date</span>
            <span class="value">
                <?php echo date('d M Y h:i A', strtotime($payment['created_at'])); ?>
            </span>
        </div>

        <!-- Total -->
        <div class="total-row">
            <span class="label">Total Amount Paid</span>
            <span class="value">Rs. <?php echo number_format($payment['amount'], 0); ?></span>
        </div>

        <!-- Buttons -->
        <button class="btn-dashboard"
                onclick="window.location='my_appointments.php'">
            <i class="fas fa-home"></i> Go to Dashboard
        </button>
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>

    </div>

    <!-- Footer -->
    <div class="receipt-footer">
        <p>🏥 MediCare Hospital System — Government Degree College Hayatabad</p>
        <p>This is an electronically generated receipt</p>
    </div>

</div>

</body>
</html>