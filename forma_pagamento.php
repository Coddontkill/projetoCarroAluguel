<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        if (isset($_POST['remover_pagamento'])) {
            $id_pagamento_remover = intval($_POST['id_pagamento_remover']);
            $usuario_id = $_SESSION['usuario_id'];

            $stmt = $conn->prepare("DELETE FROM formas_pagamento WHERE id = ? AND usuario_id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $id_pagamento_remover, $usuario_id);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['message'] = "Forma de pagamento removida com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Forma de pagamento não encontrada ou você não tem permissão para removê-la.";
                        $_SESSION['message_type'] = "warning";
                    }
                } else {
                    $_SESSION['message'] = "Erro ao tentar remover a forma de pagamento.";
                    $_SESSION['message_type'] = "error";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Erro ao preparar a consulta de remoção.";
                $_SESSION['message_type'] = "error";
            }
        }
        else if (isset($_POST['cadastrar_pagamento'])) {
            $tipo_pagamento = htmlspecialchars(trim($_POST['tipo_pagamento']));
            $detalhes = htmlspecialchars(trim($_POST['detalhes']));
            $usuario_id = $_SESSION['usuario_id'];

            $valid_types = ['Cartão de Crédito', 'Cartão de Débito', 'Pix'];
            if (empty($tipo_pagamento) || empty($detalhes)) {
                $_SESSION['message'] = "Por favor, preencha todos os campos.";
                $_SESSION['message_type'] = "error";
            } else if (!in_array($tipo_pagamento, $valid_types)) {
                $_SESSION['message'] = "Tipo de pagamento inválido.";
                $_SESSION['message_type'] = "error";
            } else if (strlen($detalhes) < 4 && $tipo_pagamento !== 'Pix') {
                $_SESSION['message'] = "Detalhes (número) muito curtos.";
                $_SESSION['message_type'] = "error";
            } else {
                $stmt = $conn->prepare("INSERT INTO formas_pagamento (usuario_id, tipo_pagamento, detalhes) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("iss", $usuario_id, $tipo_pagamento, $detalhes);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Forma de pagamento cadastrada com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao cadastrar forma de pagamento: " . $stmt->error;
                        $_SESSION['message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = "Erro ao preparar a consulta de cadastro: " . $conn->error;
                    $_SESSION['message_type'] = "error";
                }
            }
        }
    } else {
        $_SESSION['message'] = "Erro de validação CSRF. Ação não permitida.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: forma_pagamento.php");
    exit;
}

$formas_pagamento = [];
$stmt = $conn->prepare("SELECT id, tipo_pagamento, detalhes FROM formas_pagamento WHERE usuario_id = ? ORDER BY tipo_pagamento ASC, detalhes ASC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $formas_pagamento = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Erro ao buscar formas de pagamento: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Formas de Pagamento</title>
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
        h1 {
            margin: 0;
            font-size: 2.2em;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            flex-grow: 1;
        }
        h2 {
            color: #333;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group select {
            width: calc(100% - 0px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }
        .form-group button {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .form-group button:hover {
            background-color: #218838;
        }
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        hr {
            border: 0;
            height: 1px;
            background: #eee;
            margin: 30px 0;
        }
        .payment-list {
            margin-top: 20px;
        }
        .payment-list ul {
            list-style: none;
            padding: 0;
        }
        .payment-list li {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .payment-list li span {
            font-weight: 600;
            color: #333;
            flex-basis: auto;
        }
        .payment-list li small {
            color: #777;
            font-size: 0.9em;
            flex-basis: auto;
        }
        .payment-list .remove-form {
            margin: 0;
            display: inline-block;
        }
        .payment-list .remove-button {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .payment-list .remove-button:hover {
            background-color: #c82333;
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
    <h1>Formas de Pagamento</h1>
</header>

<?php include 'nav.php'; ?>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= htmlspecialchars($_SESSION['message_type']) ?>">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <h2>Cadastrar Nova Forma de Pagamento</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form-group">
            <label for="tipo_pagamento">Tipo de Pagamento:</label>
            <select id="tipo_pagamento" name="tipo_pagamento" required>
                <option value="">Selecione um tipo</option> <option value="Cartão de Crédito">Cartão de Crédito</option>
                <option value="Cartão de Débito">Cartão de Débito</option>
                <option value="Pix">Pix</option> </select>
        </div>

        <div class="form-group">
            <label for="detalhes">Detalhes (Número do cartão, chave Pix, etc.):</label>
            <input type="text" id="detalhes" name="detalhes" required placeholder="Ex: **** **** **** 1234 (Cartão), seu CPF (Pix)">
        </div>

        <div class="form-group">
            <button type="submit" name="cadastrar_pagamento">Cadastrar Forma de Pagamento</button>
        </div>
    </form>

    <hr>

    <h2>Formas de Pagamento Cadastradas</h2>
    <div class="payment-list">
        <?php if (!empty($formas_pagamento)): ?>
            <ul>
                <?php foreach ($formas_pagamento as $pagamento): ?>
                    <li>
                        <span><?= htmlspecialchars($pagamento['tipo_pagamento']) ?></span>
                        <small><?= htmlspecialchars($pagamento['detalhes']) ?></small>
                        <form action="forma_pagamento.php" method="POST" class="remove-form" onsubmit="return confirm('Tem certeza que deseja remover esta forma de pagamento?');">
                            <input type="hidden" name="id_pagamento_remover" value="<?= $pagamento['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" name="remover_pagamento" class="remove-button">Remover</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Você ainda não cadastrou nenhuma forma de pagamento.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
</footer>
</body>
</html>