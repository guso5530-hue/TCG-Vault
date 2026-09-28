<?php
session_start();
include('conexao.php');

// Verifica se o produto foi selecionado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$produto_id = intval($_GET['id']);

// Procura as informações do produto
$stmt = $conn->prepare("SELECT p.*, c.nome AS categoria_nome FROM produtos p JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if (!$produto) {
    header("Location: index.php");
    exit;
}

$mensagem_compra = "";

// Processa a confirmação de compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_compra'])) {
    // Exige que o utilizador esteja logado para comprar
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $quantidade = isset($_POST['quantidade']) ? max(1, intval($_POST['quantidade'])) : 1;
    $valor_total = $produto['preco'] * $quantidade;

    // Garante que a tabela 'pedidos' e 'itens_pedido' existem antes de registar
    $conn->query("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
        valor_total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS itens_pedido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    )");

    // Insere o pedido
    $stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, valor_total) VALUES (?, ?)");
    $stmt_pedido->bind_param("id", $usuario_id, $valor_total);

    if ($stmt_pedido->execute()) {
        $pedido_id = $conn->insert_id;

        // Insere o item do pedido
        $stmt_item = $conn->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $pedido_id, $produto_id, $quantidade, $produto['preco']);
        $stmt_item->execute();

        $mensagem_compra = "sucesso";
    } else {
        $mensagem_compra = "erro";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar <?php echo $produto['nome']; ?> - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning" href="index.php">🃏 TCG Vault</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Voltar ao Catálogo</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
    <?php if ($mensagem_compra === "sucesso"): ?>
        <div class="alert alert-success text-center p-4">
            <h3 class="fw-bold">🎉 Compra realizada com sucesso!</h3>
            <p>Obrigado por comprar no <strong>TCG Vault</strong>.</p>
            <a href="index.php" class="btn btn-warning fw-bold mt-2">Continuar a Comprar</a>
        </div>
    <?php elseif ($mensagem_compra === "erro"): ?>
        <div class="alert alert-danger text-center">
            Ocorreu um erro ao processar o seu pedido. Tente novamente.
        </div>
    <?php else: ?>

        <div class="row bg-secondary rounded shadow p-4 align-items-center">
            <div class="col-md-5 text-center">
              <img src="img/<?php echo $produto['imagem']; ?>" class="img-fluid rounded shadow p-2 bg-dark" style="max-height: 350px;" alt="<?php echo $produto['nome']; ?>">
            </div>
            <div class="col-md-7">
                <span class="badge bg-warning text-dark mb-2"><?php echo $produto['categoria_nome']; ?></span>
                <h2 class="fw-bold text-warning"><?php echo $produto['nome']; ?></h2>
                <p class="text-light fs-5 my-3"><?php echo $produto['descricao']; ?></p>
                <h3 class="fw-bold text-success display-6 mb-4">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h3>

                <form action="comprar.php?id=<?php echo $produto['id']; ?>" method="POST">
                    <div class="mb-3" style="max-width: 150px;">
                        <label for="quantidade" class="form-label fw-bold">Quantidade:</label>
                        <input type="number" name="quantidade" id="quantidade" value="1" min="1" class="form-control text-center fw-bold">
                    </div>

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <button type="submit" name="confirmar_compra" class="btn btn-warning btn-lg fw-bold w-100 mb-2">Finalizar Compra</button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-warning btn-lg fw-bold w-100 mb-2">Faça Login para Comprar</a>
                        <p class="small text-warning text-center">É necessário ter uma conta para finalizar o pedido.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>