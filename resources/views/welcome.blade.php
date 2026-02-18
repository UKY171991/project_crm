<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM Attendance Monitor</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-color);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #6366f1, #ec4899, #8b5cf6);
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #cbd5e1;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn-fancy {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            text-decoration: none;
            display: inline-block;
        }

        .btn-fancy:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
            color: white;
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }

        .btn-outline-fancy {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.1);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .btn-outline-fancy:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: rgba(99, 102, 241, 0.1);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            color: #818cf8;
            margin: 0 auto 1.5rem;
        }

        /* Floating shapes */
        .shape {
            position: absolute;
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            z-index: -1;
            animation: float 10s infinite ease-in-out;
        }

        .shape-1 {
            top: 20%;
            left: 20%;
            width: 300px;
            height: 300px;
        }

        .shape-2 {
            bottom: 20%;
            right: 20%;
            width: 250px;
            height: 250px;
            background: linear-gradient(45deg, #ec4899, #f43f5e);
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -50px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        .navbar {
            padding: 1.5rem 2rem;
            background: transparent;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            color: #818cf8;
        }

        .nav-link {
            color: #cbd5e1 !important;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }
    </style>
</head>
<body>
    
    <!-- Background Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="#" class="logo-text">
                <i class="fas fa-cube logo-icon"></i>
                Project CRM
            </a>
            
            <div class="d-flex align-items-center">
                @if (Route::has('login'))
                    <div class="d-flex gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-fancy text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">Log in</a>

                            @if (Route::has('register') && \App\Models\Setting::get('registration_enabled', '1') == '1')
                                <a href="{{ route('register') }}" class="btn-fancy text-sm" style="padding: 8px 20px; font-size: 0.9rem;">Register</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Content -->
    <div class="hero-section">
        <div class="glass-card">
            <div class="feature-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            
            <h1 class="hero-title">Work Smarter, <br>Not Harder</h1>
            
            <p class="hero-subtitle">
                The ultimate CRM solution for tracking attendance, managing projects, and boosting team productivity with automated screenshot monitoring.
            </p>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-fancy">
                        <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-fancy">
                        <i class="fas fa-sign-in-alt me-2"></i> Get Started
                    </a>
                    @if (Route::has('register') && \App\Models\Setting::get('registration_enabled', '1') == '1')
                    <a href="{{ route('register') }}" class="btn-outline-fancy">
                        <i class="fas fa-user-plus me-2"></i> Sign Up
                    </a>
                    @endif
                @endauth
            </div>

            <div class="mt-5 pt-4 border-top border-secondary border-opacity-25 row text-start">
                <div class="col-4 text-center">
                    <h3 class="fw-bold mb-0 text-white">100+</h3>
                    <small class="text-white-50">Active Users</small>
                </div>
                <div class="col-4 text-center border-start border-end border-secondary border-opacity-25">
                    <h3 class="fw-bold mb-0 text-white">99%</h3>
                    <small class="text-white-50">Uptime</small>
                </div>
                <div class="col-4 text-center">
                    <h3 class="fw-bold mb-0 text-white">24/7</h3>
                    <small class="text-white-50">Monitoring</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
