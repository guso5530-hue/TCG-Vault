<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$erro = "";
$sucesso = "";

$id_edit = isset($_GET['editar']) ? intval($_GET['editar']) : 0;
$produto = ['nome' => '', 'descricao' => '', 'preco' => '', 'categoria_id' => '', 'imagem' => ''];

if ($id_edit > 0) {
    $res = $conn->query("SELECT * FROM produtos WHERE id = $id_edit");
    if ($res->num_rows > 0) {
        $produto = $res->fetch_assoc();
    }
}

// Salvar / Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval(str_replace(',', '.', $_POST['preco']));
    $categoria_id = intval($_POST['categoria_id']);
    $imagem_nome = $produto['imagem'];

    // Upload de Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $extensao;
        $destino = 'img/' . $novo_nome;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $imagem_nome = $novo_nome;
        }
    }

    if ($id_edit > 0) {
        $stmt = $conn->prepare("UPDATE produtos SET nome=?, descricao=?, preco=?, categoria_id=?, imagem=? WHERE id=?");
        $stmt->bind_param("ssdisi", $nome, $descricao, $preco, $categoria_id, $imagem_nome, $id_edit);
        if ($stmt->execute()) {
            $sucesso = "Produto atualizado com sucesso!";
        } else {
            $erro = "Erro ao atualizar produto.";
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, categoria_id, imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $nome, $descricao, $preco, $categoria_id, $imagem_nome);
        if ($stmt->execute()) {
            $sucesso = "Produto cadastrado com sucesso!";
            $produto = ['nome' => '', 'descricao' => '', 'preco' => '', 'categoria_id' => '', 'imagem' => ''];
        } else {
            $erro = "Erro ao cadastrar produto.";
        }
    }
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id_edit > 0 ? 'Editar' : 'Cadastrar'; ?> Produto - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning" href="admin.php">🃏 TCG Vault - Painel Admin</a>
    <a class="btn btn-outline-light btn-sm ms-auto" href="admin.php"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
  </div>
</nav>

<div class="container pb-5" style="max-width: 700px;">
    <div class="card-custom p-4 shadow-lg">
        <h4 class="fw-bold text-warning mb-4">
            <i class="bi bi-box-seam"></i> <?php echo $id_edit > 0 ? 'Editar Produto' : 'Cadastrar Novo Produto'; ?>
        </h4>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger py-2"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success py-2"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label text-secondary">Nome do Produto</label>
                <input type="text" name="nome" class="form-control bg-dark text-light border-secondary" required 
                       value="<?php echo htmlspecialchars($produto['nome']); ?>">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary">Preço (R$)</label>
                    <input type="text" name="preco" class="form-control bg-dark text-light border-secondary" required 
                           value="<?php echo htmlspecialchars($produto['preco']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary">Categoria</label>
                    <select name="categoria_id" class="form-select bg-dark text-light border-secondary" required>
                        <option value="">Selecione...</option>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $produto['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary">Descrição</label>
                <textarea name="descricao" rows="4" class="form-control bg-dark text-light border-secondary"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary">Imagem do Produto</label>
                <input type="file" name="imagem" class="form-control bg-dark text-light border-secondary" accept="image/*">
                <?php if (!empty($produto['imagem'])): ?>
                    <div class="mt-2 text-center bg-dark p-2 rounded border border-secondary">
                        <small class="text-secondary d-block mb-1">Imagem atual:</small>
                        <img src="img/<?php echo $produto['imagem']; ?>" style="max-height: 100px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <a href="admin.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-lg"></i> Salvar Produto</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>