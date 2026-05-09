<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Update handle karo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);

    mysqli_query($conn,
        "UPDATE users SET fullname='$fullname', phone='$phone'
         WHERE id='$patient_id'");

    // Session update karo
    $_SESSION['user_name'] = $fullname;

    // Password change
    if (!empty($_POST['new_password'])) {
        $new_pass = md5($_POST['new_password']);
        mysqli_query($conn,
            "UPDATE users SET password='$new_pass'
             WHERE id='$patient_id'");
    }

    echo "<script>alert('Profile Updated Successfully!');
          window.location='profile.php';</script>";
    exit();
}

// Patient info fetch karo
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id='$patient_id'"));

// Appointment stats
$total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM appointments
     WHERE patient_id='$patient_id'"));
$completed = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM appointments
     WHERE patient_id='$patient_id' AND status='completed'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4ff; }
        .sidebar {
            width: 260px; height: 100vh;
            background: linear-gradient(180deg, #0d6efd, #0a58ca);
            position: fixed; left: 0; top: 0;
            padding: 30px 0; z-index: 100;
        }
        .sidebar-logo {
            text-align: center; color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-logo i { font-size: 40px; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; color: rgba(255,255,255,0.85);
            text-decoration: none; padding: 13px 25px;
            font-size: 15px; transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white; padding-left: 35px;
        }
        .sidebar-menu a i { margin-right: 10px; width: 20px; }
        .sidebar-logout {
            position: absolute; bottom: 30px;
            width: 100%; padding: 0 20px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2); color: white;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 10px; padding: 10px;
            width: 100%; font-weight: 600;
        }
        .btn-logout:hover { background: white; color: #0d6efd; }
        .main-content { margin-left: 260px; padding: 30px; }
        .topbar {
            background: white; border-radius: 15px;
            padding: 20px 25px;
            display: flex; justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }
        .topbar h5 { margin: 0; font-weight: 700; color: #333; }
        .user-badge {
            background: #f0f4ff; color: #0d6efd;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 600; font-size: 14px;
        }
        .profile-card {
            background: white; border-radius: 15px;
            padding: 35px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .profile-avatar {
            width: 100px; height: 100px; border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            display: flex; align-items: center;
            justify-content: center;
            font-size: 45px; color: white;
            margin: 0 auto 20px;
        }
        .profile-name {
            text-align: center; font-size: 22px;
            font-weight: 700; color: #333; margin-bottom: 5px;
        }
        .profile-role {
            text-align: center; color: #0d6efd;
            font-weight: 600; margin-bottom: 25px;
        }
        .stat-mini {
            background: #f0f4ff; border-radius: 10px;
            padding: 15px; text-align: center;
        }
        .stat-mini h4 { font-weight: 700; color: #0d6efd; margin: 0; }
        .stat-mini p { color: #888; margin: 5px 0 0; font-size: 13px; }
        .form-label { font-weight: 600; color: #333; }
        .form-control {
            border-radius: 10px; padding: 12px 15px;
            border: 2px solid #e0e0e0; font-size: 15px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .btn-update {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white; border: none; border-radius: 10px;
            padding: 13px 40px; font-size: 16px;
            font-weight: 600; transition: opacity 0.3s;
        }
        .btn-update:hover { opacity: 0.9; color: white; }
        .section-title {
            font-weight: 700; color: #0d6efd;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px; margin-bottom: 20px;
            margin-top: 30px;
        }
        .info-badge {
            background: #f0f4ff; border: 1px solid #c5d8ff;
            border-radius: 10px; padding: 15px 20px;
            margin-bottom: 20px;
        }
        .info-badge p { margin: 0; color: #555; font-size: 14px; }
        .info-badge strong { color: #0d6efd; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-hospital-alt"></i>
        <h4>MediCare</h4>
        <small style="opacity:0.7">Patient Portal</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        <a href="my_appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a>
        <a href="video_call.php"><i class="fas fa-video"></i> Video Call</a>
        <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
    </div>
    <div class="sidebar-logout">
        <a href="../logout.php">
            <button class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="topbar">
        <h5><i class="fas fa-user"></i> My Profile</h5>
        <div class="user-badge">
            <i class="fas fa-user"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="profile-card">

                <!-- Avatar -->
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?php echo $user['fullname']; ?></div>
                <div class="profile-role">🧑‍💼 Patient</div>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="stat-mini">
                            <h4><?php echo $total['cnt']; ?></h4>
                            <p>Total Appointments</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-mini">
                            <h4><?php echo $completed['cnt']; ?></h4>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="info-badge">
                    <p>
                        <strong>Email:</strong> <?php echo $user['email']; ?>
                        &nbsp;|&nbsp;
                        <strong>Joined:</strong>
                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>

                <!-- Update Form -->
                <form method="POST">

                    <div class="section-title">
                        <i class="fas fa-user"></i> Personal Information
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control"
                                   name="fullname"
                                   value="<?php echo $user['fullname']; ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control"
                                   name="phone"
                                   value="<?php echo $user['phone']; ?>">
                        </div>
                    </div>

                    <div class="section-title mt-4">
                        <i class="fas fa-lock"></i> Change Password
                        <small style="font-size:13px;color:#888;font-weight:400">
                            (Khali choro agar nahi badalna)
                        </small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control"
                                   name="new_password"
                                   placeholder="New password">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>