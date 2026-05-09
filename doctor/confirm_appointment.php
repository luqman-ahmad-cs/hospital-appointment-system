<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

mysqli_query($conn,
    "UPDATE appointments SET status='confirmed' WHERE id='$id'");

echo "<script>alert('Appointment Confirmed!');
      window.location='dashboard.php';</script>";
?>