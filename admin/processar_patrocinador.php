<?php
require_once 'includes/admin_auth.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$pasta_logos_relativa = 'assets/img/patrocinadores/';
$pasta_logos_absoluta = __DIR__ . '/../' . $pasta_logos_relativa;

$tamanho_maximo = 2 * 1024 * 1024; // 2MB
$formatos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

function handle_logo_upload($pasta_absoluta, $pasta_relativa, $tamanho_maximo, $formatos_permitidos) {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($pasta_absoluta)) {
            if (!mkdir($pasta_absoluta, 0775, true)) {
                throw new Exception("Falha ao criar a pasta de destino: {$pasta_absoluta}");
            }
        }
        if (!is_writable($pasta_absoluta)) {
            throw new Exception("A pasta de destino ('{$pasta_absoluta}') não tem permissão de escrita.");
        }

        if ($_FILES['logo']['size'] > $tamanho_maximo) {
            throw new Exception("O arquivo excede o limite de 2MB.");
        }

        $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $formatos_permitidos)) {
            throw new Exception("Formato de arquivo não permitido. Use: " . implode(', ', $formatos_permitidos));
        }

        $nome_arquivo = uniqid('logo_') . '.' . $extensao;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $pasta_absoluta . $nome_arquivo)) {
            return $pasta_relativa . $nome_arquivo;
        } else {
            throw new Exception("Erro desconhecido ao mover o arquivo para a pasta de destino.");
        }
    }
    return null; 
}

try {
    switch ($action) {
        case 'adicionar':
            $nome_empresa = trim($_POST['nome_empresa'] ?? '');
            $link_site = trim($_POST['link_site'] ?? '');
            $ordem = intval($_POST['ordem'] ?? 0);
            $ativo = intval($_POST['ativo'] ?? 1);

            if (empty($nome_empresa)) {
                throw new Exception("O nome da empresa é obrigatório.");
            }

            $logo_path = handle_logo_upload($pasta_logos_absoluta, $pasta_logos_relativa, $tamanho_maximo, $formatos_permitidos);

            $stmt = $pdo->prepare("INSERT INTO patrocinadores (nome_empresa, logo_path, link_site, ordem, ativo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome_empresa, $logo_path, $link_site, $ordem, $ativo]);
            registrar_log('Patrocinador Adicionado', "Nome: {$nome_empresa}");
            header("Location: patrocinadores.php?status=adicionado");
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $nome_empresa = trim($_POST['nome_empresa'] ?? '');
            $link_site = trim($_POST['link_site'] ?? '');
            $ordem = intval($_POST['ordem'] ?? 0);
            $ativo = intval($_POST['ativo'] ?? 1);
            $logo_atual = $_POST['logo_atual'] ?? null;

            if (!$id) throw new Exception("ID do patrocinador inválido.");
            
            $novo_logo_path = handle_logo_upload($pasta_logos_absoluta, $pasta_logos_relativa, $tamanho_maximo, $formatos_permitidos);
            $logo_final = $novo_logo_path ?? $logo_atual;

            if ($novo_logo_path && $logo_atual && file_exists(__DIR__ . '/../' . $logo_atual)) {
                unlink(__DIR__ . '/../' . $logo_atual);
            }

            $stmt = $pdo->prepare("UPDATE patrocinadores SET nome_empresa = ?, logo_path = ?, link_site = ?, ordem = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$nome_empresa, $logo_final, $link_site, $ordem, $ativo, $id]);
            registrar_log('Patrocinador Editado', "ID: {$id}, Nome: {$nome_empresa}");
            header("Location: patrocinadores.php?status=editado");
            break;

        case 'deletar':
            break;

        default:
            header("Location: patrocinadores.php");
            break;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: patrocinadores.php?status=erro");
    exit();
}
?>