<?php
require_once '../../../db/entities/usuarios.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

require_once '../../../db/entities/banco02.php';
require_once '../../../db/entities/banco01.php';
require_once 'buscar_documento.php';


$acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_STRING) ?? filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_STRING);
if($acao == 'processar') {
    
    function parse_ofx($filePath) {
        $content = file_get_contents($filePath);

        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $lines = explode("\n", $content);

        $conta = filter_input(INPUT_POST, 'conta');
        $data_atual = (new DateTime())->format('Y-m-d');
        $ultima_data = buscarData($conta);
        $importadas = Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta);      
        $importadas_set = array_flip($importadas);


        // Tenta converter para UTF-8 se não estiver
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        $lines = explode("\n", $content);
        $transactions = [];
        $current = [];
        $conta = filter_input(INPUT_POST, 'conta');
        foreach ($lines as $line) {
            
            if (str_starts_with($line, '<DTPOSTED>') !== false) {
                $date = substr($line, strlen('<DTPOSTED>'), 8);
                $current['data'] = date('d/m/Y', strtotime($date));
                $date = str_replace('</DTPOSTED>', '', $date);
                $current['data_analizada'] = date('Y-m-d', strtotime($date));
                
            }
            if(isset($current['data_analizada'])){;

            $data_analizada = $current['data_analizada'];
            
                if (isset($importadas_set[$data_analizada])) continue;

                if( $ultima_data != null && ($data_analizada >= $data_atual || $data_analizada == $ultima_data)) {
                    continue;
                }
            }
           

            if (str_starts_with($line, '<TRNAMT>') !== false) {
                $current['valor'] = number_format((float)substr($line, strlen('<TRNAMT>')), 2, '.', '');
                $current['tipo'] = ($current['valor'] < 0) ? 'Débito' : 'Crédito';
            }
            if (str_starts_with($line, '<CHECKNUM>') !== false) {
                $doc = substr($line, strlen('<CHECKNUM>'));
                $doc = str_replace('</CHECKNUM>', '', $doc);
                $current['documento'] = $doc;
            }
            if (str_starts_with($line, '<MEMO>') !== false) {
                $desc = substr($line, strlen('<MEMO>'));
                $desc = str_replace('</MEMO>', '', $desc);
                // Garante que a descrição está em UTF-8
                $current['descricao'] = $desc;
            }
            if(str_starts_with($line, '<NAME>') !== false) {
                $name = substr($line, strlen('<NAME>'));
                $name = str_replace('</NAME>', '', $name);
                // Garante que a descrição está em UTF-8
                $current['descricao'] .= ' ' . $name;
            }
            if (str_starts_with($line, '</STMTTRN>') !== false && !empty($current)) {
                $transactions[] = $current;
            }
        }
        return $transactions;
    }

    if (isset($_FILES['ofx']) && $_FILES['ofx']['error'] === UPLOAD_ERR_OK) {
        $ofxPath = $_FILES['ofx']['tmp_name'];
        $transactions = parse_ofx($ofxPath);
        $_SESSION['ofx_transactions']['transactions'] = $transactions;
        $_SESSION['ofx_transactions']['ofx_conta'] = $_POST['conta'];
        $_SESSION['ofx_transactions']['file_name'] = $ofxPath;
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
    $conta = $_POST['conta'];
    $imp_lista = [];
    $dias_usados = [];
    $cadastrado = false;
    $documento = buscarDocumento();

        if(Ban02::read(documento:$documento)) {
            $cadastrado = true;
        }
   if($cadastrado === false) {

    for($i = 0; $i < $_POST['total_linhas']; $i++) {
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
            $_SESSION['dias_usados'][$i] = DateTime::createFromFormat('Y-m-d', $data_formatada)->format('d-m-Y');
        }
        $documento++;
    }
    if(!empty($imp_lista)) {
        $sucesso = true;
        foreach($imp_lista as $imp) {
        if(!Ban02Imp::create($imp)) {
            $sucesso = false;
        }
    }
    }
    
    if($sucesso) {
        header('Location: movimentacao.php?sucesso=sucesso');
    }
    
    exit;
        
   } else {
    header('Location: movimentacao.php?sucesso=cadastrado');
    exit;
   }
} else if($acao == 'conciliar') {
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $id = filter_input(INPUT_POST, 'id');
    $movimentacao_antiga = Ban02::read($id)[0];

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
    echo '<pre>';
    print_r($movimentacao_antiga);
    echo '</pre>';
    echo '<pre>';
    print_r($movimentacao);
    echo '</pre>';

    header('Location: movimentacao.php?sucesso=sucesso');
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
} else if($acao == 'conciliar_todas'){
    require_once '../../../db/entities/palavra_chave.php';
    require_once '../../../db/entities/contas.php';

    $palavras_lista = Pal01::read(id_empresa:$_SESSION['usuario']->id_empresa);
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
    header('Location: movimentacao.php');
    }

    

} else if ($acao == 'limpar_titulo') {
    $ban02_lista = Ban02::read(null, $_SESSION['usuario']->id_empresa);
    foreach($ban02_lista as $ban02) {
        $novo_ban02 = new Ban02 (
            $ban02->id,
            $ban02->id_empresa,
            $ban02->id_ban01,
            $ban02->data,
            $ban02->documento,
            null,
            null,
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

    

?>