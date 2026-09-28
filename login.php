<?php
session_start();
include('conexao.php');

$erro = "";
$sucesso = "";

// Processar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_login'])) {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        // Verificação simples ou com password_verify
        if ($senha === $user['senha'] || password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_tipo'] = $user['tipo'];

            if ($user['tipo'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "E-mail não encontrado!";
    }
}

// Processar Cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_cadastro'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado!";
        } else {
            $stmt_ins = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')");
            $stmt_ins->bind_param("sss", $nome, $email, $senha);
            if ($stmt_ins->execute()) {
                $sucesso = "Conta criada com sucesso! Faça login abaixo.";
            } else {
                $erro = "Erro ao cadastrar usuário.";
            }
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 12px; width: 100%; max-width: 420px; }
        .nav-tabs .nav-link { color: #aaa; border: none; }
        .nav-tabs .nav-link.active { background-color: transparent; color: #ffc107; border-bottom: 2px solid #ffc107; font-weight: bold; }
    </style>
</head>
<body>

<div class="card-custom p-4 shadow-lg">
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none">
            <h3 class="fw-bold text-warning mb-1">🃏 TCG Vault</h3>
        </a>
        <p class="text-secondary small">Acesse a sua conta ou cadastre-se</p>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger py-2 text-center small fw-bold"><?php echo $erro; ?></div>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <div class="alert alert-success py-2 text-center small fw-bold"><?php echo $sucesso; ?></div>
    <?php endif; ?>

    <!-- Abas de Login / Cadastro -->
    <ul class="nav nav-tabs nav-fill mb-3" id="authTabs">
        <li class="nav-item">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel">Entrar</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-panel">Cadastrar</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Form de Login -->
        <div class="tab-pane fade show active" id="login-panel">
            <form method="POST">
                <input type="hidden" name="acao_login" value="1">
                <div class="mb-3">
                    <label class="form-label text-secondary small">E-mail</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required placeholder="seu@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Senha</label>
                    <input type="password" name="senha" class="form-control bg-dark text-light border-secondary" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold mt-2"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
            </form>
        </div>

        <!-- Form de Cadastro -->
        <div class="tab-pane fade" id="register-panel">
            <form method="POST">
                <input type="hidden" name="acao_cadastro" value="1">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Nome Completo</label>
                    <input type="text" name="nome" class="form-control bg-dark text-light border-secondary" required placeholder="Seu Nome">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">E-mail</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required placeholder="seu@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Senha</label>
                    <input type="password" name="senha" class="form-control bg-dark text-light border-secondary" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold mt-2"><i class="bi bi-person-plus-fill"></i> Criar Conta</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-4 pt-2 border-top border-secondary">
        <a href="index.php" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left"></i> Voltar para a loja</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>