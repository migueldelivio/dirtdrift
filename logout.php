<?php
require_once 'includes/config.php';
$email = $_SESSION['user_nome'] ?? $_SESSION['admin_nome'] ?? 'Desconhecido';
registrar_log('Logout', "Usuário: {$email}");

session_unset();
session_destroy();

header("Location: login.php?status=logout");
exit();
?>