<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MediCare</title>

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
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo i {
            font-size: 50px;
            color: #0d6efd;
        }

        .login-logo h3 {
            font-weight: 700;
            color: #0d6efd;
            margin-top: 10px;
        }

        .login-logo p {
            color: #888;
            font-size: 14px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
        }

        .form-control:focus {
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

        .btn-login {
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

        .btn-login:hover {
            opacity: 0.9;
            color: white;
        }

        .role-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
            width: 100%;
            color: #333;
        }

        .role-select:focus {
            border-color: #0d6efd;
            outline: none;
        }

        .divider {
            text-align: center;
            color: #aaa;
            margin: 20px 0;
            position: relative;
        }

        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .register-link a {
            color: #0d6efd;
            font-weight: 600;
            text-decoration: none;
        }

        .back-home {
            text-align: center;
            margin-top: 15px;
        }

        .back-home a {
            color: #888;
            font-size: 14px;
            text-decoration: none;
        }

        .back-home a:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo">
            <i class="fas fa-hospital-alt"></i>
            <h3>MediCare</h3>
            <p>Login to your account</p>
        </div>

        <!-- Login Form -->
        <form action="login_process.php" method="POST">

            <!-- Role Select -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user-tag"></i> Login As
                </label>
                <select class="role-select" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="patient">🧑‍💼 Patient</option>
                    <option value="doctor">👨‍⚕️ Doctor</option>
                    <option value="admin">🔧 Admin</option>
                </select>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email"
                           placeholder="Enter your email" required>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password"
                           placeholder="Enter your password" required>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label text-muted" for="remember">Remember me</label>
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

        </form>

        <div class="divider">or</div>

        <!-- Register Link -->
        <div class="register-link">
            Don't have an account?
            <a href="register.php">Register Here</a>
        </div>

        <!-- Back to Home -->
        <div class="back-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>