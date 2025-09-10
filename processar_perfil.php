<?php
require_once 'includes/auth.php'; // Garante que o usuário está logado e carrega $user

$action = $_POST['action'] ?? null;
$user_id = $_SESSION['user_id'];

if ($action === 'atualizar_info') {
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $nome_exibicao = trim($_POST['nome_exibicao'] ?? '');

    if (!empty($nome_completo) && !empty($nome_exibicao)) {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome_completo = ?, nome_exibicao = ? WHERE id = ?");
            $stmt->execute([$nome_completo, $nome_exibicao, $user_id]);
            header("Location: editar_perfil.php?status=sucesso");
            exit();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            header("Location: editar_perfil.php?status=erro");
            exit();
        }
    }
}

if ($action === 'mudar_senha') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';

    // Verifica se a senha atual está correta
    if (password_verify($senha_atual, $user['senha_hash'])) {
        // Se estiver correta, atualiza para a nova senha
        $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
        $stmt->execute([$novo_hash, $user_id]);
        header("Location: editar_perfil.php?status=sucesso");
        exit();
    } else {
        // Se a senha atual estiver errada, redireciona com erro
        header("Location: editar_perfil.php?status=erro_senha");
        exit();
    }
}

// Se nenhuma ação for válida, volta para a página de edição
header("Location: editar_perfil.php");
exit();
?>