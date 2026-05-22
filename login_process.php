<?php
include 'db/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim($_POST['email']);
    $password = md5($_POST['password']);
    $role     = $_POST['role'];

    $sql = "SELECT * FROM users
            WHERE email='$email'
            AND password='$password'
            AND role='$role'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['status'] === 'blocked') {
            echo "<script>alert('Your account is blocked! Contact admin.');
                  window.location='login.php';</script>";
            exit();
        }

        if ($user['status'] === 'pending') {
            echo "<script>alert('Your account is pending admin approval!');
                  window.location='login.php';</script>";
            exit();
        }

        // Session set karo — sab users ke liye email save karo
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['fullname'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_email'] = $user['email'];

        if ($role === 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($role === 'doctor') {
            header("Location: doctor/dashboard.php");
        } else {
            header("Location: patient/dashboard.php");
        }
        exit();

    } else {
        echo "<script>alert('Invalid email, password or role!');
              window.location='login.php';</script>";
    }
}
?>