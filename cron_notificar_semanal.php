<?php 
require_once __DIR__ . '/db/entities/empresas.php';
require_once __DIR__ . '/db/entities/pagar.php';

$empresas = Empresa::readEmpresasNotificacaoSemanal();

foreach($empresas as $empresa) {
    $detalhes = Pag02::readDetalhesSemana($empresa->id);

    enviarNotificacaoTelegram(
        $empresa->id,
        $empresa->nom_fant,
        $empresa,
        parse_ini_file(__DIR__ . '/.env'),
        detalhes: $detalhes,
        total_vencido: $detalhes['total_vencidas'] ?? 0,
        total_semana: $detalhes['total_a_vencer'] ?? 0
    );
}

function enviarNotificacaoTelegram($id_empresa, $nome, $empresa_usuario_obj, $env, $total_vencido = 0, $total_semana = 0, $detalhes = []) {

    $TELEGRAM_TOKEN = $env['TELEGRAM_TOKEN'];
    $CHAT_ID_LISTA = [
        $empresa_usuario_obj->celular1_atividade,
        $empresa_usuario_obj->celular2_atividade
    ];

    // Monta bloco de contas vencidas
    $mensagem = "*Empresa:* " . htmlspecialchars($nome) . "\n\n";
    $mensagem .= "*Contas Vencidas:*\n";

    if (!empty($detalhes['vencidas'])) {
        foreach ($detalhes['vencidas'] as $item) {
            $venc = date('d/m/Y', strtotime($item['vencimento']));
            $valor = number_format($item['valor_par'], 2, ',', '.');
            $mensagem .= "• Doc: {$item['documento']} | {$venc} | {$item['descricao']} | R$ {$valor}\n";
        }
    } else {
        $mensagem .= "Nenhuma conta vencida.\n";
    }

    // Monta bloco de contas a vencer
    $mensagem .= "\n*Contas a vencer nos próximos 07 dias:*\n";

    if (!empty($detalhes['a_vencer'])) {
        foreach ($detalhes['a_vencer'] as $item) {
            $venc = date('d/m/Y', strtotime($item['vencimento']));
            $valor = number_format($item['valor_par'], 2, ',', '.');
            $mensagem .= "• Doc: {$item['documento']} | {$venc} | {$item['descricao']} | R$ {$valor}\n";
        }
    } else {
        $mensagem .= "Nenhuma conta a vencer nos próximos 7 dias.\n";
    }

    $mensagem .= "\n*Total vencido:* R$ {$total_vencido}";
    $mensagem .= "\n*Total vence na semana:* R$ {$total_semana}";

    foreach ($CHAT_ID_LISTA as $CHAT_ID) {
        if (empty($CHAT_ID)) continue;

        $url = "https://api.telegram.org/bot{$TELEGRAM_TOKEN}/sendMessage";
        $parametros = [
            'chat_id'    => $CHAT_ID,
            'text'       => $mensagem,
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
    }
}



?>