<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['message'] = "Você não tem permissão para acessar esta página.";
    $_SESSION['message_type'] = "error";
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carro'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $modelo = htmlspecialchars(trim($_POST['modelo']));
        $marca = htmlspecialchars(trim($_POST['marca']));
        $ano = isset($_POST['ano']) ? intval($_POST['ano']) : 0; 
        $preco_diaria = floatval(str_replace(',', '.', $_POST['preco_diaria']));

        if (empty($modelo) || empty($marca) || $preco_diaria <= 0 || $ano <= 0) {
            $_SESSION['message'] = "Todos os campos (Modelo, Marca, Ano, Preço da Diária) são obrigatórios e devem ser válidos.";
            $_SESSION['message_type'] = "error";
        } else {
            $imagem_nome = '';
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                $nome_original = $_FILES['imagem']['name'];
                $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

                if (in_array($extensao, $extensoes_permitidas)) {
                    $imagem_nome = uniqid('carro_') . '.' . $extensao;
                    $caminho_destino = "img/" . $imagem_nome;

                    if (!is_dir('img')) {
                        mkdir('img', 0777, true);
                    }

                    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_destino)) {
                        $_SESSION['message'] = "Erro ao fazer upload da imagem.";
                        $_SESSION['message_type'] = "error";
                        $imagem_nome = '';
                    }
                } else {
                    $_SESSION['message'] = "Tipo de arquivo de imagem não permitido. Apenas JPG, JPEG, PNG, GIF.";
                    $_SESSION['message_type'] = "error";
                }
            } else if ($_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['message'] = "Erro no upload da imagem: " . $_FILES['imagem']['error'];
                $_SESSION['message_type'] = "error";
            } else {
                $_SESSION['message'] = "É necessário enviar uma imagem para o carro.";
                $_SESSION['message_type'] = "error";
            }

            if (!isset($_SESSION['message_type']) || $_SESSION['message_type'] !== 'error') {
                $stmt = $conn->prepare("INSERT INTO carros (modelo, marca, ano, preco_diaria, imagem) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssids", $modelo, $marca, $ano, $preco_diaria, $imagem_nome);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Carro adicionado com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao adicionar carro ao banco de dados: " . $stmt->error;
                        $_SESSION['message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = "Erro ao preparar a consulta de adição: " . $conn->error;
                    $_SESSION['message_type'] = "error";
                }
            }
        }
    } else {
        $_SESSION['message'] = "Erro de validação CSRF. Ação não permitida.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: adicionar_carro.php");
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Carro - FlexCar</title>
    
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
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            flex-grow: 1;
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
        }

        form {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            font-size: 1em;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="file"]:focus {
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
        <h1>Adicionar Novo Carro</h1>
        <p>Preencha os dados para incluir um novo veículo no site.</p>
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
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo">
                </div>

                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca">
                </div>

                <div class="form-group">
                    <label for="ano">Ano:</label>
                    <input type="number" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>">
                </div>

                <div class="form-group">
                    <label for="preco_diaria">Preço da Diária (R$):</label>
                    <input type="number" id="preco_diaria" name="preco_diaria" step="0.01" min="0.01">
                </div>

                <div class="form-group">
                    <label for="imagem">Imagem do Carro:</label>
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg, image/png, image/gif" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="adicionar_carro" class="botao">Adicionar Carro</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>