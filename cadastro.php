<?php
session_start();
include('conexao.php');

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        // Verifica se o e-mail já está registado na base de dados
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $mensagem = "O e-mail introduzido já se encontra registado!";
            $tipo_mensagem = "danger";
        } else {
            // Criptografa a palavra-passe por questões de segurança
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere o novo cliente na tabela (por padrão o tipo é 'cliente')
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt->execute()) {
                $mensagem = "Registo efetuado com sucesso! <a href='login.php' class='alert-link'>Clique aqui para fazer login</a>.";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "Ocorreu um erro ao registar a conta. Tente novamente.";
                $tipo_mensagem = "danger";
            }
        }
    } else {
        $mensagem = "Por favor, preencha todos os campos obrigatórios!";
        $tipo_mensagem = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TCG Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light d-flex align-items-center justify-content-center vh-100">

<div class="card bg-secondary text-light p-4 shadow-lg" style="max-width: 450px; width: 100%;">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-warning">🃏 TCG Vault</h2>
        <p class="text-light">Crie a sua conta de colecionador</p>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form action="cadastro.php" method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" name="nome" id="nome" class="form-control" placeholder="Introduza o seu nome" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="seuemail@exemplo.com" required>
        </div>

        <div class="mb-3">
            <label for="senha" class="form-label">Palavra-passe</label>
            <input type="password" name="senha" id="senha" class="form-control" placeholder="Crie uma palavra-passe" required>
        </div>

        <button type="submit" class="btn btn-warning w-100 fw-bold mb-3">Criar Conta</button>
    </form>

    <div class="text-center">
        <p class="mb-1 small">Já possui uma conta? <a href="login.php" class="text-warning fw-bold text-decoration-none">Iniciar Sessão</a></p>
        <p class="small"><a href="index.php" class="text-light text-decoration-underline">Voltar para a loja</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>