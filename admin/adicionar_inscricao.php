<?php
require_once 'includes/admin_auth.php';

// --- ETAPA 1: VALIDAR O EVENTO ---
// Pega o ID do evento da URL e garante que é um número válido.
$evento_id = filter_input(INPUT_GET, 'evento_id', FILTER_VALIDATE_INT);
if (!$evento_id) {
    header("Location: eventos.php?status=erro_id_invalido");
    exit();
}

// Busca os detalhes do evento para usar no título da página e confirmar que ele existe.
$stmt_evento = $pdo->prepare("SELECT titulo FROM eventos WHERE id = ?");
$stmt_evento->execute([$evento_id]);
$evento = $stmt_evento->fetch();

// Se o evento não for encontrado no banco de dados, volta para a lista de eventos.
if (!$evento) {
    header("Location: eventos.php?status=evento_nao_encontrado");
    exit();
}

// --- ETAPA 2: INICIALIZAR VARIÁVEIS ---
$resultados_busca = [];
$termo_busca = trim($_GET['q'] ?? '');
$mensagem = '';
$tipo_mensagem = '';

// --- ETAPA 3: PROCESSAR A INSCRIÇÃO (QUANDO O BOTÃO "INSCREVER" É CLICADO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inscrever_usuario') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($user_id) {
        try {
            // Tenta inserir o registro. ON DUPLICATE KEY UPDATE previne um erro fatal se já existir.
            $stmt_insert = $pdo->prepare("INSERT INTO inscricoes (id_usuario, id_evento) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_usuario = VALUES(id_usuario)");
            $stmt_insert->execute([$user_id, $evento_id]);
            $mensagem = "Usuário inscrito com sucesso!";
            $tipo_mensagem = 'success';
        } catch (PDOException $e) {
            $mensagem = "Erro de banco de dados ao tentar inscrever o usuário.";
            $tipo_mensagem = 'error';
            error_log("Erro em adicionar_inscricao.php ao inscrever: " . $e->getMessage());
        }
    }
}

// --- ETAPA 4: PROCESSAR A BUSCA (QUANDO O FORMULÁRIO DE BUSCA É USADO) ---
if (!empty($termo_busca)) {
    // Esta query busca por usuários que correspondem ao termo de busca E que ainda NÃO estão inscritos neste evento específico.
    $sql = "SELECT u.id, u.nome_completo, u.email, u.nome_exibicao
            FROM usuarios u
            LEFT JOIN inscricoes i ON u.id = i.id_usuario AND i.id_evento = ?
            WHERE u.is_admin = 0 AND i.id IS NULL AND (u.nome_completo LIKE ? OR u.email LIKE ? OR u.nome_exibicao LIKE ?)";
    
    $stmt_busca = $pdo->prepare($sql);
    // Passa o evento_id como primeiro parâmetro para o LEFT JOIN
    $stmt_busca->execute([$evento_id, "%$termo_busca%", "%$termo_busca%", "%$termo_busca%"]);
    $resultados_busca = $stmt_busca->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Inscritos ao Evento</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Adicionar Inscrito ao Evento</h2>
            <h3 style="color: var(--accent-color); margin-top: -15px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($evento['titulo']); ?>
            </h3>
            <a href="eventos.php" class="btn" style="margin-bottom: 20px;">&larr; Voltar para a Lista de Eventos</a>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo $tipo_mensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Buscar Usuário para Inscrever</h3>
                <p>Busque por nome, apelido ou e-mail. Apenas usuários que ainda não estão inscritos neste evento aparecerão na busca.</p>
                <form action="adicionar_inscricao.php" method="get">
                    <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
                    <input type="text" name="q" placeholder="Digite aqui para buscar..." value="<?php echo htmlspecialchars($termo_busca); ?>" required>
                    <button type="submit">Buscar</button>
                </form>
            </div>

            <?php if (!empty($resultados_busca)): ?>
            <div class="card">
                <h3>Resultados da Busca</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Nome (Apelido)</th><th>Email</th><th>Ação</th></tr></thead>
                        <tbody>
                            <?php foreach ($resultados_busca as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nome_completo']); ?> (<?php echo htmlspecialchars($usuario['nome_exibicao']); ?>)</td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td>
                                        <form action="adicionar_inscricao.php?evento_id=<?php echo $evento_id; ?>&q=<?php echo urlencode($termo_busca); ?>" method="post">
                                            <input type="hidden" name="action" value="inscrever_usuario">
                                            <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="btn btn-primary">Inscrever</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif(!empty($termo_busca)): ?>
                 <div class="card"><p>Nenhum usuário encontrado com o termo "<?php echo htmlspecialchars($termo_busca); ?>" ou todos os encontrados já estão inscritos.</p></div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>