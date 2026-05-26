<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id      = $_SESSION['user_id'];
$appointment_id = $_GET['id'];

// Appointment details fetch
$apt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, u.fullname as patient_name,
            u.email as patient_email,
            u.phone as patient_phone,
            d.specialization
     FROM appointments a
     JOIN users u ON a.patient_id = u.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE a.id = '$appointment_id'
     AND a.doctor_id = '$doctor_id'"));

if (!$apt) {
    echo "<script>alert('Invalid appointment!');
          window.location='my_appointments.php';</script>";
    exit();
}

// Already prescribed check
$already = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id FROM prescriptions
     WHERE appointment_id = '$appointment_id'"));

if ($already) {
    header("Location: view_prescription.php?id=" .
           $already['id']);
    exit();
}

// Form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diagnosis    = trim($_POST['diagnosis']);
    $medicines    = trim($_POST['medicines']);
    $instructions = trim($_POST['instructions']);
    $follow_up    = $_POST['follow_up_date'];
    $patient_id   = $apt['patient_id'];

    mysqli_query($conn,
        "INSERT INTO prescriptions
         (appointment_id, doctor_id, patient_id,
          diagnosis, medicines, instructions, follow_up_date)
         VALUES
         ('$appointment_id','$doctor_id','$patient_id',
          '$diagnosis','$medicines','$instructions',
          " . ($follow_up ? "'$follow_up'" : "NULL") . ")");

    $pres_id = mysqli_insert_id($conn);

    echo "<script>
            alert('Prescription written successfully!');
            window.location='view_prescription.php?id=$pres_id';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Prescription — MediCare</title>
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
        .pres-card {
            background: white; border-radius: 15px;
            padding: 35px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .patient-info {
            background: #f0f4ff;
            border-radius: 12px; padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #0d6efd;
        }
        .form-label { font-weight: 600; color: #333; }
        .form-control {
            border-radius: 10px; padding: 12px 15px;
            border: 2px solid #e0e0e0; font-size: 15px;
        }
        .form-control:focus {
            border-color: #0f5132;
            box-shadow: 0 0 0 3px rgba(15,81,50,0.15);
        }
        .section-title {
            font-weight: 700; color: #0f5132;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px; margin-bottom: 20px;
            margin-top: 25px; font-size: 16px;
        }
        .btn-write {
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            color: white; border: none; border-radius: 10px;
            padding: 13px 40px; font-size: 16px;
            font-weight: 600; transition: opacity 0.3s;
        }
        .btn-write:hover { opacity: 0.9; color: white; }

        /* Medicine rows */
        .medicine-row {
            background: #f8f9fa; border-radius: 10px;
            padding: 15px; margin-bottom: 10px;
            border: 1px solid #e0e0e0;
        }
        .btn-add-medicine {
            background: #0d6efd; color: white;
            border: none; border-radius: 8px;
            padding: 8px 20px; font-size: 14px;
            font-weight: 600; cursor: pointer;
        }
        .btn-remove {
            background: #dc3545; color: white;
            border: none; border-radius: 8px;
            padding: 5px 12px; font-size: 12px;
            cursor: pointer;
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
        <a href="my_appointments.php" class="active">
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

    <div class="topbar">
        <h5>
            <i class="fas fa-prescription"></i>
            Write Prescription
        </h5>
        <div class="doctor-badge">
            <i class="fas fa-user-md"></i>
            Dr. <?php echo $_SESSION['user_name']; ?>
        </div>
    </div>

    <div class="pres-card">

        <!-- Patient Info -->
        <div class="patient-info">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-1 text-muted"
                       style="font-size:12px;">Patient Name</p>
                    <p class="fw-bold mb-0">
                        <?php echo $apt['patient_name']; ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted"
                       style="font-size:12px;">Phone</p>
                    <p class="fw-bold mb-0">
                        <?php echo $apt['patient_phone']; ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted"
                       style="font-size:12px;">Date</p>
                    <p class="fw-bold mb-0">
                        <?php echo date('d M Y',
                            strtotime($apt['appointment_date'])); ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted"
                       style="font-size:12px;">Doctor</p>
                    <p class="fw-bold mb-0">
                        Dr. <?php echo $_SESSION['user_name']; ?>
                        <br>
                        <small class="text-muted">
                            <?php echo $apt['specialization']; ?>
                        </small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Prescription Form -->
        <form method="POST" id="presForm">

            <!-- Diagnosis -->
            <div class="section-title">
                <i class="fas fa-stethoscope"></i>
                Diagnosis
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Patient Diagnosis / Condition
                </label>
                <textarea class="form-control" name="diagnosis"
                          rows="3" required
                          placeholder="e.g. Acute pharyngitis, Upper respiratory tract infection, Fever with cough...">
                </textarea>
            </div>

            <!-- Medicines -->
            <div class="section-title">
                <i class="fas fa-pills"></i>
                Medicines
            </div>

            <div id="medicinesContainer">
                <div class="medicine-row" id="med_1">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text"
                                   class="form-control"
                                   name="med_name[]"
                                   placeholder="Medicine name (e.g. Paracetamol 500mg)"
                                   required>
                        </div>
                        <div class="col-md-3">
                            <input type="text"
                                   class="form-control"
                                   name="med_dose[]"
                                   placeholder="Dosage (e.g. 1 tablet)"
                                   required>
                        </div>
                        <div class="col-md-3">
                            <input type="text"
                                   class="form-control"
                                   name="med_timing[]"
                                   placeholder="Timing (e.g. 3x daily)"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input type="text"
                                   class="form-control"
                                   name="med_days[]"
                                   placeholder="Days (e.g. 5)"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn-add-medicine mt-2"
                    onclick="addMedicine()">
                <i class="fas fa-plus"></i> Add Medicine
            </button>

            <!-- Instructions -->
            <div class="section-title">
                <i class="fas fa-notes-medical"></i>
                Instructions & Advice
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Special Instructions
                </label>
                <textarea class="form-control" name="instructions"
                          rows="3"
                          placeholder="e.g. Take medicines after meals. Drink plenty of water. Rest for 3 days. Avoid cold drinks...">
                </textarea>
            </div>

            <!-- Follow Up -->
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Follow Up
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">
                        Follow Up Date (Optional)
                    </label>
                    <input type="date" class="form-control"
                           name="follow_up_date"
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <button type="submit" class="btn-write">
                <i class="fas fa-file-medical"></i>
                Write Prescription
            </button>

        </form>
    </div>
</div>

<script>
let medCount = 1;

function addMedicine() {
    medCount++;
    const container = document.getElementById(
        'medicinesContainer');
    const div = document.createElement('div');
    div.className = 'medicine-row';
    div.id = 'med_' + medCount;
    div.innerHTML = `
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control"
                       name="med_name[]"
                       placeholder="Medicine name"
                       required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control"
                       name="med_dose[]"
                       placeholder="Dosage"
                       required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control"
                       name="med_timing[]"
                       placeholder="Timing"
                       required>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control"
                       name="med_days[]"
                       placeholder="Days"
                       required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn-remove"
                        onclick="removeMed('med_${medCount}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    container.appendChild(div);
}

function removeMed(id) {
    document.getElementById(id).remove();
}

// Form submit — medicines ko JSON mein convert karo
document.getElementById('presForm').addEventListener(
    'submit', function(e) {
        e.preventDefault();

        const names   = [...document.querySelectorAll(
            '[name="med_name[]"]')].map(i => i.value);
        const doses   = [...document.querySelectorAll(
            '[name="med_dose[]"]')].map(i => i.value);
        const timings = [...document.querySelectorAll(
            '[name="med_timing[]"]')].map(i => i.value);
        const days    = [...document.querySelectorAll(
            '[name="med_days[]"]')].map(i => i.value);

        const medicines = names.map((name, i) => ({
            name: name,
            dose: doses[i],
            timing: timings[i],
            days: days[i]
        }));

        // Hidden input banao JSON ke liye
        let hidden = document.getElementById('medicines_json');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = 'medicines_json';
            hidden.name = 'medicines';
            this.appendChild(hidden);
        }
        hidden.value = JSON.stringify(medicines);

        this.submit();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>