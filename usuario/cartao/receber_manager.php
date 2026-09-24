<?php
require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
session_start();

$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_cartao != 1 || $empresa_usuario_obj->permissao_cartao != 1) {
    header('Location: /');
    exit;
}

if($_SESSION['usuario']->processar !== 1) {
    header('Location: /usuario/cartao/cadastro_vendas.php?erro=permissao');
    exit;
}

require_once __DIR__ . '/../../db/entities/ope01.php';
require_once __DIR__ . '/../../db/entities/band01.php';
require_once __DIR__ . '/../../db/entities/pra01.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/buscar_documento_rec.php';

$aprovadas_raw = json_decode($_POST['aprovadas_json'] ?? '[]', true);
$canceladas_raw = json_decode($_POST['canceladas_json'] ?? '[]', true);

$transactions['canceladas'] = [];
foreach ($canceladas_raw as $linha) {
    $transactions['canceladas'][] = [
        'data'        => $linha['data'] ?? '',
        'bandeira'    => $linha['bandeira'] ?? '',
        'tipo'        => $linha['tipo'] ?? '',
        'estado'      => $linha['status'] ?? '',
        'valor'       => $linha['valor'] ?? 0,
        'comprovante' => $linha['comprovante'] ?? null,
    ];
}

$transactions['aprovadas'] = [];
foreach ($aprovadas_raw as $linha) {
    $tipo = $linha['tipo'] ?? '';
    $bandeira = $linha['bandeira'] ?? '';

    $transactions['aprovadas'][] = [
        'data'        => $linha['data'] ?? null,
        'bandeira'    => ($tipo === 'Pix') ? 'Pix' : $bandeira,
        'tipo'        => $tipo,
        'parcela'     => $linha['parcela'] ?? 1,
        'valor_b'     => $linha['valor_b'] ?? 0,
        'valor_l'     => $linha['valor_l'] ?? 0,
        'id_bandeira' => $linha['bandeira_id'] ?? null,
    ];
}

$operadora_id = $_POST['operadora'] ?? null;
$operadora = Ope01::read($operadora_id)[0];

$grupos = [];
foreach ($transactions['aprovadas'] as $t) {
    $data       = $t['data'];
    $bandeira   = $t['bandeira'];
    $tipo       = $t['tipo'];
    $n_parcelas = $t['parcela'];

    $key = "{$operadora->id}|{$data}|{$bandeira}|{$tipo}|{$n_parcelas}";
    if (!isset($grupos[$key])) {
        $grupos[$key] = [];
    }
    $grupos[$key][] = $t;
}

$documento = buscarDocumentoRec();
$rec01_lista = [];
$rec02_lista = [];
$rec03_lista = [];
$grupo_feito = [];
$cache_prazo = []; 

$bandeiras_unicas = [];
foreach($grupos as $group) {
    $first = $group[0];
    $id_bandeira = $first['id_bandeira'];
    if ($id_bandeira && !isset($bandeiras_unicas[$id_bandeira])) {
        $bandeiras_unicas[$id_bandeira] = true;
    }
}

foreach(array_keys($bandeiras_unicas) as $id_bandeira) {
    $cache_prazo[$id_bandeira] = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, direcao: 'DESC')[0] ?? null;
}

