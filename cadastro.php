<?php
session_start();
require_once __DIR__ . '/funcoes.php';

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmacao = (string) ($_POST['confirmacao'] ?? '');

    if ($email === '' || $senha === '' || $confirmacao === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não conferem.';
    } else {
        try {
            $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
            foreach ($usuarios->usuario as $usuario) {
                if (strcasecmp((string) $usuario->email, $email) === 0) {
                    $erro = 'Este e-mail já está cadastrado.';
                    break;
                }
            }

            if ($erro === '') {
                $novo = $usuarios->addChild('usuario');
                $novo->addChild('email', $email);
                $novo->addChild('senha', password_hash($senha, PASSWORD_DEFAULT));
                salvarXml($usuarios, ARQUIVO_USUARIOS);

                $_SESSION['mensagem_sucesso'] = 'Cadastro realizado com sucesso. Faça o login.';
                header('Location: login.php');
                exit;
            }
        } catch (RuntimeException $excecao) {
            $erro = $excecao->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --primary: #2c6bed;
            --primary-dark: #1f4fb7;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #dfe7f5;
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
            width: min(100%, 440px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
            padding: 32px 28px;
        }

        h1 {
            margin: 0 0 20px;
            text-align: center;
            font-size: 2rem;
        }

        .alert {
            margin: 0 0 16px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(180, 35, 24, 0.08);
            color: var(--danger);
            border: 1px solid rgba(180, 35, 24, 0.2);
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

        .link {
            margin-top: 18px;
            text-align: center;
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
        <h1>Cadastro</h1>
        <?php if ($erro !== ''): ?>
            <p class="alert"><?= escapar($erro) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
            <label>E-mail: <input type="email" name="email" value="<?= escapar($email) ?>" required></label>
            <label>Senha: <input type="password" name="senha" required></label>
            <label>Confirmar senha: <input type="password" name="confirmacao" required></label>
            <button type="submit">Cadastrar</button>
        </form>

        <p class="link"><a href="login.php">Voltar para o login</a></p>
    </div>
</body>
</html>
