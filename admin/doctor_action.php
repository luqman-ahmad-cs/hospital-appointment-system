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
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id='$id'");
    mysqli_query($conn, "UPDATE doctors SET status='active' WHERE user_id='$id'");
    echo "<script>alert('Doctor Approved!');
          window.location='manage_doctors.php';</script>";

} elseif ($action === 'block') {
    mysqli_query($conn, "UPDATE users SET status='blocked' WHERE id='$id'");
    echo "<script>alert('Doctor Blocked!');
          window.location='manage_doctors.php';</script>";

} elseif ($action === 'unblock') {
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id='$id'");
    echo "<script>alert('Doctor Unblocked!');
          window.location='manage_doctors.php';</script>";

} elseif ($action === 'delete') {
    mysqli_query($conn, "DELETE FROM doctors WHERE user_id='$id'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    echo "<script>alert('Doctor Deleted!');
          window.location='manage_doctors.php';</script>";
}
?>