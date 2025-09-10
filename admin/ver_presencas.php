<?php
require_once 'includes/admin_auth.php';

$evento_id = filter_input(INPUT_GET, 'evento_id', FILTER_VALIDATE_INT);
if (!$evento_id) {
    header("Location: eventos.php");
    exit();
}

$stmt_evento = $pdo->prepare("SELECT titulo FROM eventos WHERE id = ?");
$stmt_evento->execute([$evento_id]);
$evento = $stmt_evento->fetch();

if (!$evento) {
    header("Location: eventos.php");
    exit();
}

$stmt_presencas = $pdo->prepare(
    "SELECT u.nome_completo, u.nome_exibicao, u.email, u.tipo_usuario, p.validado_em
     FROM presencas AS p
     JOIN usuarios AS u ON p.id_usuario = u.id
     WHERE p.id_evento = ?
     ORDER BY u.tipo_usuario DESC, p.validado_em DESC"
);
$stmt_presencas->execute([$evento_id]);
$presencas = $stmt_presencas->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Presenças - <?php echo htmlspecialchars($evento['titulo']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Presenças Confirmadas</h2>
            <h3 style="color: var(--accent-color); margin-top: -15px; margin-bottom: 20px;">
                Evento: <?php echo htmlspecialchars($evento['titulo']); ?>
            </h3>

            <a href="eventos.php" class="btn" style="margin-bottom: 20px;">&larr; Voltar para Eventos</a>
            
            <div class="card">
                <h3>Total de Participantes: <?php echo count($presencas); ?></h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome Completo</th>
                                <th>Apelido</th>
                                <th>Tipo</th> <th>Email</th>
                                <th>Horário do Check-in</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($presencas) > 0): ?>
                                <?php foreach ($presencas as $participante): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participante['nome_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($participante['nome_exibicao']); ?></td>
                                    
                                    <td>
                                        <span class="user-type-<?php echo strtolower($participante['tipo_usuario']); ?>">
                                            <?php echo htmlspecialchars($participante['tipo_usuario']); ?>
                                        </span>
                                    </td>

                                    <td><?php echo htmlspecialchars($participante['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($participante['validado_em'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; color: var(--text-secondary);">Nenhuma presença confirmada para este evento ainda.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>