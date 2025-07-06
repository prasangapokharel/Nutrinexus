<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($data['title']) ? $data['title'] . ' - ' : '' ?>Nutri Nexas</title>
    <script src="https://cdn.tailwindcss.com"></script>
        <script src="<?= URLROOT ?>/assets/js/tailwind.js"></script>


    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="<?= URLROOT ?>/assets/css/icon.allmin.css" rel="stylesheet">
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
                        'body': ['MFjalla One', 'sans-serif'],
                    }
                }
            }
        }
    </script>
   <style>
    
@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');
        body {
            display: fixed;
             font-family: "Fjalla One", sans-serif;
  font-weight: 400;
  font-style: normal
          
        }
        header{
        display: fixed;
             font-family: "Fjalla One", sans-serif;

        }
        footer{
             font-family: "Fjalla One", sans-serif;

        }

    .card{
background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            /* Updated clip-path for subtle corner clips matching your image */
            clip-path: polygon(12px 0%, 100% 0%, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0% 100%, 0% 12px);
    }

    .clip{
        border-radius: 8px;
            padding: 12px 24px;
                    clip-path: polygon(8px 0%, 100% 0%, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0% 100%, 0% 8px);
overflow: hidden;
    
    }
   /* Primary Button Styling */
.btn-primary {
   background-color: #0A3167;
            color: white;
            border-radius: 8px;
            padding: 12px 24px;
            text-decoration: none;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 14px;
            /* Updated clip-path for a more subtle clipped corner effect */
            clip-path: polygon(8px 0%, 100% 0%, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0% 100%, 0% 8px);
            position: relative;
            overflow: hidden;
}

.btn-accent {
   background-color: #C5A572;
            color: white;
            border-radius: 8px;
            padding: 12px 24px;
            text-decoration: none;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 14px;
            /* Updated clip-path for a more subtle clipped corner effect */
            clip-path: polygon(8px 0%, 100% 0%, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0% 100%, 0% 8px);
            position: relative;
            overflow: hidden;
}
    </style>
</head>
<body class="bg-gray-50">
    <?php require APPROOT . '/views/includes/navbar.php'; ?>
