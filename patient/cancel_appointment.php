<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$id         = $_GET['id'];
$patient_id = $_SESSION['user_id'];

mysqli_query($conn,
    "UPDATE appointments SET status='cancelled' 
     WHERE id='$id' AND patient_id='$patient_id'");

echo "<script>
        alert('Appointment Cancelled!');
        window.location='my_appointments.php';
      </script>";
?>