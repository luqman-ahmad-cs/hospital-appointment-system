<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

include '../db/connection.php';

$doctor_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname       = trim($_POST['fullname']);
    $phone          = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $qualification  = trim($_POST['qualification']);
    $experience     = trim($_POST['experience']);

    mysqli_query($conn,
        "UPDATE users SET fullname='$fullname', phone='$phone'
         WHERE id='$doctor_id'");

    mysqli_query($conn,
        "UPDATE doctors SET
         specialization='$specialization',
         qualification='$qualification',
         experience='$experience'
         WHERE user_id='$doctor_id'");

    if (!empty($_POST['new_password'])) {
        $new_pass = md5($_POST['new_password']);
        mysqli_query($conn,
            "UPDATE users SET password='$new_pass'
             WHERE id='$doctor_id'");
    }

    $_SESSION['user_name'] = $fullname;

    echo "<script>alert('Profile Updated Successfully!');
          window.location='profile.php';</script>";
    exit();
}

// Doctor info fetch
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT u.*, d.specialization, d.qualification,
            d.experience
     FROM users u
     JOIN doctors d ON u.id = d.user_id
     WHERE u.id = '$doctor_id'"));

// Ratings fetch
$ratings = mysqli_query($conn,
    "SELECT r.*, u.fullname as patient_name,
            date_format(r.created_at, '%d %M %Y') as date
     FROM ratings r
     JOIN users u ON r.patient_id = u.id
     WHERE r.doctor_id = '$doctor_id'
     ORDER BY r.created_at DESC");

$avg = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT ROUND(AVG(rating),1) as avg,
            COUNT(*) as total
     FROM ratings WHERE doctor_id='$doctor_id'"));
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

        /* Sidebar */
        .sidebar {
            width: 260px; height: 100vh;
            background: linear-gradient(180deg, #0f5132, #1a7a4a);
            position: fixed; left: 0; top: 0;
            padding: 30px 0; z-index: 100;
        }
        .sidebar-logo {
            text-align: center; color: white;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .sidebar-logo i { font-size: 40px; color: #90ee90; }
        .sidebar-logo h4 { font-weight: 700; margin-top: 10px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; color: rgba(255,255,255,0.8);
            text-decoration: none; padding: 13px 25px;
            font-size: 15px; transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white; border-left: 4px solid #90ee90;
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

        /* Main */
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
        .doctor-badge {
            background: #0f5132; color: white;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 600; font-size: 14px;
        }

        /* Profile Card */
        .profile-card {
            background: white; border-radius: 15px;
            padding: 35px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 100px; height: 100px; border-radius: 50%;
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            display: flex; align-items: center;
            justify-content: center;
            font-size: 45px; color: white;
            margin: 0 auto 20px;
        }
        .profile-name {
            text-align: center; font-size: 22px;
            font-weight: 700; color: #333; margin-bottom: 5px;
        }
        .profile-spec {
            text-align: center; color: #0f5132;
            font-weight: 600; margin-bottom: 25px;
        }
        .form-label { font-weight: 600; color: #333; }
        .form-control, .form-select {
            border-radius: 10px; padding: 12px 15px;
            border: 2px solid #e0e0e0; font-size: 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0f5132;
            box-shadow: 0 0 0 3px rgba(15,81,50,0.15);
        }
        .btn-update {
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            color: white; border: none; border-radius: 10px;
            padding: 13px 40px; font-size: 16px;
            font-weight: 600; transition: opacity 0.3s;
        }
        .btn-update:hover { opacity: 0.9; color: white; }
        .section-title {
            font-weight: 700; color: #0f5132;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px; margin-bottom: 20px;
            margin-top: 30px;
        }
        .info-badge {
            background: #f0fff4;
            border: 1px solid #90ee90;
            border-radius: 10px; padding: 15px 20px;
            margin-bottom: 20px;
        }
        .info-badge p { margin: 0; color: #555; font-size: 14px; }
        .info-badge strong { color: #0f5132; }

        /* Review Cards */
        .review-card {
            background: #f8f9fa; border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #ffc107;
            height: 100%;
        }

        .patient-initial {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            display: flex; align-items: center;
            justify-content: center; color: white;
            font-weight: 700; font-size: 16px;
            flex-shrink: 0;
        }

        .avg-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 15px;
            padding: 15px 25px;
            text-align: center;
        }
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
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="my_appointments.php">
            <i class="fas fa-calendar-check"></i> My Appointments
        </a>
        <a href="video_call.php">
            <i class="fas fa-video"></i> Video Call
        </a>
        <a href="profile.php" class="active">
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
        <h5><i class="fas fa-user"></i> My Profile</h5>
        <div class="doctor-badge">
            <i class="fas fa-user-md"></i>
            Dr. <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <!-- Profile Form Card -->
    <div class="profile-card">

        <div class="profile-avatar">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="profile-name">
            Dr. <?php echo $user['fullname']; ?>
        </div>
        <div class="profile-spec">
            <?php echo $user['specialization']; ?>
        </div>

        <!-- Info Badge -->
        <div class="info-badge">
            <p>
                <strong>Email:</strong>
                <?php echo $user['email']; ?>
                &nbsp;|&nbsp;
                <strong>Joined:</strong>
                <?php echo date('d M Y',
                    strtotime($user['created_at'])); ?>
                &nbsp;|&nbsp;
                <strong>Status:</strong> Active
            </p>
        </div>

        <!-- Update Form -->
        <form method="POST">

            <div class="section-title">
                <i class="fas fa-user"></i>
                Personal Information
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
                <i class="fas fa-stethoscope"></i>
                Professional Information
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Specialization</label>
                    <select class="form-select" name="specialization">
                        <?php
                        $specs = ['Cardiologist','Dermatologist',
                                  'General Physician','Neurologist',
                                  'Orthopedic','Pediatrician',
                                  'Psychiatrist','Surgeon'];
                        foreach ($specs as $spec):
                        ?>
                        <option value="<?php echo $spec; ?>"
                            <?php echo ($user['specialization']==$spec)
                                ?'selected':''; ?>>
                            <?php echo $spec; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Qualification</label>
                    <input type="text" class="form-control"
                           name="qualification"
                           value="<?php echo $user['qualification']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Experience (Years)</label>
                    <input type="number" class="form-control"
                           name="experience"
                           value="<?php echo $user['experience']; ?>">
                </div>
            </div>

            <div class="section-title mt-4">
                <i class="fas fa-lock"></i>
                Change Password
                <small style="font-size:13px;color:#888;
                              font-weight:400;">
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

    <!-- ═══ RATINGS SECTION ═══ -->
    <div class="profile-card">

        <!-- Header -->
        <div class="d-flex justify-content-between
                    align-items-center mb-4
                    flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="fas fa-star text-warning"></i>
                    Patient Reviews & Ratings
                </h5>
                <p class="text-muted mb-0"
                   style="font-size:13px;">
                    What patients say about
                    Dr. <?php echo $user['fullname']; ?>
                </p>
            </div>

            <?php if($avg['total'] > 0): ?>
            <div class="avg-box">
                <div style="font-size:36px;font-weight:700;
                            color:#856404;line-height:1;">
                    <?php echo $avg['avg']; ?>
                </div>
                <div style="margin:5px 0;">
                    <?php for($s=1;$s<=5;$s++): ?>
                        <span style="color:<?php echo
                            $s<=$avg['avg']?'#ffc107':'#ddd'; ?>;
                            font-size:20px;">★</span>
                    <?php endfor; ?>
                </div>
                <div style="font-size:12px;color:#888;">
                    <?php echo $avg['total']; ?> total reviews
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Grid -->
        <?php if(mysqli_num_rows($ratings) == 0): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-star fa-4x mb-3"
                   style="color:#ddd"></i>
                <h5>No reviews yet!</h5>
                <p>Patients will rate you after
                   completed appointments.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
            <?php while($r = mysqli_fetch_assoc($ratings)): ?>
                <div class="col-md-6">
                    <div class="review-card">

                        <!-- Patient + Stars Row -->
                        <div class="d-flex justify-content-between
                                    align-items-start mb-3">

                            <!-- Patient Info -->
                            <div class="d-flex
                                        align-items-center gap-2">
                                <div class="patient-initial">
                                    <?php echo strtoupper(
                                        substr($r['patient_name'],
                                        0,1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight:700;
                                                color:#333;
                                                font-size:14px;">
                                        <?php echo $r['patient_name']; ?>
                                    </div>
                                    <div style="font-size:11px;
                                                color:#aaa;">
                                        <?php echo $r['date']; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Stars -->
                            <div>
                                <?php for($s=1;$s<=5;$s++): ?>
                                    <span style="color:<?php echo
                                        $s<=$r['rating']
                                        ?'#ffc107':'#ddd'; ?>;
                                        font-size:16px;">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Rating Label Badge -->
                        <?php
                        $labels = [
                            1=>['Poor','#dc3545'],
                            2=>['Fair','#fd7e14'],
                            3=>['Good','#ffc107'],
                            4=>['Very Good','#20c997'],
                            5=>['Excellent','#28a745']
                        ];
                        $lbl = $labels[$r['rating']];
                        ?>
                        <span style="background:<?php echo
                                     $lbl[1]; ?>20;
                                     color:<?php echo $lbl[1]; ?>;
                                     padding:3px 12px;
                                     border-radius:50px;
                                     font-size:11px;
                                     font-weight:700;
                                     display:inline-block;
                                     margin-bottom:10px;">
                            <?php echo $lbl[0]; ?>
                        </span>

                        <!-- Review Text -->
                        <?php if($r['review']): ?>
                        <p style="margin:0;color:#555;
                                  font-size:13px;
                                  line-height:1.6;
                                  font-style:italic;">
                            "<?php echo $r['review']; ?>"
                        </p>
                        <?php else: ?>
                        <p style="margin:0;color:#aaa;
                                  font-size:13px;
                                  font-style:italic;">
                            No written review provided.
                        </p>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
    <!-- ═══ END RATINGS ═══ -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>