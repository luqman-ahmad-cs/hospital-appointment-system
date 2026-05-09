<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — MediCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 0;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .register-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-logo i { font-size: 45px; color: #0d6efd; }
        .register-logo h3 { font-weight: 700; color: #0d6efd; margin-top: 10px; }
        .register-logo p { color: #888; font-size: 14px; }

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

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background: #f0f4ff;
            border: 2px solid #e0e0e0;
            color: #0d6efd;
        }

        .input-group .form-control {
            border-radius: 0 10px 10px 0;
            border-left: none;
        }

        /* Role Cards */
        .role-selector { display: flex; gap: 10px; margin-bottom: 5px; }

        .role-card {
            flex: 1;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .role-card:hover { border-color: #0d6efd; background: #f0f4ff; }
        .role-card.active { border-color: #0d6efd; background: #f0f4ff; }
        .role-card i { font-size: 28px; color: #0d6efd; }
        .role-card p { font-size: 13px; font-weight: 600; margin: 5px 0 0; color: #333; }

        /* Doctor Extra Fields */
        #doctorFields { display: none; }

        .btn-register {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: opacity 0.3s;
        }

        .btn-register:hover { opacity: 0.9; color: white; }

        .login-link { text-align: center; margin-top: 20px; color: #666; }
        .login-link a { color: #0d6efd; font-weight: 600; text-decoration: none; }

        .back-home { text-align: center; margin-top: 15px; }
        .back-home a { color: #888; font-size: 14px; text-decoration: none; }
        .back-home a:hover { color: #0d6efd; }
    </style>
</head>

<body>

<div class="register-card">

    <!-- Logo -->
    <div class="register-logo">
        <i class="fas fa-hospital-alt"></i>
        <h3>MediCare</h3>
        <p>Create your account</p>
    </div>

    <!-- Register Form -->
    <form action="register_process.php" method="POST">

        <!-- Role Selection -->
        <div class="mb-4">
            <label class="form-label"><i class="fas fa-user-tag"></i> Register As</label>
            <div class="role-selector">

                <div class="role-card active" onclick="selectRole('patient', this)">
                    <i class="fas fa-user"></i>
                    <p>Patient</p>
                    <input type="radio" name="role" value="patient" checked hidden>
                </div>

                <div class="role-card" onclick="selectRole('doctor', this)">
                    <i class="fas fa-user-md"></i>
                    <p>Doctor</p>
                    <input type="radio" name="role" value="doctor" hidden>
                </div>

            </div>
        </div>

        <!-- Full Name -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-user"></i> Full Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="fullname"
                       placeholder="Enter your full name" required>
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" name="email"
                       placeholder="Enter your email" required>
            </div>
        </div>

        <!-- Phone -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" class="form-control" name="phone"
                       placeholder="03XX-XXXXXXX" required>
            </div>
        </div>

        <!-- Doctor Extra Fields -->
        <div id="doctorFields">

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-stethoscope"></i> Specialization</label>
                <select class="form-select" name="specialization">
                    <option value="">-- Select Specialization --</option>
                    <option>Cardiologist</option>
                    <option>Dermatologist</option>
                    <option>General Physician</option>
                    <option>Neurologist</option>
                    <option>Orthopedic</option>
                    <option>Pediatrician</option>
                    <option>Psychiatrist</option>
                    <option>Surgeon</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-graduation-cap"></i> Qualification</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                    <input type="text" class="form-control" name="qualification"
                           placeholder="e.g. MBBS, MD">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-clock"></i> Experience (Years)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                    <input type="number" class="form-control" name="experience"
                           placeholder="e.g. 5">
                </div>
            </div>

        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" name="password"
                       placeholder="Create a password" required>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-check"></i></span>
                <input type="password" class="form-control" name="confirm_password"
                       placeholder="Re-enter your password" required>
            </div>
        </div>

        <!-- Register Button -->
        <button type="submit" class="btn btn-register">
            <i class="fas fa-user-plus"></i> Create Account
        </button>

    </form>

    <!-- Login Link -->
    <div class="login-link">
        Already have an account? <a href="login.php">Login Here</a>
    </div>

    <!-- Back to Home -->
    <div class="back-home">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Role card select karne ka function
    function selectRole(role, element) {
        // Pehle sab cards se active class hatao
        document.querySelectorAll('.role-card').forEach(card => {
            card.classList.remove('active');
            card.querySelector('input[type=radio]').checked = false;
        });

        // Selected card ko active karo
        element.classList.add('active');
        element.querySelector('input[type=radio]').checked = true;

        // Doctor fields show/hide karo
        if (role === 'doctor') {
            document.getElementById('doctorFields').style.display = 'block';
        } else {
            document.getElementById('doctorFields').style.display = 'none';
        }
    }
</script>

</body>
</html>