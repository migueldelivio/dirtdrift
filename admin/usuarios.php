
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/admin_auth.php';
require_once 'includes/admin_auth.php';

$search_term = $_GET['q'] ?? '';
$params = [];
// Query completa selecionando todas as colunas necessárias
$sql = "SELECT id, nome_completo, nome_exibicao, email, tipo_usuario, mensalidade_status, conta_status 
        FROM usuarios 
        WHERE is_admin = 0"; // Garante que administradores não apareçam na lista

if (!empty($search_term)) {
    $sql .= " AND (nome_completo LIKE ? OR email LIKE ? OR nome_exibicao LIKE ?)";
    $params = ["%$search_term%", "%$search_term%", "%$search_term%"];
}
$sql .= " ORDER BY criado_em DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerenciar Usuários</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Gerenciar Usuários</h2>
            
            <div class="card">
                <form method="get" action="usuarios.php" class="search-form">
                    <input type="text" name="q" placeholder="Buscar por nome, apelido ou email..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome (Apelido)</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Mensalidade</th>
                            <th>Status da Conta</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($usuario['nome_completo']); ?><br>
                                    <small>(<?php echo htmlspecialchars($usuario['nome_exibicao']); ?>)</small>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                
                                <td>
                                    <form action="processar_admin.php" method="post" class="inline-form">
                                        <input type="hidden" name="action" value="alterar_status_usuario">
                                        <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                        <input type="hidden" name="campo" value="tipo_usuario">
                                        <select name="valor" onchange="this.form.submit()">
                                            <option value="Visitante" <?php if ($usuario['tipo_usuario'] == 'Visitante') echo 'selected'; ?>>Visitante</option>
                                            <option value="Piloto" <?php if ($usuario['tipo_usuario'] == 'Piloto') echo 'selected'; ?>>Piloto</option>
                                        </select>
                                    </form>
                                </td>

                                <td>
                                    <form action="processar_admin.php" method="post" class="inline-form">
                                        <input type="hidden" name="action" value="alterar_status_usuario">
                                        <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                        <input type="hidden" name="campo" value="mensalidade_status">
                                        <select name="valor" onchange="this.form.submit()">
                                            <option value="ativa" <?php if ($usuario['mensalidade_status'] == 'ativa') echo 'selected'; ?>>Ativa</option>
                                            <option value="inativa" <?php if ($usuario['mensalidade_status'] == 'inativa') echo 'selected'; ?>>Inativa</option>
                                        </select>
                                    </form>
                                </td>

                                <td>
                                    <form action="processar_admin.php" method="post" class="inline-form">
                                        <input type="hidden" name="action" value="alterar_status_usuario">
                                        <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                        <input type="hidden" name="campo" value="conta_status">
                                        <select name="valor" onchange="this.form.submit()">
                                            <option value="pendente" <?php if ($usuario['conta_status'] == 'pendente') echo 'selected'; ?>>Pendente</option>
                                            <option value="aprovado" <?php if ($usuario['conta_status'] == 'aprovado') echo 'selected'; ?>>Aprovado</option>
                                            <option value="recusado" <?php if ($usuario['conta_status'] == 'recusado') echo 'selected'; ?>>Recusado</option>
                                        </select>
                                    </form>
                                </td>

                                <td><a href="processar_admin.php?action=resetar_senha&id=<?php echo $usuario['id']; ?>" class="btn-reset" onclick="return confirm('Deseja resetar a senha deste usuário?')">Resetar Senha</a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">Nenhum usuário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>