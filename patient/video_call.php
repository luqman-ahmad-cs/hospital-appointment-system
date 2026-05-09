<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Confirmed video call appointments fetch karo
$appointments = mysqli_query($conn,
    "SELECT a.*, u.fullname as doctor_name, d.specialization
     FROM appointments a
     JOIN users u ON a.doctor_id = u.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE a.patient_id = '$patient_id'
     AND a.type = 'video-call'
     AND a.status = 'confirmed'
     ORDER BY a.appointment_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call — MediCare</title>
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
        .appointment-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 5px solid #0d6efd;
        }
        .doctor-avatar {
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            display: flex; align-items: center;
            justify-content: center;
            font-size: 25px; color: white;
        }
        .btn-join {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white; border: none; border-radius: 10px;
            padding: 12px 30px; font-size: 15px;
            font-weight: 600; cursor: pointer;
            transition: opacity 0.3s;
        }
        .btn-join:hover { opacity: 0.9; color: white; }

        /* Video Call Room */
        #videoCallRoom {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: #1a1a2e;
            z-index: 9999;
        }

        #videoCallRoom .call-header {
            background: rgba(0,0,0,0.5);
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        #videoCallRoom .call-header h5 {
            margin: 0; font-weight: 700;
        }

        #myCallContainer {
            width: 100%;
            height: calc(100vh - 70px);
        }

        .btn-end-call {
            background: #dc3545; color: white;
            border: none; border-radius: 10px;
            padding: 10px 25px; font-weight: 600;
            font-size: 15px; cursor: pointer;
        }

        .btn-end-call:hover { background: #b02a37; }

        .empty-state {
            background: white; border-radius: 15px;
            padding: 60px; text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
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
        <a href="video_call.php" class="active"><i class="fas fa-video"></i> Video Call</a>
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

<!-- VIDEO CALL ROOM (Full Screen) -->
<div id="videoCallRoom">
    <div class="call-header">
        <h5><i class="fas fa-video"></i> 
            MediCare Video Consultation
        </h5>
        <button class="btn-end-call" onclick="endCall()">
            <i class="fas fa-phone-slash"></i> End Call
        </button>
    </div>
    <div id="myCallContainer"></div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="topbar">
        <h5><i class="fas fa-video"></i> Video Consultations</h5>
        <div class="user-badge">
            <i class="fas fa-user"></i>
            <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <?php if (mysqli_num_rows($appointments) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-video fa-4x mb-3" style="color:#ccc"></i>
            <h5>No Video Call Appointments!</h5>
            <p class="text-muted">
                Book a video call appointment first — 
                doctor confirm kare phir yahan show hoga.
            </p>
            <a href="book_appointment.php"
               style="background:linear-gradient(135deg,#0d6efd,#0a58ca);
                      color:white;border:none;border-radius:10px;
                      padding:12px 30px;font-weight:600;
                      text-decoration:none;display:inline-block;
                      margin-top:15px;">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
        </div>
    <?php else: ?>

        <h5 class="fw-bold mb-4">
            <i class="fas fa-calendar-check text-success"></i>
            Confirmed Video Call Appointments
        </h5>

        <?php while ($apt = mysqli_fetch_assoc($appointments)): ?>
        <div class="appointment-card">
            <div class="d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center gap-3">
                    <div class="doctor-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">
                            Dr. <?php echo $apt['doctor_name']; ?>
                        </h5>
                        <p class="mb-1 text-muted" style="font-size:14px">
                            <?php echo $apt['specialization']; ?>
                        </p>
                        <p class="mb-0" style="font-size:14px">
                            <i class="fas fa-calendar text-primary"></i>
                            <?php echo date('d M Y', strtotime($apt['appointment_date'])); ?>
                            &nbsp;
                            <i class="fas fa-clock text-primary"></i>
                            <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                        </p>
                    </div>
                </div>

                <button class="btn-join"
                    onclick="startCall(
                        '<?php echo $apt['id']; ?>',
                        '<?php echo addslashes($apt['doctor_name']); ?>'
                    )">
                    <i class="fas fa-video"></i> Join Call
                </button>

            </div>
        </div>
        <?php endwhile; ?>

    <?php endif; ?>
</div>

<!-- ZegoCloud SDK -->
<script src="https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>

<script>
    const APP_ID   = 2101106645;
    const APP_SIGN = "94910155912595ae481875c2e881b62d9ecd6ce846adefcc7d708ce74b9f6302";

    const USER_ID   = "patient_<?php echo $_SESSION['user_id']; ?>";
    const USER_NAME = "<?php echo addslashes($_SESSION['user_name']); ?>";

    let zpInstance = null;

    function startCall(appointmentId, doctorName) {
        // Room ID = appointment ID se banao
        const ROOM_ID = "medicare_room_" + appointmentId;

        document.getElementById('videoCallRoom').style.display = 'block';

        const kitToken = ZegoUIKitPrebuilt.generateKitTokenForTest(
            APP_ID,
            APP_SIGN,
            ROOM_ID,
            USER_ID,
            USER_NAME
        );

        zpInstance = ZegoUIKitPrebuilt.create(kitToken);

        zpInstance.joinRoom({
            container: document.getElementById('myCallContainer'),
            scenario: {
                mode: ZegoUIKitPrebuilt.OneONoneCall,
            },
            showPreJoinView: true,
            turnOnCameraWhenJoining: true,
            turnOnMicrophoneWhenJoining: true,
            showLeavingView: false,
            onLeaveRoom: () => {
                endCall();
            }
        });
    }

    function endCall() {
        if (zpInstance) {
            zpInstance.destroy();
            zpInstance = null;
        }
        document.getElementById('videoCallRoom').style.display = 'none';
    }
</script>

</body>
</html>