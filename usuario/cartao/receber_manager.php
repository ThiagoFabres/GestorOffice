<?php
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
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

$parcela_lista = $_POST['parcela'];
$data_lista = $_POST['data'];
$valor_b_lista = $_POST['valor_b'];
$valor_l_lista = $_POST['valor_l'];

$bandeira_lista = $_POST['bandeira'];
$id_bandeira_lista = $_POST['bandeira_id'];
$tipo_lista = $_POST['tipo'];
$transactions = [];
$i = 0;
$tamanho = count($parcela_lista);


while($tamanho != 0 ) {
    $transactions[$i] = [
        'data' => $data_lista[$i],
        'bandeira' => $tipo_lista[$i] == 'Pix' ? 'Pix' : $bandeira_lista[$i],
        'tipo' => $tipo_lista[$i],
        'parcela' => $parcela_lista[$i],
        'valor_b' => $valor_b_lista[$i],
        'valor_l' => $valor_l_lista[$i],
        'id_bandeira' => $id_bandeira_lista[$i],
    ];
$tamanho--;
$i++;
}
$operadora_id = $_POST['operadora'] ?? null; // se existir
$operadora = Ope01::read($operadora_id)[0];


$grupos = [];
foreach ($transactions as $t) {
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

    

    if($valor_b_total != $valor_l_total){
    $prazo = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $id_bandeira, parcela: $max_parcela)[0] ?? $cache_prazo[$id_bandeira];
    
    $valor_liq_go = $valor_b_total - (($valor_b_total / 100) * $prazo->taxa );
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
        $valor_l_total == 0 ? $valor_liq_go : $valor_l_total,
        $max_parcela,
        $data,
        $_SESSION['usuario']->id,
        $operadora->id_custos,
        null,
        $valor_b_total,
        $valor_liq_go
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
            $valor_l_total = $valor_liq_go;
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
header('Location: cadastro_vendas.php');
exit;






?>