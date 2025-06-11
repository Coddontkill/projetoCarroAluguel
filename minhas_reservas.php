<?php
session_start();
require_once 'conexao.php';

$reservas = [];

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['message'] = "Você precisa estar logado para ver suas reservas.";
    $_SESSION['message_type'] = "warning";
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT a.id, a.data_inicio, a.data_fim, a.preco_total, c.modelo, c.marca, c.imagem 
        FROM alugueis a 
        JOIN carros c ON a.carro_id = c.id 
        WHERE a.usuario_id = ?
        ORDER BY a.data_inicio DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $reservas = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Erro ao buscar reservas: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - FlexCar</title>
    
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
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .carro .botao:hover {
            background-color: #c82333;
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
        <h1>Minhas Reservas</h1>
        <p>Acompanhe e gerencie suas reservas de veículos.</p>
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
            if (!empty($reservas)):
                foreach ($reservas as $reserva):
            ?>
                    <div class="carro">
                        <h3><?= htmlspecialchars($reserva['marca']) . ' ' . htmlspecialchars($reserva['modelo']) ?></h3>
                        <img src="img/<?= htmlspecialchars($reserva['imagem']) ?>" alt="Imagem do carro">
                        <p><strong>De:</strong> <?= htmlspecialchars((new DateTime($reserva['data_inicio']))->format('d/m/Y')) ?></p>
                        <p><strong>Até:</strong> <?= htmlspecialchars((new DateTime($reserva['data_fim']))->format('d/m/Y')) ?></p>
                        <p><strong>Total:</strong> R$ <?= number_format($reserva['preco_total'], 2, ',', '.') ?></p>
                        <?php 
                        if ($reserva['data_inicio'] >= date('Y-m-d')): 
                        ?>
                            <a class="botao" href="cancelar_reserva.php?id=<?= htmlspecialchars($reserva['id']) ?>" onclick="return confirm('Tem certeza que deseja cancelar esta reserva?')">Cancelar Reserva</a>
                        <?php else: ?>
                            <p style="color: gray; font-style: italic;">Reserva concluída</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Você ainda não tem reservas ativas ou concluídas.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>