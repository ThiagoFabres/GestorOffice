<?php
require_once '../../../vendor/autoload.php';
require_once '../../../db/entities/usuarios.php';

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

require_once '../../../db/entities/banco02.php';
require_once '../../../db/entities/banco01.php';
require_once '../../../db/entities/pagar.php';
require_once '../../../db/entities/recebimentos.php';
require_once 'buscar_documento.php';


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
        $conta = filter_input(INPUT_POST, 'conta');
        $data_atual = (new DateTime())->format('Y-m-d');
        $ultima_data = buscarData($conta);
        $importadas = Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta);      
        $importadas_set = [];
        foreach ($importadas as $imp) {
            $importadas_set[$imp->data] = true;
            
        }
        
        $conta_obj = Ban01::read($conta)[0];
        $transactions = [];
        $transactions['current'] = [];
        $transactions['debug'] = [];
        
        try {
            // Usar PhpSpreadsheet para ler XLSX
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Começa a ler a partir da linha 2 (linha 1 é cabeçalho)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('A', 'Z');
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $cells = [];
                foreach ($cellIterator as $cell) {
                    if($cell->getValue() !== null) {
                        $cells[] = $cell->getValue();
                        $transactions['debug']['cells'][] = $cell->getValue();
                    }
                }
                if(count($cells) == 0 || count($cells) > 3) {
                    return;
                }
                
                if(empty($cells)) {
                    continue;
                }
                // Verifica se a linha tem dados
                if (empty($cells[0]) && empty($cells[1]) && empty($cells[2])) {
                    continue;
                }
                
                $data_raw = $cells[0];
                
                $descricao = trim((string)$cells[1]);
                $valor_str = $cells[2];

                
                // Se não houver data ou valor, pula a linha
                if (empty($data_raw) || empty($valor_str)) {
                    continue;
                }
                
                // Converte data: pode ser número serial do Excel ou string
                try {
                    if (is_numeric($data_raw)) {
                        // É número serial do Excel - converte para data
                        $dateTime = ExcelDate::excelToDateTimeObject($data_raw);
                        $data_analizada = $dateTime->format('Y-m-d');
                        $data_formatada = $dateTime->format('d/m/Y');
                    } else {
                        // Tenta formato texto
                        $data_str = trim((string)$data_raw);
                        $data_str = str_replace('-', '/', $data_str);
                        $data_obj = DateTime::createFromFormat('d/m/Y', $data_str);
                        if (!$data_obj) {
                            $data_obj = DateTime::createFromFormat('Y-m-d', $data_str);
                        }
                        if (!$data_obj) {
                            continue;
                        }
                        $data_analizada = $data_obj->format('Y-m-d');
                        $data_formatada = $data_obj->format('d/m/Y');
                    }
                } catch (Exception $e) {
                    continue;
                }
                
                // Valida se já foi importada
                if (isset($importadas_set[$data_analizada])) {
                    continue;
                }
                
                if($ultima_data != null && ($data_analizada >= $data_atual || $data_analizada == $ultima_data) || $conta_obj->data > $data_analizada) {
                    continue;
                }
                
                // Converte valor para número (suporta formato brasileiro)
                $valor = (float)$valor_str;
                if ($valor == 0) {
                    continue;
                }
                $data_formatada = str_replace('-', '/', $data_formatada);
                $current = [
                    'data' => $data_formatada,
                    'descricao' => $descricao,
                    'valor' => number_format($valor, 2, ',', '.'),
                ];
                
                $transactions['current'][] = $current;
            }

            return $transactions;
        } catch (Exception $e) {
            error_log('Erro ao processar XLSX: ' . $e->getMessage());
            return ['current' => [], 'debug' => ['error' => $e->getMessage()]];
        }
    }

    
    // function parse_csv($filePath) {
    //     $conta = filter_input(INPUT_POST, 'conta');
    //     $data_atual = (new DateTime())->format('Y-m-d');
    //     $ultima_data = buscarData($conta);
    //     $importadas = Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta);      
    //     $importadas_set = [];
    //     foreach ($importadas as $imp) {
    //         $importadas_set[$imp->data] = true;
    //     }
        
    //     $conta_obj = Ban01::read($conta)[0];
    //     $transactions = [];
    //     $transactions['current'] = [];
    //     $transactions['debug'] = [];
        
    //     try {
    //         $handle = fopen($filePath, 'r');
    //         $row_number = 0;
            
    //         while (($row = fgetcsv($handle, 1000, ',')) !== false) {
    //             $row_number++;
                
    //             // Pula cabeçalho
    //             if ($row_number === 1) {
    //                 continue;
    //             }
                
    //             // Verifica se a linha tem dados
    //             if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
    //                 continue;
    //             }
                
    //             $data_str = trim($row[0] ?? '');
    //             $descricao = trim($row[1] ?? '');
    //             $valor_str = trim($row[2] ?? '');
                
    //             // Se não houver data ou valor, pula a linha
    //             if (empty($data_str) || empty($valor_str)) {
    //                 continue;
    //             }
                
    //             // Converte data dd/mm/yyyy para Y-m-d
    //             try {
    //                 $data_obj = DateTime::createFromFormat('d/m/Y', $data_str);
    //                 if (!$data_obj) {
    //                     $data_obj = DateTime::createFromFormat('Y-m-d', $data_str);
    //                 }
    //                 if (!$data_obj) {
    //                     continue;
    //                 }
    //                 $data_analizada = $data_obj->format('Y-m-d');
    //                 $data_formatada = $data_obj->format('d/m/Y');
    //             } catch (Exception $e) {
    //                 continue;
    //             }
                
    //             // Valida se já foi importada
    //             if (isset($importadas_set[$data_analizada])) {
    //                 continue;
    //             }
                
    //             if($ultima_data != null && ($data_analizada >= $data_atual || $data_analizada == $ultima_data) || $conta_obj->data > $data_analizada) {
    //                 continue;
    //             }
                
    //             // Converte valor para número
    //             $valor_str = str_replace('.', '', $valor_str);
    //             $valor_str = str_replace(',', '.', $valor_str);
    //             $valor = (float)$valor_str;
                
    //             if ($valor == 0) {
    //                 continue;
    //             }
                
    //             $current = [
    //                 'data' => $data_formatada,
    //                 'data_analizada' => $data_analizada,
    //                 'descricao' => $descricao,
    //                 'valor' => number_format($valor, 2, '.', ''),
    //                 'tipo' => ($valor < 0) ? 'Débito' : 'Crédito',
    //                 'documento' => ''
    //             ];
                
    //             $transactions['current'][] = $current;
    //         }
            
    //         fclose($handle);
    //         return $transactions;
    //     } catch (Exception $e) {
    //         error_log('Erro ao processar CSV: ' . $e->getMessage());
    //         return ['current' => [], 'debug' => ['error' => $e->getMessage()]];
    //     }
    // }
    
    function parse_ofx($filePath) {
        if (is_file($filePath)) {
        $content = file_get_contents($filePath);
    } else {
        // Senão assume que já é o conteúdo
        $content = $filePath;
    }


        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $lines = explode("\n", $content);

        $conta = filter_input(INPUT_POST, 'conta');
        $conta_obj = Ban01::read($conta)[0];
        $data_atual = (new DateTime())->format('Y-m-d');
        $ultima_data = buscarData($conta);
        $importadas = Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta);      
        $importadas_set = [];
        foreach ($importadas as $imp) {
            $importadas_set[$imp->data] = true;
        }


        

        // Tenta converter para UTF-8 se não estiver
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        $lines = explode("\n", $content);
        $transactions = [];
        $current = [];
        $conta = filter_input(INPUT_POST, 'conta');
        foreach ($lines as $line) {
            $line =  preg_replace('/\s\s+/', '', $line);
            
            
            // $transactions['debug']['line'][] = $line;
            if (strpos($line, '<DTPOSTED>') !== false) {
                $date = substr($line, strlen('<DTPOSTED>'), 8);
                $current['data'] = date('d/m/Y', strtotime($date));
                $date = str_replace('</DTPOSTED>', '', $date);
                $current['data_analizada'] = date('Y-m-d', strtotime($date));
                
            }
            if(isset($current['data_analizada'])){

            $data_analizada = $current['data_analizada'];
            
                if (isset($importadas_set[$data_analizada])) continue;
                if (isset($importadas_set[$data_analizada])){
                     $erro = 'importada';
                     $transactions['debug']['erro'][] = $erro ?? null;
                     continue;
                     }
                if(Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta, $data_analizada)) continue;
                // echo $conta_obj->data;
                // echo '<br>';
                // echo $data_analizada;
                // exit;

                // echo 'conta_obj->data: ' . $conta_obj->data . ' - data_analizada: ' . $data_analizada ;
                // exit;
                
                if(
                    $ultima_data != null 
                && ($data_analizada >= $data_atual || $data_analizada == $ultima_data )
                 ) {
                    $erro = 'uso';
                    $transactions['debug']['erro'][] = $erro ?? null;
                    continue;
                }
                if($conta_obj->data > $data_analizada) {
                    $erro = 'data_conta';
                    $transactions['debug']['erro'][] = $erro ?? null;
                    continue;
                }
            

        
           

            if ((strpos($line, '<TRNAMT>')) !== false) {
                
                $line = str_replace(',', '.', $line);
                $current['valor'] = number_format((float)substr($line, strlen('<TRNAMT>')), 2, '.', '');
                $current['tipo'] = ($current['valor'] < 0) ? 'Débito' : 'Crédito';
                $current['valor'] = number_format((float)substr($line, strlen('<TRNAMT>')), 2, ',', '.');
                
                // $transactions['debug']['current']['valor'][] = $current['valor'];
            }
            if (strpos($line, '<CHECKNUM>') !== false) {
                $doc = substr($line, strlen('<CHECKNUM>'));
                    $doc = str_replace('</CHECKNUM>', '', $doc);
                
                $current['documento'] = $doc;
            }
            if (strpos($line, '<MEMO>') !== false) {
                $desc = substr($line, strlen('<MEMO>'));
                $desc = str_replace('<MEMO>', '', $desc);
                $desc = str_replace('</MEMO>', '', $desc);
  
                // Garante que a descrição está em UTF-8
                $current['descricao'] = $desc;
            }
            if(strpos($line, '<NAME>') !== false) {
                $name = substr($line, strlen('<NAME>'));
                $name = str_replace('</NAME>', '', $name);
             
                // Garante que a descrição está em UTF-8
                if(isset($current['descricao'])) {
                    $current['descricao'] .= ' ' . $name;
                } else {
                    $current['descricao'] = $name;
                }
            }
            if ((strpos($line, '</STMTTRN>'))  !== false  ) {
                $transactions['current'][] = $current;
                $current = []; 
            }
        } 
            // $transactions['debug']['current'][] = $current;
            
        }
        return $transactions;
    }

    if (isset($_FILES['ofx']) && $_FILES['ofx']['error'] === UPLOAD_ERR_OK) {
        $filePath = $_FILES['ofx']['tmp_name'];
        $fileName = $_FILES['ofx']['name'];

        $fileExt = str_ends_with(strtolower($fileName), '.ofx') ? 'ofx' : 'xlsx';
        // Detecta o tipo de arquivo pela extensão
        $filePath = $_FILES['ofx']['tmp_name'];
$fileName = $_FILES['ofx']['name'];

$fileExt = str_ends_with(strtolower($fileName), '.ofx') ? 'ofx' : 'xlsx';

if ($fileExt === 'ofx') {

    // Lê o conteúdo do OFX
    $conteudo = file_get_contents($filePath);

    // Formata adicionando quebra de linha entre tags
    $conteudoFormatado = preg_replace('/></', ">\n<", $conteudo);

    // Remove múltiplas linhas vazias
    $conteudoFormatado = preg_replace("/\n+/", "\n", $conteudoFormatado);

    // Passa o conteúdo direto para o parser
    $transactions = parse_ofx($conteudoFormatado);

} elseif ($fileExt === 'xlsx' || $fileExt === 'xls' || $fileExt === 'csv') {
            $transactions = parse_excel($fileExt, $filePath);
        } else {
            // Tenta OFX por padrão
            $transactions = parse_ofx($filePath);
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
                header('Location: movimentacao.php?erro=' . $transactions_debug['erro'][0]);
                exit;
            } else {
            header('Location: movimentacao.php?erro=cadastrado');
            exit;
        }
        }
        
        $_SESSION['ofx_transactions']['transactions'] = $transactions_current;
        $_SESSION['ofx_transactions']['ofx_conta'] = $_POST['conta'];
        $_SESSION['ofx_transactions']['file_name'] = $fileName;
        $_SESSION['ofx_transactions']['file_extension'] = $fileExt;



        header('Location: movimentacao.php?ofx=1');
        exit;
    } else {
        // Se não houver arquivo, limpa sessão e retorna para a tela
        unset($_SESSION['ofx_transactions']);
        header('Location: movimentacao.php?ofx=0');
        exit;
    }
} else if($acao == 'adicionar') {



    $tipo = $_POST['tipo'];
    $data = $_POST['data'];
    $valor = $_POST['valor'];
    $descricao = $_POST['descricao'];
    $descricao_comp = $_POST['descricao_comp'];
    $tamanho = $_POST['total_linhas'] ?? count($_POST['valor']);
    $conta = $_POST['conta'];
    $imp_lista = [];
    $dias_usados = [];
    $cadastrado = false;
    $documento = buscarDocumento();
    $sucesso = true;
    

        if(Ban02::read( id_empresa:$_SESSION['usuario']->id_empresa,documento:$documento)) {
            $cadastrado = true;
        }

   if($cadastrado === false) {
    for($i = 0; $i < $tamanho; $i++) {
        $valor[$i] = str_replace('.', '', $valor[$i]);
        $valor[$i] = str_replace(',', '.', $valor[$i]);
        
        $data_formatada = DateTime::createFromFormat('d/m/Y', $data[$i])->format('Y-m-d');
        
        if(!Ban02Imp::read($_SESSION['usuario']->id_empresa,$conta,$data_formatada)) {
            $nova_movimentacao = new Ban02(
            null,
            $_SESSION['usuario']->id_empresa,
            $conta,
            (DateTime::createFromFormat('d/m/Y', $data[$i]))->format('Y-m-d'),
            $documento,
            null,
            null,
            $descricao[$i],
            $descricao_comp[$i],
            $valor[$i],
            null,
            1
        );

        
        $novo_imp = new Ban02Imp (
            $_SESSION['usuario']->id_empresa,
            $conta,
            $data_formatada
        );

        Ban02::create($nova_movimentacao);

        if(!in_array($novo_imp, $imp_lista)) {
            $imp_lista[] = $novo_imp;
        }

        } else {
            $_SESSION['dias_usados'][$i] = $data_formatada;
        }
        $documento++;
    }


    if(!empty($imp_lista)) {
        
        
        foreach($imp_lista as $imp) {
        if(!Ban02Imp::create($imp)) {
            $sucesso = false;
        }
    }
    }
    
    if($sucesso) {
        header('Location: movimentacao.php?sucesso=sucesso');
        exit;
    } else {
        header('Location: movimentacao.php?sucesso=sucesso2');
        exit;
    }
    
    
        
   } else {
    header('Location: movimentacao.php?sucesso=cadastrado2');
    exit;
   }
} else if($acao == 'conciliar') {
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $id = filter_input(INPUT_POST, 'id');
    $movimentacao_antiga = Ban02::read($id)[0];
    $caminho = filter_input(INPUT_POST, 'caminho');

    $movimentacao = new Ban02(
        $id,
        $movimentacao_antiga->id_empresa,
        $movimentacao_antiga->id_ban01,
        $movimentacao_antiga->data,
        $movimentacao_antiga->documento,
        $titulo,
        $subtitulo,
        $movimentacao_antiga->descricao,
        $movimentacao_antiga->descricao_comp,
        $movimentacao_antiga->valor,
        $movimentacao_antiga->id_original,
        $movimentacao_antiga->ativo,
    );
    Ban02::update($movimentacao);


    header('Location: '. $caminho . 'status=sucesso');
    exit;
} else if($acao == 'conciliar_palavra') {

    require_once '../../../db/entities/palavra_chave.php';
    require_once '../../../db/entities/contas.php';


    $id_palavra = filter_input(INPUT_POST, 'palavra_chave');
    $palavra = Pal01::read($id_palavra)[0];
    $titulo = Con01::read($palavra->id_con01)[0];
    $tipo = $titulo->tipo;
    $titulo = $titulo->id;
    $subtitulo = Con02::read($palavra->id_con02)[0]->id;
    $ban02_lista = Ban02::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo:$tipo, palavra: strtolower($palavra->palavra));

    foreach($ban02_lista as $ban02) {
        $novo_ban02 = new Ban02 (
            $ban02->id,
            $ban02->id_empresa,
            $ban02->id_ban01,
            $ban02->data,
            $ban02->documento,
            $titulo,
            $subtitulo,
            $ban02->descricao,
            $ban02->descricao_comp,
            $ban02->valor,
            $ban02->id_original,
            $ban02->ativo
        );
        Ban02::update($novo_ban02);
    }


    header('Location: movimentacao.php');
} 

