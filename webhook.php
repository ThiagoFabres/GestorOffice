<?php
require_once __DIR__ . '/db/entities/empresas.php';

// Recebe o JSON do Telegram
$update = json_decode(file_get_contents('php://input'), true);

if (!$update || !isset($update['message'])) {
    exit;
}

$chat_id = $update['message']['chat']['id'];
$texto   = $update['message']['text'] ?? '';
$nome    = $update['message']['chat']['first_name'] ?? 'Gestor';

$TELEGRAM_TOKEN = getenv('TELEGRAM_TOKEN');

// Verifica se é um comando /start com parâmetro
if (str_starts_with($texto, '/start')) {
    $partes = explode(' ', $texto);
    $payload = isset($partes[1]) ? $partes[1] : null; // ex: "42_1"

    if ($payload) {
        $dados      = explode('_', $payload);
        $id_empresa = intval($dados[0]); // 42
        $numero     = intval($dados[1]); // 1 ou 2

        if ($numero === 1) {
            Empresa::salvarChatId1($id_empresa, $chat_id);
        } else if ($numero === 2) {
            Empresa::salvarChatId2($id_empresa, $chat_id);
        }

        $resposta = "Olá, {$nome}! Telegram {$numero} vinculado com sucesso.\nVocê receberá notificações da sua loja.";
    } else {
        $resposta = "Link inválido. Acesse o painel e clique novamente.";
    }
    // Responde ao gestor
    $url = "https://api.telegram.org/bot{$TELEGRAM_TOKEN}/sendMessage";
    $parametros = [
        'chat_id'    => $chat_id,
        'text'       => $resposta,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parametros));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

exit;