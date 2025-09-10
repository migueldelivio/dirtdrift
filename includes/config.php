<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'u116192033_drt');
define('DB_USER', 'u116192033_ademiro'); 
define('DB_PASS', 'Drtdrft123');     
define('CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
    die("Erro crítico no sistema. Tente novamente mais tarde.");
}

function registrar_log($acao, $detalhes = '') {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $sql = "INSERT INTO logs (acao, detalhes, ip_address) VALUES (?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$acao, $detalhes, $ip]);
    } catch (\PDOException $e) {
        error_log("Falha ao registrar log: " . $e->getMessage());
    }
}
?>