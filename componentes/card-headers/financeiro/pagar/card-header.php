<div class="card-header-div">
    <div class="card-header-borda">
        <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
            <h5 class="card-title">Filtros</h5>
            <form method="get" action="pagar.php">
                <?php if ($numero_exibir != 10) { ?> 
                    <input type="hidden" value="<?= $numero_exibir ?>" name="numero_exibido" /> 
                <?php } ?>
                <div class="form-pagamento">
                    <div class="inputs-pagamento-group">
                        <div class="row">
                            <div class="inputs-pagamento-text" id="inputs-text">
                                <div class="r-inputs-data">
                                    <!-- Data inicial -->
                                    <div style="width: 50%;">
                                        <label for="filtro_data_inicial">Data Inicial:</label>
                                        <input type="date" id="filtro_data_inicial" name="filtro_data_inicial" value="<?= $get_filtro_data_inicial; ?>" class="form-control" style="border-top-right-radius: 0;">
                                    </div>
                                    <!-- Data final -->
                                    <div style="width: 50%;">
                                        <label for="filtro_data_final">Data Final:</label>
                                        <input type="date" id="filtro_data_final" name="filtro_data_final" value="<?= $get_filtro_data_final; ?>" class="form-control" style="border-radius: 0;">
                                    </div>
                                </div>
                                <div class="r-inputs-data" >
                                    <div>
                                        <label for="filtro_documento">Documento:</label>
                                        <input type="text" id="filtro_documento" name="filtro_nf" class="form-control" value="<?= $get_filtro_nf; ?>" placeholder="Documento" style="border-radius: 0;">
                                    </div>
                                    <div>
                                        <label for="filtro_descricao">Descricao:</label>
                                        <input type="text" id="filtro_descricao" name="filtro_descricao" class="form-control" value="<?= $get_filtro_descricao; ?>" placeholder="Descricao" style="border-radius: 0;">
                                    </div>
                                    <!-- Tipo de pagamento -->
                                    <div>
                                        <label for="forma_pagamento">Pagamento:</label>
                                        <select class="form-control" name="forma_pagamento" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-top-right-radius: 0.25em; border-bottom-right-radius: 0.25em;">
                                            <option value="">Selecione</option>
                                            <?php foreach (TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                                                <option value="<?= $pagamento->id ?>" <?php if ($get_filtro_pagamento == $pagamento->id) { ?> selected <?php } ?>>
                                                                        <?= $pagamento->nome ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-pagamento-text inputs-pagamento-select input-select-geral" id="inputs-select">
                                <div class="r-inputs-data" style="width:100%;">
                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                        <label for="forma_pagamento">Cliente / Fornecedor:</label>
                                        <select class="form-control" name="filtro_cadastro">
                                            <option value="">Selecione</option>
                                            <?php foreach (Cadastro::read(null, null, $_SESSION['usuario']->id_empresa) as $cadastro) { ?>
                                                <option value="<?= $cadastro->id_cadastro ?>" <?php if ($get_filtro_cadastro == $cadastro->id_cadastro) { ?> selected <?php } ?>>
                                                                <?= $cadastro->nom_fant ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                        <label for="centro-custos-filtro">Centro de custos:</label>
                                        <select class="form-control" name="filtro_custo" id="custo-filtro">
                                            <option value="">Selecione</option>
                                            <?php  
                                            $centro_custos = CentroCustos::read(null, $_SESSION['usuario']->id_empresa);
                                            foreach ($centro_custos as $custo) { ?>
                                                <option value="<?= $custo->id ?>" <?php if ($get_filtro_custo == $custo->id) { ?> selected <?php } ?>>
                                                    <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>        
                                </div>
                                <div class="r-inputs-data" style="width:100%;">
                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                        <label for="forma_pagamento">Titulo:</label>
                                        <select class="form-control" name="filtro_titulo" id="titulo-filtro" onchange="filtroSubtitulo(true)">
                                            <option value="">Selecione</option>
                                            <?php foreach (Con01::read(null, $_SESSION['usuario']->id_empresa, 'D') as $titulo) { ?>
                                                <option value="<?= $titulo->id ?>" <?php if ($get_filtro_titulo == $titulo->id) { ?> selected <?php } ?> >
                                                    <?= $titulo->nome ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                        <label for="subtitulo-filtro">Subtitulo:</label>
                                        <select class="form-control" name="filtro_subtitulo" id="subtitulo-filtro">
                                            <?php
                                                $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                foreach ($todosSubtitulos as $sub) { ?>
                                                    <option value="<?= $sub->id ?>" data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($get_filtro_subtitulo == $sub->id) { ?> selected <?php } ?>>
                                                        <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>             
                            </div>
                                            </div> 
                                        </div> 

                                        <div class="selects-pagamento" style="padding-top:3%;">
                                            <div style="display: flex; flex-direction: row;">
                                            <div style="width: 15%;">
                                                <h5 style="font-size: 75%;">Opção:</h5>
                                            </div>
                                            <!-- Primeira linha de radios (opção) -->
                                            <div class="radio-pagamento">
                                                
                                                <div>                    
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="todos">Todos</label>
                                                        <input class="form-check-input" type="radio" id="todos"
                                                            name="opcao_filtro" value="" <?php if ($get_filtro_opcao == '' || empty($get_filtro_opcao)) { ?> checked <?php } ?>>
                                                        
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="abertos">Abertos</label>
                                                        <input class="form-check-input" type="radio" id="abertos"
                                                            name="opcao_filtro" value="abertos" <?php if ($get_filtro_opcao == 'abertos') { ?> checked <?php } ?>
                                                            value="abertos">
                                                        
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-check">
                                                        <label class="form-check-label" style="font-size: 90%;" for="quitados">Quitados</label>
                                                        <input class="form-check-input" type="radio" id="quitados"
                                                            name="opcao_filtro" value="quitados" <?php if ($get_filtro_opcao == 'quitados') { ?> checked <?php } ?>
                                                            value="quitados">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                

                                            <!-- Segunda linha de radios (filtro por) -->
                                        <div style="display: flex; flex-direction: row;">                      
                                                <div style="width: 15%;">
                                                    <h5 style="font-size: 75%;">Filtro:</h5>
                                                </div>
                                            <div class="radio-pagamento">
                                                
                                                <div >                        
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="lancamento">Lançamento</label>
                                                        <input class="form-check-input" type="radio" id="lancamento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'lancamento' || empty($get_filtro_por)) { ?> checked <?php } ?>
                                                            value="lancamento">
                                                        
                                                    </div>
                                                </div>
                                                <div >
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="vencimento">Vencimento</label>
                                                        <input class="form-check-input" type="radio" id="vencimento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'vencimento') { ?>
                                                                checked <?php } ?> value="vencimento">
                                                        
                                                    </div>
                                                </div>
                                                <div >                    
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="pagamento">Pagamento</label>
                                                        <input class="form-check-input" type="radio" id="pagamento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'pagamento') { ?>
                                                                checked <?php } ?> value="pagamento">
                                                        
                                                    </div>
                                                </div>
                   </div>
                </div>  

                </div> 
                <div class="btn-filtro">
                    <button type="submit" class="btn btn-primary"style="background-color: #5856d6;">Filtrar</button>
                    <a href="pagar.php" class="btn btn-secondary">Limpar</a>
                </div>
               </div>
            </div>
        </form>
    </div>              
</div>