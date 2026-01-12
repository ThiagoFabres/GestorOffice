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
            
            
            $transactions['debug']['line'][] = $line;
            if (strpos($line, '<DTPOSTED>') !== false) {
                $date = substr($line, strlen('<DTPOSTED>'), 8);
                $current['data'] = date('d/m/Y', strtotime($date));
                $date = str_replace('</DTPOSTED>', '', $date);
                $current['data_analizada'] = date('Y-m-d', strtotime($date));
                
            }
            if(isset($current['data_analizada'])){;

            $data_analizada = $current['data_analizada'];
            
                // if (isset($importadas_set[$data_analizada])) continue;
                if (isset($importadas_set[$data_analizada])) continue;
                // if(Ban02Imp::read($_SESSION['usuario']->id_empresa, $conta, $data_analizada)) continue;

                if( $ultima_data != null && ($data_analizada >= $data_atual || $data_analizada == $ultima_data)) {
                    continue;
                }
            

        
           

            if ((strpos($line, '<TRNAMT>')) !== false) {
                
                $line = str_replace(',', '.', $line);
                $valor = $line;
                $current['valor'] = number_format((float)substr($line, strlen('<TRNAMT>')), 2, '.', '');
                $current['tipo'] = ($current['valor'] < 0) ? 'Débito' : 'Crédito';
                $transactions['debug']['current']['valor'][] = $current['valor'];
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
                $current['descricao'] .= ' ' . $name;
            }
            if ((strpos($line, '</STMTTRN>'))  !== false  ) {
                $transactions['current'][] = $current;
            }
        } 
            $transactions['debug']['current'][] = $current;
        }
        return $transactions;
    }

    if (isset($_FILES['ofx']) && $_FILES['ofx']['error'] === UPLOAD_ERR_OK) {
        $ofxPath = $_FILES['ofx']['tmp_name'];
        $transactions = parse_ofx($ofxPath);
        $transactions_current = $transactions['current'];

        $transactions_debug = $transactions['debug'];

        // echo '<pre>';
        // print_r($transactions_debug['current']['valor']);
        // exit;

        if(empty($transactions_current)) {
            header('Location: movimentacao.php?erro=cadastrado');
            exit;
        }
        $_SESSION['ofx_transactions']['transactions'] = $transactions_current;
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
    $sucesso = true;
    

        if(Ban02::read( id_empresa:$_SESSION['usuario']->id_empresa,documento:$documento)) {
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

}

    

?>