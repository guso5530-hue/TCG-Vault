<?php
session_start();
include('conexao.php');

// Proteção: Apenas administradores podem aceder
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "";

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome         = trim($_POST['nome']);
    $descricao    = trim($_POST['descricao']);
    $preco        = floatval($_POST['preco']);
    $categoria_id = intval($_POST['categoria_id']);
    
    // Tratamento da Imagem
    $nome_imagem = "default.jpg";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $nome_imagem = uniqid() . "." . $extensao;
            $destino = "img/" . $nome_imagem;

            // Cria a pasta 'img' se ainda não existir
            if (!is_dir('img')) {
                mkdir('img', 0777, true);
            }

            move_uploaded_file($_FILES['imagem']['tmp_name'], $destino);
        } else {
            $mensagem = "Formato de imagem inválido! Apenas JPG, PNG e WEBP são permitidos.";
            $tipo_mensagem = "danger";
        }
    }

    if (empty($mensagem) && !empty($nome) && $preco > 0 && $categoria_id > 0) {
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, imagem, categoria_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsi", $nome, $descricao, $preco, $nome_imagem, $categoria_id);

        if ($stmt->execute()) {
            $mensagem = "Produto cadastrado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao cadastrar o produto na base de dados.";
            $tipo_mensagem = "danger";
        }
    }
}

// Procura todas as categorias ativas
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");

// Procura os últimos produtos cadastrados para exibição lateral
$ultimos_produtos = $conn->query("SELECT p.nome, p.preco, p.imagem, c.nome AS categoria_nome 
                                 FROM produtos p 
                                 LEFT JOIN categorias c ON p.categoria_id = c.id 
                                 ORDER BY p.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; }
        .img-preview-box { height: 180px; display: flex; align-items: center; justify-content: center; background-color: #121212; border-radius: 8px; border: 2px dashed #444; overflow: hidden; }
        .img-preview-box img { max-height: 100%; max-width: 100%; object-fit: contain; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center gap-2" href="admin.php">
      🃏 TCG Vault - Painel Admin
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto gap-2">
        <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="btn btn-warning btn-sm fw-bold" href="index.php"><i class="bi bi-shop"></i> Ver Loja</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container pb-5">

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show text-center fw-bold" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Formulário de Cadastro -->
        <div class="col-lg-7">
            <div class="card-custom p-4 shadow-lg">
                <h3 class="fw-bold text-warning mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i> Cadastrar Novo Produto
                </h3>

                <form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nome" class="form-label fw-bold">Nome do Produto</label>
                        <input type="text" name="nome" id="nome" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Booster Box Magic The Gathering..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label fw-bold">Categoria</label>
                            <select name="categoria_id" id="categoria_id" class="form-select bg-dark text-light border-secondary" required>
                                <option value="">Selecione...</option>
                                <?php while ($cat = $categorias->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="preco" class="form-label fw-bold">Preço (R$)</label>
                            <input type="number" step="0.01" name="preco" id="preco" class="form-control bg-dark text-light border-secondary" placeholder="160.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label fw-bold">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Detalhes do produto..."></textarea>
                    </div>

                    <!-- Upload de Imagem com Prévia Visual -->
                    <div class="mb-4">
                        <label for="imagem" class="form-label fw-bold">Imagem do Produto</label>
                        <input type="file" name="imagem" id="imagem" class="form-control bg-dark text-light border-secondary mb-3" accept="image/*" onchange="previewImagem(event)">
                        
                        <div class="img-preview-box text-center p-2">
                            <div id="previewPlaceholder" class="text-secondary">
                                <i class="bi bi-cloud-arrow-up fs-1 d-block"></i>
                                <span class="small">Selecione uma imagem para ver a prévia</span>
                            </div>
                            <img id="previewImg" src="#" alt="Prévia" class="d-none">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2"><i class="bi bi-check-lg"></i> Cadastrar Produto</button>
                </form>
            </div>
        </div>

        <!-- Painel Lateral: Útimos Cadastrados -->
        <div class="col-lg-5">
            <div class="card-custom p-4 shadow-lg">
                <h4 class="fw-bold text-warning mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i> Últimos Cadastrados
                </h4>
                <p class="small text-secondary mb-3">Confira como as fotos estão aparecendo nos seus produtos recentes:</p>

                <div class="d-flex flex-column gap-3">
                    <?php if ($ultimos_produtos->num_rows > 0): ?>
                        <?php while ($prod = $ultimos_produtos->fetch_assoc()): ?>
                            <?php 
                                $caminho = "img/" . $prod['imagem'];
                                $existe = file_exists($caminho) && !empty($prod['imagem']);
                            ?>
                            <div class="d-flex align-items-center gap-3 p-2 rounded bg-dark border border-secondary border-opacity-25">
                                <div style="width: 50px; height: 50px;" class="d-flex align-items-center justify-content-center bg-secondary rounded overflow-hidden flex-shrink-0">
                                    <?php if ($existe): ?>
                                        <img src="<?php echo $caminho; ?>" alt="<?php echo $prod['nome']; ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 text-light text-truncate"><?php echo $prod['nome']; ?></h6>
                                    <small class="text-warning"><?php echo $prod['categoria_nome'] ?? 'Geral'; ?></small>
                                </div>
                                <div class="ms-auto text-end flex-shrink-0">
                                    <span class="fw-bold text-success">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Nenhum produto cadastrado ainda.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary text-center">
                    <a href="gerenciar_imagens.php" class="btn btn-outline-warning btn-sm w-100 fw-bold">
                        <i class="bi bi-images"></i> Ir para o Gerenciador de Imagens
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Função para gerar a prévia instantânea da imagem no cadastro
    function previewImagem(event) {
        const file = event.target.files[0];
        const previewImg = document.getElementById('previewImg');
        const placeholder = document.getElementById('previewPlaceholder');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
                placeholder.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.src = '#';
            previewImg.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }
    }
</script>
</body>
</html>