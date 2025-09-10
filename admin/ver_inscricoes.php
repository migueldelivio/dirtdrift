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

$stmt_inscricoes = $pdo->prepare(
    "SELECT 
        u.nome_completo, u.nome_exibicao, u.email, u.tipo_usuario, 
        i.data_inscricao, i.carro_modelo, i.carro_ano, i.motorizacao, i.equipe
     FROM inscricoes AS i
     JOIN usuarios AS u ON i.id_usuario = u.id
     WHERE i.id_evento = ?
     ORDER BY u.tipo_usuario DESC, i.data_inscricao ASC"
);
$stmt_inscricoes->execute([$evento_id]);
$inscricoes = $stmt_inscricoes->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Inscrições - <?php echo htmlspecialchars($evento['titulo']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .detalhes-veiculo {
            font-size: 0.9em;
            color: var(--text-secondary);
            list-style-type: none;
            padding-left: 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Lista de Inscritos</h2>
            <h3 style="color: var(--accent-color); margin-top: -15px; margin-bottom: 20px;">
                Evento: <?php echo htmlspecialchars($evento['titulo']); ?>
            </h3>

            <a href="eventos.php" class="btn" style="margin-bottom: 20px;">&larr; Voltar para Eventos</a>
            
            <div class="card">
                <h3>Total de Inscritos: <?php echo count($inscricoes); ?></h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome Completo (Apelido)</th>
                                <th>Tipo</th>
                                <th>Email</th>
                                <th>Data Inscrição</th>
                                <th>Detalhes do Veículo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($inscricoes) > 0): ?>
                                <?php foreach ($inscricoes as $inscrito): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($inscrito['nome_completo']); ?><br>
                                        <small>(<?php echo htmlspecialchars($inscrito['nome_exibicao']); ?>)</small>
                                    </td>
                                    <td>
                                        <span class="user-type-<?php echo strtolower($inscrito['tipo_usuario']); ?>">
                                            <?php echo htmlspecialchars($inscrito['tipo_usuario']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($inscrito['data_inscricao'])); ?></td>
                                    <td>
                                        <?php if ($inscrito['tipo_usuario'] == 'Piloto' && $inscrito['carro_modelo']): ?>
                                            <ul class="detalhes-veiculo">
                                                <li><strong>Carro:</strong> <?php echo htmlspecialchars($inscrito['carro_modelo']); ?> (<?php echo htmlspecialchars($inscrito['carro_ano']); ?>)</li>
                                                <li><strong>Motor:</strong> <?php echo htmlspecialchars($inscrito['motorizacao']); ?></li>
                                                <?php if($inscrito['equipe']): ?>
                                                    <li><strong>Equipe:</strong> <?php echo htmlspecialchars($inscrito['equipe']); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; color: var(--text-secondary);">
                                        Nenhuma inscrição para este evento ainda.
                                    </td>
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