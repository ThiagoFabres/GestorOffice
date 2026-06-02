<?php
require_once __DIR__ . '/db/entities/empresas.php';
require_once __DIR__ . '/db/entities/ativ01.php';


$env   = parse_ini_file(__DIR__ . '/.env');
$token = $env['TELEGRAM_TOKEN'];

if (!$env || empty($env['TELEGRAM_TOKEN'])) {
    die('TELEGRAM_TOKEN não configurado.');
}

date_default_timezone_set('America/Sao_Paulo');
$data_atual = date('Y-m-d');
$hora_atual = date('H:i:s');

// Busca todas as empresas ativas com horário de início configurado
$empresas = Empresa::readEmpresasAtrasadas(); // ajuste conforme seu método

foreach ($empresas as $empresa) {

    $chats = array_filter([
        $empresa->celular1_atividade,
        $empresa->celular2_atividade
    ]);

    foreach ($chats as $chat_id) {
        enviarAlerta(
            $token,
            $chat_id,
            $empresa->nom_fant,
            $empresa->ativ_inicio,
            $hora_limite = date('H:i:s', strtotime($empresa->ativ_inicio . ' +'. $empresa->tolerancia . ' minutes'))
        );
    }

    Empresa::registrarNotificacaoAtraso(
        $empresa->id,
        date('Y-m-d')
    );
}

function enviarAlerta($token, $chat_id, $nome_empresa, $ativ_inicio, $hora_limite) {
    $mensagem  = "*Atividade não registrada!*\n\n";
    $mensagem .= "*Empresa:* {$nome_empresa}\n";
    $mensagem .= "*Deveria iniciar às:* " . date('H:i', strtotime($ativ_inicio)) . "\n";
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
    return json_decode($resposta, true);
}