foreach($grupos as $key => $group) {
    $valor_l_total = 0;
    $valor_b_total = 0;
    $grupo_base = $group[0];
    $tamanho_grupo = count($group);
    $id_bandeira = $grupo_base['id_bandeira'];

    $max_parcela = 0;

    foreach($group as $idx => $parcela) {
        $valor_l = $parcela['valor_l'];
        if($valor_l === null || $valor_l === 'Não Informado') {
            $valor_l = 0;
        }
        if (is_string($valor_l)) {
            $valor_l = str_replace('.', '', $valor_l);
            $valor_l = str_replace(',', '.', $valor_l);
        }
        $valor_l = floatval($valor_l);
        $parcela['valor_l'] = $valor_l;

        $valor_b = $parcela['valor_b'];
        if (is_string($valor_b)) {
            $valor_b = str_replace('.', '', $valor_b);
            $valor_b = str_replace(',', '.', $valor_b);
        }
        $valor_b = floatval($valor_b);
        $parcela['valor_b'] = $valor_b;

        $valor_l_total += $valor_l;
        $valor_b_total += $valor_b;

        $numero_parcela = (int) $parcela['parcela'];
        if ($numero_parcela > $max_parcela) {
            $max_parcela = $numero_parcela;
        }

        $group[$idx] = $parcela;
    }

    $prazo = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, parcela: $max_parcela)[0] ?? $cache_prazo[$id_bandeira];
    
    if($valor_b_total != $valor_l_total && $prazo){
        $valor_liq_go = $valor_b_total - (($valor_b_total / 100) * $prazo->taxa);
    } else {
        $valor_liq_go = $valor_b_total;
    }

    // TRATAMENTO DA DATA (LINHA 144 CORRIGIDA):
    // Aceita múltiplos formatos sem quebrar a execução
    $raw_data = $grupo_base['data'];
    $dt = DateTime::createFromFormat('d/m/Y', $raw_data);
    if (!$dt) {
        $dt = new DateTime($raw_data);
    }
    $data = $dt->format('Y-m-d');

    if(Rec03::read(
        null, 
        id_empresa:$_SESSION['usuario']->id_empresa, 
        data:$data, 
        operadora_id:$operadora->id, 
        bandeira_id:$id_bandeira, 
        tipo_id:null, 
        prazo_id:$prazo ? $prazo->id : null
    )) {
        continue;
    }

    $desc = $operadora->descricao . ' - ' .$grupo_base['bandeira'] . ' - ' . $grupo_base['tipo']. ' - ' . ($prazo ? $prazo->parcela : $max_parcela) . 'x';
    
    $rec01[$documento] = new Rec01 (
        null,
        $_SESSION['usuario']->id_empresa,
        $operadora->id_cliente,
        $operadora->id_con01,
        $operadora->id_con02,
        $documento,
        $desc,
        $valor_l_total == 0 ? 0 : $valor_l_total,
        $max_parcela,
        $data,
        $_SESSION['usuario']->id,
        $operadora->id_custos,
        null,
        $valor_b_total,
        $valor_liq_go,
        $prazo ? $prazo->taxa : 0
    );
    
    $rec03[$documento] = new Rec03 (
        null,
        $_SESSION['usuario']->id_empresa,
        $data,
        $operadora->id,
        $id_bandeira,
        null,
        $prazo ? $prazo->id : null
    );

    if(!in_array($rec03[$documento], $rec03_lista)) {
        $rec03_lista[] = $rec03[$documento];
    }
    $rec01_lista[] = $rec01[$documento];
    Rec01::create($rec01[$documento]);
    $id_rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, documento:$documento)[0]->id;

    $last_rec02 = null;
    $parcel_value = $max_parcela > 0 ? round($valor_l_total / $max_parcela, 2) : 0;
    $prazo_por_parcela = [];

    for ($num = 1; $num <= $max_parcela; $num++) {
        if ($num == $max_parcela) {
            $valor_parcela = $valor_l_total - $parcel_value * ($max_parcela - 1);
        } else {
            $valor_parcela = $parcel_value;
        }

        if (!isset($prazo_por_parcela[$num])) {
            $prazo_por_parcela[$num] = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, parcela: $num)[0] ?? $prazo;
        }
        $prazo_parcela = $prazo_por_parcela[$num];
        $dias_prazo = $prazo_parcela ? $prazo_parcela->prazo : 30;

        $data_venc = (new DateTime($data))
                        ->modify("+" . $dias_prazo . " Days")
                        ->format('Y-m-d');

        $rec02_entry = new Rec02 (
            null,
            $_SESSION['usuario']->id_empresa,
            $id_rec01,
            $valor_parcela,
            $num,
            $data_venc,
            $valor_parcela,
            $data_venc,
            null,
            null
        );
        $rec02_lista[] = $rec02_entry;
        
        Rec02::create($rec02_entry);
        $last_rec02 = $rec02_entry;
    }

    $grupo_feito[] = [
        $rec01[$documento],
        $last_rec02
    ];

    $documento++;
}

foreach($rec03_lista as $rec03) {
    Rec03::create($rec03);
}

require_once __DIR__ . '/../../db/entities/cancelada.php';

$canceladas_para_processar = [];

foreach($transactions['canceladas'] as $t) {
    $data        = $t['data'];
    $bandeira    = $t['bandeira'];
    $tipo        = $t['tipo'];
    $estado      = $t['estado'];
    $valor       = $t['valor'];
    $comprovante = $t['comprovante'];

    if ($valor === null || $valor === 'Não Informado') {
        $valor = 0;
    }
    if (is_string($valor)) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    $valor = floatval($valor);

    if (!empty($data)) {
        $dt_canc = DateTime::createFromFormat('d/m/Y', $data);
        if (!$dt_canc) {
            $dt_canc = new DateTime($data);
        }
        $data = $dt_canc->format('Y-m-d');
    }

    $nova_cancelada = new Cancelada(
        null,
        $_SESSION['usuario']->id_empresa,
        $data,
        $bandeira,
        $tipo,
        $estado,
        $valor,
        $comprovante,
        id_custos: $operadora->id_custos,
        id_cadastro: $operadora->id_cliente,
        id_con01: $operadora->id_con01,
        id_con02: $operadora->id_con02
    );

    Cancelada::create($nova_cancelada);
    $canceladas_para_processar[] = $nova_cancelada;
}

// Limpa as variáveis da sessão após salvar para resetar o modal
unset($_SESSION['vendas']);
unset($_SESSION['vendas_invalidas']);

header('Location: /usuario/cartao/cadastro_vendas.php?sucesso=1');
exit;