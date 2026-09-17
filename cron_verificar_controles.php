<?php

require_once __DIR__ . '/db/base.php';

date_default_timezone_set('America/Sao_Paulo');

$env = parse_ini_file(__DIR__ . '/.env') ?: [];
header('Content-Type: application/json; charset=utf-8');
$pdo = (new Database())->connect();
$agora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
$dataHoje = $agora->format('Y-m-d');
$horaAtual = $agora->format('H:i:s');
$estadoPath = __DIR__ . '/cron_controles_estado.json';
$lockHandle = fopen(__DIR__ . '/cron_controles.lock', 'c');
if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Outra execução está em andamento.']);
    exit;
}

$estado = carregarEstado($estadoPath);
$estadoAlterado = false;
$alertas = 0;
$erros = [];

$sql = <<<'SQL'
SELECT
    t.id_usuario,
    u.nome AS nome_usuario,
    u.id_empresa,
    e.nom_fant AS nome_empresa,
    e.celular1_atividade,
    e.celular2_atividade,
    c.id AS id_controle,
    c.hora AS hora_esperada,
    c.tolerancia
FROM turnos t
INNER JOIN usuario u ON u.id = t.id_usuario
INNER JOIN empresas e ON e.id = u.id_empresa
INNER JOIN controle c ON c.id_empresa = u.id_empresa
WHERE t.status = 'ativo'
  AND u.status = 1
  AND e.status = 1
  AND e.permissao_seguranca = 1
SQL;

$registros = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($registros as $registro) {
    $horaEsperada = substr((string) $registro['hora_esperada'], 0, 8);
    $esperada = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i:s',
        $dataHoje . ' ' . $horaEsperada,
        new DateTimeZone('America/Sao_Paulo')
    );

    if (!$esperada) {
        $erros[] = "Horário inválido no controle {$registro['id_controle']}.";
        continue;
    }

    $limite = $esperada->modify('+' . max(0, (int) $registro['tolerancia']) . ' minutes');
    // O controle só fica pendente depois de ultrapassar o horário e a tolerância.
    if ($agora->getTimestamp() <= $limite->getTimestamp()) {
        continue;
    }

    $chave = implode(':', [
        $dataHoje,
        (int) $registro['id_usuario'],
        (int) $registro['id_controle'],
    ]);

    if (!empty($estado[$chave])) {
        continue;
    }

    $insert = $pdo->prepare(
        'INSERT INTO controle02
            (id_empresa, id_usuario, hora_esperada, hora_respondida, tolerancia)
         VALUES
            (:id_empresa, :id_usuario, :hora_esperada, NULL, :tolerancia)'
    );
    $insert->execute([
        ':id_empresa' => $registro['id_empresa'],
        ':id_usuario' => $registro['id_usuario'],
        ':hora_esperada' => $horaEsperada,
        ':tolerancia' => $registro['tolerancia'],
    ]);

    $mensagem = "🚨 <b>PONTO DE CONTROLE ATRASADO</b>\n"
        . 'Empresa: ' . htmlspecialchars((string) $registro['nome_empresa'], ENT_QUOTES, 'UTF-8') . "\n"
        . 'Usuário: ' . htmlspecialchars((string) $registro['nome_usuario'], ENT_QUOTES, 'UTF-8') . "\n"
        . 'Esperado: ' . $esperada->format('d/m/Y H:i:s') . "\n"
        . 'Limite: ' . $limite->format('d/m/Y H:i:s');

    foreach ([(string) $registro['celular1_atividade'], (string) $registro['celular2_atividade']] as $chatId) {
        if ($chatId === '') {
            continue;
        }

        if (!enviarTelegram((string) ($env['TELEGRAM_TOKEN'] ?? ''), $chatId, $mensagem)) {
            $erros[] = "Falha ao enviar Telegram para o usuário {$registro['id_usuario']}.";
        }
    }

    $estado[$chave] = true;
    $estadoAlterado = true;
    $alertas++;
}

if ($estadoAlterado) {
    salvarEstado($estadoPath, $estado);
}

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

echo json_encode([
    'success' => true,
    'executado_em' => $agora->format(DateTimeInterface::ATOM),
    'alertas_criados' => $alertas,
    'erros' => $erros,
], JSON_UNESCAPED_UNICODE);

function carregarEstado(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $conteudo = file_get_contents($path);
    $estado = json_decode($conteudo ?: '', true);

    return is_array($estado) ? $estado : [];
}

function salvarEstado(string $path, array $estado): void
{
    $limite = (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))
        ->modify('-2 days')
        ->format('Y-m-d');

    foreach (array_keys($estado) as $chave) {
        if (substr($chave, 0, 10) < $limite) {
            unset($estado[$chave]);
        }
    }

    file_put_contents($path, json_encode($estado, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function enviarTelegram(string $token, string $chatId, string $mensagem): bool
{
    if ($token === '') {
        return false;
    }

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'chat_id' => $chatId,
            'text' => $mensagem,
            'parse_mode' => 'HTML',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    $resposta = curl_exec($ch);
    $erro = curl_errno($ch);
    curl_close($ch);

    if ($erro !== 0 || $resposta === false) {
        return false;
    }

    $dados = json_decode($resposta, true);
    return is_array($dados) && ($dados['ok'] ?? false) === true;
}
