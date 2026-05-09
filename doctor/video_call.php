<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

$appointments = mysqli_query($conn,
    "SELECT a.*, u.fullname as patient_name, u.phone
     FROM appointments a
     JOIN users u ON a.patient_id = u.id
     WHERE a.doctor_id = '$doctor_id'
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
        .appointment-card {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 5px solid #0f5132;
        }
        .patient-avatar {
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            display: flex; align-items: center;
            justify-content: center;
            font-size: 25px; color: white;
        }
        .btn-join {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white; border: none; border-radius: 10px;
            padding: 12px 30px; font-size: 15px;
            font-weight: 600; cursor: pointer;
        }
        .btn-join:hover { opacity: 0.9; color: white; }
        #videoCallRoom {
            display: none; position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: #1a1a2e; z-index: 9999;
        }
        #videoCallRoom .call-header {
            background: rgba(0,0,0,0.5);
            padding: 15px 25px;
            display: flex; justify-content: space-between;
            align-items: center; color: white;
        }
        #videoCallRoom .call-header h5 { margin: 0; font-weight: 700; }
        #myCallContainer { width: 100%; height: calc(100vh - 70px); }
        .btn-end-call {
            background: #dc3545; color: white;
            border: none; border-radius: 10px;
            padding: 10px 25px; font-weight: 600;
            font-size: 15px; cursor: pointer;
        }
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
        <i class="fas fa-user-md"></i>
        <h4 style="color:white">MediCare</h4>
        <small style="opacity:0.6">Doctor Portal</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
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

<!-- VIDEO CALL ROOM -->
<div id="videoCallRoom">
    <div class="call-header">
        <h5><i class="fas fa-video"></i> MediCare Video Consultation</h5>
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
        <div class="doctor-badge">
            <i class="fas fa-user-md"></i>
            Dr. <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <?php if (mysqli_num_rows($appointments) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-video fa-4x mb-3" style="color:#ccc"></i>
            <h5>No Video Call Appointments!</h5>
            <p class="text-muted">
                Jab patient video call book kare aur 
                tum confirm karo — yahan show hoga.
            </p>
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
                    <div class="patient-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">
                            <?php echo $apt['patient_name']; ?>
                        </h5>
                        <p class="mb-1 text-muted" style="font-size:14px">
                            📞 <?php echo $apt['phone']; ?>
                        </p>
                        <p class="mb-0" style="font-size:14px">
                            <i class="fas fa-calendar text-success"></i>
                            <?php echo date('d M Y', strtotime($apt['appointment_date'])); ?>
                            &nbsp;
                            <i class="fas fa-clock text-success"></i>
                            <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                        </p>
                    </div>
                </div>
                <button class="btn-join"
                    onclick="startCall(
                        '<?php echo $apt['id']; ?>',
                        '<?php echo addslashes($apt['patient_name']); ?>'
                    )">
                    <i class="fas fa-video"></i> Join Call
                </button>
            </div>
        </div>
        <?php endwhile; ?>

    <?php endif; ?>
</div>

<script src="https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>

<script>
    const APP_ID   = 2101106645;
    const APP_SIGN = "94910155912595ae481875c2e881b62d9ecd6ce846adefcc7d708ce74b9f6302";

    const USER_ID   = "doctor_<?php echo $_SESSION['user_id']; ?>";
    const USER_NAME = "Dr. <?php echo addslashes($_SESSION['user_name']); ?>";

    let zpInstance = null;

    function startCall(appointmentId, patientName) {
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