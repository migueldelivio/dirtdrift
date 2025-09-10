<?php
require_once 'includes/admin_auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: patrocinadores.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM patrocinadores WHERE id = ?");
$stmt->execute([$id]);
$patrocinador = $stmt->fetch();

if (!$patrocinador) {
    header("Location: patrocinadores.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Editar Patrocinador</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sponsor-logo-preview { max-width: 150px; height: auto; display: block; margin-bottom: 10px; background: #333; padding: 5px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Editar Patrocinador</h2>

            <div class="card">
                <form action="processar_patrocinador.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($patrocinador['id']); ?>">
                    <input type="hidden" name="logo_atual" value="<?php echo htmlspecialchars($patrocinador['logo_path']); ?>">

                    <div class="form-group">
                        <label for="nome_empresa">Nome da Empresa</label>
                        <input type="text" id="nome_empresa" name="nome_empresa" value="<?php echo htmlspecialchars($patrocinador['nome_empresa']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo do Patrocinador</label>
                        <?php if ($patrocinador['logo_path']): ?>
                            <img src="../<?php echo htmlspecialchars($patrocinador['logo_path']); ?>" alt="<?php echo htmlspecialchars($patrocinador['nome_empresa']); ?>" class="sponsor-logo-preview">
                        <?php endif; ?>
                        <input type="file" id="logo" name="logo">
                        <small>Envie um novo arquivo para substituir o atual. Formatos: JPG, PNG, GIF, WEBP. Tamanho máx: 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="link_site">Link do Site (URL Completa)</label>
                        <input type="url" id="link_site" name="link_site" value="<?php echo htmlspecialchars($patrocinador['link_site']); ?>" placeholder="https://www.exemplo.com">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ordem">Ordem de Exibição</label>
                            <input type="number" id="ordem" name="ordem" value="<?php echo htmlspecialchars($patrocinador['ordem']); ?>">
                            <small>Números menores aparecem primeiro.</small>
                        </div>

                        <div class="form-group">
                            <label for="ativo">Ativo no Site?</label>
                            <select id="ativo" name="ativo">
                                <option value="1" <?php echo $patrocinador['ativo'] ? 'selected' : ''; ?>>Sim</option>
                                <option value="0" <?php echo !$patrocinador['ativo'] ? 'selected' : ''; ?>>Não</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="patrocinadores.php" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>