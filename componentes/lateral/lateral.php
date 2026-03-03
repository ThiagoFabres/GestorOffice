<nav id="barra-lateral">
        <div id="logo-container">
            <div class="d-flex flex-column">
                <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
                <p id="versao-lateral" class="text-center position-absolute" style="color: #ffffff7c; top: 170px; left: 110px;">V.2.2</p>
            </div>
        </div>
        <div id="itens-menu">
            <div class="menu-item <?php if($lateral_target == 'dashboard') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/index.php">
                    <div style=" align-items:center;"><i class="bi bi-layers"></i></div> Dashboard
                </a>
            </div>
                <div class="menu-item accordion <?php if($lateral_target == 'cadastro') {?> menu-item-atual<?php } ?>">

                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button"
                        aria-expanded="false" aria-controls="cadastrosMenu">
                        <div style=" align-items:center;"><i class="bi bi-person"></i></div> Cadastros
                    </a>
                    <div class="<?php if($lateral_target != 'cadastro'){?>collapse<?php } ?>" id="cadastrosMenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'cliente') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i
                                        class="bi bi-person"></i>Cliente/Fornecedor</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'bairro') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i
                                        class="bi bi-houses"></i>Bairro</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'cidade') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i
                                        class="bi bi-buildings"></i>Cidade</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'pagamento') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i
                                        class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'categoria') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i
                                        class="bi bi-tag"></i>Categoria</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'custo') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cadastrar.php?cadastro=custo" class="link-light text-decoration-none"><i 
                                        class="bi bi-bank"></i>Centro de custos</a></li>
                            <li class=" menu-li <?php if(isset($get_cadastro) && $get_cadastro == 'contas') { ?> menu-li-atual <?php } ?>"><a href="/usuario/contas.php" class="link-light text-decoration-none">
                                <i class="bi bi-journal-bookmark"></i>Plano de Contas</a></li>

                        </ul>
                    </div>
                </div>


            <div class="menu-item accordion <?php if( isset($lateral_financeiro) && $lateral_financeiro ){ 
                ?>menu-item-atual<?php } ?>">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#fincanceiroMenu" role="button"
                    aria-expanded="false" aria-controls="fincanceiroMenu">
                    <div style=" align-items:center;"><i class="bi bi-briefcase"></i></div> Controle Financeiro
                </a>
                <div class="<?php if( !isset($lateral_financeiro) || !$lateral_financeiro ){ ?>collapse<?php } ?>" id="fincanceiroMenu">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'receber') { ?> menu-li-atual <?php } ?>"><a href="/usuario/receber.php" class="link-light text-decoration-none">
                            <i class="bi bi-wallet"></i>
                            Contas a Receber</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'pagar') { ?> menu-li-atual <?php } ?>"><a href="/usuario/pagar.php" class="link-light text-decoration-none">
                            <i class="bi bi-cash-stack"></i>
                            Contas a Pagar</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'dre') { ?> menu-li-atual <?php } ?>"><a href="/usuario/dre/sintetico.php" class="link-light text-decoration-none">
                            <i class="bi bi-file-earmark-text"></i>
                            DRE Financeiro</a></li>
 
                    </ul>
                </div>
            </div>
            <div class="menu-item accordion <?php if( isset($lateral_bancario) && $lateral_bancario ){ 
                ?>menu-item-atual<?php } ?>">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#bancarioMenu" role="button"
                    aria-expanded="false" aria-controls="bancarioMenu">
                    <div style=" align-items:center;"><i class="bi bi-bank"></i></div> Controle Bancário
                </a>
                <div class="<?php if( !isset($lateral_bancario) || !$lateral_bancario ){ ?>collapse<?php } ?>" id="bancarioMenu">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'contaBancario') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/contas/conta.php" class="link-light text-decoration-none"><i
                                        class="bi bi-person"></i>Cadastro de conta</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'palavraChave') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/palavra/palavra_chave.php" class="link-light text-decoration-none"><i
                                        class="bi bi-key"></i>Palavra Chave</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'movimentacao') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/movimentacao/movimentacao.php" class="link-light text-decoration-none"><i
                                        class="bi bi-buildings"></i>Movimentação bancária</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'dreBancario') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/dre/sintetico.php" class="link-light text-decoration-none"><i
                                        class="bi bi-cash-coin"></i>Dre Bancário</a></li>
 
                    </ul>
                </div>
            </div>
            
            <div class="menu-item accordion <?php if( isset($lateral_cartao) && $lateral_cartao ){ 
                ?>menu-item-atual<?php } ?>">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#cartaoMenu" role="button"
                    aria-expanded="false" aria-controls="cartaoMenu">
                    <div style=" align-items:center;"><i class="bi bi-credit-card"></i></div>Controle Cartão
                </a>
                <div class="<?php if( !isset($lateral_cartao) || !$lateral_cartao ){ ?>collapse<?php } ?>" id="cartaoMenu">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'cadastro_cartao') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cartao/cadastro_cartao.php" class="link-light text-decoration-none">
                            <i class="bi bi-wallet"></i>Cadastro</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'cartao_vendas') { ?> menu-li-atual <?php } ?>"><a href="/usuario/cartao/cadastro_vendas.php" class="link-light text-decoration-none">
                            <i class="bi bi-coin"></i></i>Lançamento Vendas</a></li>
 
                    </ul>
                </div>
            </div>
            <div class="menu-item accordion <?php if( isset($lateral_recorrente) && $lateral_recorrente ){ 
                ?>menu-item-atual<?php } ?>">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#recorrenteMenu" role="button"
                    aria-expanded="false" aria-controls="recorrenteMenu">
                    <div style=" align-items:center;"><i class="bi bi-clock"></i></div>Recorrentes
                </a>
                <div class="<?php if( !isset($lateral_recorrente) || !$lateral_recorrente ){ ?>collapse<?php } ?>" id="recorrenteMenu">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'recorrente_receber') { ?> menu-li-atual <?php } ?>"><a href="/usuario/recorrente/receber.php" class="link-light text-decoration-none">
                            <i class="bi bi-wallet"></i>Contas a Receber</a></li>
                        <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'recorrente_pagar') { ?> menu-li-atual <?php } ?>"><a href="/usuario/recorrente/pagar.php" class="link-light text-decoration-none">
                            <i class="bi bi-cash-stack"></i>Contas a Pagar</a></li>
 
                    </ul>
                </div>
            </div>
            
            <div class="menu-item <?php if($lateral_target == 'comparativo') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/dre/comparativo.php">
                    <div style=" align-items:center;"><i class="bi bi-arrow-left-right"></i></div> DRE Comparativo
                </a>
            </div>

            <div class="menu-item <?php if($lateral_target == 'manual') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/manual/manual.php">
                    <div style=" align-items:center;"><i class="bi bi-binoculars"></i></div> Manual
                </a>
            </div>


        </div>
        </div>

    </nav>
    <?php require_once __DIR__ . '/../../componentes/footer/footer.php'?>