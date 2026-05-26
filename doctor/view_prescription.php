<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pres_id = $_GET['id'];

$pres = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT p.*,
            doc.fullname as doctor_name,
            doc.email as doctor_email,
            pat.fullname as patient_name,
            pat.phone as patient_phone,
            d.specialization, d.qualification,
            a.appointment_date, a.appointment_time
     FROM prescriptions p
     JOIN users doc ON p.doctor_id = doc.id
     JOIN users pat ON p.patient_id = pat.id
     JOIN doctors d ON p.doctor_id = d.user_id
     JOIN appointments a ON p.appointment_id = a.id
     WHERE p.id = '$pres_id'"));

if (!$pres) {
    echo "<script>alert('Prescription not found!');
          window.history.back();</script>";
    exit();
}

$medicines = json_decode($pres['medicines'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4ff; padding: 30px; }

        .prescription {
            background: white;
            max-width: 800px; margin: 0 auto;
            border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        /* Header */
        .pres-header {
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            color: white; padding: 30px 40px;
        }

        .hospital-name {
            font-size: 26px; font-weight: 700;
        }

        .hospital-sub {
            opacity: 0.8; font-size: 13px;
        }

        .rx-symbol {
            font-size: 50px; font-weight: 700;
            color: rgba(255,255,255,0.3);
            font-style: italic;
        }

        /* Doctor Info */
        .doctor-info {
            background: #e8f5e9;
            padding: 15px 40px;
            border-bottom: 2px dashed #90ee90;
        }

        /* Body */
        .pres-body { padding: 30px 40px; }

        /* Patient box */
        .patient-box {
            background: #f0f4ff;
            border-radius: 10px; padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #c5d8ff;
        }

        .info-label {
            font-size: 11px; color: #888;
            text-transform: uppercase; margin-bottom: 3px;
        }

        .info-value {
            font-weight: 700; color: #333; font-size: 15px;
        }

        /* Diagnosis */
        .diagnosis-box {
            background: #fff3cd;
            border-radius: 10px; padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #ffc107;
        }

        .section-head {
            font-weight: 700; font-size: 15px;
            color: #333; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }

        /* Medicine Table */
        .med-table {
            width: 100%; border-collapse: collapse;
            margin-bottom: 25px;
        }

        .med-table th {
            background: #0f5132; color: white;
            padding: 12px 15px; font-size: 13px;
            font-weight: 600;
        }

        .med-table td {
            padding: 12px 15px; font-size: 14px;
            border-bottom: 1px solid #e0e0e0;
        }

        .med-table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .med-num {
            background: #0f5132; color: white;
            width: 24px; height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
        }

        /* Instructions */
        .instruction-box {
            background: #f8f9fa;
            border-radius: 10px; padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #0d6efd;
        }

        /* Follow Up */
        .followup-box {
            background: #d1e7dd;
            border-radius: 10px; padding: 15px 20px;
            margin-bottom: 25px;
            display: flex; align-items: center; gap: 10px;
        }

        /* Signature */
        .signature-box {
            text-align: right; margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .signature-line {
            border-top: 2px solid #333;
            width: 200px; margin-left: auto;
            margin-bottom: 5px;
        }

        /* Footer */
        .pres-footer {
            background: #f8f9fa;
            padding: 15px 40px;
            text-align: center;
            border-top: 2px solid #e0e0e0;
            font-size: 12px; color: #888;
        }

        /* Print button */
        .btn-print {
            background: #0f5132; color: white;
            border: none; border-radius: 10px;
            padding: 12px 30px; font-size: 15px;
            font-weight: 600; cursor: pointer;
            margin-right: 10px;
        }

        .btn-back {
            background: #6c757d; color: white;
            border: none; border-radius: 10px;
            padding: 12px 30px; font-size: 15px;
            font-weight: 600; cursor: pointer;
        }

        .action-bar {
            max-width: 800px; margin: 0 auto 20px;
            display: flex; gap: 10px;
        }

        @media print {
            .action-bar { display: none; }
            body { background: white; padding: 0; }
            .prescription {
                box-shadow: none; border-radius: 0;
            }
        }
    </style>
</head>
<body>

<!-- Action Buttons -->
<div class="action-bar">
    <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Prescription
    </button>
    <button class="btn-back" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>
</div>

<!-- Prescription -->
<div class="prescription">

    <!-- Header -->
    <div class="pres-header">
        <div class="d-flex justify-content-between
                    align-items-center">
            <div>
                <div class="hospital-name">
                    <i class="fas fa-hospital-alt"></i>
                    MediCare Hospital
                </div>
                <div class="hospital-sub">
                    Online Hospital Appointment
                    Management System
                </div>
                <div class="hospital-sub mt-1">
                    Government Degree College Hayatabad,
                    Peshawar
                </div>
            </div>
            <div class="rx-symbol">Rx</div>
        </div>
    </div>

    <!-- Doctor Info -->
    <div class="doctor-info">
        <div class="row">
            <div class="col-md-6">
                <strong>
                    Dr. <?php echo $pres['doctor_name']; ?>
                </strong>
                <span class="text-muted" style="font-size:13px;">
                    — <?php echo $pres['specialization']; ?>
                </span>
            </div>
            <div class="col-md-3">
                <span style="font-size:13px;color:#555;">
                    <?php echo $pres['qualification']; ?>
                </span>
            </div>
            <div class="col-md-3 text-end">
                <span style="font-size:13px;color:#555;">
                    Date:
                    <?php echo date('d M Y',
                        strtotime($pres['appointment_date'])); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="pres-body">

        <!-- Patient Info -->
        <div class="patient-box">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Patient Name</div>
                    <div class="info-value">
                        <?php echo $pres['patient_name']; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Phone</div>
                    <div class="info-value">
                        <?php echo $pres['patient_phone']; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">
                        Prescription Date
                    </div>
                    <div class="info-value">
                        <?php echo date('d M Y',
                            strtotime($pres['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnosis -->
        <div class="diagnosis-box">
            <div class="section-head">
                <i class="fas fa-stethoscope
                           text-warning"></i>
                Diagnosis
            </div>
            <p style="margin:0;color:#333;font-size:15px;">
                <?php echo $pres['diagnosis']; ?>
            </p>
        </div>

        <!-- Medicines -->
        <div class="section-head mb-3">
            <i class="fas fa-pills text-success"></i>
            Prescribed Medicines
        </div>

        <table class="med-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine Name</th>
                    <th>Dosage</th>
                    <th>Timing</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php if($medicines):
                foreach($medicines as $i => $med): ?>
                <tr>
                    <td>
                        <span class="med-num">
                            <?php echo $i+1; ?>
                        </span>
                    </td>
                    <td>
                        <strong>
                            <?php echo $med['name']; ?>
                        </strong>
                    </td>
                    <td><?php echo $med['dose']; ?></td>
                    <td><?php echo $med['timing']; ?></td>
                    <td><?php echo $med['days']; ?> days</td>
                </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>

        <!-- Instructions -->
        <?php if($pres['instructions']): ?>
        <div class="instruction-box">
            <div class="section-head">
                <i class="fas fa-notes-medical
                           text-primary"></i>
                Instructions & Advice
            </div>
            <p style="margin:0;color:#333;font-size:14px;
                      line-height:1.7;">
                <?php echo $pres['instructions']; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Follow Up -->
        <?php if($pres['follow_up_date']): ?>
        <div class="followup-box">
            <i class="fas fa-calendar-check
                       text-success fa-lg"></i>
            <div>
                <strong>Follow Up Date:</strong>
                <?php echo date('d M Y',
                    strtotime($pres['follow_up_date'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Signature -->
        <div class="signature-box">
            <div class="signature-line"></div>
            <div style="font-weight:700;color:#333;">
                Dr. <?php echo $pres['doctor_name']; ?>
            </div>
            <div style="font-size:13px;color:#888;">
                <?php echo $pres['specialization']; ?> |
                <?php echo $pres['qualification']; ?>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="pres-footer">
        <p style="margin:0;">
            This is a digitally generated prescription from
            MediCare Hospital System |
            Government Degree College Hayatabad, Peshawar
        </p>
    </div>

</div>

</body>
</html>