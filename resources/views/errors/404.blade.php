<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | CRM</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --accent: #f43f5e;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 10;
            padding: 2rem;
            max-width: 600px;
        }

        .error-code {
            font-size: 12rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        .error-code::after {
            content: "404";
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: blur(20px);
            opacity: 0.3;
            z-index: -1;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
        }

        /* Decorative Elements */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            opacity: 0.05;
            z-index: 1;
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -100px;
        }

        .circle-2 {
            width: 300px;
            height: 300px;
            bottom: -150px;
            left: -50px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @media (max-width: 480px) {
            .error-code { font-size: 8rem; }
            h1 { font-size: 1.8rem; }
            p { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="container">
        <div class="error-code">404</div>
        <h1>Lost in the Cloud?</h1>
        <p>The page you're looking for seems to have vanished into thin air. Don't worry, our team is already looking for it!</p>
        
        <a href="{{ url('/dashboard') }}" class="btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <script>
        // Subtle mouse parallax effect
        document.addEventListener('mousemove', (e) => {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
            
            document.querySelector('.error-code').style.transform = `translate(${moveX}px, ${moveY - 10}px)`;
            document.querySelector('.circle-1').style.transform = `translate(${moveX * -2}px, ${moveY * -2}px)`;
            document.querySelector('.circle-2').style.transform = `translate(${moveX * -1.5}px, ${moveY * -1.5}px)`;
        });
    </script>
</body>
</html>
