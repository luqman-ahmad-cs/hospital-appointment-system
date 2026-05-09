<?php
session_start();

// Login check — agar login nahi hai tou wapas bhejo
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard — MediCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }

        body { background: #f0f4ff; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #0d6efd, #0a58ca);
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 0;
            z-index: 100;
        }

        .sidebar-logo {
            text-align: center;
            color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar-logo i { font-size: 40px; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }

        .sidebar-menu { padding: 20px 0; }

        .sidebar-menu a {
            display: block;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 13px 25px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            padding-left: 35px;
        }

        .sidebar-menu a i { margin-right: 10px; width: 20px; }

        .sidebar-logout {
            position: absolute;
            bottom: 30px;
            width: 100%;
            padding: 0 20px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: white;
            color: #0d6efd;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        /* Top Bar */
        .topbar {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }

        .topbar h5 { margin: 0; font-weight: 700; color: #333; }

        .user-badge {
            background: #f0f4ff;
            color: #0d6efd;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 20px;
            height: 100%;
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            flex-shrink: 0;
        }

        .stat-info h3 { font-weight: 700; margin: 0; font-size: 28px; }
        .stat-info p { margin: 0; color: #888; font-size: 14px; }

        /* Quick Actions */
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            color: inherit;
        }

        .action-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .action-card h5 { font-weight: 700; }
        .action-card p { color: #888; font-size: 13px; margin: 0; }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(13,110,253,0.3);
        }

        .welcome-card h4 { font-weight: 700; font-size: 22px; }
        .welcome-card p { opacity: 0.85; margin: 0; }
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
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="book_appointment.php">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
            <a href="my_appointments.php">
                <i class="fas fa-calendar-check"></i> My Appointments
            </a>
            <a href="video_call.php">
                <i class="fas fa-video"></i> Video Call
            </a>
            <a href="profile.php">
                <i class="fas fa-user"></i> My Profile
            </a>
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

        <!-- Top Bar -->
        <div class="topbar">
            <h5><i class="fas fa-home"></i> Patient Dashboard</h5>
            <div class="user-badge">
                <i class="fas fa-user"></i>
                <?php echo $_SESSION['user_name']; ?>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="welcome-card">
            <h4>👋 Welcome back, <?php echo $_SESSION['user_name']; ?>!</h4>
            <p>Manage your appointments and consult doctors online via video call.</p>
        </div>

        <!-- Stats Row -->
        <?php
        include '../db/connection.php';
        $patient_id = $_SESSION['user_id'];

        // Total appointments
        $total = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as cnt FROM appointments 
             WHERE patient_id='$patient_id'"));

        // Pending appointments
        $pending = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as cnt FROM appointments 
             WHERE patient_id='$patient_id' 
             AND status='pending'"));

        // Completed appointments
        $completed = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as cnt FROM appointments 
             WHERE patient_id='$patient_id' 
             AND status='completed'"));
        ?>

        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg,#0d6efd,#0a58ca)">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total['cnt']; ?></h3>
                        <p>Total Appointments</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg,#ffc107,#e0a800)">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending['cnt']; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg,#28a745,#1e7e34)">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed['cnt']; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Quick Actions -->
        <h5 class="fw-bold mb-3">Quick Actions</h5>
        <div class="row g-4">

            <div class="col-md-4">
                <a href="book_appointment.php" class="action-card">
                    <div class="action-icon">📅</div>
                    <h5>Book Appointment</h5>
                    <p>Schedule a visit with your preferred doctor</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="my_appointments.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <h5>My Appointments</h5>
                    <p>View all your upcoming and past appointments</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="video_call.php" class="action-card">
                    <div class="action-icon">🎥</div>
                    <h5>Video Consultation</h5>
                    <p>Consult your doctor via secure video call</p>
                </a>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>