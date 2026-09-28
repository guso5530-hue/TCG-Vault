<?php
session_start();
include('conexao.php');

// 1. Receber os parâmetros de busca, categoria e ordenação via GET
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordem = isset($_GET['ordem']) ? trim($_GET['ordem']) : 'recente';

// 2. Verificar se o utilizador é Cliente Novo (Exclui Administradores do desconto)
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_tipo = $_SESSION['usuario_tipo'] ?? 'cliente';
$e_novo_usuario = false;

if ($usuario_id > 0 && $usuario_tipo !== 'admin') {
    $check_pedidos = $conn->query("SHOW TABLES LIKE 'pedidos'");
    if ($check_pedidos && $check_pedidos->num_rows > 0) {
        $qtd_pedidos = $conn->query("SELECT COUNT(*) AS total FROM pedidos WHERE usuario_id = $usuario_id")->fetch_assoc()['total'];
        if ($qtd_pedidos == 0) {
            $e_novo_usuario = true;
        }
    } else {
        $e_novo_usuario = true;
    }
}

// 3. Montar a consulta SQL com filtros
$sql = "SELECT p.*, c.nome AS categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";

if ($categoria_id > 0) {
    $sql .= " AND p.categoria_id = " . $categoria_id;
}

if (!empty($busca)) {
    $busca_escaped = $conn->real_escape_string($busca);
    $sql .= " AND (p.nome LIKE '%$busca_escaped%' OR p.descricao LIKE '%$busca_escaped%')";
}

// 4. Definir a ordenação
switch ($ordem) {
    case 'nome_asc':
        $sql .= " ORDER BY p.nome ASC";
        break;
    case 'nome_desc':
        $sql .= " ORDER BY p.nome DESC";
        break;
    case 'preco_asc':
        $sql .= " ORDER BY p.preco ASC";
        break;
    case 'preco_desc':
        $sql .= " ORDER BY p.preco DESC";
        break;
    default:
        $sql .= " ORDER BY p.id DESC";
        break;
}

