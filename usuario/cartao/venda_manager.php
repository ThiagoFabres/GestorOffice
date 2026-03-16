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

//processar o arquivo de vendas (Excel)
//transformar vendas em recebimentos


function parse_excel($numero_arquivo = null) {
    if($numero_arquivo == 1) {
        $numero_arquivo = 2;
    }
    require __DIR__ . '/operadoras_suporte.php';
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $id_operadora = filter_input(INPUT_POST, 'operadora');
    if($id_operadora == null) {
        header('location: cadastro_vendas.php?erro=operadora');
        exit;
    }

    $file = $_FILES['vendas_excel'];
    $fileName = $_FILES['vendas_excel']['name'];
    if(str_ends_with($fileName, '.xlsx')) {
        $file_ext = 'xlsx';
    } else if(str_ends_with($fileName, '.xls')) {
        $file_ext = 'xls';
    } else {
        header('location: cadastro_vendas.php?erro=arquivo');
        exit;
    }
    $arquivos_multi = [
        'sicredi' => 2,
        'getnet' => 2
    ];
    $operadoras_suportadas = [
        'stone',
        'getnet',
        'rede',
        'sicredi',
        'fazpay',
        'cielo'
    ];
    
    $tipo_arquivo = filter_input(INPUT_POST, 'tipo_arquivo');
    $operadora = Ope01::read($id_operadora, $_SESSION['usuario']->id_empresa )[0];
    $operadora_descricao_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $operadora->descricao)));
    if($tipo_arquivo == 'padrao'){
        if(!in_array($operadora_descricao_preg, $operadoras_suportadas)) {
            header('Location:cadastro_vendas.php?erro=suporte');
            exit;
        }
        $operadora_sup = $operadoras_suporte[$operadora_descricao_preg][$file_ext.$numero_arquivo];

        
        if($operadora_sup == null) {
            header('location: cadastro_vendas.php?erro=arquivo');
            exit;
        }
    }
    $importadas = Rec03::read(id_empresa: $_SESSION['usuario']->id_empresa, operadora_id:$operadora->id);

    $importadas_set = [];
    foreach($importadas as $imp) {
        $importadas_set[$imp->data_lanc][$imp->bandeira_id][$imp->prazo_id] = true;
    }
    $bandeiras = Band01::read(null, $_SESSION['usuario']->id_empresa, $id_operadora);
    $parcelas = [];
    $bandeiras_parcelas = [];
    $bandeiras_tipo = [];
    $bandeiras_obj = [];
    $prazos = [];
    $prazos_lista = Pra01::read(null, $id_operadora, $_SESSION['usuario']->id_empresa);
    foreach($prazos_lista as $prazo) {
        $prazos[$prazo->id_bandeira][] = $prazo;
    }

    foreach($bandeiras as $bandeira) {
        $bandeiras_obj[] = $bandeira; 
        $prazo_bandeira = $prazos[$bandeira->id] ?? null;
        $prazo_bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->descricao)));
        $prazo_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
        $bandeiras_parcelas[] = $prazo_bandeira_preg;
        if($prazo_bandeira == null) {
            continue;
        }
        foreach($prazo_bandeira as $prazo) {
            
            $prazo_lista[$bandeira->id][] = $prazo;
            $parcelas[] = $prazo->parcela;
            $bandeiras_tipo[] =  preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
            $tipos_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $bandeira->tipo;
            $parcelas_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $prazo->parcela;
        }
        
    }

    
    $vendas_invalidas = [];

    //criar uma lista com as vendas que possuem parcelas ou bandeiras que não foram cadastradas
    
    

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $transactions = [];
    $excluded_columns = [];
    if($tipo_arquivo == 'padrao'){
        $excluded_columns = $operadora_sup['excluded_columns'];
    }
    if($tipo_arquivo == 'padrao') {
        $highestRow = $worksheet->getHighestDataRow();
        $worksheet_lines = $worksheet->getRowIterator($operadora_sup['start_row'], $highestRow);
    } else {
        $worksheet_lines = $worksheet->getRowIterator(2);
    }
    $transactions = [
    'lancamentos' => [],
    'invalido' => []
];

    foreach ($worksheet_lines as $i => $row) {
            if($tipo_arquivo == 'padrao'){
                $cellIterator = $row->getCellIterator($operadora_sup['start_end_columns']['start'], $operadora_sup['start_end_columns']['end']);
            } else {
                $cellIterator = $row->getCellIterator();
            }
        
        $cellIterator->setIterateOnlyExistingCells(false);
        $cells = [];
        $cells_p = [];
        $cellIndex = 0;
        $bandeira_id = null;
        $prazo_id = null;

        foreach ($cellIterator as $cell) {
            if(in_array($cell->getColumn(), $excluded_columns)) {
                continue;
            }
            if($tipo_arquivo == 'padrao'){
                    $cells_p[] = $cell->getCalculatedValue();
            }  else {
                    $cells[] = $cell->getCalculatedValue();
                }
                $cellIndex++;
        }
        if($tipo_arquivo == 'padrao') {
            $operadora_sup_org = $operadora_sup['organizador'];
            $cells = [
                //data
                0 => $cells_p[$operadora_sup_org['data']],
                //bandeira
                1 => $cells_p[$operadora_sup_org['bandeira']],
                //tipo
                2 => $cells_p[$operadora_sup_org['tipo']],
                //parcela
                3 => $cells_p[$operadora_sup_org['parcela']],
                //valor bruto
                4 =>$cells_p[$operadora_sup_org['valor_b']],
                //valor Liquido
                5 =>$cells_p[$operadora_sup_org['valor_l']],
                //estado
                6 => $cells_p[$operadora_sup_org['estado']]
            ]; 
        }
        
        // $linha_vazia = true;
        // foreach ($cells as $valor) {
        //     if ($valor !== null && trim($valor) !== '') {
        //         $linha_vazia = false;
        //         break;
        //     }
        // }

        // if ($linha_vazia) {
        //     break;
        // }
    if(
        empty($cells[0]) &&
        empty($cells[1]) &&
        empty($cells[4]) &&
        empty($cells[5])
    ){
        break;
    }
        if(isset($operadora_sup['suporte_pix']) && $operadora_sup['suporte_pix'] == true) {
            
            $cells[2] = 'pix';
            $cells[1] = 'pix';
        }
        if(isset($operadora_sup['suporte_valor_taxa']) && $operadora_sup['suporte_valor_taxa'] == true) {
           $cells[4] = $cells[5] + $cells[4];   
        }

        
        if(($tipo_arquivo == 'personalizado' && isset($cells[6])) || ($tipo_arquivo == 'padrao' && !isset($cells[6])) ) {
            $multi = false;
            foreach($arquivos_multi as $i => $num) {
                if($operadora_descricao_preg == $i && $numero_arquivo <= $num) {
                    $multi = true;
                }
            }
            if ($multi) {
            $transactions_next = parse_excel($numero_arquivo + 1);

            if (!empty($transactions_next['invalido'])) {
                $transactions['invalido'] =  $transactions_next['invalido'];
            }
            
            if (!empty($transactions_next['lancamentos'])) {
                $transactions['lancamentos'] = $transactions_next['lancamentos'];
            }
            if(empty($transactions['lancamentos'])) {
                header('Location: cadastro_vendas.php?erro=cadastrado');
                exit;
            }
            if(!empty($transactions['invalido'])) {
                $_SESSION['vendas_invalidas'] = $transactions['invalido'];
                header('Location: cadastro_vendas.php?vendas_invalidas=1');
                exit;
            } else {
                $_SESSION['vendas']['transactions'] = $transactions['lancamentos'];
                $_SESSION['vendas']['conta'] = $id_operadora;
                header('Location: cadastro_vendas.php?vendas_enviadas=1');
                exit;
            }

            
        }
        else{
                header('location: cadastro_vendas.php?erro=arquivo');
                exit;
            }
        }    
        $palavras_negadas = [
            'cancelada',
            'negada',
            'desfeita'
        ];

        if($tipo_arquivo == 'padrao') {
            if(in_array(strtolower($cells[6]), $palavras_negadas)) {
                continue;
            }
        }
        $cells[3] = intval($cells[3]);



        if($tipo_arquivo == 'padrao'){
            if($operadora_sup['suporte_data'] == 'hora') {
                $cells[0] = substr($cells[0], 0, 10);
                $data_formatada = (DateTime::createFromFormat('d/m/Y', $cells[0]))->format('Y-m-d');
            } else if($operadora_sup['suporte_data'] == 'formatada') {
                $data_formatada = (DateTime::createFromFormat('d/m/Y', $cells[0]))->format('Y-m-d');
            } else {
                $data_formatada = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cells[0])->format('Y-m-d');
            }
        } else {
                $data_formatada = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cells[0])->format('Y-m-d');
        }
        

        

        $bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cells[1])));
        $tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cells[2])));
        if(str_starts_with($bandeira_preg, 'elo')) {
            $cells[1] = 'elo';
            $bandeira_preg = $cells[1];
        }
        $cells[4] = str_replace('R$', '', $cells[4]);
        $cells[5] = str_replace('R$', '', $cells[5]);
        if(isset($operadora_sup['suporte_numero']) && $operadora_sup['suporte_numero'] != 'formatado'){
            $cells[4] = str_replace('.', '', $cells[4]);
            $cells[5] = str_replace('.', '', $cells[5]);
            $cells[4] = str_replace(',', '.', $cells[4]);
            $cells[5] = str_replace(',', '.', $cells[5]);
        }
        
        $cells[4] = floatval($cells[4]);
        $cells[5] = floatval($cells[5]);

        if( $cells[4] == 0 || $cells[5] == 0) {
            continue;
        }
   
        if(str_starts_with(strtolower($cells[2]), 'pix')) {
            $cells[2] = 'pix';
        }
        if(str_starts_with($tipo_preg, 'debito')) {
            $cells[2] = 'debito';
        } 
        if(str_starts_with($tipo_preg, 'credito')) {
            $cells[2] = 'credito';
        }
        // if(str_starts_with($tipo_preg, 'voucher')) {
        //     $cells[2] = 'voucher';
        // }
        if($cells[3] == null || $cells[3] == '-' || $cells[3] == 0) {
            $cells[3] = 1;
        }
        $cells[2] = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cells[2])));
        $tipo_preg = $cells[2];        
        
        if(!empty($bandeiras_obj)){
            foreach($bandeiras_obj as $obj) {

                $obj_nome_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->descricao)));
                $obj_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->tipo)));


                if($bandeira_preg == $obj_nome_preg && $tipo_preg == $obj_tipo_preg) {
                    $bandeira_id = $obj->id; 
                }
            }
        }

            if(!empty($prazo_lista) && isset($bandeira_id)){
                if($prazo_lista == null || !isset($prazo_lista[$bandeira_id])) {
                    $prazo_id = null;
                } else {
                foreach($prazo_lista[$bandeira_id] as $obj) {
                    if($obj->parcela == $cells[3]) {
                        $prazo_id = $obj->id;
                    }
                }
                }
            }
        
        if(isset($bandeira_id) && isset($prazo_id)) {
            if(isset($importadas_set[$data_formatada][$bandeira_id][$prazo_id])) {
                $cadastrado = true;
            }
        }
        if(isset($cadastrado)) {
            continue;
        }
       
        $transactions['lancamentos'][$i] = [
            'data' =>  $data_formatada,
            'bandeira' => $cells[1],
            'tipo' => $cells[2],
            'parcela' => $cells[3],
            'valor_b' => $cells[4],
            'valor_l' => $cells[5],
            'bandeira_id' => $bandeira_id ?? null,
            'motivo' => []
        ];




        
        $bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $transactions['lancamentos'][$i]['bandeira'])));
        $tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $transactions['lancamentos'][$i]['tipo'])));
        if($transactions['lancamentos'][$i]['bandeira_id'] == null )  {
            $transactions['lancamentos'][$i]['motivo'][] = 'bandeira';
        }
        if(isset($parcelas_bandeira[$bandeira_preg][$tipo_preg])) {
                $parcelas_esperadas = $parcelas_bandeira[$bandeira_preg][$tipo_preg];
                if(!in_array($transactions['lancamentos'][$i]['parcela'], $parcelas_esperadas)) {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
                }
            } else {
                if($tipo_preg != 'pix') {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
                }
            }
        if(!in_array($bandeira_preg, $bandeiras_parcelas)) {
            $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
            $transactions['lancamentos'][$i]['motivo'][] = 'bandeira';
            $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
        } 
        if((!in_array($tipo_preg, $bandeiras_tipo) ||
            !in_array($bandeira_preg, $bandeiras_parcelas)) ) {
            if(isset($tipos_bandeira[$bandeira_preg][$tipo_preg])) {
                $tipos_esperados = $tipos_bandeira[$bandeira_preg][$tipo_preg];
                if(!in_array($tipo_preg, $tipos_esperados)) {
                    $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
                }
            } else {
                $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
            }
        }
        if(!empty($transactions['lancamentos'][$i]['motivo'])) {
            $transactions['invalido'][] = $transactions['lancamentos'][$i];
        }
        
    }

    $transactions['verify'][] = [
            'parcelas' => $parcelas,
            'bandeiras_parcelas' => $bandeiras_parcelas,
            'bandeiras_tipo' => $bandeiras_tipo
        ];

    return $transactions;
}
function parse_csv(string $caminhoCsv): array {
    
    require_once __DIR__ . '/operadoras_suporte.php';
    $id_operadora = filter_input(INPUT_POST, 'operadora');

    if($id_operadora == null) {
        header('location: cadastro_vendas.php?erro=operadora');
        exit;
    }
    $operadora = Ope01::read($id_operadora, $_SESSION['usuario']->id_empresa )[0];
    $operadora_descricao_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $operadora->descricao)));

    // sempre inicializar file_ext para evitar 'undefined variable'
    $file_ext = null;
    if(str_ends_with($_FILES['vendas_excel']['name'], '.csv')) {
        $file_ext = 'csv';
    }

    if($file_ext == null || $file_ext != 'csv') {
        header('Location: cadastro_vendas.php?erro=arquivo');
        exit;
    }

    $operadora_sup = $operadoras_suporte[$operadora_descricao_preg][$file_ext] ?? null;
    if($operadora_sup == null) {
        header('Location: cadastro_vendas.php?erro=suporte');
        exit;
    }

    // preparar estrutura retornada antes de processar linhas
    $transactions = [
        'lancamentos' => [],
        'invalido'    => []
    ];

    $dados = [];

    if (!file_exists($caminhoCsv)) {
        throw new Exception('Arquivo CSV não encontrado.');
    }

    if (($handle = fopen($caminhoCsv, 'r')) === false) {
        throw new Exception('Não foi possível abrir o CSV.');
    }
    

    // Lê o cabeçalho
    $cabecalho = fgetcsv($handle, 0, $operadora_sup['separator']);
    $linha = 0;
    if($operadora_sup['linha_inicial'] != null){
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $linha++;

            if ($linha === $operadora_sup['linha_inicial'] - 1) {
                $cabecalho = array_map('trim', mb_convert_encoding($row, 'UTF-8', $operadora_sup['encoding']));
                break;
            }
        }
    }

    // Normaliza o cabeçalho
    $mapa = array_flip(array_map('trim', $cabecalho));
    
    $bandeiras = Band01::read(null, $_SESSION['usuario']->id_empresa, $id_operadora);
    $parcelas = [];
    $bandeiras_parcelas = [];
    $bandeiras_tipo = [];
    $bandeiras_obj = [];
    foreach($bandeiras as $bandeira) {
        $bandeiras_obj[] = $bandeira;
        $prazo_bandeira = Pra01::read(null, $id_operadora, $_SESSION['usuario']->id_empresa, $bandeira->id);
        $prazo_bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->descricao)));
        $prazo_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
        $bandeiras_parcelas[] = $prazo_bandeira_preg;
        foreach($prazo_bandeira as $prazo) {
            $parcelas[] = $prazo->parcela;
            $bandeiras_tipo[] =  preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
            $tipos_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $bandeira->tipo;
            $parcelas_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $prazo->parcela;
        }
        
    }

    $i = 0;
    while (($linha = fgetcsv($handle, 0, $operadora_sup['separator'])) !== false) {
        $status = $linha[$mapa[$operadora_sup['colunas']['status']]] ?? null;
        if($operadora_sup['suporte_parcela'] === false) {
            $parcela = 1;
        } else {
            $parcela = $linha[$mapa[$operadora_sup['colunas']['parcela']]];
        }

        if(($parcela === null || $parcela == '')&& $operadora_sup['suporte_parcela'] === false) {
            $parcela = 1;
        }
        if($status === null && $operadora_sup['suporte_status']  === false) {
            $status = 'aprovada';
        }
        
        if($status == 'cancelada' || $status == 'negada') {
            continue;
        }
        $valorBruto = isset($linha[$mapa[$operadora_sup['colunas']['valor_b']]])
            ? floatval(str_replace(['.', ','], ['', '.'], $linha[$mapa[$operadora_sup['colunas']['valor_b']]]))
            : 0;

        $valorLiquido = isset($linha[$mapa[$operadora_sup['colunas']['valor_l']]])
            ? floatval(str_replace(['.', ','], ['', '.'], $linha[$mapa[$operadora_sup['colunas']['valor_l']]]))
            : 0;

        if($valorLiquido == 0|| $valorBruto == 0) {
            continue;
        }
        // Data e hora → só data
        $data = $linha[$mapa[$operadora_sup['colunas']['data']]] ?? null;

        if($operadora_sup['suporte_data'] == 'hora'){
            $data = strtolower($data);
            $data = str_replace( ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez', ',', '/'], ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '', ' '], $data);
            $data = substr($data, 0, 10);


                $data = (DateTime::createFromFormat('d m Y', $data))->format('Y-m-d');
        }
        if($operadora_sup['suporte_data'] == 'formatada'){
            $data = (DateTime::createFromFormat('d/m/Y', $data))->format('Y-m-d');
        }
        // Valores numéricos
        
        


        if(preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $parcela))) == 'avista' || intval($parcela) == 0) {
            $parcela = 1;
        }
        $parcela = intval($parcela);

        $bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', mb_convert_encoding($linha[$mapa[$operadora_sup['colunas']['bandeira']]], 'UTF-8', $operadora_sup['encoding']))));
        $tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', mb_convert_encoding($linha[$mapa[$operadora_sup['colunas']['tipo']]], 'UTF-8', $operadora_sup['encoding']))));
        $tipo = $linha[$mapa[$operadora_sup['colunas']['tipo']]];
        $tipo = $tipo_preg;
        if(str_starts_with($tipo_preg, 'debito')) {
            $tipo = 'debito';
        } 
        if(str_starts_with($tipo_preg, 'credito') || str_starts_with($tipo_preg, 'cred')) {
            $tipo = 'credito';
        }

        foreach($bandeiras_obj as $obj) {
            $obj_nome_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->descricao)));
            $obj_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->tipo)));
            if($bandeira_preg == $obj_nome_preg && $tipo == $obj_tipo_preg) {
                $bandeira_id = $obj->id;
            }
        }
        $tipo = mb_convert_encoding($tipo, 'UTF-8', $operadora_sup['encoding']);
        $bandeira = mb_convert_encoding($linha[$mapa[$operadora_sup['colunas']['bandeira']]], 'UTF-8', $operadora_sup['encoding']);

        
        $transactions['lancamentos'][$i] = [
            'data'          => $data,
            'bandeira'      => $bandeira ?? null,
            'tipo'          => $tipo ?? null,
            'parcela'       => $parcela ?? 1,
            'estado'        => $status ?? null,
            'valor_b'       => $valorBruto,
            'valor_l'       => $valorLiquido,
            'bandeira_id'   => $bandeira_id ?? null,
            'motivo'        => []
        ];


        $bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $transactions['lancamentos'][$i]['bandeira'])));
        $tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $transactions['lancamentos'][$i]['tipo'])));
        if(isset($parcelas_bandeira[$bandeira_preg][$tipo_preg])) {
                $parcelas_esperadas = $parcelas_bandeira[$bandeira_preg][$tipo_preg];
                if(!in_array($transactions['lancamentos'][$i]['parcela'], $parcelas_esperadas) && $tipo_preg != 'pix') {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
                }
            } else {
                if($tipo_preg != 'pix') {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
                }
            }
        if(!in_array($bandeira_preg, $bandeiras_parcelas) && $tipo_preg != 'pix') {
            $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
            $transactions['lancamentos'][$i]['motivo'][] = 'bandeira';
            $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
        } 
        if((!in_array($tipo_preg, $bandeiras_tipo) ||
            !in_array($bandeira_preg, $bandeiras_parcelas)) && $tipo_preg != 'pix' ) {
            if(isset($tipos_bandeira[$bandeira_preg][$tipo_preg])) {
                $tipos_esperados = $tipos_bandeira[$bandeira_preg][$tipo_preg];
                if(!in_array($tipo_preg, $tipos_esperados)) {
                    $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
                }
            } else {
                $transactions['lancamentos'][$i]['motivo'][] = 'tipo';
            }
        }
        if(!empty($transactions['lancamentos'][$i]['motivo'])) {
            $transactions['invalido'][] = $transactions['lancamentos'][$i];
        }
        $transactions['lancamentos'][$i]['bandeira'] = ucfirst($transactions['lancamentos'][$i]['bandeira']);
        
        if(strtolower($transactions['lancamentos'][$i]['bandeira']) == 'pix') {
            $transactions['lancamentos'][$i]['bandeira'] = '';
        } 

    $i++;
    }

    fclose($handle);

    return $transactions;
}

$acao = filter_input(INPUT_POST, 'acao');
if($acao == 'processar') {
    $id_operadora = filter_input(INPUT_POST, 'operadora');
    $file = $_FILES['vendas_excel'];
    if(str_ends_with($file['name'], '.csv')) {
        $transactions = parse_csv($file['tmp_name']);
    }else {
        $transactions = parse_excel();
    }
    if(empty($transactions['lancamentos'])) {
        header('Location: cadastro_vendas.php?erro=cadastrado');
        exit;
    }
    if(!empty($transactions['invalido'])) {
        $_SESSION['vendas_invalidas'] = $transactions['invalido'];
        header('Location: cadastro_vendas.php?vendas_invalidas=1');
    } else {
        $_SESSION['vendas']['transactions'] = $transactions['lancamentos'];
        $_SESSION['vendas']['conta'] = $id_operadora;
        header('Location: cadastro_vendas.php?vendas_enviadas=1');
    }
}

?>