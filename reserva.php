<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['message'] = "Você precisa estar logado para fazer uma reserva.";
    $_SESSION['message_type'] = "warning";
    header("Location: login.php");
    exit;
}

if (!isset($_GET['carro_id'])) {
    $_SESSION['message'] = "Carro não especificado para reserva.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}

$carro_id = intval($_GET['carro_id']);
$usuario_id = $_SESSION['usuario_id'];
$carro = null;
$formas_pagamento = [];
$reserva_confirmada = false;
$preco_total_reserva = 0;
$data_inicio_reserva = '';
$data_fim_reserva = '';

$sql_carro = "SELECT id, modelo, marca, ano, preco_diaria, imagem FROM carros WHERE id = ?";
$stmt_carro = $conn->prepare($sql_carro);

if ($stmt_carro) {
    $stmt_carro->bind_param("i", $carro_id);
    $stmt_carro->execute();
    $result_carro = $stmt_carro->get_result();
    $carro = $result_carro->fetch_assoc();
    $stmt_carro->close();

    if (!$carro) {
        $_SESSION['message'] = "Carro não encontrado.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['message'] = "Erro ao buscar dados do carro: " . $conn->error;
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}

$sql_formas = "SELECT id, tipo_pagamento, detalhes FROM formas_pagamento WHERE usuario_id = ?";
$stmt_formas = $conn->prepare($sql_formas);

if ($stmt_formas) {
    $stmt_formas->bind_param("i", $usuario_id);
    $stmt_formas->execute();
    $formas_result = $stmt_formas->get_result();
    $formas_pagamento = $formas_result->fetch_all(MYSQLI_ASSOC);
    $stmt_formas->close();

    if (empty($formas_pagamento)) {
        $_SESSION['message'] = "Você não tem formas de pagamento cadastradas. Por favor, adicione uma para fazer a reserva.";
        $_SESSION['message_type'] = "warning";
    }
} else {
    $_SESSION['message'] = "Erro ao buscar formas de pagamento: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $forma_pagamento_id = $_POST['forma_pagamento'];

    $data_inicio_ts = strtotime($data_inicio);
    $data_fim_ts = strtotime($data_fim);
    $hoje_ts = strtotime(date('Y-m-d'));

    if ($data_fim_ts < $data_inicio_ts) {
        $_SESSION['message'] = "A data de fim não pode ser anterior à data de início.";
        $_SESSION['message_type'] = "error";
    } elseif ($data_inicio_ts < $hoje_ts) {
        $_SESSION['message'] = "A data de início não pode ser no passado.";
        $_SESSION['message_type'] = "error";
    } else {
        $dias = ($data_fim_ts - $data_inicio_ts) / (60 * 60 * 24) + 1;
        $preco_total = $dias * $carro['preco_diaria'];

        $sql_insert = "INSERT INTO alugueis (carro_id, data_inicio, data_fim, usuario_id, preco_total, forma_pagamento_id)
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        if ($stmt_insert) {
            $stmt_insert->bind_param("issidi", $carro_id, $data_inicio, $data_fim, $usuario_id, $preco_total, $forma_pagamento_id);
            if ($stmt_insert->execute()) {
                $_SESSION['message'] = "Reserva realizada com sucesso!";
                $_SESSION['message_type'] = "success";
                $reserva_confirmada = true;
                $preco_total_reserva = $preco_total;
                $data_inicio_reserva = $data_inicio;
                $data_fim_reserva = $data_fim;
            } else {
                $_SESSION['message'] = "Erro ao realizar reserva: " . $stmt_insert->error;
                $_SESSION['message_type'] = "error";
            }
            $stmt_insert->close();
        } else {
            $_SESSION['message'] = "Erro na preparação da reserva: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Carro - FlexCar</title>
    
    <link rel="stylesheet" href="css/style.css">
    
    <link rel="icon" href="https://png.pngtree.com/png-clipart/20230816/original/pngtree-design-geometric-logo-for-company-ona-white-background-picture-image_7980581.png" type="image/png">
    
    <style>
        * {
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
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .carro-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            width: 100%;
        }

        .carro-info img {
            max-width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .carro-info h2 {
            color: #333;
            margin-top: 0;
            font-size: 2em;
        }

        .carro-info p {
            color: #555;
            margin-bottom: 5px;
            font-size: 1.1em;
        }

        .carro-info strong {
            color: #333;
        }

        form {
            width: 100%;
            max-width: 400px;
            text-align: left;
            margin-top: 20px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            font-size: 1em;
        }

        form input[type="date"],
        form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        form input[type="date"]:focus,
        form select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .botao {
            display: block;
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
        }

        .botao:hover {
            background-color: #218838;
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
            width: 100%;
            box-sizing: border-box;
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

        #comprovante {
            background: #e9f7ef;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #b3e0c7;
            border-radius: 8px;
            text-align: left;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        #comprovante h2 {
            color: #28a745;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.8em;
            text-align: center;
        }

        #comprovante p {
            margin-bottom: 10px;
            font-size: 1.05em;
            line-height: 1.6;
        }

        #comprovante p strong {
            color: #333;
            display: inline-block;
            min-width: 120px;
        }

        #comprovante button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
            transition: background-color 0.3s ease;
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        #comprovante button:hover {
            background-color: #0056b3;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #comprovante, #comprovante * {
                visibility: visible;
            }
            #comprovante {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
                box-shadow: none;
                border: none;
            }
            nav, footer, header, .message, form, .carro-info, #comprovante button {
                display: none;
            }
            #comprovante h2, #comprovante p {
                text-align: left;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Reserva de Carro</h1>
        <p>Finalize sua reserva!</p>
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
            <?php if ($carro): ?>
                <div class="carro-info">
                    <h2><?= htmlspecialchars($carro['modelo']) ?> - <?= htmlspecialchars($carro['marca']) ?></h2>
                    <img src="img/<?= htmlspecialchars($carro['imagem']) ?>" alt="<?= htmlspecialchars($carro['modelo']) ?>">
                    <p><strong>Ano:</strong> <?= htmlspecialchars($carro['ano']) ?></p>
                    <p><strong>Preço Diária:</strong> R$ <?= number_format($carro['preco_diaria'], 2, ',', '.') ?></p>
                </div>

                <?php if (!$reserva_confirmada): ?>
                    <form method="post">
                        <label for="data_inicio">Data de Início:</label>
                        <input type="date" id="data_inicio" name="data_inicio" required min="<?= date('Y-m-d') ?>"><br>

                        <label for="data_fim">Data de Fim:</label>
                        <input type="date" id="data_fim" name="data_fim" required min="<?= date('Y-m-d') ?>"><br>

                        <label for="forma_pagamento">Forma de Pagamento:</label>
                        <select id="forma_pagamento" name="forma_pagamento" required>
                            <?php if (!empty($formas_pagamento)): ?>
                                <?php foreach ($formas_pagamento as $fp): ?>
                                    <option value="<?= htmlspecialchars($fp['id']) ?>">
                                        <?= htmlspecialchars($fp['tipo_pagamento']) ?> - <?= htmlspecialchars($fp['detalhes']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled selected>Nenhuma forma de pagamento cadastrada</option>
                            <?php endif; ?>
                        </select><br>

                        <button type="submit" class="botao" <?= empty($formas_pagamento) ? 'disabled' : '' ?>>
                            Confirmar Reserva
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($reserva_confirmada): ?>
                    <div id="comprovante">
                        <h2>Comprovante de Reserva</h2>
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></p>
                        <p><strong>Carro:</strong> <?= htmlspecialchars($carro['modelo']) ?> - <?= htmlspecialchars($carro['marca']) ?></p>
                        <p><strong>Período:</strong> <?= htmlspecialchars((new DateTime($data_inicio_reserva))->format('d/m/Y')) ?> até <?= htmlspecialchars((new DateTime($data_fim_reserva))->format('d/m/Y')) ?></p>
                        <p><strong>Preço Total:</strong> R$ <?= number_format($preco_total_reserva, 2, ',', '.') ?></p>
                        <p><strong>Data da Reserva:</strong> <?= date('d/m/Y H:i') ?></p>
                        <button onclick="window.print()">Imprimir Comprovante</button>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>