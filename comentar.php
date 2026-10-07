<?php
session_start();
require_once __DIR__ . '/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    encerrarComErro('Método não permitido.', 405);
}

$csrfToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!csrfValido($csrfToken)) {
    encerrarComErro('Formulário expirado. Tente novamente.', 403);
}

$id = obterIndice($_POST['id'] ?? null);
$nome = trim((string) ($_POST['nome'] ?? ''));
$mensagem = trim((string) ($_POST['mensagem'] ?? ''));

if ($id === null || $nome === '' || $mensagem === '') {
    encerrarComErro('Preencha o nome e a mensagem do comentário.');
}

try {
    $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');

    if (!isset($topicos->topico[$id])) {
        encerrarComErro('Tópico não encontrado.', 404);
    }

    $topico = $topicos->topico[$id];

    if (!isset($topico->comentarios)) {
        $topico->addChild('comentarios');
    }

    $comentario = $topico->comentarios->addChild('comentario');
    adicionarTextoXml($comentario, 'nome', $nome);
    adicionarTextoXml($comentario, 'mensagem', $mensagem);

    salvarXml($topicos, ARQUIVO_TOPICOS);
    header('Location: listar.php');
    exit;
} catch (RuntimeException $excecao) {
    encerrarComErro($excecao->getMessage(), 500);
}
