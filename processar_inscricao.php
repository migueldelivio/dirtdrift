<?php
require_once 'includes/auth.php'; // Garante que o usuário está logado e carrega $user

$action = $_REQUEST['action'] ?? null;
$user_id = $_SESSION['user_id'];

// --- INSCRIÇÃO SIMPLES DE VISITANTE ---
if ($action === 'inscrever_visitante') {
    $evento_id = filter_input(INPUT_GET, 'evento_id', FILTER_VALIDATE_INT);
    if ($evento_id) {
        try {
            // Insere a inscrição sem dados extras
            $stmt = $pdo->prepare("INSERT INTO inscricoes (id_usuario, id_evento, aceitou_termos) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE id_usuario = id_usuario");
            $stmt->execute([$user_id, $evento_id]);
            registrar_log('Inscrição de Visitante', "Usuário ID {$user_id} no Evento ID {$evento_id}");
        } catch (PDOException $e) {
            error_log("Erro ao inscrever visitante: " . $e->getMessage());
        }
    }
    header("Location: index.php?status=inscrito");
    exit();
}

// --- INSCRIÇÃO COMPLETA DE PILOTO ---
if ($action === 'inscrever_piloto') {
    $evento_id = filter_input(INPUT_POST, 'evento_id', FILTER_VALIDATE_INT);
    $carro_modelo = trim($_POST['carro_modelo'] ?? '');
    $carro_ano = trim($_POST['carro_ano'] ?? '');
    $motorizacao = trim($_POST['motorizacao'] ?? '');
    $equipe = trim($_POST['equipe'] ?? null);
    $aceitou_termos = isset($_POST['aceitou_termos']) ? 1 : 0;

    if ($evento_id && $carro_modelo && $aceitou_termos) {
        try {
            // Lógica para verificar vagas dentro de uma transação para segurança
            $pdo->beginTransaction();

            $stmt_vagas = $pdo->prepare("SELECT vagas_pilotos FROM eventos WHERE id = ?");
            $stmt_vagas->execute([$evento_id]);
            $evento = $stmt_vagas->fetch();

            $stmt_contagem = $pdo->prepare("SELECT COUNT(*) FROM inscricoes i JOIN usuarios u ON i.id_usuario = u.id WHERE i.id_evento = ? AND u.tipo_usuario = 'Piloto'");
            $stmt_contagem->execute([$evento_id]);
            $pilotos_inscritos = $stmt_contagem->fetchColumn();

            if ($evento['vagas_pilotos'] > 0 && $pilotos_inscritos >= $evento['vagas_pilotos']) {
                // Se as vagas acabaram entre o carregamento da página e o clique
                throw new Exception("Vagas para pilotos esgotadas!");
            }

            // Insere a inscrição com todos os dados
            $stmt = $pdo->prepare("INSERT INTO inscricoes (id_usuario, id_evento, carro_modelo, carro_ano, motorizacao, equipe, aceitou_termos) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $evento_id, $carro_modelo, $carro_ano, $motorizacao, $equipe, $aceitou_termos]);
            registrar_log('Inscrição de Piloto', "Usuário ID {$user_id} no Evento ID {$evento_id}");
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Erro ao inscrever piloto: " . $e->getMessage());
            // Você pode redirecionar com uma mensagem de erro aqui
            header("Location: index.php?status=erro_vagas");
            exit();
        }
    }
    header("Location: index.php?status=inscrito");
    exit();
}

// Se nenhuma ação válida, redireciona
header("Location: index.php");
exit();
?>