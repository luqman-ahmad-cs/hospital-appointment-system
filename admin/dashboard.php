<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db/connection.php';

// Stats
$total_patients = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM users WHERE role='patient'"));
$total_doctors = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM users
     WHERE role='doctor' AND status='active'"));
$pending_doctors = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM users
     WHERE role='doctor' AND status='pending'"));
$total_appointments = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM appointments"));
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(amount) as total FROM payments
     WHERE status='completed'"));

// Monthly Appointments (Last 6 months)
$monthly_apts = mysqli_query($conn,
    "SELECT DATE_FORMAT(appointment_date, '%b %Y') as month,
            COUNT(*) as total
     FROM appointments
     WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
     ORDER BY appointment_date ASC");

$apt_labels = [];
$apt_data   = [];
while ($row = mysqli_fetch_assoc($monthly_apts)) {
    $apt_labels[] = $row['month'];
    $apt_data[]   = $row['total'];
}

// Monthly Revenue (Last 6 months)
$monthly_rev = mysqli_query($conn,
    "SELECT DATE_FORMAT(created_at, '%b %Y') as month,
            SUM(amount) as total
     FROM payments
     WHERE status='completed'
     AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
     ORDER BY created_at ASC");

$rev_labels = [];
$rev_data   = [];
while ($row = mysqli_fetch_assoc($monthly_rev)) {
    $rev_labels[] = $row['month'];
    $rev_data[]   = $row['total'];
}

// Appointment Status Distribution
$status_data = mysqli_query($conn,
    "SELECT status, COUNT(*) as cnt
     FROM appointments GROUP BY status");
$status_labels = [];
$status_counts = [];
while ($row = mysqli_fetch_assoc($status_data)) {
    $status_labels[] = ucfirst($row['status']);
    $status_counts[] = $row['cnt'];
}

// Top Rated Doctors
$top_doctors = mysqli_query($conn,
    "SELECT u.fullname, d.specialization,
            ROUND(AVG(r.rating),1) as avg_rating,
            COUNT(r.id) as total_reviews
     FROM ratings r
     JOIN users u ON r.doctor_id = u.id
     JOIN doctors d ON r.doctor_id = d.user_id
     GROUP BY r.doctor_id
     ORDER BY avg_rating DESC
     LIMIT 5");

// Pending Doctors
$pending = mysqli_query($conn,
    "SELECT u.id, u.fullname, u.email, u.created_at,
            d.specialization, d.qualification, d.experience
     FROM users u
     JOIN doctors d ON u.id = d.user_id
     WHERE u.role='doctor' AND u.status='pending'
     ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Stat Cards */
        .stat-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon {
            width: 65px; height: 65px; border-radius: 15px;
            display: flex; align-items: center;
            justify-content: center;
            font-size: 28px; color: white; flex-shrink: 0;
        }
        .stat-info h3 {
            font-weight: 700; margin: 0; font-size: 30px;
        }
        .stat-info p { margin: 0; color: #888; font-size: 14px; }

        /* Chart Cards */
        .chart-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }
        .chart-card h5 {
            font-weight: 700; margin-bottom: 20px;
            color: #333;
        }

        /* Table Card */
        .table-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }
        .table-card h5 { font-weight: 700; margin-bottom: 20px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table td, .table th { vertical-align: middle; }

        .badge-pending {
            background: #fff3cd; color: #856404;
            padding: 6px 12px; border-radius: 50px;
            font-size: 12px; font-weight: 600;
        }
        .badge-active {
            background: #d1e7dd; color: #0f5132;
            padding: 6px 12px; border-radius: 50px;
            font-size: 12px; font-weight: 600;
        }
        .btn-action {
            border: none; border-radius: 8px;
            padding: 6px 12px; font-size: 12px;
            font-weight: 600; cursor: pointer;
            text-decoration: none; margin: 2px;
            display: inline-block;
        }

        /* Top Doctor Cards */
        .doctor-rank-card {
            background: #f8f9fa; border-radius: 12px;
            padding: 15px 20px; margin-bottom: 10px;
            display: flex; align-items: center;
            justify-content: space-between;
            border-left: 4px solid #ffc107;
        }
        .rank-num {
            width: 35px; height: 35px; border-radius: 50%;
            background: linear-gradient(135deg,#ffc107,#e0a800);
            display: flex; align-items: center;
            justify-content: center;
            font-weight: 700; color: white; flex-shrink: 0;
        }
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
        <h5>
            <i class="fas fa-tachometer-alt"></i>
            Admin Dashboard
        </h5>
        <div class="admin-badge">
            <i class="fas fa-user-shield"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"
                     style="background:linear-gradient(135deg,#0d6efd,#0a58ca)">
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
                <div class="stat-icon"
                     style="background:linear-gradient(135deg,#28a745,#1e7e34)">
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
                <div class="stat-icon"
                     style="background:linear-gradient(135deg,#6f42c1,#59359a)">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_appointments['cnt']; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"
                     style="background:linear-gradient(135deg,#28a745,#1e7e34)">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>Rs. <?php echo number_format(
                        $total_revenue['total'] ?? 0); ?>
                    </h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">

        <!-- Monthly Appointments Chart -->
        <div class="col-md-8">
            <div class="chart-card">
                <h5>
                    <i class="fas fa-chart-bar text-primary"></i>
                    Monthly Appointments
                </h5>
                <canvas id="appointmentsChart"
                        height="120"></canvas>
            </div>
        </div>

        <!-- Appointment Status Pie -->
        <div class="col-md-4">
            <div class="chart-card">
                <h5>
                    <i class="fas fa-chart-pie text-success"></i>
                    Appointment Status
                </h5>
                <canvas id="statusChart"
                        height="220"></canvas>
            </div>
        </div>

    </div>

    <!-- Revenue Chart -->
    <div class="chart-card">
        <h5>
            <i class="fas fa-chart-line text-success"></i>
            Monthly Revenue (Rs.)
        </h5>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <!-- Top Doctors + Pending Row -->
    <div class="row g-4 mb-4">

        <!-- Top Rated Doctors -->
        <div class="col-md-5">
            <div class="table-card">
                <h5>
                    <i class="fas fa-star text-warning"></i>
                    Top Rated Doctors
                </h5>
                <?php
                $rank = 1;
                $top_doctors_arr = [];
                while($doc = mysqli_fetch_assoc($top_doctors)) {
                    $top_doctors_arr[] = $doc;
                }
                ?>
                <?php if(empty($top_doctors_arr)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-star fa-3x mb-3"
                           style="color:#ddd"></i>
                        <p>No ratings yet!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($top_doctors_arr as $doc): ?>
                    <div class="doctor-rank-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rank-num">
                                <?php echo $rank++; ?>
                            </div>
                            <div>
                                <div style="font-weight:700;
                                            color:#333;font-size:14px;">
                                    Dr. <?php echo $doc['fullname']; ?>
                                </div>
                                <div style="font-size:12px;color:#888;">
                                    <?php echo $doc['specialization']; ?>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700;
                                        color:#ffc107;font-size:18px;">
                                <?php echo $doc['avg_rating']; ?>
                                <span style="font-size:14px;">★</span>
                            </div>
                            <div style="font-size:11px;color:#888;">
                                <?php echo $doc['total_reviews']; ?>
                                reviews
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Doctors -->
        <div class="col-md-7">
            <div class="table-card">
                <h5>
                    <i class="fas fa-clock text-warning"></i>
                    Pending Doctor Approvals
                    <?php if(mysqli_num_rows($pending) > 0): ?>
                    <span style="background:#dc3545;color:white;
                                 border-radius:50px;padding:2px 10px;
                                 font-size:13px;margin-left:5px;">
                        <?php echo mysqli_num_rows($pending); ?>
                    </span>
                    <?php endif; ?>
                </h5>

                <?php if(mysqli_num_rows($pending) == 0): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"
                           style="color:#28a745"></i>
                        <p>No pending approvals!</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Specialization</th>
                                <th>Experience</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = mysqli_fetch_assoc($pending)): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo $row['fullname']; ?>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo $row['email']; ?>
                                    </small>
                                </td>
                                <td><?php echo $row['specialization']; ?></td>
                                <td><?php echo $row['experience']; ?> yrs</td>
                                <td>
                                    <a href="approve_doctor.php?id=<?php echo $row['id']; ?>&action=approve"
                                       class="btn-action"
                                       style="background:#28a745;color:white;">
                                        ✅ Approve
                                    </a>
                                    <a href="approve_doctor.php?id=<?php echo $row['id']; ?>&action=reject"
                                       onclick="return confirm('Reject this doctor?')"
                                       class="btn-action"
                                       style="background:#dc3545;color:white;">
                                        ❌ Reject
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js Scripts -->
<script>
// 1. Monthly Appointments Bar Chart
const aptCtx = document.getElementById(
    'appointmentsChart').getContext('2d');
new Chart(aptCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(
            empty($apt_labels)
            ? ['No Data']
            : $apt_labels); ?>,
        datasets: [{
            label: 'Appointments',
            data: <?php echo json_encode(
                empty($apt_data) ? [0] : $apt_data); ?>,
            backgroundColor: 'rgba(13,110,253,0.8)',
            borderColor: '#0d6efd',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});

// 2. Appointment Status Pie Chart
const statusCtx = document.getElementById(
    'statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(
            empty($status_labels)
            ? ['No Data']
            : $status_labels); ?>,
        datasets: [{
            data: <?php echo json_encode(
                empty($status_counts) ? [1] : $status_counts); ?>,
            backgroundColor: [
                '#ffc107','#28a745',
                '#0d6efd','#dc3545'
            ],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 12 } }
            }
        }
    }
});

// 3. Monthly Revenue Line Chart
const revCtx = document.getElementById(
    'revenueChart').getContext('2d');
new Chart(revCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(
            empty($rev_labels)
            ? ['No Data']
            : $rev_labels); ?>,
        datasets: [{
            label: 'Revenue (Rs.)',
            data: <?php echo json_encode(
                empty($rev_data) ? [0] : $rev_data); ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40,167,69,0.1)',
            borderWidth: 3,
            pointBackgroundColor: '#28a745',
            pointRadius: 6,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value;
                    }
                }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>