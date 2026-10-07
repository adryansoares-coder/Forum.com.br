<?php
session_start();
require_once __DIR__ . '/funcoes.php';

$mensagemSucesso = '';
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagemSucesso = (string) $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fórum</title>
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
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 32px 20px 60px;
            background: linear-gradient(180deg, #edf3ff, #f7f9fc);
            font-family: Arial, sans-serif;
            color: var(--text);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .success {
            margin: 0 0 18px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(31, 157, 97, 0.08);
            color: var(--success);
            border: 1px solid rgba(31, 157, 97, 0.2);
        }

        .forum-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px 22px;
            margin-bottom: 22px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
        }

        .forum-card h2 {
            margin-top: 0;
        }

        .meta {
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .comment-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
            margin: 18px 0;
        }

        .comment-box strong {
            display: block;
            margin-bottom: 8px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
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
            min-height: 120px;
            resize: vertical;
        }

        button {
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 10px 16px;
            cursor: pointer;
            transition: background 0.2s ease;
            width: fit-content;
        }

        button:hover {
            background: var(--primary-dark);
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
    <div class="container">
        <?php if ($mensagemSucesso !== ''): ?>
            <p class="success"><?= escapar($mensagemSucesso) ?></p>
        <?php endif; ?>

        <div class="topbar">
            <?php if (isset($_SESSION['usuario'])): ?>
                <span>Logado como <?= escapar((string) $_SESSION['usuario']) ?></span>
                <span>
                    <a href="criar_topico.php">Criar tópico</a> |
                    <a href="login.php?logout=1">Sair</a>
                </span>
            <?php else: ?>
                <span>Bem-vindo</span>
                <span>
                    <a href="login.php">Entrar</a> |
                    <a href="cadastro.php">Criar cadastro</a>
                </span>
            <?php endif; ?>
        </div>

        <?php
        try {
            $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
        } catch (RuntimeException $excecao) {
            echo '<p class="success">' . escapar($excecao->getMessage()) . '</p>';
            exit;
        }
        ?>

        <?php foreach ($topicos->topico as $indice => $topico): ?>
            <?php $titulo = (string) ($topico->titulo ?? ''); $autor = (string) ($topico->autor ?? ''); $mensagem = (string) ($topico->mensagem ?? ''); ?>
            <article class="forum-card">
                <h2><?= escapar($titulo) ?></h2>
                <div class="meta"><strong>Autor:</strong> <?= escapar($autor) ?></div>
                <p><?= nl2br(escapar($mensagem), false) ?></p>

                <?php if (isset($topico->comentarios->comentario)): ?>
                    <h3>Comentários</h3>
                    <?php foreach ($topico->comentarios->comentario as $comentarioIndice => $comentario): ?>
                        <?php $nomeComentario = (string) ($comentario->nome ?? ''); $textoComentario = (string) ($comentario->mensagem ?? ''); ?>
                        <div class="comment-box">
                            <strong><?= escapar($nomeComentario) ?></strong>
                            <div><?= nl2br(escapar($textoComentario), false) ?></div>
                            <?php if (isset($_SESSION['usuario']) && (string) $topico->autor === $_SESSION['usuario']): ?>
                                <form method="post" action="excluir.php">
                                    <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                                    <input type="hidden" name="id" value="<?= escapar((string) $indice) ?>">
                                    <input type="hidden" name="comentario" value="<?= escapar((string) $comentarioIndice) ?>">
                                    <button type="submit">Excluir comentário</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="post" action="comentar.php">
                    <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                    <input type="hidden" name="id" value="<?= escapar((string) $indice) ?>">
                    <label>Nome:<input type="text" name="nome" required></label>
                    <label>Mensagem:<textarea name="mensagem" required></textarea></label>
                    <button type="submit">Comentar</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</body>
</html>
