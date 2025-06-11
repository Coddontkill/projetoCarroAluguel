<?php
session_start();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossa Equipe - FlexCar</title>
    
    <link rel="icon" href="https://png.pngtree.com/png-clipart/20230816/original/pngtree-design-geometric-logo-for-company-ona-white-background-picture-image_7980581.png" type="image/png">
    
    <style>

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh; 
        }

        header {
            background-color: #007bff; 
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }

        header h1 {
            margin: 0;
            font-size: 2.5em; 
            font-weight: bold;
        }

        header p {
            margin: 5px 0 0;
            font-size: 1.1em;
            opacity: 0.9; 
        }


        .container {
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 30px; 
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            gap: 30px; 
            text-align: center; 
        }
        
        .container h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }


        .team-section {
            padding-top: 20px;
        }

        .team-members {
            display: grid;

            grid-template-columns: repeat(5, minmax(180px, 1fr)); 
            gap: 25px;
            margin-top: 30px;
            justify-content: center; 
        }

        .member-card {
            background-color: #f0f4f7;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .member-card:hover {
            transform: translateY(-5px); 
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .member-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%; 
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #007bff; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .member-card h4 {
            color: #333;
            font-size: 1.3em;
            margin-bottom: 5px;
        }

        .member-card p {
            color: #555;
            font-size: 0.95em;
            margin-bottom: 5px;
        }
        .member-card .role {
            font-weight: bold;
            color: #007bff;
        }
        .member-card .email {
            font-style: italic;
            font-size: 0.9em;
            word-break: break-all; 
            margin-top: 10px;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
        }


        @media (max-width: 1200px) { 
            .team-members {

                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            }
        }
        @media (max-width: 768px) {
            .team-members {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            }
        }
    </style> 
</head>
<body>
    <header>
        <h1>Equipe FlexCar</h1>

    </header>

    <?php include 'nav.php';  ?>

    <div class="main-content">
        <div class="container">
            <div class="team-section">
                <h2>Quem Somos</h2>
               
                <div class="team-members">
                    <div class="member-card">
                        <img src="img/Anderson.png" alt="Anderson">
                        <h4>Anderson</h4>
                        <p class="role">Lider de Desenvolvimento</p>
                        
                    </div>
                    <div class="member-card">
                        <img src="img/Matheus.png" alt="Matheus">
                        <h4>Matheus</h4>
                        <p class="role">Analista de Testes e Documentação</p>
                        
                    </div>
                    <div class="member-card">
                        <img src="img/Vitoria.png" alt="Vitória">
                        <h4>Vitória</h4>
                        <p class="role">Front-End</p>

                    </div>
                    <div class="member-card">
                        <img src="img/Pedro.png" alt="Pedro">
                        <h4>Pedro</h4>
                        <p class="role">Back-End</p>

                    </div>
                    <div class="member-card">
                        <img src="img/Chris.png" alt="Chris">
                        <h4>Chris</h4>
                        <p class="role">Database Designer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>