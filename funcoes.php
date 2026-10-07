<?php

define('ARQUIVO_USUARIOS', __DIR__ . '/usuarios.xml');
define('ARQUIVO_TOPICOS', __DIR__ . '/topicos.xml');

function escapar(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function carregarXml(string $caminho, string $rootTag): SimpleXMLElement {
    if (!file_exists($caminho) || filesize($caminho) === 0) {
        $xmlInicial = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<{$rootTag}></{$rootTag}>\n";
        if (file_put_contents($caminho, $xmlInicial) === false) {
            throw new RuntimeException('Não foi possível inicializar o arquivo XML.');
        }
    }

    $xml = simplexml_load_file($caminho);
    if ($xml === false) {
        throw new RuntimeException('Erro ao carregar o arquivo XML de dados.');
    }

    return $xml;
}

function adicionarTextoXml(SimpleXMLElement $pai, string $nomeElemento, string $valor): void {
    $child = $pai->addChild($nomeElemento);
    $domChild = dom_import_simplexml($child);
    if ($domChild === false) {
        throw new RuntimeException('Não foi possível criar o elemento XML.');
    }

    $domOwner = $domChild->ownerDocument;
    $domChild->appendChild($domOwner->createCDATASection($valor));
}

function salvarXml(SimpleXMLElement $xml, string $caminho): void {
    $dom = dom_import_simplexml($xml);
    if ($dom === false) {
        throw new RuntimeException('Não foi possível preparar a estrutura XML para salvamento.');
    }

    $documento = $dom->ownerDocument;
    $documento->formatOutput = true;

    if ($documento->save($caminho) === false) {
        throw new RuntimeException('Não foi possível salvar os dados no arquivo XML.');
    }
}

function tokenCsrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfValido($token): bool {
    if (!is_string($token)) {
        return false;
    }

    $esperado = $_SESSION['csrf_token'] ?? '';
    return hash_equals($esperado, $token);
}

function obterIndice($valor): ?int {
    if (!is_scalar($valor) && $valor !== null) {
        return null;
    }

    $texto = trim((string) $valor);
    if ($texto === '' || !preg_match('/^\d+$/', $texto)) {
        return null;
    }

    $indice = (int) $texto;
    return $indice >= 0 ? $indice : null;
}

function encerrarComErro(string $mensagem, int $status = 400): void {
    http_response_code($status);
    echo '<!doctype html>' .
        '<html lang="pt-BR">' .
        '<head><meta charset="UTF-8"><title>Erro</title></head>' .
        '<body>' .
        '<p>' . escapar($mensagem) . '</p>' .
        '<p><a href="listar.php">Voltar</a></p>' .
        '</body></html>';
    exit;
}
