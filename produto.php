<?php
session_start();
include('conexao.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_produto = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$mensagem = "";

// Ação: Favoritar / Desfavoritar
if (isset($_GET['acao_favorito']) && $usuario_id > 0) {
    $check_fav = $conn->query("SELECT id FROM favoritos WHERE usuario_id = $usuario_id AND produto_id = $id_produto");
    if ($check_fav->num_rows > 0) {
        $conn->query("DELETE FROM favoritos WHERE usuario_id = $usuario_id AND produto_id = $id_produto");
        $mensagem = "Produto removido dos favoritos.";
    } else {
        $conn->query("INSERT INTO favoritos (usuario_id, produto_id) VALUES ($usuario_id, $id_produto)");
        $mensagem = "Produto adicionado aos favoritos!";
    }
}

// Ação: Enviar Avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_avaliacao'])) {
    if ($usuario_id > 0) {
        $nota = intval($_POST['nota']);
        $comentario = trim($_POST['comentario']);

        if ($nota >= 1 && $nota <= 5) {
            $stmt_av = $conn->prepare("INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?)");
            $stmt_av->bind_param("iiis", $id_produto, $usuario_id, $nota, $comentario);
            if ($stmt_av->execute()) {
                $mensagem = "Sua avaliação foi enviada com sucesso!";
            }
        }
    } else {
        $mensagem = "Faça login para avaliar este produto.";
    }
}

