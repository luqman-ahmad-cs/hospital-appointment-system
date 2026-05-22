<?php
session_start();
include '../db/connection.php';
include '../db/email.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id     = $_GET['id'];
$action = $_GET['action'];

// Patient info fetch karo
$patient = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id='$id'"));

if (!$patient) {
    echo "<script>alert('Patient not found!');
          window.location='manage_patients.php';</script>";
    exit();
}

if ($action === 'block') {

    mysqli_query($conn,
        "UPDATE users SET status='blocked' WHERE id='$id'");

    $subject = "Account Blocked - MediCare";
    $message = "
    <h2 style='color:#dc3545;'>Account Blocked</h2>
    <p>Dear <strong>" . $patient['fullname'] . "</strong>,</p>
    <p>Your patient account has been blocked by admin.
       Please contact support for more information.</p>
    <div style='background:#f8d7da;border-radius:10px;padding:15px;'>
        <p style='margin:0;color:#842029;'>
            Contact: luqman.ahmad.cs@gmail.com
        </p>
    </div>";

    sendEmail($patient['email'], $patient['fullname'], $subject, $message);

    echo "<script>alert('Patient Blocked! Email sent.');
          window.location='manage_patients.php';</script>";

} elseif ($action === 'unblock') {

    mysqli_query($conn,
        "UPDATE users SET status='active' WHERE id='$id'");

    $subject = "Account Unblocked - MediCare";
    $message = "
    <h2 style='color:#28a745;'>Account Unblocked!</h2>
    <p>Dear <strong>" . $patient['fullname'] . "</strong>,</p>
    <p>Your account has been unblocked.
       You can login and book appointments again!</p>
    <a href='http://localhost:8080/hospital_project/login.php'
       style='display:block;background:#0d6efd;color:white;padding:14px;
              border-radius:8px;text-decoration:none;
              font-weight:bold;text-align:center;'>
        Login Now
    </a>";

    sendEmail($patient['email'], $patient['fullname'], $subject, $message);

    echo "<script>alert('Patient Unblocked! Email sent.');
          window.location='manage_patients.php';</script>";

} elseif ($action === 'delete') {

    // Email bhejo pehle
    $subject = "Account Removed - MediCare";
    $message = "
    <h2 style='color:#dc3545;'>Account Removed</h2>
    <p>Dear <strong>" . $patient['fullname'] . "</strong>,</p>
    <p>Your patient account has been removed from MediCare.</p>
    <div style='background:#f8d7da;border-radius:10px;padding:15px;'>
        <p style='margin:0;color:#842029;'>
            Contact: luqman.ahmad.cs@gmail.com
        </p>
    </div>";

    sendEmail($patient['email'], $patient['fullname'], $subject, $message);

    // Sahi order mein delete karo
    // 1. Pehle payments delete karo
    $apts = mysqli_query($conn,
        "SELECT id FROM appointments
         WHERE patient_id='$id'");
    while ($apt = mysqli_fetch_assoc($apts)) {
        mysqli_query($conn,
            "DELETE FROM payments
             WHERE appointment_id='" . $apt['id'] . "'");
    }

    // 2. Appointments delete karo
    mysqli_query($conn,
        "DELETE FROM appointments WHERE patient_id='$id'");

    // 3. User delete karo
    mysqli_query($conn,
        "DELETE FROM users WHERE id='$id'");

    echo "<script>alert('Patient Deleted Successfully!');
          window.location='manage_patients.php';</script>";
}
?>