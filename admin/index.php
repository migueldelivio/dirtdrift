<?php
require_once 'includes/admin_auth.php';

$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_admin = 0")->fetchColumn();
$total_pilotos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'Piloto'")->fetchColumn();
$total_eventos = $pdo->query("SELECT COUNT(*) FROM eventos")->fetchColumn();

$stmt_proximo_evento = $pdo->query("SELECT titulo, data_evento FROM eventos WHERE status != 'Finalizado' ORDER BY data_evento ASC LIMIT 1");
$proximo_evento = $stmt_proximo_evento->fetch();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background-color: var(--background-secondary);
            padding: 25px;
            border-radius: 8px;
            border-left: 5px solid var(--accent-color);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .stat-card h3 {
            margin-top: 0;
            font-size: 1.2rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        .stat-card .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text-primary);
        }
        .stat-card .stat-detail {
            margin-top: 10px;
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Dashboard</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Usuários</h3>
                    <p class="stat-number"><?php echo $total_usuarios; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total de Pilotos</h3>
                    <p class="stat-number"><?php echo $total_pilotos; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total de Eventos</h3>
                    <p class="stat-number"><?php echo $total_eventos; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Próximo Evento</h3>
                    <?php if ($proximo_evento): ?>
                        <p class="stat-number" style="font-size: 1.5rem;"><?php echo htmlspecialchars($proximo_evento['titulo']); ?></p>
                        <p class="stat-detail"><?php echo date('d/m/Y \à\s H:i', strtotime($proximo_evento['data_evento'])); ?></p>
                    <?php else: ?>
                        <p class="stat-number">Nenhum</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <h3>Ações Rápidas</h3>
                <div class="form-actions">
                    <a href="eventos.php" class="btn">Gerenciar Eventos</a>
                    <a href="usuarios.php" class="btn">Gerenciar Usuários</a>
                    <a href="patrocinadores.php" class="btn">Gerenciar Patrocinadores</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>