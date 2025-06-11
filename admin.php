<?php

session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <style>
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
        h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .container {
            max-width: 900px;
            margin: 40px auto; 
            background-color: #fff;
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center; 
            flex-grow: 1; 
        }
        .panel-links {
            display: flex; 
            flex-wrap: wrap; 
            justify-content: center; 
            gap: 20px; 
            margin-top: 30px;
        }
        .panel-link {
            display: block; 
            width: 200px; 
            padding: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .panel-link:hover {
            background-color: #0056b3;
            transform: translateY(-3px); 
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto; 
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header>
        <h1>Painel Administrativo</h1>
    </header>

    <?php include 'nav.php'; ?>

    <div class="container">
        <h2>Bem-vindo(a) ao Painel Administrativo!</h2>
        

        <div class="panel-links">
            <a class="panel-link" href="adicionar_carro.php">Adicionar Carro</a>
            <a class="panel-link" href="gerenciar_carro.php">Gerenciar Carros</a>
            <a class="panel-link" href="gerenciar_user.php">Gerenciar Usuários</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>