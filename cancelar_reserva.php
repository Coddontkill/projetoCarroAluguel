<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Reserva não especificada.";
    exit;
}

$reserva_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];


$sql = "DELETE FROM alugueis WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $reserva_id, $usuario_id);
$stmt->execute();

header("Location: minhas_reservas.php");
exit;