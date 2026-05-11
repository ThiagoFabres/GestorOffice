<?php
require_once __DIR__ . '/../../../db/entities/ativ01.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
if($empresa_usuario_obj->permissao_inicio == 0) {
    header('Location: ../../index.php?erro=permissao');
    exit;
}
date_default_timezone_set('America/Sao_Paulo');

$env = parse_ini_file(__DIR__ . '/../../../.env');

$data_atual = date('Y-m-d');
$hora_atual = date('H:i:s');
function enviarNotificacaoTelegram($id_empresa, $nome, $localizacao, $data, $hora, $empresa_usuario_obj, $env) {
    // Suas credenciais do Telegram Bot
    $TELEGRAM_TOKEN = $env['TELEGRAM_TOKEN'];
    $CHAT_ID_LISTA = [0 => $empresa_usuario_obj->celular1_atividade, 1 => $empresa_usuario_obj->celular2_atividade]; // ID do chat (usuário, grupo ou canal)
    foreach($CHAT_ID_LISTA as $CHAT_ID) {
        if($CHAT_ID === null || $CHAT_ID === '') {
            continue; // Pula se o chat_id não estiver configurado
        }
        $mensagem = "*Início de Atividade Registrado*\n\n";
        $mensagem .= "*Nome:* " . htmlspecialchars($nome) . "\n";
        $mensagem .= "*Localização:* " . htmlspecialchars($localizacao) . "\n";
        $mensagem .= "*Data:* " . date('d/m/Y', strtotime($data)) . "\n";
        $mensagem .= "*Hora:* " . date('H:i:s', strtotime($hora)) . "\n";
        
        $url = "https://api.telegram.org/bot{$TELEGRAM_TOKEN}/sendMessage";
        
        $parametros = [
            'chat_id' => $CHAT_ID,
            'text' => $mensagem,
            'parse_mode' => 'Markdown'
        ];
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parametros));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $resposta = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($resposta, true);
    }
}

if($_POST['localizacao'] == '') {
    header('Location: atividade.php?erro=localizacao');
    exit;
}

if($_POST['nome'] == '') {
    header('Location: atividade.php?erro=nome');
    exit;
}


$ativ01 = new Ativ01(
    id:null,
    id_empresa:$_SESSION['usuario']->id_empresa,
    data:$data_atual,
    hora:$hora_atual,
    nome:$_POST['nome'],
    localizacao:$_POST['localizacao'],
    tipo:$_POST['tipo'] ?? null
    );

Ativ01::create($ativ01);

// Enviar notificação para Telegram
enviarNotificacaoTelegram(
    $_SESSION['usuario']->id_empresa,
    $_POST['nome'],
    $_POST['localizacao'],
    $data_atual,
    $hora_atual,
    $empresa_usuario_obj,
    $env
);

header('Location: atividade.php?status=sucesso');

// Função para enviar mensagem ao Telegram
