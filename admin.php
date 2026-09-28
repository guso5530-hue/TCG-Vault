<?php
session_start();
include('conexao.php');

// 1. Proteção do Painel: Apenas administradores logados
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "";

// 2. Ação: Excluir Produto
if (isset($_GET['excluir_produto'])) {
    $id_prod = intval($_GET['excluir_produto']);
    $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id_prod);
    if ($stmt->execute()) {
        $mensagem = "Produto removido com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover produto.";
        $tipo_mensagem = "danger";
    }
}

// 3. Ação: Alterar Nível do Usuário (Alternar entre admin e cliente)
if (isset($_GET['mudar_tipo_user'])) {
    $id_user = intval($_GET['mudar_tipo_user']);
    $novo_tipo = $_GET['novo_tipo'] === 'admin' ? 'admin' : 'cliente';
    
    // Evita despromover a si mesmo
    if ($id_user == $_SESSION['usuario_id']) {
        $mensagem = "Não pode alterar o seu próprio tipo de acesso!";
        $tipo_mensagem = "warning";
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET tipo = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_tipo, $id_user);
        if ($stmt->execute()) {
            $mensagem = "Permissão do usuário atualizada!";
            $tipo_mensagem = "success";
        }
    }
}

// 4. Ação: Excluir Usuário
if (isset($_GET['excluir_user'])) {
    $id_user = intval($_GET['excluir_user']);
    if ($id_user == $_SESSION['usuario_id']) {
        $mensagem = "Não pode excluir a sua própria conta logada!";
        $tipo_mensagem = "danger";
    } else {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $mensagem = "Usuário removido com sucesso!";
            $tipo_mensagem = "success";
        }
    }
}

// Consultas para os Contadores e Tabelas
$total_produtos = $conn->query("SELECT COUNT(*) AS total FROM produtos")->fetch_assoc()['total'];
$total_categorias = $conn->query("SELECT COUNT(*) AS total FROM categorias")->fetch_assoc()['total'];
$total_usuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];

