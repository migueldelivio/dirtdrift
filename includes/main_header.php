<?php
// A sessão já é iniciada no config.php, que é incluído antes
$is_logged_in = isset($_SESSION['user_id']);
?>
<header class="main-header">
    <div class="container">
        <a href="index.php" style="text-decoration: none; color: inherit;" title="Página Inicial">
            <h1>🏁 DIRT DRIFT</h1>
        </a>
        <nav>
            <a href="index.php" class="btn">Eventos</a>
            
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="btn">Meu Painel</a>
                <a href="logout.php" class="btn">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn btn-primary">Registrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>