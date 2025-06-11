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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_carro'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $id = intval($_POST['carro_id']);
        
        $sql_verificar_reservas = "SELECT COUNT(*) FROM alugueis WHERE carro_id = ? AND data_fim >= CURDATE()";
        $stmt_verificar = $conn->prepare($sql_verificar_reservas);
        $stmt_verificar->bind_param("i", $id);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($num_reservas_ativas);
        $stmt_verificar->fetch();
        $stmt_verificar->close();

        if ($num_reservas_ativas > 0) {
            $_SESSION['message'] = "Não é possível remover o carro, pois ele possui reservas ativas ou futuras.";
            $_SESSION['message_type'] = "error";
        } else {
            $sql_get_image = "SELECT imagem FROM carros WHERE id = ?";
            $stmt_get_image = $conn->prepare($sql_get_image);
            if ($stmt_get_image) {
                $stmt_get_image->bind_param("i", $id);
                $stmt_get_image->execute();
                $result_image = $stmt_get_image->get_result();
                $carro_imagem = $result_image->fetch_assoc();
                $stmt_get_image->close();

                if ($carro_imagem && !empty($carro_imagem['imagem'])) {
                    $caminho_imagem = "img/" . $carro_imagem['imagem'];
                    if (file_exists($caminho_imagem)) {
                        unlink($caminho_imagem);
                    }
                }
            }

            $stmt = $conn->prepare("DELETE FROM carros WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['message'] = "Carro removido com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Nenhum carro encontrado com o ID fornecido.";
                        $_SESSION['message_type'] = "warning";
                    }
                } else {
                    $_SESSION['message'] = "Erro ao tentar remover o carro do banco de dados.";
                    $_SESSION['message_type'] = "error";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Erro ao preparar a consulta de remoção.";
                $_SESSION['message_type'] = "error";
            }
        }
    } else {
        $_SESSION['message'] = "Erro de validação CSRF. Ação não permitida.";
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: gerenciar_carro.php");
    exit;
}

