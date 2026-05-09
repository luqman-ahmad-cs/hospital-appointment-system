<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id     = $_GET['id'];
$action = $_GET['action'];

if ($action === 'approve') {
    mysqli_query($conn,
        "UPDATE users SET status='active' WHERE id='$id'");
    mysqli_query($conn,
        "UPDATE doctors SET status='active' WHERE user_id='$id'");
    echo "<script>alert('Doctor Approved Successfully!');
          window.location='dashboard.php';</script>";

} elseif ($action === 'reject') {
    mysqli_query($conn,
        "DELETE FROM doctors WHERE user_id='$id'");
    mysqli_query($conn,
        "DELETE FROM users WHERE id='$id'");
    echo "<script>alert('Doctor Rejected & Removed!');
          window.location='dashboard.php';</script>";
}
?>