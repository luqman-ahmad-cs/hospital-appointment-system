<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id     = $_GET['id'];
$action = $_GET['action'];

if ($action === 'block') {
    mysqli_query($conn,
        "UPDATE users SET status='blocked' WHERE id='$id'");
    echo "<script>alert('Patient Blocked!');
          window.location='manage_patients.php';</script>";

} elseif ($action === 'unblock') {
    mysqli_query($conn,
        "UPDATE users SET status='active' WHERE id='$id'");
    echo "<script>alert('Patient Unblocked!');
          window.location='manage_patients.php';</script>";

} elseif ($action === 'delete') {
    mysqli_query($conn,
        "DELETE FROM appointments WHERE patient_id='$id'");
    mysqli_query($conn,
        "DELETE FROM users WHERE id='$id'");
    echo "<script>alert('Patient Deleted!');
          window.location='manage_patients.php';</script>";
}
?>