<?php
session_start();
include '../db/connection.php';
include '../db/email.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$id        = $_GET['id'];
$doctor_id = $_SESSION['user_id'];

// Appointment + patient + doctor details fetch
$apt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*,
            p.fullname as patient_name,
            p.email    as patient_email,
            d.fullname as doctor_name
     FROM appointments a
     JOIN users p ON a.patient_id = p.id
     JOIN users d ON a.doctor_id  = d.id
     WHERE a.id = '$id'"));

// Status update
mysqli_query($conn,
    "UPDATE appointments SET status='confirmed'
     WHERE id='$id'");

$apt_date = date('d M Y', strtotime($apt['appointment_date']));
$apt_time = date('h:i A', strtotime($apt['appointment_time']));

$admin_email = ADMIN_NOTIFY_EMAIL;

// Patient ko confirmation + payment email
$subject = "Appointment Confirmed - MediCare";
$message = "
<h2 style='color:#28a745;'>Appointment Confirmed!</h2>
<p>Dear <strong>" . $apt['patient_name'] . "</strong>,</p>
<p>Your appointment has been confirmed by the doctor.</p>

<table style='width:100%;border-collapse:collapse;
              background:#f8f9fa;border-radius:8px;'>
    <tr style='background:#d1e7dd;'>
        <td style='padding:12px;color:#666;width:40%;'>Doctor</td>
        <td style='padding:12px;font-weight:600;'>
            Dr. " . $apt['doctor_name'] . "
        </td>
    </tr>
    <tr>
        <td style='padding:12px;color:#666;'>Date</td>
        <td style='padding:12px;font-weight:600;'>$apt_date</td>
    </tr>
    <tr style='background:#f8f9fa;'>
        <td style='padding:12px;color:#666;'>Time</td>
        <td style='padding:12px;font-weight:600;'>$apt_time</td>
    </tr>
    <tr>
        <td style='padding:12px;color:#666;'>Type</td>
        <td style='padding:12px;'>" . $apt['type'] . "</td>
    </tr>
</table>

<br>
<div style='background:#fff3cd;border-radius:10px;
            padding:20px;border-left:5px solid #ffc107;
            margin-top:15px;'>
    <h3 style='color:#856404;margin:0 0 15px;'>
        Payment Instructions
    </h3>
    <p style='color:#666;margin-bottom:15px;'>
        Please pay <strong>Rs. 500</strong> 
        consultation fee using any method below:
    </p>
    <table style='width:100%;border-collapse:collapse;'>
        <tr style='background:#fff;'>
            <td style='padding:10px;font-weight:700;
                       color:#EF3737;width:40%;'>
                JazzCash
            </td>
            <td style='padding:10px;'>
                <strong>0314-0908108</strong><br>
                <small style='color:#888;'>
                    Account: LUQMAN AHMED
                </small>
            </td>
        </tr>
        <tr style='background:#f8f9fa;'>
            <td style='padding:10px;font-weight:700;
                       color:#4CAF50;'>
                Easypaisa
            </td>
            <td style='padding:10px;'>
                <strong>0314-0908108</strong><br>
                <small style='color:#888;'>
                    Account: LUQMAN AHMED
                </small>
            </td>
        </tr>
        <tr style='background:#fff;'>
            <td style='padding:10px;font-weight:700;
                       color:#1565C0;'>
                Bank Transfer
            </td>
            <td style='padding:10px;'>
                <strong>HBL - 1234-5678-9012</strong><br>
                <small style='color:#888;'>
                    Account: Luqman Ahmad
                </small><br>
                <small style='color:#888;'>
                    Branch: Hayatabad, Peshawar
                </small>
            </td>
        </tr>
        <tr style='background:#f8f9fa;'>
            <td style='padding:10px;font-weight:700;
                       color:#6A1B9A;'>
                Amount
            </td>
            <td style='padding:10px;'>
                <strong style='font-size:18px;color:#28a745;'>
                    Rs. 500
                </strong>
            </td>
        </tr>
    </table>
</div>

<br>
<a href='http://localhost:8080/hospital_project/patient/my_appointments.php'
   style='display:block;background:#28a745;color:white;
          padding:14px;border-radius:8px;text-decoration:none;
          font-weight:bold;text-align:center;'>
    View My Appointments
</a>";

sendEmail($apt['patient_email'], $apt['patient_name'],
    $subject, $message);

// ── Admin ko confirmation notification ──
$subject_a = "Appointment Confirmed by Doctor - MediCare";
$message_a = "
<h2 style='color:#28a745;'>Appointment Confirmed!</h2>
<p>A doctor has confirmed an appointment.</p>

<table style='width:100%;border-collapse:collapse;
              background:#f8f9fa;border-radius:8px;'>
    <tr style='background:#d1e7dd;'>
        <td style='padding:12px;color:#666;width:40%;'>Appointment ID</td>
        <td style='padding:12px;font-weight:600;'>#$id</td>
    </tr>
    <tr>
        <td style='padding:12px;color:#666;'>Patient</td>
        <td style='padding:12px;font-weight:600;'>" . $apt['patient_name'] . "</td>
    </tr>
    <tr style='background:#d1e7dd;'>
        <td style='padding:12px;color:#666;'>Doctor</td>
        <td style='padding:12px;font-weight:600;'>Dr. " . $apt['doctor_name'] . "</td>
    </tr>
    <tr>
        <td style='padding:12px;color:#666;'>Date</td>
        <td style='padding:12px;font-weight:600;'>$apt_date</td>
    </tr>
    <tr style='background:#d1e7dd;'>
        <td style='padding:12px;color:#666;'>Time</td>
        <td style='padding:12px;font-weight:600;'>$apt_time</td>
    </tr>
    <tr>
        <td style='padding:12px;color:#666;'>Status</td>
        <td style='padding:12px;font-weight:700;color:#28a745;'>CONFIRMED</td>
    </tr>
</table>";

sendEmail($admin_email, 'Admin', $subject_a, $message_a);

echo "<script>
        alert('Appointment Confirmed! Patient notified via email.');
        window.location='dashboard.php';
      </script>";
?>