<?php
require_once __DIR__ . '/db/entities/empresas.php';
require_once __DIR__ . '/db/entities/ativ01.php';

file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . "\n", FILE_APPEND);

$env   = parse_ini_file(__DIR__ . '/.env');
$token = $env['TELEGRAM_TOKEN'];

if (!$env || empty($env['TELEGRAM_TOKEN'])) {
    die('TELEGRAM_TOKEN não configurado.');
}

date_default_timezone_set('America/Sao_Paulo');
$data_atual = date('Y-m-d');
$hora_atual = date('H:i:s');

// Busca todas as empresas ativas com horário de início configurado
$empresas = Empresa::read(); // ajuste conforme seu método

foreach ($empresas as $empresa) {
    if($empresa->tolerancia == null) {
        $empresa->tolerancia = 0; 
    }
    // Pula se não tem horário configurado
    if ($empresa->hora_inicio == null) {
        file_put_contents(__DIR__ . '/cron_log.txt', 'continue(1)' . "\n", FILE_APPEND);
        continue;
    } else {
        file_put_contents(__DIR__ . '/cron_log.txt', 'passou continue(1)' . "\n", FILE_APPEND);
    }

    // Calcula o horário limite (inicio + tolerância em minutos)
    $hora_inicio_normalizada = strtotime($empresa->hora_inicio);
    $hora_limite_timestamp = $hora_inicio_normalizada + ($empresa->tolerancia * 60);

    if (time() < $hora_limite_timestamp) {
        file_put_contents(__DIR__ . '/cron_log.txt', 'continue(2)' . "\n", FILE_APPEND);
        continue;
    }

    $hora_limite = date('H:i:s', $hora_limite_timestamp);

    // Verifica se já registrou atividade hoje
    $atividade = Ativ01::read(id_empresa: $empresa->id, data: $data_atual);
    if ($atividade) {
        file_put_contents(__DIR__ . '/cron_log.txt', 'continue(3)' . "\n", FILE_APPEND);
        continue; // já registrou, ignora
    }

    // Verifica se já enviou notificação hoje (para não ficar reenviando)
    if ($empresa->notificacao_atraso_data === $data_atual) {
        file_put_contents(__DIR__ . '/cron_log.txt', 'continue(4)' . "\n", FILE_APPEND);
        continue;
    }

    file_put_contents(__DIR__ . '/cron_log.txt', 'passou os continues' . "\n", FILE_APPEND);
    // Envia notificação para cada Telegram vinculado
    $chats = array_filter([$empresa->celular1_atividade, $empresa->celular2_atividade]);
    foreach ($chats as $chat_id) {
        enviarAlerta($token, $chat_id, $empresa->nom_fant, $empresa->hora_inicio, $hora_limite);
    }

    // Registra que já notificou hoje
    Empresa::registrarNotificacaoAtraso($empresa->id, $data_atual);
}

function enviarAlerta($token, $chat_id, $nome_empresa, $hora_inicio, $hora_limite) {
    file_put_contents(__DIR__ . '/cron_log.txt', 'enviarAlerta' . "\n", FILE_APPEND);
    $mensagem  = "*Atividade não registrada!*\n\n";
    $mensagem .= "*Empresa:* {$nome_empresa}\n";
    $mensagem .= "*Deveria iniciar às:* " . date('H:i', strtotime($hora_inicio)) . "\n";
    $mensagem .= "*Tolerância até:* " . date('H:i', strtotime($hora_limite)) . "\n";
    $mensagem .= "*Data:* " . date('d/m/Y') . "\n";

    $url    = "https://api.telegram.org/bot{$token}/sendMessage";
    $params = ['chat_id' => $chat_id, 'text' => $mensagem, 'parse_mode' => 'Markdown'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $resposta = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('Erro Telegram: ' . curl_error($ch));
    }
    curl_close($ch);
    file_put_contents(__DIR__ . '/cron_log.txt', 'enviarAlerta - Resposta: ' . $resposta . "\n", FILE_APPEND);
    return json_decode($resposta, true);
    if (isset($resposta['ok']) && !$resposta['ok']) {
        error_log('Erro Telegram: ' . $resposta['description']);
    }
}