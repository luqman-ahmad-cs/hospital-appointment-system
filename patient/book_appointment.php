<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

include '../db/connection.php';

// Doctors list
$doctors = mysqli_query($conn,
    "SELECT u.id, u.fullname, d.specialization, d.qualification, d.experience
     FROM users u
     JOIN doctors d ON u.id = d.user_id
     WHERE u.role='doctor' AND u.status='active'
     ORDER BY u.fullname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment — MediCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }

        body { background: #f0f4ff; }

        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #0d6efd, #0a58ca);
            position: fixed;
            left: 0; top: 0;
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
        }

        .btn-logout:hover { background: white; color: #0d6efd; }

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

        .user-badge {
            background: #f0f4ff;
            color: #0d6efd;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Doctor Cards */
        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 3px solid transparent;
            height: 100%;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(13,110,253,0.15);
        }

        .doctor-card.selected {
            border-color: #0d6efd;
            background: #f0f4ff;
        }

        .doctor-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            margin-bottom: 15px;
        }

        .doctor-card h5 { font-weight: 700; margin-bottom: 5px; }

        .spec-badge {
            background: #e8f0fe;
            color: #0d6efd;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .doctor-info { font-size: 13px; color: #666; }
        .doctor-info i { color: #0d6efd; margin-right: 5px; }

        /* Booking Form */
        .booking-form {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-top: 30px;
            display: none;
        }

        .booking-form h5 { font-weight: 700; margin-bottom: 25px; color: #0d6efd; }

        .form-label { font-weight: 600; color: #333; }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .btn-book {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px 40px;
            font-size: 16px;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .btn-book:hover { opacity: 0.9; color: white; }

        .selected-doctor-info {
            background: #f0f4ff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }

        .page-title {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }

        .page-title h4 { font-weight: 700; color: #0d6efd; margin: 0; }
        .page-title p { color: #888; margin: 5px 0 0; }
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
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="book_appointment.php" class="active">
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

    <!-- Topbar -->
    <div class="topbar">
        <h5><i class="fas fa-calendar-plus"></i> Book Appointment</h5>
        <div class="user-badge">
            <i class="fas fa-user"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <!-- Page Title -->
    <div class="page-title">
        <h4><i class="fas fa-user-md"></i> Select a Doctor</h4>
        <p>Choose your preferred doctor and book an appointment</p>
    </div>

    <!-- Doctors Grid -->
    <div class="row g-4" id="doctorsGrid">
        <?php if (mysqli_num_rows($doctors) == 0): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-user-md fa-4x mb-3" style="color:#ccc"></i>
                <h5>No doctors available right now!</h5>
                <p>Please check back later.</p>
            </div>
        <?php else: ?>
            <?php while ($doc = mysqli_fetch_assoc($doctors)): ?>
            <div class="col-md-4">
                <div class="doctor-card" 
                     onclick="selectDoctor(
                        <?php echo $doc['id']; ?>, 
                        '<?php echo $doc['fullname']; ?>', 
                        '<?php echo $doc['specialization']; ?>'
                     )"
                     id="card_<?php echo $doc['id']; ?>">

                    <div class="doctor-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>

                    <h5>Dr. <?php echo $doc['fullname']; ?></h5>
                    <span class="spec-badge"><?php echo $doc['specialization']; ?></span>

                    <div class="doctor-info">
                        <p><i class="fas fa-graduation-cap"></i><?php echo $doc['qualification']; ?></p>
                        <p><i class="fas fa-clock"></i><?php echo $doc['experience']; ?> Years Experience</p>
                        <?php
// Doctor ki average rating fetch karo
$avg = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT ROUND(AVG(rating), 1) as avg_rating,
            COUNT(*) as total
     FROM ratings
     WHERE doctor_id = '" . $doc['id'] . "'"));
?>
<p style="margin:5px 0 0;">
    <?php if($avg['total'] > 0): ?>
        <?php
        $stars = $avg['avg_rating'];
        for($s = 1; $s <= 5; $s++) {
            if($s <= $stars) {
                echo '<span style="color:#ffc107;">★</span>';
            } else {
                echo '<span style="color:#ddd;">★</span>';
            }
        }
        ?>
        <small style="color:#888;">
            (<?php echo $avg['avg_rating']; ?>/5
            — <?php echo $avg['total']; ?> reviews)
        </small>
    <?php else: ?>
        <span style="color:#ddd;">★★★★★</span>
        <small style="color:#888;">(No reviews yet)</small>
    <?php endif; ?>
</p>
                    </div>

                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- Booking Form (Doctor select hone ke baad dikhega) -->
    <div class="booking-form" id="bookingForm">

        <h5><i class="fas fa-calendar-plus"></i> Appointment Details</h5>

        <!-- Selected Doctor Info -->
        <div class="selected-doctor-info">
            <strong>Selected Doctor:</strong>
            <span id="selectedDoctorName"></span> —
            <span id="selectedDoctorSpec"></span>
        </div>

        <form action="book_process.php" method="POST">
            <input type="hidden" name="doctor_id" id="doctor_id_input">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-calendar"></i> Appointment Date
                    </label>
                    <input type="date" class="form-control" 
                           name="appointment_date"
                           min="<?php echo date('Y-m-d'); ?>"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-clock"></i> Preferred Time
                    </label>
                    <select class="form-select" name="appointment_time" required>
                        <option value="">-- Select Time --</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="09:30">9:30 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="10:30">10:30 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="11:30">11:30 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="14:30">2:30 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="15:30">3:30 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="16:30">4:30 PM</option>
                        <option value="17:00">5:00 PM</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-video"></i> Appointment Type
                    </label>
                    <select class="form-select" name="type" required>
                        <option value="in-person">🏥 In-Person Visit</option>
                        <option value="video-call">🎥 Video Call</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-notes-medical"></i> Notes (Optional)
                    </label>
                    <input type="text" class="form-control" 
                           name="notes"
                           placeholder="e.g. Fever since 2 days">
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn-book">
                    <i class="fas fa-calendar-check"></i> 
                    Confirm Appointment
                </button>
                &nbsp;&nbsp;
                <button type="button" 
                        onclick="cancelSelection()"
                        style="background:#6c757d;color:white;border:none;
                               border-radius:10px;padding:13px 30px;
                               font-size:16px;font-weight:600;">
                    Cancel
                </button>
            </div>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function selectDoctor(id, name, spec) {
        // Sab cards se selected class hatao
        document.querySelectorAll('.doctor-card').forEach(c => {
            c.classList.remove('selected');
        });

        // Selected card highlight karo
        document.getElementById('card_' + id).classList.add('selected');

        // Form mein doctor info set karo
        document.getElementById('doctor_id_input').value = id;
        document.getElementById('selectedDoctorName').innerText = 'Dr. ' + name;
        document.getElementById('selectedDoctorSpec').innerText = spec;

        // Form dikhao
        document.getElementById('bookingForm').style.display = 'block';

        // Form tak scroll karo
        document.getElementById('bookingForm').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function cancelSelection() {
        document.querySelectorAll('.doctor-card').forEach(c => {
            c.classList.remove('selected');
        });
        document.getElementById('bookingForm').style.display = 'none';
    }
</script>

</body>
</html>