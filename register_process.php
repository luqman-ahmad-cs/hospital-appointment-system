<?php
include 'db/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname  = trim($_POST['fullname']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = md5($_POST['password']);
    $confirm   = $_POST['confirm_password'];
    $role      = $_POST['role'];

    // Password match check
    if ($_POST['password'] !== $confirm) {
        echo "<script>alert('Passwords do not match!'); 
              window.history.back();</script>";
        exit();
    }

    // Email already exists check
    $check = mysqli_query($conn, 
             "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email already registered! Try login.'); 
              window.location='login.php';</script>";
        exit();
    }

    // Doctor status pending hogi
    $status = ($role === 'doctor') ? 'pending' : 'active';

    // Users table mein insert karo
    $sql = "INSERT INTO users 
            (fullname, email, phone, password, role, status) 
            VALUES 
            ('$fullname','$email','$phone','$password','$role','$status')";

    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);

        // Agar doctor hai tou doctors table mein bhi daalo
        if ($role === 'doctor') {
            $spec = $_POST['specialization'];
            $qual = $_POST['qualification'];
            $exp  = $_POST['experience'];

            mysqli_query($conn, 
            "INSERT INTO doctors 
             (user_id, specialization, qualification, experience, status)
             VALUES 
             ('$user_id','$spec','$qual','$exp','pending')");

            echo "<script>alert('Doctor registration submitted! Wait for admin approval.');
                  window.location='login.php';</script>";
        } else {
            echo "<script>alert('Registration successful! Please login.');
                  window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Error! Try again.');
              window.history.back();</script>";
    }
}
?>