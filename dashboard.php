<?php
require_once 'includes/auth.php'; 

$stmt_historico = $pdo->prepare(
    "SELECT e.titulo, e.data_evento, e.local, p.validado_em
     FROM presencas AS p
     JOIN eventos AS e ON p.id_evento = e.id
     WHERE p.id_usuario = ?
     ORDER BY e.data_evento DESC"
);
$stmt_historico->execute([$user['id']]);
$eventos_participados = $stmt_historico->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($user['nome_exibicao']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .qrcode-container { text-align: center; margin: 20px 0; padding: 20px; background-color: #1e1e1e; border-radius: 8px; }
        .key-label { text-align: center; color: var(--text-secondary); margin-top: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'includes/main_header.php'; // Cabeçalho unificado ?>

    <main class="container">
        <div class="card">
            <h2>Minhas Informações</h2>
            <div class="user-info">
                <p><strong>Nome Completo:</strong> <?php echo htmlspecialchars($user['nome_completo']); ?></p>
                <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Mensalidade:</strong> <span class="status-<?php echo strtolower($user['mensalidade_status']); ?>"><?php echo htmlspecialchars(ucfirst($user['mensalidade_status'])); ?></span></p>
            </div>
        </div>

        <div class="card">
            <h2>Ações da Conta</h2>
            <div class="form-actions">
                <a href="editar_perfil.php" class="btn">Editar Perfil e Senha</a>
                 <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
                    <a href="admin/index.php" class="btn btn-admin">Painel Admin</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card user-key">
            <h3>Sua Credencial de Acesso</h3>
            <p>Apresente este QR Code na entrada do evento para uma validação rápida.</p>
            <div class="qrcode-container">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($user['user_key']); ?>&bgcolor=1e1e1e&color=f5f5f5&qzone=1" alt="QR Code de Acesso">
            </div>
            <p class="key-label">Sua Chave (Backup):</p>
            <p class="key-text"><?php echo htmlspecialchars($user['user_key']); ?></p>
        </div>

        <div class="card">
            <h2>Meu Histórico de Presenças</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Evento</th>
                            <th>Data do Evento</th>
                            <th>Local</th>
                            <th>Check-in Realizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventos_participados) > 0): ?>
                            <?php foreach ($eventos_participados as $evento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></td>
                                <td><?php echo htmlspecialchars($evento['local']); ?></td>
                                <td><?php echo date('d/m/Y \à\s H:i', strtotime($evento['validado_em'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color: var(--text-secondary);">
                                    Você ainda não teve presença confirmada em nenhum evento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="social-links">
                <a href="https://www.instagram.com/drtdrft" target="_blank" rel="noopener noreferrer" title="Siga-nos no Instagram @drtdrft">
                    <img src="assets/img/instagram_logo.png" alt="Instagram DRTDRFT" class="social-icon">
                </a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> DRTDRFT CONTROL. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>