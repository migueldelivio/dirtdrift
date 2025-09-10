<?php
require_once 'includes/admin_auth.php';

// Query completa e eficiente para buscar todos os dados dos eventos
$sql = "SELECT e.*,
               (SELECT COUNT(*) FROM inscricoes i WHERE i.id_evento = e.id) AS total_inscricoes,
               (SELECT COUNT(*) FROM presencas p WHERE p.id_evento = e.id) AS total_presencas
        FROM eventos AS e
        ORDER BY e.data_evento DESC";
$eventos = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerenciar Eventos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .count-link { display: block; margin-bottom: 5px; white-space: nowrap; }
        .table-wrapper table td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Gerenciar Eventos</h2>

            <div class="card">
                <h3>Criar Novo Evento</h3>
                <form action="processar_admin.php" method="post">
                    <input type="hidden" name="action" value="criar_evento">
                    <div class="form-group"><label for="titulo">Título do Evento</label><input type="text" id="titulo" name="titulo" required></div>
                    <div class="form-row">
                        <div class="form-group"><label for="data_evento">Data e Hora</label><input type="datetime-local" id="data_evento" name="data_evento" required></div>
                        <div class="form-group"><label for="local">Local</label><input type="text" id="local" name="local" required></div>
                    </div>
                    <div class="form-group"><label for="descricao">Descrição</label><textarea name="descricao" id="descricao"></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label for="status">Status do Evento</label><select id="status" name="status" required><option value="Aberto ao Público">Aberto ao Público</option><option value="Privado">Privado</option><option value="Finalizado">Finalizado</option></select></div>
                        <div class="form-group"><label for="vagas_pilotos">Vagas para Pilotos (0 = ilimitado)</label><input type="number" id="vagas_pilotos" name="vagas_pilotos" value="0" required></div>
                    </div>
                    <div class="form-group">
                        <label for="inscricoes_liberadas">Liberar botão de inscrição no site?</label>
                        <select id="inscricoes_liberadas" name="inscricoes_liberadas"><option value="0">Não (Padrão)</option><option value="1">Sim, liberar agora</option></select>
                    </div>
                    <button type="submit">Criar Evento</button>
                </form>
            </div>

            <h3>Eventos Cadastrados</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Título / Data</th>
                            <th>Status</th>
                            <th>Inscrições Liberadas?</th>
                            <th>Participantes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventos) > 0): ?>
                            <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($evento['titulo']); ?><br>
                                    <small><?php echo date('d/m/Y H:i', strtotime($evento['data_evento'])); ?></small>
                                </td>
                                
                                <td>
                                    <form action="processar_admin.php" method="post" class="inline-form">
                                        <input type="hidden" name="action" value="alterar_status_evento">
                                        <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="Aberto ao Público" <?php if($evento['status'] == 'Aberto ao Público') echo 'selected'; ?>>Aberto ao Público</option>
                                            <option value="Privado" <?php if($evento['status'] == 'Privado') echo 'selected'; ?>>Privado</option>
                                            <option value="Finalizado" <?php if($evento['status'] == 'Finalizado') echo 'selected'; ?>>Finalizado</option>
                                        </select>
                                    </form>
                                </td>

                                <td>
                                    <form action="processar_admin.php" method="post" class="inline-form">
                                        <input type="hidden" name="action" value="alterar_liberacao_inscricao">
                                        <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                        <select name="liberado" onchange="this.form.submit()">
                                            <option value="1" <?php if($evento['inscricoes_liberadas'] == 1) echo 'selected'; ?>>Sim</option>
                                            <option value="0" <?php if($evento['inscricoes_liberadas'] == 0) echo 'selected'; ?>>Não</option>
                                        </select>
                                    </form>
                                </td>
                                
                                <td>
                                    <a href="ver_inscricoes.php?evento_id=<?php echo $evento['id']; ?>" class="count-link">
                                        <strong>Inscritos:</strong> <?php echo $evento['total_inscricoes']; ?>
                                    </a>
                                    <a href="ver_presencas.php?evento_id=<?php echo $evento['id']; ?>" class="count-link">
                                        <strong>Presentes:</strong> <?php echo $evento['total_presencas']; ?>
                                    </a>
                                    <?php if ($evento['status'] != 'Finalizado'): ?>
                                        <a href="adicionar_inscricao.php?evento_id=<?php echo $evento['id']; ?>" style="font-size: 0.9em; margin-top: 8px; display: inline-block;">
                                            + Adicionar Inscrito
                                        </a>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <a href="processar_admin.php?action=deletar_evento&id=<?php echo $evento['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este evento? Todos os dados de inscrição e presença serão perdidos.')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">Nenhum evento cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>