<?php
require_once 'includes/config.php';

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nome_exibicao = trim($_POST['nome_exibicao'] ?? '');

    // Validações
    if (empty($nome_completo) || empty($email) || empty($senha) || empty($nome_exibicao)) {
        $errors[] = "Todos os campos são obrigatórios.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Formato de e-mail inválido.";
    }
    if (strlen($senha) < 6) {
        $errors[] = "A senha deve ter no mínimo 6 caracteres.";
    }

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Este e-mail já está cadastrado.";
    }

    if (empty($errors)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $user_key = hash('sha256', $email . time() . random_bytes(10)); // Key única

        $sql = "INSERT INTO usuarios (nome_completo, email, senha_hash, nome_exibicao, user_key) VALUES (?, ?, ?, ?, ?)";
        $stmt= $pdo->prepare($sql);
        if ($stmt->execute([$nome_completo, $email, $senha_hash, $nome_exibicao, $user_key])) {
            registrar_log('Novo Registro', "Usuário: {$email}");
            header("Location: login.php?registro=sucesso");
            exit();
        } else {
            $errors[] = "Erro ao registrar. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registro - DRTDRFT CONTROL</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card auth-form">
            <h2>Criar Conta</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post">
                <input type="text" name="nome_completo" placeholder="Nome Completo" required>
                <input type="text" name="nome_exibicao" placeholder="Nome de Exibição (Apelido)" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="senha" placeholder="Senha (mínimo 6 caracteres)" required>
                <button type="submit">Registrar</button>
            </form>
            <p>Já tem uma conta? <a href="login.php">Faça login</a>.</p>
        </div>
    </div>
</body>
</html>