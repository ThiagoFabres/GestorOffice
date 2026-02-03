<?php
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
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
        'bandeira' => $bandeira_lista[$i],
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

    $data     = $t['data'];
    $bandeira = $t['bandeira'];
    $tipo     = $t['tipo'];
    $parcela  = (int) $t['parcela'];

    $chaveBase = "{$data}|{$bandeira}|{$tipo}";

    if (!isset($grupos[$chaveBase])) {
        $grupos[$chaveBase] = [];
    }

    $inserido = false;

    // percorre os subgrupos existentes
    foreach ($grupos[$chaveBase] as $idx => $subgrupo) {

        // cria um índice rápido das parcelas já usadas nesse subgrupo
        if (!isset($grupos[$chaveBase][$idx]['_parcelas'])) {
            $grupos[$chaveBase][$idx]['_parcelas'] = [];
        }

        // se essa parcela ainda não existe nesse subgrupo
        if (!isset($grupos[$chaveBase][$idx]['_parcelas'][$parcela])) {

            $grupos[$chaveBase][$idx][] = $t;
            $grupos[$chaveBase][$idx]['_parcelas'][$parcela] = true;

            $inserido = true;
            break;
        }
    }

    // se não coube em nenhum subgrupo, cria um novo
    if (!$inserido) {
        $grupos[$chaveBase][] = [
            '_parcelas' => [
                $parcela => true
            ],
            $t
        ];
    }
}
foreach ($grupos as &$listaGrupos) {
    foreach ($listaGrupos as &$subgrupo) {
        unset($subgrupo['_parcelas']);
    }
}

$documento = buscarDocumentoRec();
$rec01_lista = [];
$rec02_lista = [];
$rec03_lista = [];
$grupo_feito = [];
foreach($grupos as $grupo) {
    $valor_l_total = 0;
    $valor_b_total = 0;
    $grupo_base = $grupo[0][0];
    $tamanho_grupo = count($grupo[0]);
    $prazo = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $grupo_base['id_bandeira'], direcao: 'DESC')[0];
    foreach($grupo[0] as $parcela) {
        
        $valor_l = $parcela['valor_l'];
        $valor_l = str_replace('.', '', $valor_l);
        $valor_l = str_replace(',', '.',$valor_l);

        $valor_b = $parcela['valor_b'];
        $valor_b = str_replace('.', '', $valor_b);
        $valor_b = str_replace(',', '.',$valor_b);
        $valor_b = floatval($valor_b);

        $valor_l_total += $valor_l;
        $valor_b_total += $valor_b;
    }
    $valor_liq_go = $valor_b_total - (($valor_b_total / 100) * $prazo->taxa );

    $data = (DateTime::createFromFormat('d/m/Y', $grupo_base['data']))->format('Y-m-d');
    if(Rec03::read(null, $_SESSION['usuario']->id_empresa, $data, $operadora->id, $grupo_base['id_bandeira'], null, $prazo->id )) {
        continue;
    }


    $desc = $operadora->descricao . ' - ' .$grupo_base['bandeira'] . ' - ' . $grupo_base['tipo']. ' - ' . $prazo->prazo;

    $rec01[$documento] = new Rec01 (
        null,
        $_SESSION['usuario']->id_empresa,
        $operadora->id_cliente,
        $operadora->id_con01,
        $operadora->id_con02,
        $documento,
        $desc,
        $valor_l_total,
        $tamanho_grupo,
        $data,
        $_SESSION['usuario']->id_usuario,
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
        $grupo_base['id_bandeira'],
        null,
        $prazo->id
    );
    if(!in_array($rec03[$documento],$rec03_lista)) {
        Rec03::create($rec03[$documento]);
        $rec03_lista[] = $rec03[$documento];
    }
    

    $rec01_lista[] = $rec01[$documento];
    Rec01::create($rec01[$documento]);
    $id_rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, documento:$documento)[0]->id;
    foreach($grupo[0] as $parcela) {
        $prazo = Pra01::read(id_empresa:$_SESSION['usuario']->id_empresa, id_bandeira: $parcela['id_bandeira'], direcao: 'DESC')[0];
        $data_venc = $data;
        $data_venc =( new DateTime($data))->modify(" +$prazo->prazo  Days")->format('Y-m-d');

        $rec02[$documento] = new Rec02 (
            null,
            $_SESSION['usuario']->id_empresa,
            $id_rec01 ?? null,
            $parcela['valor_l'],
            $parcela['parcela'],
            $data_venc,
            0,
            null,
            null,
            null,
        );
        $rec02_lista[] = $rec02[$documento];
        Rec02::create($rec02[$documento]);
    };
    $grupo_feito[] = [
        $rec01[$documento],
        $rec02[$documento]  
    ];
    $documento++;
}
header('Location: venda_manager.php');
exit;






?>