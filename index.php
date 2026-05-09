<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Hospital</title>

    <!-- Bootstrap CSS - Ready made design library -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome - Icons ke liye -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Google Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            padding: 15px 0;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: white !important;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 5px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn-hero {
            background: white;
            color: #0d6efd;
            font-weight: 600;
            padding: 12px 35px;
            border-radius: 50px;
            font-size: 16px;
            margin: 5px;
            border: none;
        }

        .btn-hero:hover {
            background: #f0f0f0;
            color: #0a58ca;
        }

        .btn-hero-outline {
            background: transparent;
            color: white;
            font-weight: 600;
            padding: 12px 35px;
            border-radius: 50px;
            font-size: 16px;
            margin: 5px;
            border: 2px solid white;
        }

        .btn-hero-outline:hover {
            background: white;
            color: #0d6efd;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 50px;
            color: #0d6efd;
            margin-bottom: 20px;
        }

        .feature-card h4 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
        }

        .stat-label {
            font-size: 16px;
            opacity: 0.85;
        }

        /* Footer */
        footer {
            background: #1a1a2e;
            color: #aaa;
            text-align: center;
            padding: 25px 0;
        }

        footer span {
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt"></i> MediCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-heartbeat"></i> Welcome to MediCare</h1>
            <p>Online Hospital Appointment System — Book appointments & consult doctors via video call</p>
            <a href="register.php" class="btn btn-hero">
                <i class="fas fa-user-plus"></i> Register as Patient
            </a>
            <a href="login.php" class="btn btn-hero-outline">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Why Choose MediCare?</h2>
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                        <h4>Easy Appointment Booking</h4>
                        <p class="text-muted">Book your doctor appointment online in just a few clicks — anytime, anywhere.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-video"></i></div>
                        <h4>Video Consultation</h4>
                        <p class="text-muted">Consult your doctor face-to-face via secure video call from the comfort of your home.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-user-md"></i></div>
                        <h4>Expert Doctors</h4>
                        <p class="text-muted">Choose from a wide range of specialist doctors available for online consultation.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- STATS SECTION -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Doctors</div>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Patients</div>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Specializations</div>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Online Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>© 2026 <span>MediCare</span> — Online Hospital Appointment System | Made by Luqman Ahmad & Maaz Ahmad</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>