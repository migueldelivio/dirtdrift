<?php
require_once 'includes/admin_auth.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Adicionar Patrocinador</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '_admin_header.php'; ?>
        <main>
            <h2>Adicionar Novo Patrocinador</h2>

            <div class="card">
                <form action="processar_patrocinador.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="adicionar">

                    <div class="form-group">
                        <label for="nome_empresa">Nome da Empresa</label>
                        <input type="text" id="nome_empresa" name="nome_empresa" required>
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo do Patrocinador</label>
                        <input type="file" id="logo" name="logo">
                        <small>Formatos: JPG, PNG, GIF, WEBP. Tamanho máx: 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="link_site">Link do Site (URL Completa)</label>
                        <input type="url" id="link_site" name="link_site" placeholder="https://www.exemplo.com">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ordem">Ordem de Exibição</label>
                            <input type="number" id="ordem" name="ordem" value="0">
                            <small>Números menores aparecem primeiro.</small>
                        </div>

                        <div class="form-group">
                            <label for="ativo">Ativo no Site?</label>
                            <select id="ativo" name="ativo">
                                <option value="1" selected>Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Adicionar Patrocinador</button>
                        <a href="patrocinadores.php" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>