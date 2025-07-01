<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Supplement Store - Loading</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0A3167',
                            dark: '#082850'
                        },
                        accent: {
                            DEFAULT: '#C5A572',
                            dark: '#B89355'
                        }
                    },
                    fontFamily: {
                        'heading': ['Playfair Display', 'serif'],
                        'body': ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #FAFAFA;
        }
        .loader {
            width: 120px;
            height: 120px;
            position: relative;
        }
        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid #0A3167;
            border-radius: 50%;
            position: absolute;
        }
        .loader-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background-color: #0A3167;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .loader-logo::after {
            content: "NX";
            color: #C5A572;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
        }
        .loader-progress {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 4px solid #C5A572;
            border-radius: 50%;
            clip-path: polygon(0% 0%, 50% 0%, 50% 50%, 0% 50%);
            animation: progress 2s linear infinite;
        }
        @keyframes progress {
            0% { clip-path: polygon(0% 0%, 0% 0%, 0% 0%, 0% 0%); }
            25% { clip-path: polygon(0% 0%, 50% 0%, 50% 50%, 0% 50%); }
            50% { clip-path: polygon(0% 0%, 100% 0%, 100% 50%, 0% 50%); }
            75% { clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%); }
            100% { clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%); }
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-progress"></div>
            <div class="loader-logo"></div>
        </div>
        <p class="mt-8 text-primary font-body text-lg">Loading your premium experience...</p>
        
        <div class="mt-16 text-center">
            <h1 class="font-heading text-3xl md:text-4xl text-primary">NX Supplements</h1>
            <p class="font-body text-sm md:text-base text-gray-600 mt-2">Premium Quality. Superior Results.</p>
        </div>
    </div>

    <script>
        // Simulate loading and redirect to main page after 3 seconds
        setTimeout(() => {
            window.location.href = "cart.html";
        }, 3000);
    </script>
</body>
</html>
