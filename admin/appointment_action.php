<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id     = $_GET['id'];
$action = $_GET['action'];

if ($action === 'complete') {
    mysqli_query($conn,
        "UPDATE appointments SET status='completed' WHERE id='$id'");
    echo "<script>alert('Appointment Marked Completed!');
          window.location='manage_appointments.php';</script>";

} elseif ($action === 'cancel') {
    mysqli_query($conn,
        "UPDATE appointments SET status='cancelled' WHERE id='$id'");
    echo "<script>alert('Appointment Cancelled!');
          window.location='manage_appointments.php';</script>";

} elseif ($action === 'delete') {
    mysqli_query($conn,
        "DELETE FROM appointments WHERE id='$id'");
    echo "<script>alert('Appointment Deleted!');
          window.location='manage_appointments.php';</script>";
}
?>