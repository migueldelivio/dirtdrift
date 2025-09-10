<?php
require_once 'includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>Editar Perfil</h1>
            <nav><a href="dashboard.php" class="btn">Voltar ao Dashboard</a></nav>
        </div>
    </header>
    <main class="container">
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'sucesso'): ?>
                <div class="alert success">Perfil atualizado com sucesso!</div>
            <?php elseif ($_GET['status'] == 'erro_senha'): ?>
                <div class="alert error">Erro: A senha atual está incorreta.</div>
            <?php else: ?>
                <div class="alert error">Ocorreu um erro ao atualizar o perfil.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card">
            <h3>Alterar Informações</h3>
            <form action="processar_perfil.php" method="post">
                <input type="hidden" name="action" value="atualizar_info">
                <div class="form-group">
                    <label for="nome_completo">Nome Completo</label>
                    <input type="text" name="nome_completo" id="nome_completo" value="<?php echo htmlspecialchars($user['nome_completo']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nome_exibicao">Nome de Exibição (Apelido)</label>
                    <input type="text" name="nome_exibicao" id="nome_exibicao" value="<?php echo htmlspecialchars($user['nome_exibicao']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Informações</button>
            </form>
        </div>

        <div class="card">
            <h3>Alterar Senha</h3>
            <form action="processar_perfil.php" method="post">
                <input type="hidden" name="action" value="mudar_senha">
                <div class="form-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" name="senha_atual" id="senha_atual" required>
                </div>
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" name="nova_senha" id="nova_senha" required>
                </div>
                <button type="submit" class="btn btn-primary">Alterar Senha</button>
            </form>
        </div>
    </main>
</body>
</html>