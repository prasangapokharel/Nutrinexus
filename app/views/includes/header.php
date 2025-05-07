<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($data['title']) ? $data['title'] . ' - ' : '' ?>Nutri Nexas</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    }
                }
            }
        }
    </script>
    <style>
                @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        body {
            
            font-family: 'Montserrat', sans-serif;
            font-weight: 100;
            font-size: small;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php require APPROOT . '/views/includes/navbar.php'; ?>
