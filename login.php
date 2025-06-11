<?php
session_start();
require_once 'conexao.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_input = $_POST['usuario'] ?? '';
    $senha_input = $_POST['senha'] ?? '';

    if (empty($usuario_input) || empty($senha_input)) {
        $message = "Por favor, preencha todos os campos.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE email = ? OR cpf = ?");
        
        if ($stmt) {
            $stmt->bind_param("ss", $usuario_input, $usuario_input);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {
                $usuarioDB = $resultado->fetch_assoc();

                if (password_verify($senha_input, $usuarioDB['senha'])) {
                    $_SESSION['usuario_id'] = $usuarioDB['id'];
                    $_SESSION['usuario_nome'] = $usuarioDB['nome'];
                    $_SESSION['usuario_tipo'] = $usuarioDB['tipo']; 

                    $_SESSION['message'] = "Login realizado com sucesso! Bem-vindo(a), " . htmlspecialchars($usuarioDB['nome']) . ".";
                    $_SESSION['message_type'] = "success";

                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Credenciais inválidas. Verifique seu usuário e senha.";
                    $message_type = "error";
                }
            } else {
                $message = "Credenciais inválidas. Verifique seu usuário e senha.";
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Erro interno ao preparar a consulta de login.";
            $message_type = "error";
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
    <title>Login - FlexCar</title>
    
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
            max-width: 450px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: left;
        }
        
        .container h1 {
            color: #333;
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            font-size: 2em;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            width: 100%;
        }

        form {
            width: 100%;
            padding: 0;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            font-size: 1em;
        }

        form input[type="text"],
        form input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        form input[type="text"]:focus,
        form input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .botao {
            display: block;
            width: 100%;
            background-color: #007bff;
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
            background-color: #0056b3;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
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
        <h1>Conta FlexCar</h1>
        <p>Faça login para agendar a sua reserva o quanto antes!</p>
    </header>

    <?php include 'nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Login</h1>
            <?php 
            if (!empty($message)): 
            ?>
                <div class="message <?= htmlspecialchars($message_type) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php 
            $message = ''; 
            $message_type = '';
            endif; 

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

            <form method="POST" action="">
                <label for="usuario">E-mail ou CPF:</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Seu e-mail ou CPF">

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha">

                <button type="submit" class="botao">Entrar</button>
            </form>
            <p style="text-align: center; margin-top: 20px; font-size: 0.95em; color: #555;">
                Ainda não tem uma conta? <a href="cadastro.php" style="color: #218838; text-decoration: none; font-weight: bold;">Cadastre-se aqui</a>.
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>