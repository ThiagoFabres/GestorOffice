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

//processar o arquivo de vendas (Excel)
//transformar vendas em recebimentos


function parse_excel($numero_arquivo = null) {
    $data_atual = (new DateTime())->format('Y-m-d');
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
        'getnet' => 2,
        'saudeservice' => 2,
        'capim' => 2
    ];

    // Verificar limite máximo de arquivos para o operador
    $operadora_descricao_preg = $operadora_descricao_preg ?? null;
    if($operadora_descricao_preg != null) {
        $limite_arquivo = $arquivos_multi[$operadora_descricao_preg] ?? 2;
        if($numero_arquivo > $limite_arquivo) {
            header('Location: cadastro_vendas.php?erro=arquivo');
            exit;
        }
    }
    $operadoras_suportadas = [
        'stone',
        'getnet',
        'rede',
        'sicredi',
        'fazpay',
        'cielo',
        'capim',
        'saudeservice',
        'ton'
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

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);  

    

    $spreadsheet = $reader->load($file['tmp_name']);

    
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
                
            ]; 
        }

        

        

        if(isset($cells_p[$operadora_sup_org['estado']]) && $cells_p[$operadora_sup_org['estado']] != null) {
            //estado    
            $cells[6] = $cells_p[$operadora_sup_org['estado']];
        }
        
        if(isset($operadora_sup['suporte_estado']) && $operadora_sup['suporte_estado']) {
            $cells[6] = 'aprovada';
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
    

    if(isset($operadora_sup['suporte_numero']) && $operadora_sup['suporte_numero'] == 'formatado') {
        $cells[4] = str_replace('.', '', $cells[4]);
        $cells[5] = str_replace('.', '', $cells[5]);
        $cells[4] = str_replace(',', '.', $cells[4]);
        $cells[5] = str_replace(',', '.', $cells[5]);
    }
    
    
       
        if(isset($operadora_sup['suporte_pix']) && $operadora_sup['suporte_pix'] == true) {
            
            $cells[2] = 'pix';
            $cells[1] = 'pix';
        }
        if(isset($operadora_sup['suporte_valor_taxa']) && $operadora_sup['suporte_valor_taxa'] == true) {
           $cells[4] = $cells[5] + $cells[4];   
        }
        $multi = false;
            foreach($arquivos_multi as $j => $num) {
                if($operadora_descricao_preg == $j && $numero_arquivo <= $num) {
                    $multi = true;
                }
            }

            
        
        if(($tipo_arquivo == 'personalizado' && isset($cells[6])) || ($tipo_arquivo == 'padrao' && !isset($cells[6])) ) {
            if(!empty($transactions['lancamentos'])) {
                continue;
            }
            if ($multi) {

                if (isset($spreadsheet)) {
                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet, $worksheet, $worksheet_lines);
                }

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
        
        if (isset($operadora_sup['suporte_parcela']) && $operadora_sup['suporte_parcela'] == 'formatada(0/0)') {
            if (strpos($cells[3], '/') !== false) {
                $partes = explode('/', $cells[3]);
                $cells[3] = $partes[1];
            }
        }

        

    $cells[3] = intval($cells[3]);

        
    try {
        if($tipo_arquivo == 'padrao'){
            if(isset($operadora_sup['suporte_data']) && $operadora_sup['suporte_data'] == 'hora') {
                $cells[0] = substr($cells[0], 0, 10);
                $dateObj = DateTime::createFromFormat('d/m/Y', $cells[0]);
                if (!$dateObj) {
                    throw new Exception('Data inválida: ' . $cells[0]);
                }
                $data_formatada = $dateObj->format('Y-m-d');
            } else if(isset($operadora_sup['suporte_data']) && $operadora_sup['suporte_data'] == 'formatada') {
                $dateObj = DateTime::createFromFormat('d/m/Y', $cells[0]);
                if (!$dateObj) {
                    throw new Exception('Data inválida: ' . $cells[0]);
                }
                $data_formatada = $dateObj->format('Y-m-d');
            } else if (isset($operadora_sup['suporte_data']) && $operadora_sup['suporte_data'] == 'formatada(Y-m-d)') {
                $dateObj = DateTime::createFromFormat('Y-m-d', $cells[0]);
                if (!$dateObj) {
                    throw new Exception('Data inválida: ' . $cells[0]);
                }
                $data_formatada = $dateObj->format('Y-m-d');
            } else {
                $data_formatada = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cells[0])->format('Y-m-d');
            }
        } else {
            $data_formatada = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cells[0])->format('Y-m-d');
        }
        if($data_formatada >= $data_atual) {
            continue;
        }
        
    } catch (Exception $e) {
        if(!empty($transactions['lancamentos'])) {
            continue;
        }
        if (isset($multi) && $multi) {
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
        } else {
            header('location: cadastro_vendas.php?erro=arquivo');
            exit;
        }
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

        if( $cells[4] == 0 || ($cells[5] == 0 && $tipo_arquivo == 'padrao')) {
            continue;
        }
   
        if(str_starts_with(strtolower($cells[2]), 'pix') || str_starts_with(strtolower($cells[1]), 'pix')) {
            $cells[2] = 'pix';
            $cells[1] = 'pix';
        }
        if(str_starts_with($tipo_preg, 'debit')) {
            $cells[2] = 'debito';
        } 
        if(str_starts_with($tipo_preg, 'credit')) {
            $cells[2] = 'credito';
        }
        // if(str_starts_with($tipo_preg, 'voucher')) {
        //     $cells[2] = 'voucher';
        // }
        
        if($cells[3] == null || $cells[3] == '-' || $cells[3] == 0 || $cells[3] == '') {
            $cells[3] = 1;
        }
        $cells[2] = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cells[2])));
        $tipo_preg = $cells[2];
        
        // Recalcular $bandeira_preg após possíveis alterações em $cells[1]
        $bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cells[1])));
        
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
        $cadastrado = false;
        if(isset($bandeira_id) && isset($prazo_id)) {
            if(isset($importadas_set[$data_formatada][$bandeira_id][$prazo_id])) {
                $cadastrado = true;
            }
        }
        if(isset($cadastrado) && $cadastrado === true) {
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
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
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
        // usleep(50000); 
        
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

    // Ler arquivo inteiro e converter encoding
    $conteudo = file_get_contents($caminhoCsv);
    $conteudo = mb_convert_encoding($conteudo, 'UTF-8', $operadora_sup['encoding']);
    $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo); // remove BOM se existir

    // Dividir em linhas
    $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
    
    // Processar cabeçalho
    $cabecalho_str = array_shift($linhas);
    $cabecalho = str_getcsv($cabecalho_str, $operadora_sup['separator']);
    $cabecalho = array_map('trim', $cabecalho);
    foreach ($cabecalho as &$chave) {
        $chave = str_replace("\xEF\xBB\xBF", '', $chave);
    }
    unset($chave);
    
    $linha_num = 1;
    if($operadora_sup['linha_inicial'] != null){
        while($linha_num < $operadora_sup['linha_inicial'] && !empty($linhas)) {
            array_shift($linhas);
            $linha_num++;
        }
        if(!empty($linhas)) {
            $cabecalho_str = array_shift($linhas);
            $cabecalho = str_getcsv($cabecalho_str, $operadora_sup['separator']);
            $cabecalho = array_map('trim', $cabecalho);
        }
    }

    // Normaliza o cabeçalho
    $mapa = array_flip($cabecalho);

    
    // Função auxiliar para remover acentos
    $remover_acentos = function($str) {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str)));
    };

    // Criar mapa normalizado para busca
    $mapa_normalizado = [];
    foreach($cabecalho as $chave) {
        $chave_normalizada = $remover_acentos($chave);
        $mapa_normalizado[$chave_normalizada] = $chave;
    }

    // Aplicar variáveis globalmente para reutilização
    $GLOBALS['mapa'] = $mapa;
    $GLOBALS['mapa_normalizado'] = $mapa_normalizado;
    $GLOBALS['remover_acentos'] = $remover_acentos;
    
    // Buscar lançamentos já importados para verificar duplicatas
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
    $prazo_lista = [];
    foreach($bandeiras as $bandeira) {
        $bandeiras_obj[] = $bandeira;
        $prazo_bandeira = Pra01::read(null, $id_operadora, $_SESSION['usuario']->id_empresa, $bandeira->id);
        $prazo_bandeira_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->descricao)));
        $prazo_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
        $bandeiras_parcelas[] = $prazo_bandeira_preg;
        foreach($prazo_bandeira as $prazo) {
            $prazo_lista[$bandeira->id][] = $prazo;
            $parcelas[] = $prazo->parcela;
            $bandeiras_tipo[] =  preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira->tipo)));
            $tipos_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $bandeira->tipo;
            $parcelas_bandeira[$prazo_bandeira_preg][$prazo_tipo_preg][] = $prazo->parcela;
        }
        
    }

    $i = 0;
    foreach ($linhas as $linha_str) {
        if (trim($linha_str) === '') continue;
        
        $linha = str_getcsv($linha_str, $operadora_sup['separator']);
        
        // Função auxiliar para obter valor da linha pela coluna
        $get_valor_coluna = function($nome_coluna) use ($linha) {
            $mapa_normalizado = $GLOBALS['mapa_normalizado'];
            $mapa = $GLOBALS['mapa'];
            $remover_acentos = $GLOBALS['remover_acentos'];
            
            $nome_normalizado = $remover_acentos($nome_coluna);
            if(isset($mapa_normalizado[$nome_normalizado])) {
                $chave_original = $mapa_normalizado[$nome_normalizado];
                if(isset($mapa[$chave_original])) {
                    $indice = $mapa[$chave_original];
                    if(isset($linha[$indice])) {
                        return $linha[$indice];
                    }
                }
            }
            return null;
        };
        
        $status = $get_valor_coluna($operadora_sup['colunas']['status']) ?? null;
        if($operadora_sup['suporte_parcela'] === false) {
            $parcela = 1;
        } else {
            $parcela = $get_valor_coluna($operadora_sup['colunas']['parcela']);
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

        if(isset($operadora_sup['suporte_valor_liquido'])) {
            $valor_liquido = 0;
        }
        
        $valor_b_str = $get_valor_coluna($operadora_sup['colunas']['valor_b']);
        // Limpar "R$" e espaços antes de converter
        $valor_b_str = trim(str_replace('R$', '', $valor_b_str ?? ''));

        if($operadora_sup['suporte_numero'] == 'formatado(.)') {
            $valorBruto = $valor_b_str;
        } else {
        $valorBruto = !empty($valor_b_str) 
            ? floatval(str_replace(['.', ','], ['', '.'], $valor_b_str))
            : 0;
        }
        $valor_l_str = isset($valor_liquido) ? $valor_liquido : $get_valor_coluna($operadora_sup['colunas']['valor_l']);
        
        
        // Limpar "R$" e espaços antes de converter
        $valor_l_str = trim(str_replace('R$', '', $valor_l_str ?? ''));
        $valorLiquido = !empty($valor_l_str) 
            ? floatval(str_replace(['.', ','], ['', '.'], $valor_l_str))
            : 0;

        if(($valorLiquido == 0 && $operadora_sup['suporte_valor_liquido'] === false) || $valorBruto == 0) {
            continue;
        }
        
        
        // Data e hora → só data
        $data = $get_valor_coluna($operadora_sup['colunas']['data']) ?? null;
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
    
        $bandeira_valor = $get_valor_coluna($operadora_sup['colunas']['bandeira']) ?? '';
        $tipo_valor = $get_valor_coluna($operadora_sup['colunas']['tipo']) ?? '';

        if($bandeira_valor == null && $operadora_sup['suporte_bandeira'] == 'pix') {
            $bandeira_valor = 'pix';
        }
        if($tipo_valor == null && $operadora_sup['suporte_tipo'] == 'pix') {
            $tipo_valor = 'pix';
        }
        
        // Normalizar: remover acentos, números e caracteres especiais
        $bandeira_preg = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $bandeira_valor));
        $bandeira_preg = preg_replace('/[^a-z]/', '', $bandeira_preg);
        
        $tipo_preg = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $tipo_valor));
        $tipo_preg = preg_replace('/[^a-z]/', '', $tipo_preg);
        if($tipo_preg == 'dibito') {
            $tipo_preg = 'debito';
        }
        
        if($tipo_preg == 'criditoivista' || $tipo_preg == 'creditoavista' || $tipo_preg == 'credav') {
            $tipo_preg = 'credito';
        }
        if($tipo_preg == 'dibitoivista' || $tipo_preg == 'debitoavista' || $tipo_preg == 'debav') {
            $tipo_preg = 'debito';
        }
        if(str_starts_with($tipo_preg, 'pix')) {
            $tipo_preg = 'pix';
        }
        
        $tipo = $tipo_preg;
        
        if(str_starts_with($tipo_preg, 'debito')) {
            $tipo = 'debito';
        } 
        if(str_starts_with($tipo_preg, 'credito') || str_starts_with($tipo_preg, 'cred')) {
            $tipo = 'credito';
        }

        // Definir bandeira como 'pix' antes de procurar nos bandeiras_obj
        $bandeira = $bandeira_valor;
        if($tipo == 'pix' || str_starts_with($tipo_preg, 'pix')) {
            $bandeira = 'pix';
            $bandeira_preg = 'pix';
        }

        foreach($bandeiras_obj as $obj) {
            $obj_nome_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->descricao)));
            $obj_tipo_preg = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $obj->tipo)));
            if($bandeira_preg == $obj_nome_preg && $tipo == $obj_tipo_preg) {
                $bandeira_id = $obj->id;
            }
        }
        
        // Buscar prazo_id correspondente
        $prazo_id = null;
        if(!empty($prazo_lista) && isset($bandeira_id)){
            if($prazo_lista == null || !isset($prazo_lista[$bandeira_id])) {
                $prazo_id = null;
            } else {
                foreach($prazo_lista[$bandeira_id] as $obj) {
                    if($obj->parcela == $parcela) {
                        $prazo_id = $obj->id;
                    }
                }
            }
        }
        
        // Verificar se o lançamento já foi cadastrado
        $cadastrado = false;
        if(isset($bandeira_id) && isset($prazo_id)) {
            if(isset($importadas_set[$data][$bandeira_id][$prazo_id])) {
                $cadastrado = true;
            }
        }
        if(isset($cadastrado) && $cadastrado === true) {
            $i++;
            continue;
        }
        
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
                if(!in_array($transactions['lancamentos'][$i]['parcela'], $parcelas_esperadas)) {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
                }
            } else {
                    $transactions['lancamentos'][$i]['motivo'][] = 'parcela';
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
        $transactions['lancamentos'][$i]['bandeira'] = ucfirst($transactions['lancamentos'][$i]['bandeira']);
        
    // usleep(50000); 
    $i++;
    }

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