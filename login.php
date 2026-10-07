<?php
session_start();
require_once __DIR__ . '/funcoes.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    try {
        $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
        foreach ($usuarios->usuario as $usuario) {
            $hash = (string) $usuario->senha;
            $senhaCorreta = password_verify($senha, $hash)
                || (preg_match('/^[a-f0-9]{32}$/i', $hash) === 1 && hash_equals(strtolower($hash), md5($senha)));

            if (strcasecmp((string) $usuario->email, $email) === 0 && $senhaCorreta) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = (string) $usuario->email;

                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
                    salvarXml($usuarios, ARQUIVO_USUARIOS);
                }

                header('Location: listar.php');
                exit;
            }
        }

        $erro = 'Login inválido.';
    } catch (RuntimeException $excecao) {
        $erro = $excecao->getMessage();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --primary: #2c6bed;
            --primary-dark: #1f4fb7;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #dfe7f5;
            --success: #1f9d61;
            --danger: #b42318;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #edf3ff, #f7f9fc);
            font-family: Arial, sans-serif;
            color: var(--text);
        }

        .card {
            width: min(100%, 420px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
            padding: 32px 28px;
        }

        h1 {
            margin: 0 0 20px;
            font-size: 2rem;
            text-align: center;
        }

        .alert {
            margin: 0 0 16px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(31, 157, 97, 0.08);
            color: var(--success);
            border: 1px solid rgba(31, 157, 97, 0.2);
        }

        .error {
            background: rgba(180, 35, 24, 0.08);
            color: var(--danger);
            border-color: rgba(180, 35, 24, 0.2);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 107, 237, 0.12);
        }

        button {
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 12px 18px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        button:hover {
            background: var(--primary-dark);
        }

        .links {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Login</h1>

        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
            <p class="alert"><?= escapar((string) $_SESSION['mensagem_sucesso']) ?></p>
            <?php unset($_SESSION['mensagem_sucesso']); ?>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <p class="alert error"><?= escapar($erro) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
            <label>E-mail: <input type="email" name="email" value="<?= escapar($email) ?>" required></label>
            <label>Senha: <input type="password" name="senha" required></label>
            <button type="submit">Entrar</button>
        </form>

        <p class="links"><a href="cadastro.php">Criar cadastro</a> | <a href="listar.php">Ver tópicos</a></p>
    </div>
</body>
</html>
