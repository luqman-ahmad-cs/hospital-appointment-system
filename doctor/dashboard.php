<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard — MediCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }

        body { background: #f0f4ff; }

        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #0f5132, #1a7a4a);
            position: fixed;
            left: 0; top: 0;
            padding: 30px 0;
            z-index: 100;
        }

        .sidebar-logo {
            text-align: center;
            color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar-logo i { font-size: 40px; color: #90ee90; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }

        .sidebar-menu { padding: 20px 0; }

        .sidebar-menu a {
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 13px 25px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid #90ee90;
        }

        .sidebar-menu a i { margin-right: 10px; width: 20px; }

        .sidebar-logout {
            position: absolute;
            bottom: 30px;
            width: 100%;
            padding: 0 20px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover { background: #dc3545; color: white; }

        .main-content { margin-left: 260px; padding: 30px; }

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

        .doctor-badge {
            background: #0f5132;
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 20px;
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

        .stat-info h3 { font-weight: 700; margin: 0; font-size: 30px; }
        .stat-info p { margin: 0; color: #888; font-size: 14px; }

        .welcome-card {
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(15,81,50,0.3);
        }

        .welcome-card h4 { font-weight: 700; font-size: 22px; }
        .welcome-card p { opacity: 0.85; margin: 0; }

        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-top: 25px;
        }

        .table-card h5 { font-weight: 700; margin-bottom: 20px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table td, .table th { vertical-align: middle; }

        .badge-pending { background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-confirmed { background: #d1e7dd; color: #0f5132; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-completed { background: #cfe2ff; color: #084298; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-video { background: #f8d7da; color: #842029; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }

        .btn-confirm {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-confirm:hover { background: #1e7e34; color: white; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-user-md"></i>
        <h4 style="color:white">MediCare</h4>
        <small style="opacity:0.6">Doctor Portal</small>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="active">
            <i class="fas fa-home"></i> Dashboard
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

    <!-- Topbar -->
    <div class="topbar">
        <h5><i class="fas fa-stethoscope"></i> Doctor Dashboard</h5>
        <div class="doctor-badge">
            <i class="fas fa-user-md"></i>
            Dr. <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <?php
    include '../db/connection.php';
    $doctor_id = $_SESSION['user_id'];

    // Doctor info
    $doc_info = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT d.specialization, d.qualification, d.experience
         FROM doctors d WHERE d.user_id = '$doctor_id'"));

    // Stats
    $total = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments 
         WHERE doctor_id='$doctor_id'"));

    $pending = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments 
         WHERE doctor_id='$doctor_id' AND status='pending'"));

    $confirmed = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments 
         WHERE doctor_id='$doctor_id' AND status='confirmed'"));

    $completed = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments 
         WHERE doctor_id='$doctor_id' AND status='completed'"));
    ?>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <h4>👨‍⚕️ Welcome, Dr. <?php echo $_SESSION['user_name']; ?>!</h4>
        <p>
            <?php if($doc_info): ?>
                <?php echo $doc_info['specialization']; ?> |
                <?php echo $doc_info['qualification']; ?> |
                <?php echo $doc_info['experience']; ?> Years Experience
            <?php endif; ?>
        </p>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#0d6efd,#0a58ca)">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total['cnt']; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#ffc107,#e0a800)">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending['cnt']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#28a745,#1e7e34)">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $confirmed['cnt']; ?></h3>
                    <p>Confirmed</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#6f42c1,#59359a)">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $completed['cnt']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Today's Appointments -->
    <div class="table-card">
        <h5><i class="fas fa-calendar-day text-success"></i> 
            Today's Appointments 
            <small class="text-muted" style="font-size:14px">
                (<?php echo date('d M Y'); ?>)
            </small>
        </h5>

        <?php
        $today = date('Y-m-d');
        $todays = mysqli_query($conn,
            "SELECT a.*, u.fullname as patient_name, u.phone
             FROM appointments a
             JOIN users u ON a.patient_id = u.id
             WHERE a.doctor_id='$doctor_id' 
             AND a.appointment_date='$today'
             ORDER BY a.appointment_time ASC");
        ?>

        <?php if (mysqli_num_rows($todays) == 0): ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>No appointments today!</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($todays)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['patient_name']; ?></strong></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td>
                            <?php if($row['type'] == 'video-call'): ?>
                                <span class="badge-video">🎥 Video Call</span>
                            <?php else: ?>
                                <span class="badge-confirmed">🏥 In-Person</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'pending'): ?>
                                <span class="badge-pending">⏳ Pending</span>
                            <?php elseif($row['status'] == 'confirmed'): ?>
                                <span class="badge-confirmed">✅ Confirmed</span>
                            <?php else: ?>
                                <span class="badge-completed">🏁 Completed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'pending'): ?>
                            <a href="confirm_appointment.php?id=<?php echo $row['id']; ?>" 
                               class="btn-confirm">✅ Confirm</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>