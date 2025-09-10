<?php
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    session_destroy();
    header("Location: ../login.php?erro=acesso_negado");
    exit();
}
?>