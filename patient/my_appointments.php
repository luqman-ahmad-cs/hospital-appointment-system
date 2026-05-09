<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

$appointments = mysqli_query($conn,
    "SELECT a.*, u.fullname as doctor_name, 
            d.specialization
     FROM appointments a
     JOIN users u ON a.doctor_id = u.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE a.patient_id = '$patient_id'
     ORDER BY a.appointment_date DESC, a.appointment_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments — MediCare</title>

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

        .table-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }

        .table-card h5 { font-weight: 700; margin-bottom: 20px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table td, .table th { vertical-align: middle; }

        .badge-pending { background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-confirmed { background: #d1e7dd; color: #0f5132; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-completed { background: #cfe2ff; color: #084298; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-cancelled { background: #f8d7da; color: #842029; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-video { background: #e2d9f3; color: #6f42c1; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-inperson { background: #d1e7dd; color: #0f5132; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }

        .btn-cancel {
            background: #dc3545; color: white;
            border: none; border-radius: 8px;
            padding: 6px 15px; font-size: 13px;
            font-weight: 600; cursor: pointer;
            text-decoration: none;
        }

        .btn-cancel:hover { background: #b02a37; color: white; }

        .btn-book-new {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white; border: none; border-radius: 10px;
            padding: 10px 25px; font-weight: 600;
            text-decoration: none; font-size: 14px;
        }

        .btn-book-new:hover { opacity: 0.9; color: white; }
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
        <a href="my_appointments.php" class="active"><i class="fas fa-calendar-check"></i> My Appointments</a>
        <a href="video_call.php"><i class="fas fa-video"></i> Video Call</a>
        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
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
        <h5><i class="fas fa-calendar-check"></i> My Appointments</h5>
        <div class="user-badge">
            <i class="fas fa-user"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-list text-primary"></i> All Appointments</h5>
            <a href="book_appointment.php" class="btn-book-new">
                <i class="fas fa-plus"></i> Book New
            </a>
        </div>

        <?php if (mysqli_num_rows($appointments) == 0): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-4x mb-3" style="color:#ccc"></i>
                <h5>No appointments yet!</h5>
                <p>Book your first appointment now.</p>
                <a href="book_appointment.php" class="btn-book-new">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong>Dr. <?php echo $row['doctor_name']; ?></strong></td>
                        <td><?php echo $row['specialization']; ?></td>
                        <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td>
                            <?php if($row['type'] == 'video-call'): ?>
                                <span class="badge-video">🎥 Video Call</span>
                            <?php else: ?>
                                <span class="badge-inperson">🏥 In-Person</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'pending'): ?>
                                <span class="badge-pending">⏳ Pending</span>
                            <?php elseif($row['status'] == 'confirmed'): ?>
                                <span class="badge-confirmed">✅ Confirmed</span>
                            <?php elseif($row['status'] == 'completed'): ?>
                                <span class="badge-completed">🏁 Completed</span>
                            <?php else: ?>
                                <span class="badge-cancelled">❌ Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'pending'): ?>
                            <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Cancel this appointment?')"
                               class="btn-cancel">❌ Cancel</a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
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