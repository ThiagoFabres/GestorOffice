<nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
        <div id="itens-menu">
            <div class="menu-item <?php if($lateral_target == 'dashboard') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/index.php">
                    <div style=" align-items:center;"><i class="bi bi-layers"></i></div> Dashboard
                </a>
            </div>
            <?php if ($_SESSION['usuario']->processar == 1) { ?>
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

                        </ul>
                    </div>
                </div>
            <?php } ?>

            <div class="menu-item <?php if($lateral_target == 'contas') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/contas.php">
                    <div style=" align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano
                    de Contas
                </a>
            </div>

            <div class="menu-item <?php if($lateral_target == 'receber') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/receber.php">
                    <div style=" align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber
                </a>
            </div>

            <div class="menu-item <?php if($lateral_target == 'pagar') {?>menu-item-atual<?php } ?>">
                <a href="/usuario/pagar.php">
                    <div style=" align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a
                    Pagar
                </a>
            </div>

            <div class="menu-item <?php if($lateral_target == 'dre'){ ?>menu-item-atual<?php } ?>">
                <a href="/usuario/dre/sintetico.php">
                    <div style=" align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE Financeiro
                </a>
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
                            <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'palavraChave') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/palavraChave.php" class="link-light text-decoration-none"><i
                                        class="bi bi-key"></i>Palavra Chave</a></li>
                            <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'movimentacao') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/movimentacao.php" class="link-light text-decoration-none"><i
                                        class="bi bi-buildings"></i>Movimentação bancária</a></li>
                            <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'dreBancario') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/dre.php" class="link-light text-decoration-none"><i
                                        class="bi bi-cash-coin"></i>Dre Bancário</a></li>
                            <li class=" menu-li <?php if(isset($lateral_target) && $lateral_target == 'relatorioBancario') { ?> menu-li-atual <?php } ?>"><a href="/usuario/bancario/relatorio.php" class="link-light text-decoration-none"><i 
                                        class="bi bi-journal-bookmark"></i>Relatórios</a></li>
 
                        </ul>
                    </div>
                </div>


        </div>
        </div>

    </nav>