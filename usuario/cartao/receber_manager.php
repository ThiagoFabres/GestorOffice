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

$aprovada_parcela_lista = $_POST['aprovada']['parcela'] ?? [];
$aprovada_data_lista = $_POST['aprovada']['data'] ?? [];
$aprovada_valor_b_lista = $_POST['aprovada']['valor_b'] ?? [];
$aprovada_valor_l_lista = $_POST['aprovada']['valor_l'] ?? [];
$aprovada_bandeira_lista = $_POST['aprovada']['bandeira'] ?? [];
$aprovada_id_bandeira_lista = $_POST['aprovada']['bandeira_id'] ?? [];
$aprovada_tipo_lista = $_POST['aprovada']['tipo'] ?? [];
$transactions['aprovadas'] = [];
$i = 0;
$aprovada_tamanho = count($aprovada_parcela_lista) ?? 0;

$cancelada_data_lista = $_POST['cancelada']['data'] ?? [];
$cancelada_bandeira_lista= $_POST['cancelada']['bandeira'] ?? [];
$cancelada_tipo_lista= $_POST['cancelada']['tipo'] ?? [];
$cancelada_estado_lista= $_POST['cancelada']['estado'] ?? [];
$cancelada_valor_lista= $_POST['cancelada']['valor'] ?? [];
$cancelada_comprovante_lista= $_POST['cancelada']['comprovante'] ?? [];
$transactions['canceladas'] = [];
$j = 0;
$cancelada_tamanho = count($cancelada_data_lista) ?? 0;

$canceladas = $_POST['cancelada'] ?? [];
$transactions['canceladas'] = [];

if (!empty($canceladas['data']) && is_array($canceladas['data'])) {
    foreach ($canceladas['data'] as $j => $data) {
        $transactions['canceladas'][] = [
            'data'        => $data,
            'bandeira'    => $canceladas['bandeira'][$j] ?? '',
            'tipo'        => $canceladas['tipo'][$j] ?? '',
            'estado'      => $canceladas['estado'][$j] ?? '',
            'valor'       => $canceladas['valor'][$j] ?? 0,
            'comprovante' => $canceladas['comprovante'][$j] ?? null,
        ];
    }
}

$aprovadas = $_POST['aprovada'] ?? [];
$transactions['aprovadas'] = [];

if (!empty($aprovadas['parcela']) && is_array($aprovadas['parcela'])) {
    foreach ($aprovadas['parcela'] as $i => $parcela) {
        $tipo = $aprovadas['tipo'][$i] ?? '';
        $bandeira = $aprovadas['bandeira'][$i] ?? '';

        $transactions['aprovadas'][] = [
            'data'        => $aprovadas['data'][$i] ?? null,
            'bandeira'    => ($tipo === 'Pix') ? 'Pix' : $bandeira,
            'tipo'        => $tipo,
            'parcela'     => $parcela,
            'valor_b'     => $aprovadas['valor_b'][$i] ?? 0,
            'valor_l'     => $aprovadas['valor_l'][$i] ?? 0,
            'id_bandeira' => $aprovadas['bandeira_id'][$i] ?? null,
        ];
    }
}
$operadora_id = $_POST['operadora'] ?? null; // se existir
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
    if (!isset($bandeiras_unicas[$id_bandeira])) {
        $bandeiras_unicas[$id_bandeira] = true;
    }
}


foreach(array_keys($bandeiras_unicas) as $id_bandeira) {
    $cache_prazo[$id_bandeira] = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, direcao: 'DESC')[0];
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
        if($parcela['valor_l'] === null || $parcela['valor_l'] == 'Não Informado') {
            $valor_l = 0;
        }
        $valor_l = str_replace('.', '', $valor_l);
        $valor_l = str_replace(',', '.',$valor_l);
        $valor_l = floatval($valor_l);
        $parcela['valor_l'] = $valor_l;

        $valor_b = $parcela['valor_b'];
        $valor_b = str_replace('.', '', $valor_b);
        $valor_b = str_replace(',', '.',$valor_b);
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
    if($valor_b_total != $valor_l_total){
        $valor_liq_go = $valor_b_total - (($valor_b_total / 100) * $prazo->taxa);
    } else {
        $valor_liq_go = $valor_b_total;
    }

    $data = (DateTime::createFromFormat('d/m/Y', $grupo_base['data']))->format('Y-m-d');
    if(Rec03::read(
        null, 
        id_empresa:$_SESSION['usuario']->id_empresa, 
        data:$data, 
        operadora_id:$operadora->id, 
        bandeira_id:$id_bandeira, 
        tipo_id:null, 
        prazo_id:$prazo->id 
    )) {
        continue;
    }

    $desc = $operadora->descricao . ' - ' .$grupo_base['bandeira'] . ' - ' . $grupo_base['tipo']. ' - ' . $prazo->parcela . 'x';
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
        $prazo->taxa
    );
    
    $rec03[$documento] = new Rec03 (
        null,
        $_SESSION['usuario']->id_empresa,
        $data,
        $operadora->id,
        $id_bandeira,
        null,
        $prazo->id
    );
    if(!in_array($rec03[$documento],$rec03_lista)) {
        $rec03_lista[] = $rec03[$documento];
    }
    $rec01_lista[] = $rec01[$documento];
    Rec01::create($rec01[$documento]);
    $id_rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, documento:$documento)[0]->id;

    $last_rec02 = null;

    $parcel_value = $max_parcela > 0 ? round($valor_l_total / $max_parcela, 2) : 0;

    $prazo_por_parcela = [];

    for ($num = 1; $num <= $max_parcela; $num++) {
        if($valor_l_total == 0) {
            $valor_l_total = 0;
        }
        if ($num == $max_parcela) {
            $valor_parcela = $valor_l_total - $parcel_value * ($max_parcela - 1);
        } else {
            $valor_parcela = $parcel_value;
        }
        if (!isset($prazo_por_parcela[$num])) {
            $prazo_por_parcela[$num] = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, parcela: $num)[0] ?? $prazo;
        }
        $prazo_parcela = $prazo_por_parcela[$num];

        $data_venc = (new DateTime($data))
                        ->modify("+" . $prazo_parcela->prazo . " Days")
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

$grupos = [];
$canceladas_para_processar = [];

foreach($transactions['canceladas'] as $t) {
    $data        = $t['data'];
    $bandeira    = $t['bandeira'];
    $tipo        = $t['tipo'];
    $estado      = $t['estado'];
    $valor       = $t['valor'];
    $comprovante = $t['comprovante'];

    // Tratamento e formatação do valor
    if ($valor === null || $valor == 'Não Informado') {
        $valor = 0;
    }
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    $valor = floatval($valor);

    // Formata a data para o padrão do Banco de Dados (Y-m-d) se ela vier em d/m/Y
    if (strpos($data, '/') !== false) {
        $data = (DateTime::createFromFormat('d/m/Y', $data))->format('Y-m-d');
    }

    // Cria a nova instância do objeto Cancelada
    $nova_cancelada = new Cancelada(
        null,                               // id (auto-increment)
        $_SESSION['usuario']->id_empresa,   // id_empresa
        $data,                              // data
        $bandeira,                          // bandeira
        $tipo,                              // tipo
        $estado,                            // estado
        $valor,                             // valor
        $comprovante                        // comprovante
    );

    // Salva no banco de dados
    Cancelada::create($nova_cancelada);
    
    $canceladas_para_processar[] = $nova_cancelada;
}

// Redireciona para a página com mensagem de sucesso
header('Location: /usuario/cartao/cadastro_vendas.php?sucesso=1');
exit;
?>






?>