<?php
require_once 'includes/config.php';

// Se o usuário já estiver logado, redireciona para a página correta.
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = '';
// Lógica para exibir mensagens de erro específicas vindas da URL.
if (isset($_GET['erro'])) {
    if ($_GET['erro'] === 'cadastro_pendente') {
        $error = "Sua conta está aguardando aprovação de um administrador.";
    } elseif ($_GET['erro'] === 'cadastro_recusado') {
        $error = "Seu acesso foi recusado. Por favor, entre em contato com a organização.";
    } elseif ($_GET['erro'] === 'acesso_restrito') {
        $error = "Você precisa fazer login para acessar esta página.";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = "E-mail e senha são obrigatórios.";
    } else {
        // Busca o usuário incluindo o novo campo 'conta_status'
        $stmt = $pdo->prepare("SELECT id, nome_exibicao, senha_hash, is_admin, conta_status FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            // Se for admin, pode logar independentemente do status da conta
            if ($user['is_admin'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome_exibicao'];
                $_SESSION['is_admin'] = true;
                registrar_log('Login Admin', "Usuário (admin): {$email}");
                header("Location: admin/index.php");
                exit();
            }

            // Verifica o status da conta para usuários normais
            if ($user['conta_status'] === 'aprovado') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome_exibicao'];
                $_SESSION['is_admin'] = false;
                registrar_log('Login Usuário', "Usuário: {$email}");
                header("Location: dashboard.php");
                exit();
            } elseif ($user['conta_status'] === 'pendente') {
                header("Location: login.php?erro=cadastro_pendente");
                exit();
            } else { // Status é 'recusado'
                header("Location: login.php?erro=cadastro_recusado");
                exit();
            }
            
        } else {
            $error = "E-mail ou senha inválidos.";
            registrar_log('Falha de Login', "Tentativa para: {$email}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - DRTDRFT CONTROL</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card auth-form">
            <h2>Login</h2>
            <p>Acesse seu painel.</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert error"><p><?php echo htmlspecialchars($error); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['registro']) && $_GET['registro'] == 'sucesso'): ?>
                <div class="alert success"><p>Registro concluído! Aguarde a aprovação de um administrador para fazer login.</p></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" id="senha" required>
                </div>
                <div class="form-actions" style="border-top: none; padding-top: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
                </div>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <a href="#">Esqueci minha senha</a>
            </div>

            <hr style="border-color: var(--border-color); margin: 20px 0;">

            <div style="text-align: center;">
                <p>Não tem uma conta? <a href="register.php">Registre-se</a></p>
                <p><a href="index.php">Voltar para a página inicial</a></p>
            </div>
        </div>
    </div>
</body>
</html>