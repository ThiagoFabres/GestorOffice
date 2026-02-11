<?php


require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/recebimentos.php';
require_once __DIR__ . '/../db/entities/pagar.php';
require_once __DIR__ . '/../db/entities/centrocustos.php';

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;

}
require_once __DIR__ . '/../db/buscar_documento_pag.php';
require_once __DIR__ . '/../db/buscar_documento_rec.php';


$view = filter_input(INPUT_POST, 'view');
if ($view == null) {
    $view = filter_input(INPUT_GET, 'view');
}
$acao = filter_input(INPUT_POST, 'acao');
if ($acao == null) {
    $acao = filter_input(INPUT_GET, 'acao');
}
$caminho = filter_input(INPUT_POST, 'caminho');
$caminho = urldecode($caminho);
if ($caminho == null) {
    $caminho = filter_input(INPUT_GET, 'caminho');
}



$target = filter_input(INPUT_POST, 'target');
if ($target == null) {
    $target = filter_input(INPUT_GET, 'target');
}



$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id == null) {
    $id = filter_input(INPUT_GET, 'id');
}
$con01 = filter_input(INPUT_POST, 'con01id');


if (isset($view) && $view == 'cadastro') {





    $id_empresa = $_SESSION['usuario']->id_empresa;
    $nome = filter_input(INPUT_POST, 'nome');
    $fantasia = filter_input(INPUT_POST, 'fantasia');
    $endereco = filter_input(INPUT_POST, 'endereco');
    $bairro = filter_input(INPUT_POST, 'bairro');
    $cidade = filter_input(INPUT_POST, 'cidade');
    $estado = filter_input(INPUT_POST, 'estado');
    $cpf = filter_input(INPUT_POST, 'cpf');
    $cnpj = filter_input(INPUT_POST, 'cnpj');
    $email = filter_input(INPUT_POST, 'email');
    $celular = filter_input(INPUT_POST, 'celular');
    $fixo = filter_input(INPUT_POST, 'fixo');
    $cep = filter_input(INPUT_POST, 'cep');
    $categoria = filter_input(INPUT_POST, 'categoria');
    $nome = filter_input(INPUT_POST, 'nome');
    $tipo = filter_input(INPUT_POST, 'tipo');


    $insta = filter_input(INPUT_POST, 'insta') ?? null;
    // support receiving the quitar id either as 'id' (existing behavior) or explicit 'modal_quitar_id'
    if($target == 'pagamento') {
        $modal_quitar_id = filter_input(INPUT_POST, 'modal_quitar_id') ?? filter_input(INPUT_POST, 'id') ?? null;
    }
    


    

    if ($acao == 'adicionar') {
        switch ($target) {
            case 'cliente':
                $data_r = (new DateTime())->format('Y-m-d');
                $cadastro = new Cadastro(
                    $_SESSION['usuario']->id_empresa,
                    null,
                    $nome,
                    $fantasia ?? null,
                    $endereco ?? null,
                    $bairro ?? null,
                    $cidade ?? null,
                    $estado ?? null,
                    $cpf ?? null,
                    $cnpj ?? null,
                    $email ?? null,
                    $celular ?? null,
                    $fixo ?? null,
                    $cep ?? null,
                    $categoria ?? null,
                    $data_r,
                );

                if(isset($email) && $email != null) {
                    if (!Cadastro::read(null, $email)) {
                        Cadastro::create($cadastro);
                    if(!isset($insta) || $insta == null) {
                        header('Location: cadastrar.php?cadastro=cliente');
                        exit;
                    } else {
                        if(isset($insta) && $insta != null) {
                        if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                        if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                        exit;
                    }
                    }
                } else {
                    header('Location:cadastrar.php?cadastro=cliente&erro=repetido');
                    exit;
                }
                } else {
                    
                    Cadastro::create($cadastro);
                    if(isset($insta) && $insta != null) {
                        if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                        else if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                        else header('Location: cadastrar.php?cadastro=cliente');
                        exit;
                    }
                    header('Location:cadastrar.php?cadastro=cliente');
                    exit;
                }
                


                header('Location:cadastrar.php?cadastro=cliente');
                
                break;

            case 'bairro':

                $bairro = new Bairro(
                    null,
                    $id_empresa,
                    $nome
                );

                Bairro::create($bairro);
                if(isset($insta) && $insta != null) {
                        if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                        else if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                        else header('Location: cadastrar.php?cadastro=bairro');
                        exit;
                    }
                break;

            case 'cidade':

                $cidade = new Cidade(
                    null,
                    $id_empresa,
                    $nome
                );
                Cidade::create($cidade);
                if(isset($insta) && $insta != null) {
                    if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                    else if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                    else header('Location: cadastrar.php?cadastro=cidade');
                exit;
                }
                
                break;

            case 'categoria':

                $categoria = new Categoria(
                    null,
                    $id_empresa,
                    $nome
                );
                if (!Categoria::read(null, $id_empresa, $nome)) {
                    Categoria::create($categoria);
                    if(isset($insta) && $insta != null) {
                        if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                        else if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                        else header('Location: cadastrar.php?cadastro=categoria');
                        exit;
                    }
                } else {
                    
                }
                break;

            case 'pagamento':

                $categoria = new TipoPagamento(
                    null,
                    $id_empresa,
                    $nome
                );
                if (!TipoPagamento::read(null, $id_empresa, $nome)) {
                    TipoPagamento::create($categoria);
                    if(isset($insta) && $insta != null){
                        if($insta == 'receber') header('Location: receber.php');
                        else if($insta == 'pagar') header('Location: pagar.php');
                        else header('Location: cadastrar.php?cadastro=categoria');
                        exit;
                        
                    }
                }
                break;
            case 'custo':

                $centrocusto = new CentroCustos(
                    null,
                    $id_empresa,
                    $nome
                );
                if (!CentroCustos::read(null, $id_empresa, $nome)) {
                    CentroCustos::create($centrocusto);
                    if(isset($insta) && $insta != null) {
                        if($insta == 'pagar') header('Location: pagar.php?acao=adicionar');
                        else if($insta == 'receber') header('Location: receber.php?acao=adicionar');
                        else header('Location: cadastrar.php?cadastro=categoria');
                        exit;
                    }
                }
                break;
        }
    } else if ($acao == 'editar') {
        switch ($target) {
            case 'cliente':
                $cadastro = new Cadastro(
                    null,
                    $id,
                    $nome,
                    $fantasia,
                    $endereco,
                    $bairro,
                    $cidade,
                    $estado,
                    $cpf,
                    $cnpj,
                    $email,
                    $celular,
                    $fixo,
                    $cep,
                    $categoria
                );


                Cadastro::update($cadastro);



                break;

            case 'bairro':

                $bairro = new Bairro(
                    $id,
                    null,
                    $nome
                );

                Bairro::Update($bairro);
                break;

            case 'cidade':

                $cidade = new Cidade(
                    $id,
                    null,
                    $nome
                );

                Cidade::Update($cidade);
                break;

            case 'categoria':

                $categoria = new Categoria(
                    $id,
                    null,
                    $nome
                );

                Categoria::Update($categoria);
                break;

            case 'pagamento':

                $pagamento = new TipoPagamento(
                    $id,
                    null,
                    $nome
                );
                TipoPagamento::update($pagamento);
                break;
            
            case 'custo':
                $centrocusto = new CentroCustos(
                    $id,
                    $_SESSION['usuario']->id_empresa,
                    $nome
                );
                CentroCustos::update($centrocusto);
                break;
        }
    } else if ($acao == 'excluir') {

        switch ($target) {

            case 'bairro':


                if(Cadastro::read(filtro_bairro: $id)) {
                    header('Location:cadastrar.php?cadastro=bairro&erro=usado');
                    exit;
                } else {
                    Bairro::delete($id);
                }
                
                break;

            case 'cidade':
                
                if(Cadastro::read(filtro_cidade: $id)) {
                    header('Location:cadastrar.php?cadastro=cidade&erro=usado');
                    exit;
                } else {
                    Cidade::delete($id);
                }
                
                break;

            case 'categoria':
                if(Cadastro::read(filtro_categoria: $id)) {
                    header('Location:cadastrar.php?cadastro=categoria&erro=usado');
                    exit;
                } else {
                    Categoria::delete($id);
                }
                
                break;

            case 'pagamento':
                if(Rec02::read(filtro_pagamento: $id) || Pag02::read(filtro_pagamento:$id)) {
                    header('Location:cadastrar.php?cadastro=pagamento&erro=usado');
                    exit;
                } else {
                    TipoPagamento::delete($id);
                }
                
                break;
            case 'custo':
                if(Rec02::read(filtro_custos: $id) || Pag02::read(filtro_custos: $id)) {
                    header('Location:cadastrar.php?cadastro=custo&erro=usado');
                    exit;
                } else {
                    CentroCustos::delete($id);
                }
                break;
            case 'cliente':
                if(Rec02::read(filtro_cadastro:$id) || Pag02::read(filtro_cadastro:$id)) {
                    header('Location:cadastrar.php?cadastro=cliente&erro=usado_e');
                    exit;
                } else {
                    Cadastro::delete($id);
                }
                
        }
                

            
    }

    if(isset($insta) && $insta != null) {
        
        if(!isset($target) || $target === null) {
            if($insta == 'pagar') header('Location: pagar.php?acao=adicionar'); 
            exit;
            
            if($insta == 'receber') header('Location: receber.php?acao=adicionar'); 
            exit;
        } else {
            if($insta == 'pagar') header('Location: pagar.php?acao=adicionar&target=cadastro'); 
            exit;

            if($insta == 'receber') header('Location: receber.php?acao=adicionar&target=cadastro'); 
            exit;
        }
    }
    
    

    if(!isset($insta) || $insta == null) header('Location: cadastrar.php?cadastro=' . $target);
    exit;







} else if (isset($view) && $view == 'conta') {
    $nome = filter_input(INPUT_POST, 'nome');
    $insta = filter_input(INPUT_POST, 'insta' );
    $tipo = filter_input(INPUT_POST, 'tipo');
    $id_conta = filter_input(INPUT_POST, 'con01id');
    $id_conta02 = filter_input(INPUT_POST, 'con02id');
 
    if($target == 'titulo') {
        $operacional = filter_input(INPUT_POST, 'operacional') == 'on' ? 1 : 0;

        if (isset($acao) && $acao == 'adicionar') {
            $conta = new Con01(
                null,
                $_SESSION['usuario']->id_empresa,
                $tipo,
                $nome,
                $operacional
            );

            Con01::create($conta);
        } else if (isset($acao) && $acao == 'editar') {
            $conta = new Con01(
                $id_conta,
                null,
                $tipo,
                $nome,
                $operacional
            );
                Con01::update($conta);

            
        } else if (isset($acao) && $acao == 'excluir') {
            
            if(!Con02::read(null, null, $id_conta) ) {
                Con01::delete($id_conta);
            } else {
                header('Location: contas.php?con01id='.$id_conta.'&erro=usado_sub');
                exit;
            };
        } 
        
    }

    
    if(isset($target) && $target == 'subtitulo') {

         if (isset($acao) && $acao == 'adicionar') {
                $conta = new Con02(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    $id_conta,
                    $nome
                );
                Con02::create($conta);
            } else if (isset($acao) && $acao == 'editar') {
                $conta = new Con02(
                    $id_conta02,
                    $_SESSION['usuario']->id_empresa,
                    $id_conta,
                    $nome
                );

            Con02::update($conta);
            } else if (isset($acao) && $acao == 'excluir') {


                if(!Rec01::read(con02: $id_conta02) 
                    && !Pag01::read(con02: $id_conta02) ) {
            Con02::delete($id_conta02);
        } else {
            header('Location: contas.php?con01id='.$id_conta.'&erro=usado');
            exit;
        }
            } 
    }

    
    if(isset($insta) && $insta != null) {
        if($insta == 'pagar') {
            header('Location: pagar.php?acao=adicionar'); 
            exit;
        }
        if($insta == 'receber') {
            header('Location: receber.php?acao=adicionar');
            exit;
        }
    }
    if (isset($con01)) {
        $route = '?con01id=' . $con01;
    }
    ;
    header('Location: contas.php' . $route);
    exit;
} else if (isset($view) && ($view == 'receber' || $view == 'pagar')) {
    $cadastro = filter_input(INPUT_POST, 'cadastro');
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $documento = filter_input(INPUT_POST, 'documento');
    $descricao = filter_input(INPUT_POST, 'descricao');
    $custo = filter_input(INPUT_POST, 'custo');
    $valor = filter_input(INPUT_POST, 'valor');
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    $id_ban = filter_input(INPUT_POST, 'id_ban');
    
    $parcelas_d = filter_input(INPUT_POST, 'parcelas_d');

    $data_lanc = filter_input(INPUT_POST, 'data_lanc') ?? null;
    $parcela = $_POST['parcela'] ?? [];
    $vencimento = $_POST['vencimento'] ?? [];
    $valor_par = $_POST['valor_par'] ?? [];
    $obs02 = $_POST['obs02'] ?? [];
    $id = $_POST['id'] ?? [];
    $id_rec = filter_input(INPUT_POST, 'id_lancamento');
    $data_pag = filter_input(INPUT_POST, 'data');
    $insta = filter_input(INPUT_POST, 'insta') ?? null;
    // $data_pag = new DateTime($data_pag);
    // $data_pag = $data_pag->format('d-m-Y');
    $forma_pagamento = filter_input(INPUT_POST, 'forma_pagamento');
    if($caminho != null || $caminho != '')$numero_pagina = filter_input(INPUT_POST, 'pagina') ?? filter_input(INPUT_GET, 'pagina');
    if($caminho == null || $caminho == '')$numero_pagina = filter_input(INPUT_POST, 'pagina') ?? str_replace('?', '&', filter_input(INPUT_GET, 'pagina'));

    if($caminho != null || $caminho != '')$numero_exibir = filter_input(INPUT_POST, 'numero_exibido') ?? filter_input(INPUT_GET, 'numero_exibido');
    if($caminho == null || $caminho == '')$numero_exibir = filter_input(INPUT_POST, 'numero_exibido') ?? str_replace('k', '&', filter_input(INPUT_GET, 'numero_exibido'));
    $numero_exibir = str_replace('k', '&', $numero_exibir);
    
    if ((isset($_POST['pagar']) || isset($_GET['pagar'])) && ($_POST['pagar'] == 1 || $_GET['pagar'] == 1)) {
        $pagar = true;
    } else {
        $pagar = false;
    }


    if($id_rec != null && $acao == 'editar') {
        if($pagar) {
        if(!Pag01::read($id_rec)) {
            $acao = 'adicionar';
        };
    }else {
        if(!Rec01::read(id: $id_rec)) {
            $acao = 'adicionar';
        };
    }
    }


    if(($id_rec == null || $id_rec == '' )&& $acao == 'editar') {
        $acao  = 'adicionar';
    }

    if($pagar) {
        $documento = buscarDocumentoPag();
    } else if(!$pagar) {
        $documento = buscarDocumentoRec();
    }

    




    if (isset($acao) && $acao == 'adicionar') {

        $recebimento = new Rec01(
            null,
            $_SESSION['usuario']->id_empresa,
            $cadastro,
            $titulo,
            $subtitulo,
            $documento,
            $descricao,
            $valor,
            $parcelas_d,
            $data_lanc,
            $_SESSION['usuario']->id,
            $custo,
            $id_ban
        );

        echo '<pre>';
        print_r($recebimento);  

        if ($pagar) {
            if(!Pag01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0]) {
                Pag01::create($recebimento);
            } else {
                header('Location:pagar.php?erro=repetido');
                exit;
            }
            $rec01 = Pag01::read(null, $_SESSION['usuario']->id_empresa, id_cadastro: $cadastro, documento: $documento)[0];
        } else {
            if (!Rec01::read(null, $_SESSION['usuario']->id_empresa, id_cadastro: $cadastro, documento: $documento)) {
                Rec01::create($recebimento);
            } else {
                header('Location:receber.php?erro=repetido');
                exit;
            }
            $rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }

        $parcelas = [];
        for ($i = 1; $i < $parcelas_d + 1; $i++) {
            $valor_parcela = str_replace('.', '', $valor_par[$i]);
            $valor_parcela = str_replace(',', '.', $valor_parcela);
            $valor_parcela = floatval($valor_parcela);

            $vencimento_data = $vencimento[$i];
            $obs_parcela = $obs02[$i];

            if ($pagar) {
                $parcelas[$i] = new Pag02(
                    null,                           // id (PK da parcela)
                    $_SESSION['usuario']->id_empresa,
                    $rec01->id,                     // id_pag01 (FK)
                    $valor_parcela,
                    $i,
                    $vencimento_data,
                    0,
                    null,
                    $obs_parcela,
                    null
                );
                Pag02::create($parcelas[$i]);
            } else {
                $parcelas[$i] = new Rec02(
                    null,                           // id (PK da parcela)
                    $_SESSION['usuario']->id_empresa,
                    $rec01->id,                     // id_rec01 (FK)
                    $valor_parcela,
                    $i,
                    $vencimento_data,
                    0,
                    null,
                    $obs_parcela,
                    null
                );
                Rec02::create($parcelas[$i]);
            }
            ;


        }
        if(isset($insta)) {
            if($insta == 'movimentacao') {
                header('Location: /usuario/movimentacao/movimentacao.php');
                exit;
            }
        } else {
            if ($pagar) {
                header('Location: pagar.php');
                exit;
            } else {
                header('Location: receber.php');
                exit;
            }
        }

        
    } else if (isset($acao) && $acao == 'editar') {

        if ($pagar) {

            $rec01 = Pag01::read($id_rec, $_SESSION['usuario']->id_empresa, null)[0];
        } else {
            $rec01 = Rec01::read($id_rec, $_SESSION['usuario']->id_empresa, null)[0];
        }
        $data_lanc = filter_input(INPUT_POST, 'data_lanc') ?? $rec01->data_lanc;
        $recebimento = new Rec01(
            $id_rec,
            $_SESSION['usuario']->id_empresa,
            $cadastro,
            $titulo,
            $subtitulo,
            $documento,
            $descricao,
            $valor,
            $parcelas_d,
            $data_lanc,
            $_SESSION['usuario']->id,
            $custo
        );

        if ($pagar) {
            if ($rec01->documento == $recebimento->documento || !Pag01::read(null, $_SESSION['usuario']->id_empresa, id_cadastro: $cadastro, documento: $documento)[0]) {
                Pag01::update($recebimento);
            } else {
                header('Location:pagar.php?erro=repetido');
                exit;
            }
            $rec01 = Pag01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        } else {
            if ($rec01->documento == $recebimento->documento || !Rec01::read(null, $_SESSION['usuario']->id_empresa,id_cadastro: $cadastro, documento: $documento)) {
                Rec01::update($recebimento);
            } else {
                header('Location:receber.php?erro=repetido');
                exit;
            }
            $rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }



        // $parcelas = [];
        // for($i = 1; $i < $parcelas_d + 1; $i++) {
        //     $valor_parcela = $valor_par[$i];
        //     $vencimento_data = $vencimento[$i];
        //     $obs_parcela = $obs02[$i];
        //     $id_parcela = $id[$i];

        //     $parcelas[$i] = new Rec02(
        //     $id_parcela,
        //     $_SESSION['usuario']->id_empresa,
        //     $rec01->id,
        //     $valor_parcela,
        //     $i,
        //     $vencimento_data,
        //     0,
        //     null,
        //     $obs_parcela,
        //     null

        // );
        // if($pagar) {
        //     $update = (Pag02::update($parcelas[$i]));
        //     if(!$update) {
        //         Pag02::create($parcelas[$i]);
        //     }
        // } else {
        //     $update = Rec02::update($parcelas[$i]);
        //     if(!$update) {
        //         Rec02::create($parcelas[$i]);
        //     }
        // }

        // }

        if ($pagar) {
            Pag02::deletebypag01($id_rec);
        } else {
            Rec02::deletebyrec01($id_rec);
        }


        $parcelas = [];
        for ($i = 1; $i < $parcelas_d + 1; $i++) {
            $valor_parcela = str_replace('.', '', $valor_par[$i]);
            $valor_parcela = str_replace(',', '.', $valor_parcela);
            $valor_parcela = floatval($valor_parcela);
            
            $vencimento_data = $vencimento[$i];
            $obs_parcela = $obs02[$i];
            
            if ($pagar) {
                $parcelas[$i] = new Pag02(
                    null,                           // id (PK da parcela)
                    $_SESSION['usuario']->id_empresa,
                    $id_rec,                     // id_pag01 (FK)
                    $valor_parcela,
                    $i,
                    $vencimento_data,
                    0,
                    null,
                    $obs_parcela,
                    null
                );
                
                Pag02::create($parcelas[$i]);
            } 
            else {
                $parcelas[$i] = new Rec02(
                    null,                           // id (PK da parcela)
                    $_SESSION['usuario']->id_empresa,
                    $id_rec,                     // id_rec01 (FK)
                    $valor_parcela,
                    $i,
                    $vencimento_data,
                    0,
                    null,
                    $obs_parcela,
                    null
                );
                Rec02::create($parcelas[$i]);
            }
            ;

        }
        if ($pagar) {
            header('Location: pagar.php');
            exit;
        } else {
            header('Location: receber.php');
            exit;
        }

    } else if (isset($acao) && $acao == 'quitar') {
        $insta = filter_input(INPUT_POST, 'insta');
        if($valor != '' && $valor != null) {
        $valor_parcela = str_replace(',', '.', $valor);
        $valor_parcela = floatval($valor_parcela);
        }

        var_dump($valor) . '<br>';
        
        if ($target == 'parcela') {
            if ($pagar) {
                echo 'a';
                $parcela_antiga = Pag02::read($id)[0];
                
            } else {
                $parcela_antiga = Rec02::read($id)[0];
            }

            if($valor == '' || $valor == null) {
                $valor_parcela = $parcela_antiga->valor_par;
            }
            $parcela = new Rec02(
                $id,
                null,
                null,
                $parcela_antiga->valor_par,
                $parcela_antiga->parcela,
                $parcela_antiga->vencimento,
                $valor_parcela + $parcela_antiga->valor_pag,
                $data_pag,
                $parcela_antiga->obs,
                $forma_pagamento
            );
            if ($pagar) {
                Pag02::update($parcela);
                
            } else {
                Rec02::update($parcela);
            }
            if($insta == 'bancario') {
                header('Location: bancario/movimentacao/'.$caminho.$numero_pagina.$numero_exibir);
                exit;
            }
            header('Location:' . $caminho.$numero_pagina.$numero_exibir);
            exit;


        }
    } else if (isset($acao) && $acao == 'estornar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id == null) {
            $id = filter_input(INPUT_GET, 'id');
        }


        if($caminho != 'receber.php' && $caminho != 'pagar.php') {
            $numero_pagina = str_replace('?', '&', $numero_pagina);
        }

        $caminho_completo = $caminho.$numero_pagina.$numero_exibir;
        
        $caminho_completo = str_replace(' ', '', $caminho_completo);

       
        if ($target == 'parcela') {
            if ($pagar) {
                $parcela_antiga = Pag02::read($id)[0];
            } else {
                $parcela_antiga = Rec02::read($id)[0];
            }
            
            $parcela = new Rec02(
                id: $id,
                valor_par: $parcela_antiga->valor_par,
                parcela: $parcela_antiga->parcela,
                vencimento: $parcela_antiga->vencimento,
                valor_pag: 0,
                data_pag: $parcela_antiga->data_pag,
                obs: $parcela_antiga->obs,
            );
            
            if ($pagar) {
                Pag02::update($parcela);
                header('Location:' . $caminho_completo);
                exit;
            } else {

                Rec02::update($parcela);
                header('Location:' . $caminho_completo);
                exit;
            }


        }
    } else if(isset($acao) && $acao == 'excluir') {
        $parcelas_pagas = false;
        if($pagar) {
            foreach(Pag02::read(id_pag01:$id_rec, id_empresa:$_SESSION['usuario']->id_empresa) as $parcela) {
                if($parcela->valor_pag != 0){
                    $parcelas_pagas = true;
                    header('Location: pagar.php?erro=usado');
                    exit;
                }
                if($parcelas_pagas == false) {
                    Pag01::delete($id_rec);
                    header('Location: pagar.php');
                }
            }
        } else if($pagar == false) {

            foreach(Rec02::read(id_rec01:$id_rec, id_empresa:$_SESSION['usuario']->id_empresa) as $parcela) {
                if($parcela->valor_pag != 0){
                    $parcelas_pagas = true;
                    header('Location: receber.php?erro=usado');
                    exit;
                }
                if($parcelas_pagas == false) {
                    Rec01::delete($id_rec);
                    header('Location: receber.php');
                }
            }
        }
    }

}


?>