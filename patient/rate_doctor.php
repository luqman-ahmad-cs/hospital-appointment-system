<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$appointment_id = $_GET['id'];
$patient_id     = $_SESSION['user_id'];

$apt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, u.fullname as doctor_name,
            d.specialization
     FROM appointments a
     JOIN users u ON a.doctor_id = u.id
     JOIN doctors d ON a.doctor_id = d.user_id
     WHERE a.id = '$appointment_id'
     AND a.patient_id = '$patient_id'
     AND a.status = 'completed'"));

if (!$apt) {
    echo "<script>alert('Invalid appointment!');
          window.location='my_appointments.php';</script>";
    exit();
}

$already = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id FROM ratings
     WHERE appointment_id = '$appointment_id'
     AND patient_id = '$patient_id'"));

if ($already) {
    echo "<script>alert('You have already rated this doctor!');
          window.location='my_appointments.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);
    $doc_id = $apt['doctor_id'];

    mysqli_query($conn,
        "INSERT INTO ratings
         (doctor_id, patient_id, appointment_id, rating, review)
         VALUES
         ('$doc_id','$patient_id','$appointment_id',
          '$rating','$review')");

    echo "<script>
            alert('Thank you for your feedback!');
            window.location='my_appointments.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Doctor — MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            min-height: 100vh;
            display: flex; align-items: center;
            justify-content: center; padding: 30px;
        }

        .rating-card {
            background: white; border-radius: 20px;
            padding: 0; max-width: 580px; width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            overflow: hidden;
        }

        /* Header */
        .card-header-custom {
            background: linear-gradient(135deg, #0f5132, #1a7a4a);
            padding: 30px; text-align: center; color: white;
        }

        .doctor-avatar {
            width: 85px; height: 85px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.5);
            display: flex; align-items: center;
            justify-content: center;
            font-size: 38px; color: white;
            margin: 0 auto 15px;
        }

        .card-header-custom h4 {
            font-weight: 700; margin: 0; font-size: 22px;
        }

        .card-header-custom p {
            margin: 5px 0 0; opacity: 0.85; font-size: 14px;
        }

        .apt-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 50px; padding: 6px 18px;
            font-size: 13px; display: inline-block;
            margin-top: 12px;
        }

        /* Body */
        .card-body-custom { padding: 35px; }

        /* Section Title */
        .section-title {
            font-size: 18px; font-weight: 700;
            color: #333; margin-bottom: 5px;
        }

        .section-desc {
            color: #888; font-size: 13px;
            margin-bottom: 25px; line-height: 1.5;
        }

        /* Star Rating */
        .star-section {
            background: #f8f9fa; border-radius: 15px;
            padding: 25px; text-align: center;
            margin-bottom: 25px;
            border: 2px solid #e0e0e0;
        }

        .star-label {
            font-weight: 700; color: #333;
            font-size: 15px; margin-bottom: 15px;
        }

        .stars {
            display: flex; justify-content: center;
            gap: 8px; flex-direction: row-reverse;
        }

        .stars input { display: none; }

        .stars label {
            font-size: 50px; color: #ddd;
            cursor: pointer; transition: all 0.2s;
            line-height: 1;
        }

        .stars label:hover,
        .stars label:hover ~ label,
        .stars input:checked ~ label {
            color: #ffc107;
            transform: scale(1.1);
        }

        .rating-desc {
            margin-top: 12px; font-size: 15px;
            font-weight: 600; min-height: 25px;
            color: #555;
        }

        /* Rating pills */
        .rating-pills {
            display: flex; justify-content: center;
            gap: 8px; margin-top: 12px; flex-wrap: wrap;
        }

        .rating-pill {
            background: #f0f4ff; border-radius: 50px;
            padding: 4px 14px; font-size: 12px;
            color: #666; cursor: pointer;
            border: 1px solid #e0e0e0;
            transition: all 0.2s;
        }

        .rating-pill:hover {
            background: #0d6efd; color: white;
            border-color: #0d6efd;
        }

        /* Review Box */
        .review-section { margin-bottom: 25px; }

        .review-label {
            font-weight: 700; color: #333;
            font-size: 15px; margin-bottom: 5px;
        }

        .review-sublabel {
            color: #888; font-size: 12px;
            margin-bottom: 10px; line-height: 1.5;
        }

        .form-control {
            border-radius: 12px; padding: 14px 16px;
            border: 2px solid #e0e0e0; font-size: 14px;
            transition: border-color 0.3s; resize: none;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
        }

        /* Quick Tags */
        .quick-tags {
            display: flex; flex-wrap: wrap;
            gap: 8px; margin-bottom: 15px;
        }

        .quick-tag {
            background: #f0f4ff; border: 1px solid #c5d8ff;
            border-radius: 50px; padding: 5px 14px;
            font-size: 12px; color: #0d6efd;
            cursor: pointer; transition: all 0.2s;
            font-weight: 600;
        }

        .quick-tag:hover {
            background: #0d6efd; color: white;
        }

        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white; border: none; border-radius: 12px;
            padding: 14px; font-size: 16px; font-weight: 700;
            width: 100%; margin-bottom: 12px;
            transition: opacity 0.3s; letter-spacing: 0.5px;
        }

        .btn-submit:hover { opacity: 0.9; color: white; }

        .btn-back {
            background: #f8f9fa; color: #666;
            border: 2px solid #e0e0e0; border-radius: 12px;
            padding: 12px; font-size: 15px; font-weight: 600;
            width: 100%; transition: all 0.3s;
        }

        .btn-back:hover {
            background: #e0e0e0; color: #333;
        }

        /* Divider */
        .divider {
            border: none; border-top: 2px solid #f0f0f0;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="rating-card">

    <!-- Header -->
    <div class="card-header-custom">
        <div class="doctor-avatar">
            <i class="fas fa-user-md"></i>
        </div>
        <h4>Dr. <?php echo $apt['doctor_name']; ?></h4>
        <p><?php echo $apt['specialization']; ?></p>
        <div class="apt-badge">
            <i class="fas fa-calendar"></i>
            <?php echo date('d M Y',
                strtotime($apt['appointment_date'])); ?>
            at
            <?php echo date('h:i A',
                strtotime($apt['appointment_time'])); ?>
        </div>
    </div>

    <!-- Body -->
    <div class="card-body-custom">

        <div class="section-title">
            How was your experience with this doctor?
        </div>
        <div class="section-desc">
            Your rating and feedback help us improve
            healthcare services for everyone.
            Your honest review helps other patients
            make better decisions.
        </div>

        <form method="POST">

            <!-- Star Rating -->
            <div class="star-section">
                <div class="star-label">
                    <i class="fas fa-star text-warning"></i>
                    Rate Your Experience
                </div>

                <div class="stars">
                    <input type="radio" name="rating"
                           id="star5" value="5">
                    <label for="star5">★</label>

                    <input type="radio" name="rating"
                           id="star4" value="4">
                    <label for="star4">★</label>

                    <input type="radio" name="rating"
                           id="star3" value="3">
                    <label for="star3">★</label>

                    <input type="radio" name="rating"
                           id="star2" value="2">
                    <label for="star2">★</label>

                    <input type="radio" name="rating"
                           id="star1" value="1">
                    <label for="star1">★</label>
                </div>

                <div class="rating-desc" id="ratingDesc">
                    Click a star to rate your experience
                </div>

                <!-- Rating Pills -->
                <div class="rating-pills">
                    <span class="rating-pill">😞 Poor</span>
                    <span class="rating-pill">😐 Fair</span>
                    <span class="rating-pill">🙂 Good</span>
                    <span class="rating-pill">😊 Very Good</span>
                    <span class="rating-pill">🌟 Excellent</span>
                </div>
            </div>

            <hr class="divider">

            <!-- Review Section -->
            <div class="review-section">
                <div class="review-label">
                    <i class="fas fa-comment-dots text-primary"></i>
                    Patient Feedback & Review
                </div>
                <div class="review-sublabel">
                    Please provide honest feedback about the
                    doctor's service. Your review helps other
                    patients make better decisions.
                </div>

                <!-- Quick Tags -->
                <div class="quick-tags">
                    <span class="quick-tag"
                          onclick="addTag('Very professional')">
                        👍 Very professional
                    </span>
                    <span class="quick-tag"
                          onclick="addTag('Explained clearly')">
                        💬 Explained clearly
                    </span>
                    <span class="quick-tag"
                          onclick="addTag('Very friendly')">
                        😊 Very friendly
                    </span>
                    <span class="quick-tag"
                          onclick="addTag('Highly recommended')">
                        ⭐ Highly recommended
                    </span>
                    <span class="quick-tag"
                          onclick="addTag('On time')">
                        ⏰ On time
                    </span>
                    <span class="quick-tag"
                          onclick="addTag('Great experience')">
                        🏆 Great experience
                    </span>
                </div>

                <textarea class="form-control"
                    name="review" id="reviewText"
                    rows="4"
                    placeholder="Write your review here...
Example: The doctor was very professional, friendly, and explained everything clearly. I highly recommend this doctor to everyone.">
                </textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit"
                    onclick="return validateRating()">
                <i class="fas fa-paper-plane"></i>
                Post Review
            </button>

            <button type="button" class="btn-back"
                onclick="window.location='my_appointments.php'">
                <i class="fas fa-arrow-left"></i>
                Back to Appointments
            </button>

        </form>
    </div>
</div>

<script>
const ratingData = {
    1: { text: "Poor 😞", color: "#dc3545" },
    2: { text: "Fair 😐", color: "#fd7e14" },
    3: { text: "Good 🙂", color: "#ffc107" },
    4: { text: "Very Good 😊", color: "#20c997" },
    5: { text: "Excellent! 🌟", color: "#28a745" }
};

document.querySelectorAll('.stars input').forEach(input => {
    input.addEventListener('change', function() {
        const data = ratingData[this.value];
        const desc = document.getElementById('ratingDesc');
        desc.textContent = data.text;
        desc.style.color = data.color;
    });
});

function addTag(text) {
    const ta = document.getElementById('reviewText');
    if (ta.value && !ta.value.endsWith(' ')) {
        ta.value += ' ';
    }
    ta.value += text + '. ';
    ta.focus();
}

function validateRating() {
    const selected = document.querySelector(
        '.stars input:checked');
    if (!selected) {
        alert('Please select a star rating first!');
        return false;
    }
    return true;
}
</script>

</body>
</html>