// Buscar Produto
$stmt = $conn->prepare("SELECT p.*, c.nome AS categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$produto = $resultado->fetch_assoc();

// Verificar Favorito
$e_favorito = false;
if ($usuario_id > 0) {
    $fav_res = $conn->query("SELECT id FROM favoritos WHERE usuario_id = $usuario_id AND produto_id = $id_produto");
    if ($fav_res->num_rows > 0) { $e_favorito = true; }
}

// Buscar Avaliações do Produto
$avaliacoes = $conn->query("SELECT a.*, u.nome AS usuario_nome FROM avaliacoes a JOIN usuarios u ON a.usuario_id = u.id WHERE a.produto_id = $id_produto ORDER BY a.id DESC");
$media_nota = $conn->query("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE produto_id = $id_produto")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome']); ?> - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; min-height: 100vh; display: flex; flex-direction: column; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; }
        .img-detalhes { max-height: 380px; width: 100%; object-fit: contain; background-color: #181818; border-radius: 12px; }
        .star-gold { color: #ffc107; }
        footer { margin-top: auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center gap-2" href="index.php">🃏 TCG Vault</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto gap-2 align-items-center">
        <li class="nav-item">
            <a class="btn btn-outline-warning btn-sm me-2" href="carrinho.php"><i class="bi bi-cart3"></i> Carrinho</a>
        </li>
        <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="index.php"><i class="bi bi-arrow-left"></i> Voltar à Loja</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mb-5">
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-warning text-center fw-bold py-2 mb-4"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <div class="card-custom p-4 shadow-lg mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-5 text-center">
                <img src="img/<?php echo !empty($produto['imagem']) ? $produto['imagem'] : 'default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="img-detalhes p-3 border border-secondary">
            </div>

            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($produto['categoria_nome'] ?? 'TCG'); ?></span>
                    
                    <!-- Botão Favoritar -->
                    <?php if ($usuario_id > 0): ?>
                        <a href="produto.php?id=<?php echo $id_produto; ?>&acao_favorito=1" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-heart<?php echo $e_favorito ? '-fill' : ''; ?>"></i> <?php echo $e_favorito ? 'Favoritado' : 'Favoritar'; ?>
                        </a>
                    <?php endif; ?>
                </div>

                <h2 class="fw-bold text-light mb-2"><?php echo htmlspecialchars($produto['nome']); ?></h2>
                
                <!-- Média Avaliações -->
                <div class="mb-3">
                    <?php 
                    $media = round($media_nota['media'] ?? 0);
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<i class="bi bi-star' . ($i <= $media ? '-fill star-gold' : ' text-secondary') . '"></i> ';
                    }
                    ?>
                    <small class="text-secondary ms-2">(<?php echo $media_nota['total']; ?> avaliações)</small>
                </div>

                <h3 class="text-success fw-bold mb-4">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h3>

                <h6 class="text-warning fw-bold mb-2"><i class="bi bi-file-text"></i> Descrição do Produto</h6>
                <p class="text-secondary mb-4" style="white-space: pre-line; line-height: 1.6;">
                    <?php echo !empty($produto['descricao']) ? htmlspecialchars($produto['descricao']) : 'Nenhuma descrição disponível.'; ?>
                </p>

                <!-- Form Adicionar ao Carrinho -->
                <form method="POST" action="carrinho.php?acao=add&id=<?php echo $id_produto; ?>" class="d-flex gap-3 align-items-center">
                    <div style="width: 90px;">
                        <input type="number" name="quantidade" value="1" min="1" class="form-control bg-dark text-light border-secondary text-center">
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="bi bi-cart-plus"></i> Adicionar ao Carrinho</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Avaliações -->
    <div class="card-custom p-4 shadow-lg">
        <h4 class="fw-bold text-warning mb-4"><i class="bi bi-chat-left-text"></i> Avaliações dos Clientes</h4>

        <!-- Form Escrever Avaliação -->
        <?php if ($usuario_id > 0): ?>
            <form method="POST" class="mb-4 pb-4 border-bottom border-secondary">
                <input type="hidden" name="enviar_avaliacao" value="1">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-secondary small">Sua Nota</label>
                        <select name="nota" class="form-select bg-dark text-light border-secondary" required>
                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                            <option value="3">⭐⭐⭐ (3/5)</option>
                            <option value="2">⭐⭐ (2/5)</option>
                            <option value="1">⭐ (1/5)</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label text-secondary small">Seu Comentário</label>
                        <input type="text" name="comentario" class="form-control bg-dark text-light border-secondary" placeholder="O que achou deste produto?" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning btn-sm fw-bold mt-3"><i class="bi bi-send"></i> Enviar Avaliação</button>
            </form>
        <?php else: ?>
            <p class="text-secondary mb-4"><a href="login.php" class="text-warning">Faça login</a> para deixar uma avaliação.</p>
        <?php endif; ?>

        <!-- Lista Avaliações -->
        <?php if ($avaliacoes && $avaliacoes->num_rows > 0): ?>
            <?php while ($av = $avaliacoes->fetch_assoc()): ?>
                <div class="mb-3 pb-3 border-bottom border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-light"><?php echo htmlspecialchars($av['usuario_nome']); ?></strong>
                        <small class="text-secondary"><?php echo date('d/m/Y', strtotime($av['data_avaliacao'])); ?></small>
                    </div>
                    <div class="mb-2">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<i class="bi bi-star' . ($i <= $av['nota'] ? '-fill star-gold' : ' text-secondary') . '"></i> ';
                        }
                        ?>
                    </div>
                    <p class="text-secondary mb-0 small"><?php echo htmlspecialchars($av['comentario']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-secondary mb-0">Nenhuma avaliação enviada ainda. Seja o primeiro a avaliar!</p>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-secondary text-center text-secondary py-3 border-top border-secondary">
    <div class="container"><small>&copy; <?php echo date('Y'); ?> TCG Vault</small></div>
</footer>
<?php
// Lógica para pegar o nome/categoria do produto no produto.php
$musica_fundo = "music/Wii plaza.mp3";

if (isset($produto['categoria_nome'])) {
    $cat_nome = strtolower($produto['categoria_nome']);
    
    if (str_contains($cat_nome, 'yugioh') || str_contains($cat_nome, 'yu-gi-oh')) {
        $musica_fundo = "music/yugioh battle.mp3";
    } elseif (str_contains($cat_nome, 'pokemon') || str_contains($cat_nome, 'pokémon')) {
        $musica_fundo = "music/Wild battle.mp3";
    }
}
?>

<audio id="bg-music" loop>
    <source src="<?php echo $musica_fundo; ?>" type="audio/mpeg">
</audio>

<button id="btn-audio" onclick="toggleAudio()" class="btn btn-warning rounded-circle shadow position-fixed bottom-0 end-0 m-4" style="z-index: 1000; width: 50px; height: 50px;">
    <i id="icon-audio" class="bi bi-volume-mute-fill fs-5"></i>
</button>

<script>
    const audio = document.getElementById('bg-music');
    const icon = document.getElementById('icon-audio');

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