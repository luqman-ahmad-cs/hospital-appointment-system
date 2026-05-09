<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$appointments = mysqli_query($conn,
    "SELECT a.*, 
            p.fullname as patient_name, 
            d.fullname as doctor_name,
            doc.specialization
     FROM appointments a
     JOIN users p ON a.patient_id = p.id
     JOIN users d ON a.doctor_id = d.id
     JOIN doctors doc ON a.doctor_id = doc.user_id
     ORDER BY a.appointment_date DESC, a.appointment_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4ff; }
        .sidebar {
            width: 260px; height: 100vh;
            background: linear-gradient(180deg, #1a1a2e, #16213e);
            position: fixed; left: 0; top: 0;
            padding: 30px 0; z-index: 100;
        }
        .sidebar-logo {
            text-align: center; color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-logo i { font-size: 40px; color: #0d6efd; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; color: rgba(255,255,255,0.7);
            text-decoration: none; padding: 13px 25px;
            font-size: 15px; transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(13,110,253,0.2);
            color: white; border-left: 4px solid #0d6efd;
        }
        .sidebar-menu a i { margin-right: 10px; width: 20px; }
        .sidebar-logout {
            position: absolute; bottom: 30px;
            width: 100%; padding: 0 20px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.1); color: white;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px; padding: 10px;
            width: 100%; font-weight: 600;
        }
        .btn-logout:hover { background: #dc3545; color: white; }
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
        .admin-badge {
            background: #1a1a2e; color: white;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 600; font-size: 14px;
        }
        .filter-card {
            background: white; border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }
        .table-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .table-card h5 { font-weight: 700; margin-bottom: 20px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table td, .table th { vertical-align: middle; font-size: 14px; }
        .badge-pending { background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-confirmed { background: #d1e7dd; color: #0f5132; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-completed { background: #cfe2ff; color: #084298; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-cancelled { background: #f8d7da; color: #842029; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-video { background: #e2d9f3; color: #6f42c1; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-inperson { background: #d1e7dd; color: #0f5132; padding: 5px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .btn-action {
            border: none; border-radius: 8px;
            padding: 5px 10px; font-size: 11px;
            font-weight: 600; cursor: pointer;
            text-decoration: none; margin: 1px;
            display: inline-block;
        }
        .btn-complete { background: #0d6efd; color: white; }
        .btn-cancel { background: #dc3545; color: white; }
        .btn-delete { background: #6c757d; color: white; }

        /* Stats */
        .stat-card {
            background: white; border-radius: 15px;
            padding: 20px; text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .stat-card h3 { font-weight: 700; font-size: 28px; margin: 0; }
        .stat-card p { color: #888; margin: 5px 0 0; font-size: 13px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-hospital-alt"></i>
        <h4 style="color:white">MediCare</h4>
        <small style="opacity:0.5">Admin Panel</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a>
        <a href="manage_patients.php"><i class="fas fa-users"></i> Manage Patients</a>
        <a href="manage_appointments.php" class="active">
            <i class="fas fa-calendar-alt"></i> Appointments
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

    <div class="topbar">
        <h5><i class="fas fa-calendar-alt"></i> Manage Appointments</h5>
        <div class="admin-badge">
            <i class="fas fa-user-shield"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <?php
    // Stats
    $total = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments"));
    $pending = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments WHERE status='pending'"));
    $confirmed = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments WHERE status='confirmed'"));
    $completed = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments WHERE status='completed'"));
    ?>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h3 style="color:#0d6efd"><?php echo $total['cnt']; ?></h3>
                <p>Total</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 style="color:#ffc107"><?php echo $pending['cnt']; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 style="color:#28a745"><?php echo $confirmed['cnt']; ?></h3>
                <p>Confirmed</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 style="color:#6f42c1"><?php echo $completed['cnt']; ?></h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <h5><i class="fas fa-calendar-alt text-primary"></i>
            All Appointments
            <span style="font-size:14px;color:#888;font-weight:400">
                (<?php echo mysqli_num_rows($appointments); ?> total)
            </span>
        </h5>

        <?php if (mysqli_num_rows($appointments) == 0): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-4x mb-3" style="color:#ccc"></i>
                <p>No appointments yet!</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['patient_name']; ?></strong></td>
                        <td>Dr. <?php echo $row['doctor_name']; ?></td>
                        <td><?php echo $row['specialization']; ?></td>
                        <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td>
                            <?php if($row['type'] == 'video-call'): ?>
                                <span class="badge-video">🎥 Video</span>
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
                            <?php if($row['status'] == 'confirmed'): ?>
                                <a href="appointment_action.php?id=<?php echo $row['id']; ?>&action=complete"
                                   class="btn-action btn-complete">🏁 Complete</a>
                            <?php endif; ?>
                            <?php if($row['status'] == 'pending' || $row['status'] == 'confirmed'): ?>
                                <a href="appointment_action.php?id=<?php echo $row['id']; ?>&action=cancel"
                                   onclick="return confirm('Cancel this appointment?')"
                                   class="btn-action btn-cancel">❌ Cancel</a>
                            <?php endif; ?>
                            <a href="appointment_action.php?id=<?php echo $row['id']; ?>&action=delete"
                               onclick="return confirm('Delete this appointment?')"
                               class="btn-action btn-delete">🗑️ Delete</a>
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