<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Ações do Carrinho
if (isset($_GET['acao'])) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($_GET['acao'] === 'add' && $id > 0) {
        $qtd = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 1;
        if (isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id] += $qtd;
        } else {
            $_SESSION['carrinho'][$id] = $qtd;
        }
        header("Location: carrinho.php");
        exit;
    }

    if ($_GET['acao'] === 'remover' && $id > 0) {
        unset($_SESSION['carrinho'][$id]);
        header("Location: carrinho.php");
        exit;
    }

    if ($_GET['acao'] === 'limpar') {
        $_SESSION['carrinho'] = [];
        header("Location: carrinho.php");
        exit;
    }
}

// Atualizar Quantidades via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_carrinho'])) {
    foreach ($_POST['qtd'] as $id => $qtd) {
        $qtd = intval($qtd);
        if ($qtd > 0) {
            $_SESSION['carrinho'][$id] = $qtd;
        } else {
            unset($_SESSION['carrinho'][$id]);
        }
    }
    header("Location: carrinho.php");
    exit;
}

// Buscar produtos do carrinho no Banco de Dados
$produtos_carrinho = [];
$total_geral = 0;

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    $sql = "SELECT * FROM produtos WHERE id IN ($ids)";
    $res = $conn->query($sql);
    
    while ($prod = $res->fetch_assoc()) {
        $qtd = $_SESSION['carrinho'][$prod['id']];
        $subtotal = $prod['preco'] * $qtd;
        $total_geral += $subtotal;
        
        $prod['qtd'] = $qtd;
        $prod['subtotal'] = $subtotal;
        $produtos_carrinho[] = $prod;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; min-height: 100vh; display: flex; flex-direction: column; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; }
        footer { margin-top: auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center gap-2" href="index.php">
      🃏 TCG Vault
    </a>
    <a class="btn btn-outline-light btn-sm ms-auto" href="index.php"><i class="bi bi-shop"></i> Continuar Comprando</a>
  </div>
</nav>

<div class="container mb-5" style="max-width: 900px;">
    <h3 class="fw-bold text-warning mb-4"><i class="bi bi-cart3"></i> Meu Carrinho de Compras</h3>

    <?php if (!empty($produtos_carrinho)): ?>
        <form method="POST" action="carrinho.php">
            <input type="hidden" name="atualizar_carrinho" value="1">
            <div class="card-custom p-4 shadow-lg mb-4">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 border-secondary">
                        <thead>
                            <tr class="text-warning">
                                <th>Produto</th>
                                <th>Preço</th>
                                <th style="width: 120px;">Qtd.</th>
                                <th>Subtotal</th>
                                <th class="text-end">Remover</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos_carrinho as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="img/<?php echo !empty($item['imagem']) ? $item['imagem'] : 'default.jpg'; ?>" 
                                                 style="width: 50px; height: 50px; object-fit: contain;" class="bg-dark rounded p-1 border border-secondary">
                                            <strong class="text-light"><?php echo htmlspecialchars($item['nome']); ?></strong>
                                        </div>
                                    </td>
                                    <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                    <td>
                                        <input type="number" name="qtd[<?php echo $item['id']; ?>]" value="<?php echo $item['qtd']; ?>" min="1" class="form-control bg-dark text-light border-secondary text-center">
                                    </td>
                                    <td class="text-success fw-bold">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                    <td class="text-end">
                                        <a href="carrinho.php?acao=remover&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                    <a href="carrinho.php?acao=limpar" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Esvaziar o carrinho?');">Limpar Carrinho</a>
                    <button type="submit" class="btn btn-outline-warning btn-sm fw-bold"><i class="bi bi-arrow-repeat"></i> Atualizar Quantidades</button>
                </div>
            </div>

            <!-- Total e Finalizar -->
            <div class="card-custom p-4 shadow-lg text-end">
                <h4 class="fw-bold mb-3">Total: <span class="text-success">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></span></h4>
                <button type="button" onclick="alert('Pedido efetuado com sucesso!')" class="btn btn-warning btn-lg fw-bold"><i class="bi bi-check-lg"></i> Finalizar Compra</button>
            </div>
        </form>
    <?php else: ?>
        <div class="card-custom p-5 text-center">
            <i class="bi bi-cart-x fs-1 text-warning mb-2"></i>
            <h5 class="text-light">Seu carrinho está vazio.</h5>
            <p class="text-secondary mb-3">Navegue pelas ofertas da loja e adicione seus cards e boxes favoritos!</p>
            <a href="index.php" class="btn btn-warning fw-bold"><i class="bi bi-shop"></i> Ver Produtos</a>
        </div>
    <?php endif; ?>
</div>

<footer class="bg-secondary text-center text-secondary py-3 border-top border-secondary">
    <div class="container"><small>&copy; <?php echo date('Y'); ?> TCG Vault</small></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>