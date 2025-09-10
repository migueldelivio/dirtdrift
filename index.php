<?php
require_once 'includes/config.php';

// --- BUSCA DE DADOS ATUALIZADA ---
// Busca a nova coluna 'inscricoes_liberadas'
$stmt_eventos = $pdo->query("SELECT * FROM eventos WHERE status IN ('Aberto ao Público', 'Privado') ORDER BY data_evento ASC");
$eventos = $stmt_eventos->fetchAll();

$current_user = null;
$minhas_inscricoes = [];
if (isset($_SESSION['user_id'])) {
    $stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $current_user = $stmt_user->fetch();

    $stmt_inscricoes = $pdo->prepare("SELECT id_evento FROM inscricoes WHERE id_usuario = ?");
    $stmt_inscricoes->execute([$_SESSION['user_id']]);
    $minhas_inscricoes = $stmt_inscricoes->fetchAll(PDO::FETCH_COLUMN);
}

$stmt_patrocinadores = $pdo->query("SELECT nome_empresa, logo_path, link_site FROM patrocinadores WHERE ativo = 1 ORDER BY ordem ASC");
$patrocinadores_ativos = $stmt_patrocinadores->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRTDRFT CONTROL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .event-card.privado { border-left: 5px solid var(--warning-color); }
        .status-privado { color: var(--warning-color); font-weight: bold; }
    </style>
</head>
<body>

    <?php include 'includes/main_header.php'; ?>

    <main class="container">
        <h2>Próximos Eventos</h2>
        <div class="event-list">
            <?php if (count($eventos) > 0): ?>
                <?php foreach ($eventos as $evento): ?>
                    <div class="card event-card <?php if ($evento['status'] == 'Privado') echo 'privado'; ?>">
                        <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                        <p><strong>🗓️ Data:</strong> <?php echo date('d/m/Y \à\s H:i', strtotime($evento['data_evento'])); ?></p>
                        <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($evento['local']); ?></p>
                        
                        <?php if ($evento['status'] == 'Aberto ao Público'): ?>
                            <p><strong>Status:</strong> <span class="status-aberto">Inscrições Abertas</span></p>
                        <?php elseif ($evento['status'] == 'Privado'): ?>
                            <p><strong>Status:</strong> <span class="status-privado">Evento Privado</span></p>
                        <?php endif; ?>

                        <div class="form-actions">
                            <?php // SÓ MOSTRA O BLOCO DE BOTÕES SE AS INSCRIÇÕES ESTIVEREM LIBERADAS
                            if ($evento['inscricoes_liberadas'] == 1): ?>
                                <?php if (isset($_SESSION['user_id'])): // Se está logado ?>
                                    <?php if (in_array($evento['id'], $minhas_inscricoes)): ?>
                                        <button class="btn" disabled style="background-color: var(--success-color); border-color: var(--success-color); color: #121212;">Inscrito ✓</button>
                                    <?php else: ?>
                                        <?php if ($current_user['tipo_usuario'] == 'Piloto' && $evento['status'] == 'Aberto ao Público'): ?>
                                            <button class="btn btn-primary" onclick="abrirModalInscricao(<?php echo $evento['id']; ?>, '<?php echo htmlspecialchars(addslashes($evento['titulo'])); ?>')">Inscrever-se como Piloto</button>
                                        <?php else: ?>
                                            <a href="processar_inscricao.php?action=inscrever_visitante&evento_id=<?php echo $evento['id']; ?>" class="btn">Confirmar Presença</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: // Se está deslogado ?>
                                    <a href="login.php" class="btn">Faça login para participar</a>
                                <?php endif; ?>
                            <?php else: // Se as inscrições NÃO estiverem liberadas ?>
                                <p style="color: var(--text-secondary); font-weight: bold;">Inscrições em breve.</p>
                            <?php endif; ?>
                        </div>
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card"><p>Nenhum evento programado no momento.</p></div>
            <?php endif; ?>
        </div>
    </main>

    <?php if (!empty($patrocinadores_ativos)): ?>
    <section class="patrocinadores">
        <div class="container">
            <h2>Nossos Patrocinadores</h2>
            <div class="patrocinadores-grid">
                <?php foreach ($patrocinadores_ativos as $patrocinador): ?>
                    <div class="patrocinador-item">
                        <?php $link = $patrocinador['link_site']; if (!empty($link) && !preg_match("~^https?://~i", $link)) { $link = "https://" . $link; } ?>
                        <?php if ($link): ?><a href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                            <?php if ($patrocinador['logo_path']): ?>
                                <img src="<?php echo htmlspecialchars($patrocinador['logo_path']); ?>" alt="<?php echo htmlspecialchars($patrocinador['nome_empresa']); ?>">
                            <?php else: ?>
                                <span class="placeholder-logo"><?php echo htmlspecialchars($patrocinador['nome_empresa']); ?></span>
                            <?php endif; ?>
                        <?php if ($link): ?></a><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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

    <div id="modalInscricao" class="modal">
        </div>
    <script src="assets/js/main.js"></script>
    <script>
        // O script do modal continua o mesmo
        const modal = document.getElementById('modalInscricao');
        function abrirModalInscricao(eventoId, eventoTitulo) {
            document.getElementById('modal-evento-id').value = eventoId;
            document.getElementById('modal-titulo-evento').innerText = 'Inscrição: ' + eventoTitulo;
            modal.style.display = 'block';
        }
        function fecharModalInscricao() { modal.style.display = 'none'; }
        window.onclick = function(event) { if (event.target == modal) { fecharModalInscricao(); } }
    </script>
</body>
</html>