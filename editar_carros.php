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

$carro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_carro'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $id = intval($_POST['id']);
        $modelo = htmlspecialchars(trim($_POST['modelo']));
        $marca = htmlspecialchars(trim($_POST['marca']));
        $ano = intval($_POST['ano']);
        $preco_diaria = floatval(str_replace(',', '.', $_POST['preco_diaria']));
        $imagem_atual = htmlspecialchars(trim($_POST['imagem_atual'] ?? ''));

        if (empty($modelo) || empty($marca) || $preco_diaria <= 0 || $ano <= 0) {
            $_SESSION['message'] = "Todos os campos (Modelo, Marca, Ano, Preço da Diária) são obrigatórios e devem ser válidos.";
            $_SESSION['message_type'] = "error";
        } else {
            $imagem_nova = $imagem_atual;

            if (isset($_FILES['nova_imagem']) && $_FILES['nova_imagem']['error'] === UPLOAD_ERR_OK) {
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                $nome_original = $_FILES['nova_imagem']['name'];
                $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

                if (in_array($extensao, $extensoes_permitidas)) {
                    $imagem_nova = uniqid('carro_') . '.' . $extensao;
                    $caminho_destino = "img/" . $imagem_nova;

                    if (!is_dir('img')) {
                        mkdir('img', 0777, true);
                    }

                    if (move_uploaded_file($_FILES['nova_imagem']['tmp_name'], $caminho_destino)) {
                        if (!empty($imagem_atual) && file_exists("img/" . $imagem_atual)) {
                            unlink("img/" . $imagem_atual);
                        }
                    } else {
                        $_SESSION['message'] = "Erro ao fazer upload da nova imagem.";
                        $_SESSION['message_type'] = "error";
                        $imagem_nova = $imagem_atual;
                    }
                } else {
                    $_SESSION['message'] = "Tipo de arquivo de nova imagem não permitido. Apenas JPG, JPEG, PNG, GIF.";
                    $_SESSION['message_type'] = "error";
                }
            } else if ($_FILES['nova_imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['message'] = "Erro no upload da nova imagem: " . $_FILES['nova_imagem']['error'];
                $_SESSION['message_type'] = "error";
            }

            if (!isset($_SESSION['message_type']) || $_SESSION['message_type'] !== 'error') {
                $stmt = $conn->prepare("UPDATE carros SET modelo = ?, marca = ?, ano = ?, preco_diaria = ?, imagem = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("ssidsi", $modelo, $marca, $ano, $preco_diaria, $imagem_nova, $id);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Carro atualizado com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao atualizar o carro no banco de dados: " . $stmt->error;
                        $_SESSION['message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = "Erro ao preparar a consulta de atualização: " . $conn->error;
                    $_SESSION['message_type'] = "error";
                }
            }
        }
    } else {
        $_SESSION['message'] = "Erro de validação CSRF. Ação não permitida.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: editar_carros.php?id=" . $id);
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, modelo, marca, ano, preco_diaria, imagem FROM carros WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $carro = $result->fetch_assoc();
        } else {
            $_SESSION['message'] = "Carro não encontrado.";
            $_SESSION['message_type'] = "error";
            header("Location: gerenciar_carro.php");
            exit;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erro ao preparar a consulta para buscar carro: " . $conn->error;
        $_SESSION['message_type'] = "error";
        header("Location: gerenciar_carro.php");
        exit;
    }
} else if (!isset($_POST['atualizar_carro'])) {
    $_SESSION['message'] = "ID do carro não fornecido para edição.";
    $_SESSION['message_type'] = "warning";
    header("Location: gerenciar_carro.php");
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Carro - FlexCar</title>
    
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

        .current-image-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .current-image-preview img {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 10px;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #0056b3;
            text-decoration: underline;
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
        <h1>Editar Carro</h1>
        <p>Atualize as informações do veículo selecionado.</p>
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
                <h1>Editar Carro: <?= htmlspecialchars($carro['modelo']) ?></h1>
                
                <form action="editar_carros.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($carro['id']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($carro['imagem']) ?>">

                    <div class="form-group">
                        <label for="modelo">Modelo:</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($carro['modelo']) ?>" required placeholder="Ex: Onix, HB20, Corolla">
                    </div>

                    <div class="form-group">
                        <label for="marca">Marca:</label>
                        <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($carro['marca']) ?>" required placeholder="Ex: Chevrolet, Hyundai, Toyota">
                    </div>

                    <div class="form-group">
                        <label for="ano">Ano:</label>
                        <input type="number" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($carro['ano']) ?>" required placeholder="Ex: <?= date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label for="preco_diaria">Preço da Diária (R$):</label>
                        <input type="number" id="preco_diaria" name="preco_diaria" step="0.01" min="0.01" value="<?= htmlspecialchars($carro['preco_diaria']) ?>" required placeholder="Ex: 85.50">
                    </div>

                    <div class="form-group">
                        <label>Imagem Atual:</label>
                        <div class="current-image-preview">
                            <?php if (!empty($carro['imagem'])): ?>
                                <img src="img/<?= htmlspecialchars($carro['imagem']) ?>" alt="Imagem atual do carro">
                            <?php else: ?>
                                <p>Nenhuma imagem atual.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nova_imagem">Nova Imagem (opcional):</label>
                        <input type="file" id="nova_imagem" name="nova_imagem" accept="image/jpeg, image/png, image/gif">
                        <small style="color: #666;">Envie uma nova imagem apenas se deseja substituir a atual.</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="atualizar_carro" class="botao">Atualizar Carro</button>
                    </div>
                </form>
            <?php else: ?>
                <p style="text-align: center;">Não foi possível carregar os dados do carro para edição. Por favor, retorne à página de gerenciamento de carros.</p>
            <?php endif; ?>

            <p style="text-align: center;">
                <a href="gerenciar_carro.php" class="back-link">&larr; Voltar para Gerenciar Carros</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>
</body>
</html>