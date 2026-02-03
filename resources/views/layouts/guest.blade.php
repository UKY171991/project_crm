<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $systemSettings['system_title'] ?? config('app.name', 'Laravel') }}</title>
    @if(!empty($systemSettings['system_favicon']))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $systemSettings['system_favicon']) }}">
    @endif

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind (Keep for compatibility if needed inside slots) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --input-bg: rgba(15, 23, 42, 0.6);
            --input-border: rgba(255, 255, 255, 0.1);
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
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow-x: hidden;
            position: relative;
        }

        /* Floating shapes animation */
        .shape {
            position: absolute;
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            z-index: -1;
            animation: float 10s infinite ease-in-out;
        }

        .shape-1 {
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
        }

        .shape-2 {
            bottom: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, #ec4899, #f43f5e);
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -50px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        /* Glassmorphism Card */
        .auth-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 480px; /* Wider card */
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px; /* Top accent line */
            background: linear-gradient(90deg, #6366f1, #ec4899);
            border-radius: 24px 24px 0 0;
        }

        /* Logo Area */
        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-area img {
            height: 60px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
        }

        .logo-area svg {
            fill: #818cf8;
            filter: drop-shadow(0 0 10px rgba(129, 140, 248, 0.5));
        }

        /* Form Elements Override */
        label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: white !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 1rem !important;
            width: 100%;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2) !important;
            outline: none;
        }

        /* Checkbox */
        input[type="checkbox"] {
            background-color: var(--input-bg);
            border-color: var(--input-border);
            color: #6366f1; /* Tailwind indigo-500 */
        }

        /* Buttons */
        button {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.5);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }

        /* Links */
        a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        a:hover {
            color: #818cf8;
            text-decoration: underline;
        }

        /* Validation Errors */
        .text-red-600 {
            color: #f87171 !important; /* Lighter red for dark mode */
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        /* Spacing Fixes */
        .mt-4 { margin-top: 1.5rem !important; }
        .block { display: block; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-end { justify-content: flex-end; }
        .justify-between { justify-content: space-between; }
        .ml-2 { margin-left: 0.5rem; }
        .ml-4 { margin-left: 1rem; }
        .w-full { width: 100%; }
        
        /* Hide default white card styles from Jetstream */
        .bg-white {
            background-color: transparent !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="auth-card">
        <div class="logo-area">
            <!-- Logo removed as per request -->
            <h2 style="font-size: 1.5rem; font-weight: 700; color: white; margin-top: 10px;">
                {{ request()->routeIs('register') ? 'Create Account' : 'Welcome Back' }}
            </h2>
            <p style="color: #94a3b8; font-size: 0.9rem;">
                {{ request()->routeIs('register') ? 'Start your journey with us' : 'Sign in to access your dashboard' }}
            </p>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