$sql = "SELECT id, modelo, marca, ano, preco_diaria, imagem FROM carros ORDER BY marca ASC, modelo ASC";
$stmt = $conn->prepare($sql);
$carros = [];

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $carros = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gerenciar Carros - FlexCar</title>
    
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

        .relatorio-controles {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .relatorio-controles label {
            font-weight: bold;
            margin-right: 10px;
            color: #555;
        }

        .relatorio-controles input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
            font-size: 1em;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
            transition: border-color 0.3s;
        }

        .relatorio-controles input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.08), 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .tabela-relatorio {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 25px 0;
            font-size: 0.95em;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .tabela-relatorio thead tr {
            background-color: #007bff;
            color: #ffffff;
            text-align: left;
            font-weight: bold;
        }

        .tabela-relatorio th, .tabela-relatorio td {
            padding: 15px;
            border-bottom: 1px solid #dddddd;
            border-right: 1px solid #dddddd;
        }
        
        .tabela-relatorio th:last-child,
        .tabela-relatorio td:last-child {
            border-right: none;
        }

        .tabela-relatorio tbody tr:last-of-type td {
            border-bottom: none;
        }

        .tabela-relatorio tbody tr:nth-of-type(even) {
            background-color: #f8fbfd;
        }

        .tabela-relatorio tbody tr:hover {
            background-color: #e6f2ff;
        }

        .tabela-relatorio thead th[data-column] {
            cursor: pointer;
            position: relative;
        }

        .tabela-relatorio thead th[data-column]:hover {
            background-color: #0056b3;
        }

        .tabela-relatorio thead th.asc::after {
            content: ' ▲';
            font-size: 0.7em;
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
        }

        .tabela-relatorio thead th.desc::after {
            content: ' ▼';
            font-size: 0.7em;
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
        }

        .action-button {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            font-size: 0.95em;
            font-family: inherit;
            margin-right: 10px;
        }

        .action-button:hover {
            color: #c82333;
        }

        .action-link {
            color: #28a745;
            text-decoration: underline;
            font-size: 0.95em;
        }

        .action-link:hover {
            color: #218838;
        }

        .tabela-relatorio img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
            vertical-align: middle;
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
        <h1>Gerenciar Carros</h1>
        <p>Gerencie os veículos do site.</p>
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
            <div class="relatorio-controles">
                <label for="filtroTabela">Buscar no relatório:</label>
                <input type="text" id="filtroTabela" placeholder="Digite para filtrar...">
            </div>

            <table id="tabelaRelatorio" class="tabela-relatorio">
                <thead>
                    <tr>
                        <th data-column="id">ID</th>
                        <th data-column="modelo">Modelo</th>
                        <th data-column="marca">Marca</th>
                        <th data-column="ano">Ano</th>
                        <th data-column="preco_diaria">Preço da Diária</th>
                        <th>Imagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($carros)): ?>
                        <?php foreach ($carros as $carro): ?>
                            <tr>
                                <td><?= htmlspecialchars($carro['id']) ?></td>
                                <td><?= htmlspecialchars($carro['modelo']) ?></td>
                                <td><?= htmlspecialchars($carro['marca']) ?></td>
                                <td><?= htmlspecialchars($carro['ano']) ?></td>
                                <td>R$ <?= number_format($carro['preco_diaria'], 2, ',', '.') ?></td>
                                <td><img src="img/<?= htmlspecialchars($carro['imagem']) ?>" alt="<?= htmlspecialchars($carro['modelo']) ?>"></td>
                                <td>
                                    <form action="gerenciar_carro.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este carro? Esta ação é irreversível.');" style="display:inline-block;">
                                        <input type="hidden" name="carro_id" value="<?= htmlspecialchars($carro['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" name="remover_carro" class="action-button">Remover</button>
                                    </form>
                                    <a href="editar_carros.php?id=<?= htmlspecialchars($carro['id']) ?>" class="action-link">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">Nenhum carro cadastrado no momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> FlexCar. Todos os direitos reservados.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroInput = document.getElementById('filtroTabela');
        const tabela = document.getElementById('tabelaRelatorio');
        const tbody = tabela.getElementsByTagName('tbody')[0];
        let linhasTabela = Array.from(tbody.getElementsByTagName('tr'));

        filtroInput.addEventListener('keyup', function() {
            const filtro = filtroInput.value.toLowerCase();
            linhasTabela.forEach(function(linha) {
                const textoLinha = linha.textContent.toLowerCase();
                if (textoLinha.includes(filtro)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });

        const headers = tabela.tHead.rows[0].cells;
        let direcaoOrdenacao = {};

        for (let i = 0; i < headers.length - 1; i++) {
            let header = headers[i];
            let columnName = header.getAttribute('data-column');
            if (columnName) {
                header.addEventListener('click', function() {
                    const isNumeric = ['id', 'ano', 'preco_diaria'].includes(columnName);
                    const currentDir = direcaoOrdenacao[columnName] === 'asc' ? 'desc' : 'asc';
                    
                    Array.from(headers).forEach(h => {
                        h.classList.remove('asc', 'desc');
                    });
                    this.classList.add(currentDir);
                    
                    direcaoOrdenacao[columnName] = currentDir;

                    linhasTabela.sort((a, b) => {
                        let aText = a.cells[i].textContent;
                        let bText = b.cells[i].textContent;

                        if (isNumeric) {
                            aText = parseFloat(aText.replace('R$', '').replace(/\./g, '').replace(',', '.'));
                            bText = parseFloat(bText.replace('R$', '').replace(/\./g, '').replace(',', '.'));
                        } else {
                            aText = aText.toLowerCase();
                            bText = bText.toLowerCase();
                        }

                        if (aText < bText) {
                            return currentDir === 'asc' ? -1 : 1;
                        }
                        if (aText > bText) {
                            return currentDir === 'asc' ? 1 : -1;
                        }
                        return 0;
                    });

                    while (tbody.firstChild) {
                        tbody.removeChild(tbody.firstChild);
                    }
                    linhasTabela.forEach(linha => tbody.appendChild(linha));
                });
            }
        }
    });
    </script>
</body>
</html>