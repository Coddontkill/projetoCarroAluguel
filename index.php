<?php
session_start();
require_once 'conexao.php'; 

$carros = [];

$sql = "SELECT id, modelo, marca, ano, preco_diaria, imagem FROM carros ORDER BY modelo ASC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $carros = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Erro ao buscar carros: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlexCar - Aluguel de Carros</title>
    
    <link rel="stylesheet" href="css/style.css">
    
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

            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .carro {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .carro img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .carro h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.4em;
        }

        .carro p {
            color: #555;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        .carro strong {
            color: #333;
        }

        .carro .botao {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .carro .botao:hover {
            background-color: #218838;
        }

        .carro .botao[style*="background-color: gray"] {
            background-color: #6c757d !important;
            cursor: not-allowed;
            pointer-events: none;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid transparent;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
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
        <h1>FlexCar</h1>
        <p>Carro para cada momento, fácil de alugar.</p>
    </header>

    <?php include 'nav.php'; ?>

    <div class="main-content">
        <?php 
        if (isset($_SESSION['message'])): 
        ?>
            <div class="message <?= htmlspecialchars($_SESSION['message_type']) ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <div class="container">
            <?php 
            if (!empty($carros)):
                foreach ($carros as $carro):
            ?>
                    <div class="carro">
                        <h3><?= htmlspecialchars($carro['modelo']) ?></h3>
                        <img src="img/<?= htmlspecialchars($carro['imagem']) ?>" alt="<?= htmlspecialchars($carro['modelo']) ?>">
                        <p><strong>Marca:</strong> <?= htmlspecialchars($carro['marca']) ?></p>
                        <p><strong>Ano:</strong> <?= htmlspecialchars($carro['ano']) ?></p>
                        <p><strong>Preço por dia:</strong> R$<?= number_format($carro['preco_diaria'], 2, ',', '.') ?></p>
                        <?php 
                        if (isset($_SESSION['usuario_id'])): 
                        ?>
                            <a href="reserva.php?carro_id=<?= $carro['id'] ?>" class="botao">Reservar</a>
                        <?php else: ?>
                            <a href="login.php" class="botao" style="background-color: gray;">Faça login para reservar</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum carro disponível para aluguel no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>