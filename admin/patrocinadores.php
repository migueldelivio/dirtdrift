<?php
require_once 'includes/admin_auth.php';

$error_message = null;
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}

$stmt = $pdo->query("SELECT * FROM patrocinadores ORDER BY ordem ASC, nome_empresa ASC");
$patrocinadores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerenciar Patrocinadores</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sponsor-logo-preview { max-width: 100px; height: auto; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Gerenciar Patrocinadores</h2>

            <?php if ($error_message): ?>
                <div class="alert error">
                    <strong>Erro:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <a href="adicionar_patrocinador.php" class="btn btn-primary">Adicionar Novo Patrocinador</a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome da Empresa</th>
                            <th>Logo</th>
                            <th>Link do Site</th>
                            <th>Ordem</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patrocinadores as $patrocinador): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patrocinador['nome_empresa']); ?></td>
                            <td>
                                <?php if ($patrocinador['logo_path']): ?>
                                    <img src="../<?php echo htmlspecialchars($patrocinador['logo_path']); ?>" alt="<?php echo htmlspecialchars($patrocinador['nome_empresa']); ?>" class="sponsor-logo-preview">
                                <?php else: ?>
                                    Sem Logo
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($patrocinador['link_site']): ?>
                                    <a href="<?php echo htmlspecialchars($patrocinador['link_site']); ?>" target="_blank" rel="noopener noreferrer">
                                        Link
                                    </a>
                                <?php else: ?>
                                    Sem Link
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($patrocinador['ordem']); ?></td>
                            <td><?php echo $patrocinador['ativo'] ? 'Sim' : 'Não'; ?></td>
                            <td>
                                <a href="editar_patrocinador.php?id=<?php echo $patrocinador['id']; ?>" class="btn">Editar</a>
                                <a href="processar_patrocinador.php?action=deletar&id=<?php echo $patrocinador['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza?')">Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>