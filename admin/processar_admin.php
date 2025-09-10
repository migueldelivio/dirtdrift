<?php
require_once 'includes/admin_auth.php';

$admin_nome = $_SESSION['user_nome'] ?? 'Admin';
$action = $_REQUEST['action'] ?? null;

try {
    switch ($action) {
       case 'criar_evento':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $data_evento = $_POST['data_evento'] ?? '';
        $local = $_POST['local'] ?? '';
        $status = $_POST['status'] ?? 'Aberto ao Público';
        $vagas_pilotos = intval($_POST['vagas_pilotos'] ?? 0);
        
        // --- INÍCIO DA ALTERAÇÃO ---
        // Pega o novo campo do formulário. O padrão é 0 (bloqueado).
        $inscricoes_liberadas = intval($_POST['inscricoes_liberadas'] ?? 0);
        // --- FIM DA ALTERAÇÃO ---

        if (!empty($titulo) && !empty($data_evento) && !empty($local)) {
            // --- INÍCIO DA ALTERAÇÃO ---
            // Adiciona o novo campo 'inscricoes_liberadas' na consulta SQL
            $sql = "INSERT INTO eventos (titulo, descricao, data_evento, local, status, vagas_pilotos, inscricoes_liberadas) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$titulo, $descricao, $data_evento, $local, $status, $vagas_pilotos, $inscricoes_liberadas]);
            // --- FIM DA ALTERAÇÃO ---
            
            registrar_log('Evento Criado', "Admin '{$admin_nome}' criou o evento: {$titulo}");
            header("Location: eventos.php?status=sucesso");
            exit();
        }
    }
    header("Location: eventos.php?status=erro_criacao");
    break;

        case 'alterar_status_evento':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $evento_id = $_POST['evento_id'] ?? 0;
                $status = $_POST['status'] ?? '';
                $status_permitidos = ['Aberto ao Público', 'Privado', 'Finalizado'];

                if ($evento_id > 0 && in_array($status, $status_permitidos)) {
                    $sql = "UPDATE eventos SET status = ? WHERE id = ?";
                    $pdo->prepare($sql)->execute([$status, $evento_id]);
                    registrar_log('Status Evento Alterado', "Admin '{$admin_nome}' alterou status para '{$status}' no evento ID {$evento_id}");
                    header("Location: eventos.php?status=status_alterado");
                    exit();
                }
            }
            header("Location: eventos.php?status=erro_status");
            break;

        case 'deletar_evento':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $pdo->prepare("DELETE FROM eventos WHERE id = ?")->execute([$id]);
                registrar_log('Evento Deletado', "Admin '{$admin_nome}' deletou o evento ID: {$id}");
                header("Location: eventos.php?status=deletado");
                exit();
            }
            break;

        case 'alterar_status_usuario':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? 0;
                $campo = $_POST['campo'] ?? '';
                $valor = $_POST['valor'] ?? '';
                // Lista de campos permitidos atualizada
$campos_permitidos = ['tipo_usuario', 'mensalidade_status', 'conta_status'];

                if ($user_id > 0 && !empty($campo) && in_array($campo, $campos_permitidos)) {
                    $sql = "UPDATE usuarios SET {$campo} = ? WHERE id = ?";
                    $pdo->prepare($sql)->execute([$valor, $user_id]);
                    header("Location: usuarios.php?status=atualizado");
                    exit();
                }
            }
            header("Location: usuarios.php?status=erro_processamento");
            break;
            
            // Adicione este novo 'case' ao seu switch
case 'alterar_liberacao_inscricao':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $evento_id = $_POST['evento_id'] ?? 0;
        $liberado = $_POST['liberado'] ?? 0;

        if ($evento_id > 0) {
            $sql = "UPDATE eventos SET inscricoes_liberadas = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$liberado, $evento_id]);
            registrar_log('Inscrições Liberadas/Bloqueadas', "Admin '{$admin_nome}' alterou para '{$liberado}' no evento ID {$evento_id}");
            header("Location: eventos.php?status=ok");
            exit();
        }
    }
    header("Location: eventos.php?status=erro");
    break;

        case 'resetar_senha':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $hash = password_hash('senha123', PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?")->execute([$hash, $id]);
                header("Location: usuarios.php?status=senha_resetada");
                exit();
            }
            break;

        default:
            header("Location: index.php"); 
            break;
    }
} catch (PDOException $e) {
    error_log("Erro no processar_admin.php: " . $e->getMessage());
    die("Ocorreu um erro fatal no servidor. Verifique os logs para mais detalhes.");
}
exit();
?>