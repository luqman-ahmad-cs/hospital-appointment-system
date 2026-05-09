<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $patient_id       = $_SESSION['user_id'];
    $doctor_id        = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $type             = $_POST['type'];
    $notes            = $_POST['notes'];

    // Same doctor same date same time already booked check
    $check = mysqli_query($conn,
        "SELECT id FROM appointments 
         WHERE doctor_id='$doctor_id' 
         AND appointment_date='$appointment_date' 
         AND appointment_time='$appointment_time'
         AND status != 'cancelled'");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>
                alert('This time slot is already booked! Please select another time.');
                window.history.back();
              </script>";
        exit();
    }

    // Appointment insert karo
    $sql = "INSERT INTO appointments 
            (patient_id, doctor_id, appointment_date, 
             appointment_time, type, notes, status)
            VALUES 
            ('$patient_id','$doctor_id','$appointment_date',
             '$appointment_time','$type','$notes','pending')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Appointment Booked Successfully! Please wait for doctor confirmation.');
                window.location='my_appointments.php';
              </script>";
    } else {
        echo "<script>
                alert('Error! Please try again.');
                window.history.back();
              </script>";
    }
}
?>