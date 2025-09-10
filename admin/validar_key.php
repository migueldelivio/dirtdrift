<?php
require_once 'includes/admin_auth.php';

// --- INÍCIO DO BLOCO DE LÓGICA PHP ---
$resultados = [];
$termo_busca = trim($_GET['q'] ?? '');
$mensagem = '';
$tipo_mensagem = '';

// Busca eventos abertos para o formulário de confirmação.
$stmt_eventos = $pdo->query("SELECT id, titulo FROM eventos WHERE status IN ('Aberto ao Público', 'Privado') ORDER BY data_evento DESC");
$eventos_abertos = $stmt_eventos->fetchAll();

// Se um termo de busca foi enviado, executa a query.
if (!empty($termo_busca)) {
    $sql = "SELECT id, nome_completo, nome_exibicao, email, tipo_usuario FROM usuarios WHERE user_key = ? OR nome_completo LIKE ? OR nome_exibicao LIKE ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$termo_busca, "%$termo_busca%", "%$termo_busca%", $termo_busca]);
    $resultados = $stmt->fetchAll();

    if (empty($resultados)) {
        $mensagem = "Nenhum usuário encontrado para: " . htmlspecialchars($termo_busca);
        $tipo_mensagem = 'error';
    }
}

// --- LÓGICA DE CONFIRMAÇÃO DE PRESENÇA (CORRIGIDA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirmar_presenca') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $evento_id = filter_input(INPUT_POST, 'evento_id', FILTER_VALIDATE_INT); 

    if ($user_id && $evento_id) {
        try {
            // 1. Verifica se a presença já não foi registrada
            $stmt_check = $pdo->prepare("SELECT id FROM presencas WHERE id_usuario = ? AND id_evento = ?");
            $stmt_check->execute([$user_id, $evento_id]);

            if ($stmt_check->fetch()) {
                $mensagem = "Presença já registrada para este usuário neste evento.";
                $tipo_mensagem = 'error';
            } else {
                // 2. Tenta inserir o novo registro de presença
                $stmt_insert = $pdo->prepare("INSERT INTO presencas (id_usuario, id_evento) VALUES (?, ?)");
                if ($stmt_insert->execute([$user_id, $evento_id])) {
                    // 3. Se a inserção foi bem-sucedida, define a mensagem de sucesso
                    $mensagem = "Presença confirmada com sucesso!";
                    $tipo_mensagem = 'success';
                    registrar_log('Presença Confirmada', "Usuário ID: {$user_id}, Evento ID: {$evento_id}");
                } else {
                    // Se execute() retornar false por algum motivo
                    throw new Exception("A operação de inserção falhou sem um erro específico.");
                }
            }
        } catch (PDOException $e) {
            // 4. Se ocorrer um erro no banco de dados, captura e exibe uma mensagem de erro real
            error_log("Erro ao confirmar presença: " . $e->getMessage());
            $mensagem = "Erro no banco de dados ao tentar marcar a presença. Por favor, tente novamente.";
            $tipo_mensagem = 'error';
        } catch (Exception $e) {
            $mensagem = "Ocorreu um erro inesperado: " . $e->getMessage();
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = "Erro: Dados inválidos recebidos. Tente novamente.";
        $tipo_mensagem = 'error';
    }
    // Limpa a busca para o próximo check-in, mas mantém a mensagem para o usuário ver
    $resultados = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Validar Credencial</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    </head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Validação de Credenciais</h2>

            <div class="card">
                <div id="qr-video-container"><video id="qr-video"></video></div>
                <div id="qr-status-message" style="text-align:center; color: var(--text-secondary); margin-top:15px; font-weight: bold;"></div>
            </div>
            <div class="card">
                <h3>Busca Manual</h3>
                <form action="validar_key.php" method="get">
                    <input type="text" id="search-input" name="q" placeholder="Buscar usuário..." value="<?php echo htmlspecialchars($termo_busca); ?>" required autocomplete="off">
                    <button type="submit">Buscar</button>
                </form>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo $tipo_mensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <?php if (!empty($resultados)): ?>
            <div class="search-results">
                <h3>Resultados da Busca</h3>
                <?php foreach ($resultados as $usuario): ?>
                    <div class="card">
                        <div class="user-info">
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome_completo']); ?> (<?php echo htmlspecialchars($usuario['nome_exibicao']); ?>)</p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                            <p><strong>Tipo:</strong> <span class="user-type-<?php echo strtolower($usuario['tipo_usuario']); ?>"><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></span></p>
                        </div>
                        <div class="user-action">
                            <form action="validar_key.php" method="post">
                                <input type="hidden" name="action" value="confirmar_presenca">
                                <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                <?php if (count($eventos_abertos) > 0): ?>
                                    <div class="form-group">
                                        <label for="evento_id_<?php echo $usuario['id']; ?>">Confirmar presença em:</label>
                                        <select name="evento_id" id="evento_id_<?php echo $usuario['id']; ?>" required>
                                            <?php foreach ($eventos_abertos as $evento): ?>
                                                <option value="<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['titulo']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Confirmar Presença</button>
                                <?php else: ?>
                                    <p style="color: var(--warning-color);">Nenhum evento aberto para check-in.</p>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script type="module">
        import QrScanner from "https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js";
        const videoElem = document.getElementById('qr-video');
        const statusMessage = document.getElementById('qr-status-message');
        const onScanSuccess = result => {
            qrScanner.stop();
            window.location.href = `validar_key.php?q=${encodeURIComponent(result.data)}`;
        };
        const qrScanner = new QrScanner(videoElem, onScanSuccess, { highlightScanRegion: true, highlightCodeOutline: true });
        statusMessage.innerText = "Iniciando câmera...";
        qrScanner.start().catch(err => {
            console.error(err);
            statusMessage.innerHTML = `<div class="alert error"><strong>Falha ao acessar a câmera.</strong></div>`;
        });
    </script>
</body>
</html>