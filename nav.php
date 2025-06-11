<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>

    nav {
        background-color: #218838; 
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        flex-wrap: wrap; 
    }

    nav a {
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-weight: 500; 
    }

    nav a:hover,
    nav a.active {
        background-color: #218838; 
        color: white;
    }

    .nav-links,
    .nav-usuario {
        display: flex;
        align-items: center;
        gap: 15px; 
        flex-wrap: wrap; 
    }

    .nav-usuario span {
        color: white;
        margin-right: 5px; 
        font-weight: 400;
    }


    @media (max-width: 768px) {
        nav {
            flex-direction: column; 
            align-items: flex-start; 
            padding: 15px;
        }

        .nav-links,
        .nav-usuario {
            width: 100%; 
            justify-content: flex-start; 
            margin-top: 10px; 
            gap: 10px; 
        }

        .nav-usuario {
            border-top: 1px solid rgba(255, 255, 255, 0.2); 
            padding-top: 10px;
        }
    }
</style>

<nav>
    <div class="nav-links">
        <a href="index.php">Início</a>
        <a href="contato.php">Contato</a>
        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <a href="login.php">Login</a>
            <a href="cadastro.php">Cadastro</a>
        <?php endif; ?>
    </div>

    <div class="nav-usuario">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?></span>
            <a href="minhas_reservas.php">Minhas Reservas</a>
            <a href="forma_pagamento.php">Pagamentos</a>

            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>

            <a href="logout.php">Sair</a>
        <?php endif; ?>
    </div>
</nav>