else if($acao == 'conciliar_marcados') {

    $lista_ban = $_POST['id_check'];
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');

    if(empty($lista_ban) || $lista_ban == '' || $titulo == '' || $subtitulo == '' ) {
        header('Location: movimentacao.php?status=erro_dados');
        exit;
    }
    foreach($lista_ban as $id) {
        $ban02 = Ban02::read($id, $_SESSION['usuario']->id_empresa)[0];
        $novo_ban02 = new Ban02 (
            $ban02->id,
            $ban02->id_empresa,
            $ban02->id_ban01,
            $ban02->data,
            $ban02->documento,
            $titulo,
            $subtitulo,
            $ban02->descricao,
            $ban02->descricao_comp,
            $ban02->valor,
            $ban02->id_original,
            $ban02->ativo
        );
        Ban02::update($novo_ban02);
    }
    header('Location: movimentacao.php?status=sucesso');
    exit;
}

else if($acao == 'conciliar_todas'){
    require_once '../../../db/entities/palavra_chave.php';
    require_once '../../../db/entities/contas.php';

    $palavras_lista = Pal01::read(id_empresa:$_SESSION['usuario']->id_empresa);
    if(empty($palavras_lista)) {
        header('Location: movimentacao.php?erro=erro_palavra');
        exit;
    } else {
        foreach($palavras_lista as $palavra) {
            $titulo = Con01::read($palavra->id_con01)[0];
            $tipo = $titulo->tipo;
            $titulo = $titulo->id;
            $subtitulo = Con02::read($palavra->id_con02)[0]->id;
            $ban02_lista = Ban02::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo:$tipo, palavra: strtolower($palavra->palavra));

            foreach($ban02_lista as $ban02) {
                $novo_ban02 = new Ban02 (
                    $ban02->id,
                    $ban02->id_empresa,
                    $ban02->id_ban01,
                    $ban02->data,
                    $ban02->documento,
                    $titulo,
                    $subtitulo,
                    $ban02->descricao,
                    $ban02->descricao_comp,
                    $ban02->valor,
                    $ban02->id_original,
                    $ban02->ativo
                );
                Ban02::update($novo_ban02);
            }
        }
    header('Location: movimentacao.php');
    exit;
    }

    

} else if($acao == 'desmembrar') {
    $id = filter_input(INPUT_POST, 'id');
    $id = intval($id);
    $valor_lista = $_POST['valor_desmembrado'];
    $descricao_comp_lista = $_POST['descricao_comp_desmembrado'];
    $descricao_comp_original = filter_input(INPUT_POST,'descricao_comp_original');
    $caminho = filter_input(INPUT_POST, 'caminho');

        
    
    $primeiro_ban02 = Ban02::read($id, $_SESSION['usuario']->id_empresa)[0];
    $valor_d_total = 0;
    foreach($valor_lista as $valor_d){
        if(preg_match('/[a-zA-Z]/', $valor_d)) {
        header('Location: ' . $caminho . 'erro=valor');
            exit;
        }

    $valor_d_total += $valor_d;

        if($primeiro_ban02->valor > 0){
            if($valor_d <= 0 || $valor_d > $primeiro_ban02->valor - 0.01) {
            header('Location: ' . $caminho);
            exit;
        }

        } 
        else if($primeiro_ban02->valor < 0) {
            if($valor_d > 0 ) {
                $valor_d *= (-1);
            }
            if($valor_d >= 0 || $valor_d < $primeiro_ban02->valor + 0.01) {
                header('Location: ' . $caminho);
                exit;
            }
        }
    }
    if($primeiro_ban02->valor < 0 ) {
        $valor_d_total *= (-1);
        if($valor_d_total <= $primeiro_ban02->valor) {
        header('Location: ' . $caminho . 'erro=valor');
        exit;
    }}else if($primeiro_ban02->valor > 0) {
        if($valor_d_total >= $primeiro_ban02->valor) {
        header('Location: ' . $caminho . 'erro=valor_total');
        exit;
    }
    }
    $i = 0;
    foreach($valor_lista as $valor_d){
        $descricao_comp_ban02 = $descricao_comp_lista[$i];
        if($primeiro_ban02->valor < 0) {
            $valor_d *= (-1);
        }
    $ban02_novo = new Ban02(
        null,
            $primeiro_ban02->id_empresa,
            $primeiro_ban02->id_ban01,
            $primeiro_ban02->data,
            $primeiro_ban02->documento,
            $primeiro_ban02->id_con01,
            $primeiro_ban02->id_con02,
            $primeiro_ban02->descricao,
            $descricao_comp_ban02,
            $valor_d,
            $primeiro_ban02->id,
            $primeiro_ban02->ativo
    );
    Ban02::create($ban02_novo);
    $i++;
    }

    if($valor_d < 0) {
        $valor_novo = $primeiro_ban02->valor * (-1);
        $valor_novo += $valor_d_total;
        $valor_novo *= (-1);
    } else {
        $valor_novo = $primeiro_ban02->valor - $valor_d_total;
    }
    $ban02_atualizado = new Ban02(
            $id,
            $primeiro_ban02->id_empresa,
            $primeiro_ban02->id_ban01,
            $primeiro_ban02->data,
            $primeiro_ban02->documento,
            $primeiro_ban02->id_con01,
            $primeiro_ban02->id_con02,
            $primeiro_ban02->descricao,
            $descricao_comp_original,
            $valor_novo,
            $id,
            $primeiro_ban02->ativo
    );
    Ban02::update($ban02_atualizado);

    header('Location:'. $caminho);

} else if($acao == 'cancelar_desmembramento') {
    $id = filter_input(INPUT_POST, 'id');
    $primeiro_ban02 = Ban02::read(id:$id, id_empresa: $_SESSION['usuario']->id_empresa)[0];
    $id_lista = $_POST['desmembramento_id'];
    $caminho = filter_input(INPUT_POST, 'caminho');

    $valor_desmembrado = 0;
    foreach($id_lista as $id_ban02) {
        $ban02 = Ban02::read($id_ban02, $_SESSION['usuario']->id_empresa)[0];
        $valor_desmembrado += $ban02->valor;

        Ban02::delete($id_ban02);
    }

    $ban02_atualizado = new Ban02(
            $id,
            $primeiro_ban02->id_empresa,
            $primeiro_ban02->id_ban01,
            $primeiro_ban02->data,
            $primeiro_ban02->documento,
            $primeiro_ban02->id_con01,
            $primeiro_ban02->id_con02,
            $primeiro_ban02->descricao,
            $primeiro_ban02->descricao_comp,
            $primeiro_ban02->valor + $valor_desmembrado,
            null,
            $primeiro_ban02->ativo
    );
    Ban02::update($ban02_atualizado);
    header('Location:'. $caminho);
    exit;
} else if($acao == 'editar_desmembramento') {
    $id = filter_input(INPUT_POST, 'id');
    $ban02 = Ban02::read($id, $_SESSION['usuario']->id_empresa)[0];
    if($ban02->id_original == null) {
        $primeiro_ban02 = $ban02;
    } else {
        $primeiro_ban02 = Ban02::read(id_empresa:$_SESSION['usuario']->id_empresa, id_original: $ban02->id_original)[0];
    }
    

    $descricao_comp_original = filter_input(INPUT_POST, 'descricao_comp_original');

    $desmembramento_id_lista = $_POST['desmembramento_id'];
    $descricao_comp_lista = $_POST['descricao_comp'];

    $caminho = filter_input(INPUT_POST, 'caminho');

    $i = 0;

    foreach($desmembramento_id_lista as $id) {
        $ban02_antigo = Ban02::read($id)[0];
        $descricao_comp = $descricao_comp_lista[$i];
        $desmembramento = new Ban02(
            $id,
            $ban02_antigo->id_empresa,
            $ban02_antigo->id_ban01,
            $ban02_antigo->data,
            $ban02_antigo->documento,
            $ban02_antigo->id_con01,
            $ban02_antigo->id_con02,
            $ban02_antigo->descricao,
            $descricao_comp,
            $ban02_antigo->valor,
            $ban02_antigo->id_original,
            $ban02_antigo->ativo
        );
        Ban02::update($desmembramento);
        
        $i++;
    }

    $ban02_atualizado = new Ban02(
        $primeiro_ban02->id,
        $primeiro_ban02->id_empresa,
            $primeiro_ban02->id_ban01,
            $primeiro_ban02->data,
            $primeiro_ban02->documento,
            $primeiro_ban02->id_con01,
            $primeiro_ban02->id_con02,
            $primeiro_ban02->descricao,
            $descricao_comp_original,
            $primeiro_ban02->valor,
            $primeiro_ban02->id_original,
            $primeiro_ban02->ativo
    );
    Ban02::update($ban02_atualizado);
    header('Location:'. $caminho);

} else if($acao == 'mov_pagar' || $acao == 'mov_receber') {

    $documento = filter_input(INPUT_POST, 'documento');
    $custo = filter_input(INPUT_POST, 'custo');
    $cadastro = filter_input(INPUT_POST, 'cadastro');
    $data_lanc = filter_input(INPUT_POST, 'data_lanc');
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $valor = filter_input(INPUT_POST, 'valor');
    $tipo_pagamento = filter_input(INPUT_POST, 'tipo_pagamento');
    $descricao = filter_input(INPUT_POST, 'descricao');
    $id_ban = filter_input(INPUT_POST, 'id_ban');

    $lanc01 = new Rec01 (
        null,
        $_SESSION['usuario']->id_empresa,
        $cadastro,
        $titulo,
        $subtitulo,
        $documento,
        $descricao,
        $valor,
        1,
        $data_lanc,
        $_SESSION['usuario']->id,
        $custo,
        $id_ban,
    );

    
    if($acao == 'mov_receber') {
        if(!Rec01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento: $documento)) {
            Rec01::create($lanc01);
            $lanc_criado = Rec01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento: $documento)[0];
        } else {
            header('Location:movimentacao.php?erro=cadastrado');
            exit;
        }
    } else if($acao == 'mov_pagar') {
        if(!Pag01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento: $documento)) {
            Pag01::create($lanc01);
            $lanc_criado = Pag01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento: $documento)[0];
        }else {
            header('Location:movimentacao.php?erro=cadastrado');
            exit;
        }
    }
    $lanc02 = new Rec02(
        null,
        $_SESSION['usuario']->id_empresa,
        $lanc_criado->id,
        $valor,
        1,
        $data_lanc,
        $valor,
        $data_lanc,
        null,
        $tipo_pagamento,
    );
    if($acao == 'mov_receber') {
        Rec02::create($lanc02);
    } else if($acao == 'mov_pagar') {
        Pag02::create($lanc02);
    }

    header('Location:movimentacao.php');
    exit;
    
    


    
}



    

?>