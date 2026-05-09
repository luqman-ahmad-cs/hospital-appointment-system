<?php
session_start();

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — MediCare</title>

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
            background: linear-gradient(180deg, #1a1a2e, #16213e);
            position: fixed;
            left: 0; top: 0;
            padding: 30px 0;
            z-index: 100;
        }

        .sidebar-logo {
            text-align: center;
            color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo i { font-size: 40px; color: #0d6efd; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }
        .sidebar-logo small { opacity: 0.5; }

        .sidebar-menu { padding: 20px 0; }

        .sidebar-menu a {
            display: block;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 13px 25px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(13,110,253,0.2);
            color: white;
            border-left: 4px solid #0d6efd;
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

        .btn-logout:hover { background: #dc3545; color: white; border-color: #dc3545; }

        /* Main */
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

        .admin-badge {
            background: #1a1a2e;
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Stat Cards */
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

        /* Table Card */
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
        .badge-active { background: #d1e7dd; color: #0f5132; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }

        .btn-approve {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-approve:hover { background: #1e7e34; }

        .btn-block {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-block:hover { background: #b02a37; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-hospital-alt"></i>
        <h4 style="color:white">MediCare</h4>
        <small>Admin Panel</small>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="active">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="manage_doctors.php">
            <i class="fas fa-user-md"></i> Manage Doctors
        </a>
        <a href="manage_patients.php">
            <i class="fas fa-users"></i> Manage Patients
        </a>
        <a href="manage_appointments.php">
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

    <!-- Topbar -->
    <div class="topbar">
        <h5><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h5>
        <div class="admin-badge">
            <i class="fas fa-user-shield"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <?php
    include '../db/connection.php';

    // Stats
    $total_patients = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM users WHERE role='patient'"));

    $total_doctors = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM users WHERE role='doctor' AND status='active'"));

    $pending_doctors = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM users WHERE role='doctor' AND status='pending'"));

    $total_appointments = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as cnt FROM appointments"));
    ?>

    <!-- Stats -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#0d6efd,#0a58ca)">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_patients['cnt']; ?></h3>
                    <p>Total Patients</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#28a745,#1e7e34)">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_doctors['cnt']; ?></h3>
                    <p>Active Doctors</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#ffc107,#e0a800)">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_doctors['cnt']; ?></h3>
                    <p>Pending Doctors</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#6f42c1,#59359a)">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_appointments['cnt']; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Pending Doctors Table -->
    <div class="table-card">
        <h5><i class="fas fa-clock text-warning"></i> Pending Doctor Approvals</h5>

        <?php
        $pending = mysqli_query($conn,
            "SELECT u.id, u.fullname, u.email, u.phone, u.created_at,
                    d.specialization, d.qualification, d.experience
             FROM users u
             JOIN doctors d ON u.id = d.user_id
             WHERE u.role='doctor' AND u.status='pending'
             ORDER BY u.created_at DESC");
        ?>

        <?php if (mysqli_num_rows($pending) == 0): ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-check-circle fa-3x mb-3" style="color:#28a745"></i>
                <p>No pending approvals!</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Qualification</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($pending)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['fullname']; ?></strong></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['specialization']; ?></td>
                        <td><?php echo $row['qualification']; ?></td>
                        <td><?php echo $row['experience']; ?> yrs</td>
                        <td><span class="badge-pending">⏳ Pending</span></td>
                        <td>
                            <a href="approve_doctor.php?id=<?php echo $row['id']; ?>&action=approve">
                                <button class="btn-approve">✅ Approve</button>
                            </a>
                            &nbsp;
                            <a href="approve_doctor.php?id=<?php echo $row['id']; ?>&action=reject">
                                <button class="btn-block">❌ Reject</button>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- All Patients Table -->
    <div class="table-card">
        <h5><i class="fas fa-users text-primary"></i> Registered Patients</h5>

        <?php
        $patients = mysqli_query($conn,
            "SELECT * FROM users WHERE role='patient' ORDER BY created_at DESC");
        ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($patients)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['fullname']; ?></strong></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td><span class="badge-active">✅ Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>