$produtos = $conn->query("SELECT p.*, c.nome AS categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
$usuarios = $conn->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TCG Vault Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; }
        .table-dark-custom { background-color: #1e1e1e; color: #e0e0e0; }
    </style>
</head>
<body>

<!-- Navbar com Logout -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center gap-2" href="admin.php">
      🃏 TCG Vault - Painel Admin
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto gap-2 align-items-center">
        <li class="nav-item text-light me-2">
            Olá, <strong class="text-warning"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Admin'); ?></strong>
        </li>
        <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="index.php"><i class="bi bi-shop"></i> Ver Loja</a>
        </li>
        <li class="nav-item">
            <!-- Botão de Logout chamando o arquivo logout.php -->
            <a class="btn btn-danger btn-sm fw-bold" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container pb-5">

    <!-- Mensagens de Feedback -->
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show text-center fw-bold" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-custom p-3 d-flex align-items-center gap-3">
                <i class="bi bi-box-seam fs-1 text-primary"></i>
                <div>
                    <h6 class="text-secondary mb-0">Total de Produtos</h6>
                    <h2 class="fw-bold text-light mb-0"><?php echo $total_produtos; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 d-flex align-items-center gap-3">
                <i class="bi bi-tags fs-1 text-success"></i>
                <div>
                    <h6 class="text-secondary mb-0">Categorias Ativas</h6>
                    <h2 class="fw-bold text-light mb-0"><?php echo $total_categorias; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 d-flex align-items-center gap-3">
                <i class="bi bi-people fs-1 text-warning"></i>
                <div>
                    <h6 class="text-secondary mb-0">Usuários Cadastrados</h6>
                    <h2 class="fw-bold text-light mb-0"><?php echo $total_usuarios; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção 1: Gerenciamento de Produtos -->
    <div class="card-custom p-4 mb-5 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-warning mb-0"><i class="bi bi-controller"></i> Gerenciar Produtos</h4>
            <a href="cadastrar_produto.php" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-plus-lg"></i> Cadastrar Novo Produto</a>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 border-secondary">
                <thead>
                    <tr class="text-warning">
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($produtos->num_rows > 0): ?>
                        <?php while ($prod = $produtos->fetch_assoc()): ?>
                            <tr>
                                <td style="width: 60px;">
                                    <img src="img/<?php echo !empty($prod['imagem']) ? $prod['imagem'] : 'default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($prod['nome']); ?>" 
                                         style="width: 45px; height: 45px; object-fit: contain;" class="bg-dark rounded p-1 border border-secondary">
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($prod['nome']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($prod['categoria_nome'] ?? 'Sem Categoria'); ?></span></td>
                                <td class="text-success fw-bold">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                                <td class="text-end">
                                    <a href="cadastrar_produto.php?editar=<?php echo $prod['id']; ?>" class="btn btn-outline-info btn-sm me-1" title="Editar Produto">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="admin.php?excluir_produto=<?php echo $prod['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       onclick="return confirm('Tem certeza de que deseja remover este produto?');" 
                                       title="Excluir Produto">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhum produto cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Seção 2: Gerenciamento de Usuários -->
    <div class="card-custom p-4 shadow-lg">
        <h4 class="fw-bold text-warning mb-3"><i class="bi bi-person-gear"></i> Gerenciar Usuários</h4>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 border-secondary">
                <thead>
                    <tr class="text-warning">
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo de Acesso</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usuarios->num_rows > 0): ?>
                        <?php while ($user = $usuarios->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($user['nome']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['tipo'] === 'admin'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill"></i> Administrador</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark"><i class="bi bi-person"></i> Cliente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($user['tipo'] === 'admin'): ?>
                                        <a href="admin.php?mudar_tipo_user=<?php echo $user['id']; ?>&novo_tipo=cliente" 
                                           class="btn btn-outline-warning btn-sm me-1" title="Tornar Cliente">
                                            Tornar Cliente
                                        </a>
                                    <?php else: ?>
                                        <a href="admin.php?mudar_tipo_user=<?php echo $user['id']; ?>&novo_tipo=admin" 
                                           class="btn btn-outline-success btn-sm me-1" title="Tornar Admin">
                                            Tornar Admin
                                        </a>
                                    <?php endif; ?>

                                    <a href="admin.php?excluir_user=<?php echo $user['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       onclick="return confirm('Tem certeza que deseja remover este usuário?');" 
                                       title="Excluir Usuário">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php
// ... todo o teu código PHP e HTML do index.php continua igual acima ...

// Lógica para definir a música com base na categoria filtrada
$musica_fundo = "music/Wii plaza.mp3"; // Música padrão (Mii Plaza)

if (isset($_GET['categoria'])) {
    $cat_id = intval($_GET['categoria']);
    
    // Substitui pelos IDs reais das tuas categorias no banco de dados
    if ($cat_id == 1) { 
        $musica_fundo = "music/yugioh battle.mp3";
    } elseif ($cat_id == 2) { 
        $musica_fundo = "music/Wild battle.mp3";
    }
}
?>

<!-- Elemento de Áudio e Botão Flutuante -->
<audio id="bg-music" loop>
    <source src="<?php echo $musica_fundo; ?>" type="audio/mpeg">
</audio>

<button id="btn-audio" onclick="toggleAudio()" class="btn btn-warning rounded-circle shadow position-fixed bottom-0 end-0 m-4" style="z-index: 1000; width: 50px; height: 50px;">
    <i id="icon-audio" class="bi bi-volume-mute-fill fs-5"></i>
</button>

<script>
    const audio = document.getElementById('bg-music');
    const icon = document.getElementById('icon-audio');

    // Ativa o áudio ao primeiro clique do utilizador na página
    document.addEventListener('click', function startAudio() {
        audio.play().then(() => {
            icon.className = 'bi bi-volume-up-fill fs-5';
        }).catch(() => {});
        document.removeEventListener('click', startAudio);
    });

    function toggleAudio() {
        if (audio.paused) {
            audio.play();
            icon.className = 'bi bi-volume-up-fill fs-5';
        } else {
            audio.pause();
            icon.className = 'bi bi-volume-mute-fill fs-5';
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>