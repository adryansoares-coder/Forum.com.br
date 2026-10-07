<?php
session_start();
require_once __DIR__ . '/funcoes.php';

if (!isset($_SESSION['usuario'])) {
    encerrarComErro('Você precisa estar logado para criar um tópico.', 403);
}

$titulo = '';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }

    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

    if ($titulo === '' || $mensagem === '') {
        $erro = 'Informe o título e a mensagem.';
    } else {
        try {
            $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
            $novo = $topicos->addChild('topico');
            adicionarTextoXml($novo, 'autor', (string) $_SESSION['usuario']);
            adicionarTextoXml($novo, 'titulo', $titulo);
            adicionarTextoXml($novo, 'mensagem', $mensagem);
            $novo->addChild('comentarios');
            salvarXml($topicos, ARQUIVO_TOPICOS);

            $_SESSION['mensagem_sucesso'] = 'Tópico criado com sucesso!';
            header('Location: listar.php');
            exit;
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
    <title>Criar tópico</title>
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
            padding: 40px 20px;
            background: linear-gradient(135deg, #edf3ff, #f7f9fc);
            font-family: Arial, sans-serif;
            color: var(--text);
        }

        .card {
            max-width: 760px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
            padding: 32px 28px;
        }

        h1 {
            margin-top: 0;
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
            gap: 16px;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-weight: 600;
        }

        input, textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 107, 237, 0.12);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
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
            width: fit-content;
        }

        button:hover {
            background: var(--primary-dark);
        }

        .links {
            margin-top: 18px;
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
        <h1>Criar tópico</h1>
        <?php if ($erro !== ''): ?>
            <p class="alert"><?= escapar($erro) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
            <label>Título: <input type="text" name="titulo" value="<?= escapar($titulo) ?>" required></label>
            <label>Mensagem:<br><textarea name="mensagem" required><?= escapar($mensagem) ?></textarea></label>
            <button type="submit">Criar tópico</button>
        </form>

        <p class="links"><a href="listar.php">Voltar aos tópicos</a></p>
    </div>
</body>
</html>
