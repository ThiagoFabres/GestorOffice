<?php
require_once '../../vendor/autoload.php';
require_once '../../db/entities/usuarios.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
if($_SESSION['usuario']->processar !== 1) {
    header('Location: movimentacao.php?erro=permissao');
    exit;
}

require_once '../../db/entities/banco02.php';
require_once '../../db/entities/banco01.php';
require_once '../../db/entities/pagar.php';
require_once '../../db/entities/recebimentos.php';

$acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_STRING) ?? filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_STRING);
if($acao == 'processar') {
    if(
        $_POST['cadastro'] == '' ||
        $_POST['custos'] == '' ||
        $_POST['titulo'] == '' ||
        $_POST['subtitulo'] == '' 
    ) {
        header('Location: importar.php?erro=selecao');
        exit;
    }
    
    function parse_excel($fileName, $filePath) {
    

        
        try {
            if ($fileName === 'csv') {
                // return parse_csv($filePath);
            } elseif ($fileName === 'xlsx' || $fileName === 'xls') {
                
                return parse_xlsx($filePath);
            }
            
            return ['current' => [], 'debug' => ['error' => 'Formato de arquivo não suportado']];
        } catch (Exception $e) {
            error_log('Erro ao processar Excel: ' . $e->getMessage());
            return ['current' => [], 'debug' => ['error' => $e->getMessage()]];
        }
    }

    function parse_xlsx($filePath) {
        $tipo_lancamento = filter_input(INPUT_POST, 'tipo_lancamento');
        if($tipo_lancamento == 'receber'){
            $rec02_lista = Rec02::read(null, $_SESSION['usuario']->id_empresa);
            $current_cadastado = [];
            foreach($rec02_lista as $rec02) {
                $rec01 = Rec01::read($rec02->id_rec01, $_SESSION['usuario']->id_empresa )[0];
                $current_cadastado[] = [
                    'data' => $rec02->vencimento,
                    'valor' => $rec02->valor_par,
                    'descricao' => $rec01->descricao
                ];
            }
        } else if($tipo_lancamento == 'pagar') {
            $pag02_lista = Pag02::read(null, $_SESSION['usuario']->id_empresa);
            $current_cadastado = [];
            foreach($pag02_lista as $pag02) {
                $pag01 = Pag01::read($pag02->id_pag01, $_SESSION['usuario']->id_empresa )[0];
                $current_cadastado[] = [
                    'data' => $pag02->vencimento,
                    'valor' => $pag02->valor_par,
                    'descricao' => $pag01->descricao
                ];
            }
        }
        $data_atual = (new DateTime())->format('Y-m-d');
        $transactions = [];
        $transactions['current'] = [];
        $transactions['debug'] = [];
        
        try {
            // Usar PhpSpreadsheet para ler XLSX
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestDataRow();

            // Começa a ler a partir da linha 2 (linha 1 é cabeçalho)
            for ($row = 2; $row <= $highestRow; $row++) {
            
                // Colunas esperadas: A=DATA, B=DOCUMENTO, C=DESCRICAO, D=VALOR, E=VENCIMENTO
                $data_raw = $worksheet->getCell('A' . $row)->getValue();
                $documento_raw = $worksheet->getCell('B' . $row)->getValue();
                $descricao_raw = $worksheet->getCell('C' . $row)->getValue();
                $valor_raw = $worksheet->getCell('D' . $row)->getValue();
                $vencimento_raw = $worksheet->getCell('E' . $row)->getValue();
                $valor_pag_raw = $worksheet->getCell('F' . $row)->getValue() ?? null;
                $data_pag_raw = $worksheet->getCell('G' . $row)->getValue() ?? null;

                // Se linha vazia (todas as principais colunas vazias), pular
                if (empty($data_raw) && empty($documento_raw) && empty($descricao_raw) && empty($valor_raw) && empty($vencimento_raw)) {
                    continue;
                }

                // Data (DATA) - pode ser serial do Excel ou string
                try {
                    if (is_numeric($data_raw)) {
                        $dateTime = ExcelDate::excelToDateTimeObject($data_raw);
                        $data_analizada = $dateTime->format('Y-m-d');
                        $data_formatada = $dateTime->format('d/m/Y');
                       
                        
                    } else {
                        $data_str = trim((string)$data_raw);
                        $data_str = str_replace('-', '/', $data_str);
                        $data_obj = DateTime::createFromFormat('d/m/Y', $data_str);
                        if (!$data_obj) {
                            $data_obj = DateTime::createFromFormat('Y-m-d', $data_str);
                        }
                        if (!$data_obj) {
                            // tenta outras formatacoes comuns
                            $timestamp = strtotime($data_str);
                            if ($timestamp === false) continue;
                            $data_obj = (new DateTime())->setTimestamp($timestamp);
                        }
                        $data_analizada = $data_obj->format('Y-m-d');
                        $data_formatada = $data_obj->format('d/m/Y');
                    }
                } catch (Exception $e) {
                    // continue;
                }
                $data_pag_formatada = null;
                if($data_pag_raw != null){
                
                try {
                    
                    if (is_numeric($data_pag_raw)) {
                        $dateTimePag = ExcelDate::excelToDateTimeObject($data_pag_raw);
                        $data_pag_formatada = $dateTimePag->format('d/m/Y');
                       
                        
                    } else {
                        $data_pag_str = trim((string)$data_pag_raw);
                        $data_pag_str = str_replace('-', '/', $data_pag_str);
                        $data_pag_obj = DateTime::createFromFormat('d/m/Y', $data_pag_str);
                        if (!$data_pag_obj) {
                            $data_pag_obj = DateTime::createFromFormat('Y-m-d', $data_pag_str);
                        }
                        if (!$data_pag_obj) {
                            // tenta outras formatacoes comuns
                            $timestamp = strtotime($data_str);
                            if ($timestamp === false)
                                 continue;
                            $data_pag_obj = (new DateTime())->setTimestamp($timestamp);
                        }
                        $data_pag_formatada = $data_pag_obj->format('d/m/Y');
                    }
                } catch (Exception $e) {
                    if($data_pag_raw !== null) {
                        continue;
                    }
                }
                }

                // Vencimento (opcional) - pode ser serial do Excel ou string
                $vencimento_formatado = '';
                if (!empty($vencimento_raw)) {
                    try {
                        if (is_numeric($vencimento_raw)) {
                            $dt = ExcelDate::excelToDateTimeObject($vencimento_raw);
                            $data_excel_analizada = $dt->format('Y-m-d');
                            $vencimento_formatado = $dt->format('d/m/Y');
                        } else {
                            $vstr = trim((string)$vencimento_raw);
                            $vstr = str_replace('-', '/', $vstr);
                            $vobj = DateTime::createFromFormat('d/m/Y', $vstr);
                            if (!$vobj) $vobj = DateTime::createFromFormat('Y-m-d', $vstr);
                            if ($vobj) $vencimento_formatado = $vobj->format('d/m/Y'); $data_excel_analizada = $vobj->format('Y-m-d');
                        }
                    } catch (Exception $e) {
                        $vencimento_formatado = '';
                    }
                }

                // Documento e descrição
                $documento = trim((string)$documento_raw);
                $descricao = trim((string)$descricao_raw);
                $documento_descricao = $documento . ' - ' . $descricao;

                // Valor - aceita formatos com ponto e vírgula
                $valor = null;
                if ($valor_raw !== null && $valor_raw !== '') {
                    // Se for numérico direto (planilha), converte
                    if (is_numeric($valor_raw)) {
                        $valor = (float)$valor_raw;
                    } else {
                        $v = trim((string)$valor_raw);
                        $v = str_replace('.', '', $v);
                        $v = str_replace(',', '.', $v);
                        if (is_numeric($v)) $valor = (float)$v;
                    }
                }
                $valor_pag = null;
                if ($valor_pag_raw !== null && $valor_pag_raw !== '') {
                    // Se for numérico direto (planilha), converte
                    if (is_numeric($valor_pag_raw)) {
                        $valor_pag = (float)$valor_pag_raw;
                    } else {
                        $v = trim((string)$valor_pag_raw);
                        $v = str_replace('.', '', $v);
                        $v = str_replace(',', '.', $v);
                        if (is_numeric($v)) $valor_pag = (float)$v;
                    }
                }
                if ($valor === null || $valor == 0) {
                    continue;
                }

                // Valida se já foi importada pela data (mantendo lógica anterior)

                $current_analizado = [
                    'data' => $data_excel_analizada,
                    'valor' => number_format($valor, 2, '.', ''),
                    'descricao' => $documento_descricao,
                ];
                if(in_array($current_analizado, $current_cadastado)) {
                    continue;
                }
                

                $current = [
                    'data' => $data_formatada,
                    'data_analizada' => $data_analizada,
                    'documento' => $documento,
                    'descricao' => $descricao,
                    'valor' => number_format($valor, 2, ',', '.'),
                    'vencimento' => $vencimento_formatado,
                    'valor_pag' => $valor_pag ?? 0,
                    'data_pag' => $data_pag_formatada ?? null,
                ];
                

                $transactions['current'][] = $current;
            }

            return $transactions;
        } catch (Exception $e) {
            error_log('Erro ao processar XLSX: ' . $e->getMessage());
            return ['current' => [], 'debug' => ['error' => $e->getMessage()]];
        }
    }

    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {

        $filePath = $_FILES['arquivo']['tmp_name'];
        $fileName = $_FILES['arquivo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        
        // Detecta o tipo de arquivo pela extensão
        if ($fileExt === 'xlsx' || $fileExt === 'xls' || $fileExt === 'csv') {
            $transactions = parse_excel($fileExt, $filePath);
        } else {
            header('Location: importar.php?erro=formato');
            exit;
        }
        

        if(!empty($transactions['current'])){
            $transactions_current = $transactions['current'];
        }
        if(!empty($transactions['debug'])){
            $transactions_debug = $transactions['debug'];
        }

        if(empty($transactions_current)) {
            if(isset($transactions_debug['erro'][0])) {
                header('Location: importar.php?erro=' . $transactions_debug['erro'][0]);
                exit;
            } else {
            header('Location: importar.php?erro=cadastrado');
            exit;
        }
        }
        
        $_SESSION['excel_transactions']['transactions'] = $transactions_current;
        $_SESSION['excel_transactions']['file_name'] = $fileName;
        $_SESSION['excel_transactions']['file_extension'] = $fileExt;
        $_SESSION['excel_transactions']['tipo_lancamento'] = filter_input(INPUT_POST, 'tipo_lancamento');
        $_SESSION['excel_transactions']['cadastro'] = filter_input(INPUT_POST, 'cadastro');
        $_SESSION['excel_transactions']['custos'] = filter_input(INPUT_POST, 'custos');
        $_SESSION['excel_transactions']['titulo'] = filter_input(INPUT_POST, 'titulo');
        $_SESSION['excel_transactions']['subtitulo'] = filter_input(INPUT_POST, 'subtitulo');



        header('Location: importar.php?excel=1');
        exit;
    } else {
        // Se não houver arquivo, limpa sessão e retorna para a tela
        unset($_SESSION['ofx_transactions']);
        header('Location: importar.php?excel=0');
        exit;
    }
}
if($acao == 'adicionar') {
    $total_linhas = filter_input(INPUT_POST, 'total_linhas');
    $tipo = filter_input(INPUT_POST, 'tipo');
    $importar = $_POST['importar'];
    $valores = $_POST['valor'];
    $vencimentos = $_POST['vencimento'];
    $descricoes = $_POST['descricao'];
    $data_pag = $_POST['data_pag'];
    $data_lanc = $_POST['data'];
    $valor_pag = $_POST['valor_pag'];
    $cadastro = filter_input(INPUT_POST, 'cadastro');
    $custos = filter_input(INPUT_POST, 'custos');
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $vencimentos_formatados = [];
    foreach($data_lanc as $i => $data) {
        $datas_formatadas[$i] = DateTime::createFromFormat('d/m/Y', $data)->format('Y-m-d');
    }
    foreach($vencimentos as $i => $data) {
        $vencimentos_formatados[$i] = DateTime::createFromFormat('d/m/Y', $data)->format('Y-m-d');
    }
    foreach($data_pag as $i => $data) {
        if($data == '') {
            continue;
        }
        $data_pag_formatadas[$i] = DateTime::createFromFormat('d/m/Y', $data)->format('Y-m-d');
    }
    $vencimentos = $vencimentos_formatados;
    if($tipo == 'pagar') {
        require_once '../../db/buscar_documento_pag.php';
        $documento = buscarDocumentoPag();
       for($i = 0; $i < $total_linhas; $i++) {
        if($importar[$i] !== 'on' || !isset($importar[$i]) || $importar[$i] === null) {
            continue;
        }
            $valor = str_replace('.', '', $valores[$i]);
            $valor = str_replace(',', '.', $valor);
            $valor_pago = str_replace('.', '', $valor_pag[$i]);
            $valor_pago = str_replace(',', '.', $valor_pago);
            if(is_numeric($valor)) {
               $pag01 = new Pag01(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    $cadastro,
                    $titulo,
                    $subtitulo,
                    $documento,
                    $descricoes[$i],
                    $valor,
                    1,
                    $datas_formatadas[$i],
                    $_SESSION['usuario']->id,
                    $custos,
                );
                
                Pag01::create($pag01);
                $pag01_criado = Pag01::read(id_empresa: $_SESSION['usuario']->id_empresa, documento: $documento)[0];

                $pag02 = new Pag02(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    $pag01_criado->id,
                    $valor,
                    1,
                    $vencimentos[$i],
                    $valor_pago == '' ? 0 : $valor_pago ?? 0,
                    !isset($data_pag_formatadas[$i]) ? null : $data_pag_formatadas[$i] ?? null,
                    null,
                    null,
                );
                Pag02::create($pag02);

            }
            
            $documento++;
        }
    } else if($tipo == 'receber') {
        require_once '../../db/buscar_documento_rec.php';
        $documento = buscarDocumentoRec();
        for($i = 0; $i < $total_linhas; $i++) {
            if($importar[$i] !== 'on' || !isset($importar[$i]) || $importar[$i] === null) {
                continue;
            }
            $valor = str_replace('.', '', $valores[$i]);
            $valor = str_replace(',', '.', $valor);
            $valor_pago = str_replace('.', '', $valor_pag[$i]);
            $valor_pago = str_replace(',', '.', $valor_pago);
            if(is_numeric($valor)) {
               $rec01 = new Rec01(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    $cadastro,
                    $titulo,
                    $subtitulo,
                    $documento,
                    $descricoes[$i],
                    $valor,
                    1,
                    $datas_formatadas[$i],
                    $_SESSION['usuario']->id,
                    $custos,
                );
                
                Rec01::create($rec01);
                $rec01_criado = Rec01::read(id_empresa: $_SESSION['usuario']->id_empresa, documento: $documento)[0];

                $rec02 = new Rec02(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    $rec01_criado->id,
                    $valor,
                    1,
                    $vencimentos[$i],
                    $valor_pago == '' ? 0 : $valor_pago ?? 0,
                    !isset($data_pag_formatadas[$i]) ? null : $data_pag_formatadas[$i] ?? null,
                    null,
                    null,
                );
                Rec02::create($rec02);
            }
            
            $documento++;
        }
    }
    header('Location: importar.php?status=sucesso');
    exit;
}
?>