<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($data['title']) ? $data['title'] . ' - ' : '' ?>Nutri Nexas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="<?= URLROOT ?>/css/icon.allmin.css" rel="stylesheet">
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
            
             font-family: "Fjalla One", sans-serif;
  font-weight: 400;
  font-style: normal
          
        }
        header{
             font-family: "Fjalla One", sans-serif;

        }
        footer{
             font-family: "Fjalla One", sans-serif;

        }
    </style>
</head>
<body class="bg-gray-50">
    <?php require APPROOT . '/views/includes/navbar.php'; ?>
