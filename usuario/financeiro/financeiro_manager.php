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
        $data_atual = (new DateTime())->format('Y-m-d');
        $importadas_set = [];
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
                    continue;
                }

                // Vencimento (opcional) - pode ser serial do Excel ou string
                $vencimento_formatado = '';
                if (!empty($vencimento_raw)) {
                    try {
                        if (is_numeric($vencimento_raw)) {
                            $dt = ExcelDate::excelToDateTimeObject($vencimento_raw);
                            $vencimento_formatado = $dt->format('d/m/Y');
                        } else {
                            $vstr = trim((string)$vencimento_raw);
                            $vstr = str_replace('-', '/', $vstr);
                            $vobj = DateTime::createFromFormat('d/m/Y', $vstr);
                            if (!$vobj) $vobj = DateTime::createFromFormat('Y-m-d', $vstr);
                            if ($vobj) $vencimento_formatado = $vobj->format('d/m/Y');
                        }
                    } catch (Exception $e) {
                        $vencimento_formatado = '';
                    }
                }

                // Documento e descrição
                $documento = trim((string)$documento_raw);
                $descricao = trim((string)$descricao_raw);

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

                if ($valor === null || $valor == 0) {
                    continue;
                }

                // Valida se já foi importada pela data (mantendo lógica anterior)
                if (isset($importadas_set[$data_analizada])) {
                    continue;
                }

                if($ultima_data != null && ($data_analizada >= $data_atual || $data_analizada == $ultima_data)) {
                    continue;
                }

                $current = [
                    'data' => $data_formatada,
                    'data_analizada' => $data_analizada,
                    'documento' => $documento,
                    'descricao' => $descricao,
                    'valor' => number_format($valor, 2, ',', '.'),
                    'vencimento' => $vencimento_formatado
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
        // echo '<pre>';
        // print_r($transactions);
        // exit;

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
    $total_linhas = filter_input(INPUT_POST, 'total_linhas', FILTER_SANITIZE_NUMBER_INT);
    $valores = $_POST['valor'];
    $datas = $_POST['data'];
    $documentos = $_POST['documento'];
}
?>