$produtos = $conn->query($sql);
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCG Vault - Sua Loja de Card Games</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; min-height: 100vh; display: flex; flex-direction: column; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; transition: transform 0.2s, border-color 0.2s; }
        .card-custom:hover { transform: translateY(-4px); border-color: #ffc107; }
        .img-produto { height: 200px; object-fit: contain; background-color: #181818; border-top-left-radius: 12px; border-top-right-radius: 12px; }
        .preco-antigo { text-decoration: line-through; color: #888; font-size: 0.9rem; }
        footer { margin-top: auto; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center gap-2" href="index.php">
      🃏 TCG Vault
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-2 align-items-center">
        <li class="nav-item">
            <a class="nav-link active text-warning" href="index.php"><i class="bi bi-shop"></i> Catálogo</a>
        </li>
        <li class="nav-item">
            <a class="btn btn-outline-warning btn-sm ms-2" href="carrinho.php">
                <i class="bi bi-cart3"></i> Carrinho
                <?php if (!empty($_SESSION['carrinho'])): ?>
                    <span class="badge bg-warning text-dark"><?php echo array_sum($_SESSION['carrinho']); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="btn btn-outline-warning btn-sm" href="admin.php"><i class="bi bi-shield-lock"></i> Painel Admin</a>
                </li>
            <?php endif; ?>
            <li class="nav-item text-light me-2 ms-2">
                Olá, <strong class="text-warning"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>
            </li>
            <li class="nav-item">
                <a class="btn btn-danger btn-sm fw-bold" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="btn btn-warning btn-sm fw-bold" href="login.php"><i class="bi bi-person-fill"></i> Entrar / Cadastrar</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mb-5">

    <!-- Banner de Oferta de Boas-Vindas (Não aparece para Administradores) -->
    <?php if (($e_novo_usuario || !isset($_SESSION['usuario_id'])) && $usuario_tipo !== 'admin'): ?>
        <div class="alert alert-warning border-warning bg-dark text-warning p-3 mb-4 rounded-3 d-flex align-items-center gap-3 shadow">
            <i class="bi bi-gift-fill fs-2"></i>
            <div>
                <h5 class="fw-bold mb-1">🎁 Oferta de Boas-Vindas para Novos Clientes!</h5>
                <p class="mb-0 text-light small">
                    Garante **35% DE DESCONTO** em qualquer produto + **15% EXTRA** em toda a linha **Yu-Gi-Oh!** 
                    <?php if (!isset($_SESSION['usuario_id'])): ?>
                        (<a href="login.php" class="text-warning fw-bold text-decoration-underline">Cria a tua conta ou faz login</a> para ativar o desconto).
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulário com Filtros -->
    <div class="card-custom p-4 mb-4 shadow">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-dark text-warning border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control bg-dark text-light border-secondary" 
                           placeholder="Buscar produtos..." value="<?php echo htmlspecialchars($busca); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="categoria" class="form-select bg-dark text-light border-secondary">
                    <option value="0">Todas as Categorias</option>
                    <?php if ($categorias && $categorias->num_rows > 0): ?>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($categoria_id == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="ordem" class="form-select bg-dark text-light border-secondary">
                    <option value="recente" <?php echo ($ordem === 'recente') ? 'selected' : ''; ?>>Mais Recentes</option>
                    <option value="nome_asc" <?php echo ($ordem === 'nome_asc') ? 'selected' : ''; ?>>Nome (A - Z)</option>
                    <option value="nome_desc" <?php echo ($ordem === 'nome_desc') ? 'selected' : ''; ?>>Nome (Z - A)</option>
                    <option value="preco_asc" <?php echo ($ordem === 'preco_asc') ? 'selected' : ''; ?>>Preço (Menor para Maior)</option>
                    <option value="preco_desc" <?php echo ($ordem === 'preco_desc') ? 'selected' : ''; ?>>Preço (Maior para Menor)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold w-100"><i class="bi bi-filter"></i> Filtrar</button>
                <?php if ($categoria_id > 0 || !empty($busca) || $ordem !== 'recente'): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpar Filtros"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de Produtos -->
    <div class="row g-4">
        <?php if ($produtos && $produtos->num_rows > 0): ?>
            <?php while ($prod = $produtos->fetch_assoc()): ?>
                <?php
                $preco_original = $prod['preco'];
                $preco_final = $preco_original;
                $porcentagem_desconto = 0;

                $e_yugioh = (stripos($prod['categoria_nome'], 'yugioh') !== false || stripos($prod['categoria_nome'], 'yu-gi-oh') !== false);

                // Aplica o desconto apenas se for um novo utilizador E NÃO for admin
                if ($e_novo_usuario) {
                    $porcentagem_desconto = 35;
                    if ($e_yugioh) {
                        $porcentagem_desconto += 15;
                    }
                    $preco_final = $preco_original * (1 - ($porcentagem_desconto / 100));
                }
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card-custom h-100 d-flex flex-column shadow position-relative">
                        
                        <?php if ($porcentagem_desconto > 0): ?>
                            <span class="position-absolute top-0 end-0 badge bg-danger m-2 px-2 py-1 fs-6">
                                -<?php echo $porcentagem_desconto; ?>% OFF
                            </span>
                        <?php endif; ?>

                        <img src="img/<?php echo !empty($prod['imagem']) ? $prod['imagem'] : 'default.jpg'; ?>" 
                             class="card-img-top img-produto p-2" alt="<?php echo htmlspecialchars($prod['nome']); ?>">
                        
                        <div class="card-body d-flex flex-column justify-content-between p-3">
                            <div>
                                <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($prod['categoria_nome'] ?? 'TCG'); ?></span>
                                <h6 class="card-title fw-bold text-light mb-2"><?php echo htmlspecialchars($prod['nome']); ?></h6>
                            </div>
                            
                            <div class="mt-3">
                                <?php if ($porcentagem_desconto > 0): ?>
                                    <div class="preco-antigo">R$ <?php echo number_format($preco_original, 2, ',', '.'); ?></div>
                                    <h5 class="text-success fw-bold mb-3">R$ <?php echo number_format($preco_final, 2, ',', '.'); ?></h5>
                                <?php else: ?>
                                    <h5 class="text-success fw-bold mb-3">R$ <?php echo number_format($preco_original, 2, ',', '.'); ?></h5>
                                <?php endif; ?>

                                <a href="produto.php?id=<?php echo $prod['id']; ?>" class="btn btn-warning w-100 fw-bold">
                                    <i class="bi bi-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card-custom p-5 text-center">
                    <i class="bi bi-emoji-frown fs-1 text-warning mb-2"></i>
                    <h5 class="text-light">Nenhum produto encontrado.</h5>
                    <p class="text-secondary mb-3">Tente alterar os filtros de busca ou categoria.</p>
                    <a href="index.php" class="btn btn-outline-warning btn-sm fw-bold">Limpar Filtros</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Rodapé -->
<footer class="bg-secondary text-center text-secondary py-3 border-top border-secondary">
    <div class="container">
        <small>&copy; <?php echo date('Y'); ?> TCG Vault - Todos os direitos reservados.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>