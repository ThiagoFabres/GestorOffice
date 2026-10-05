<table id="tabela-pdf">
    <?php
    $movimentacoes_pdf_vinculados = Ban02::read(
    id:null,
    id_empresa:$_SESSION['usuario']->id_empresa,
    filtro_data_inicial: $get_filtro_data_inicial  ?? null,
    filtro_data_final: $get_filtro_data_final ?? null,
    filtro_conciliado: $get_filtro_conciliado ?? null,
    filtro_titulo: $get_filtro_titulo ?? null,
    filtro_subtitulo: $get_filtro_subtitulo ?? null,
    filtro_conta: $get_filtro_conta ?? null,
    filtro_tipo: $get_filtro_tipo ?? null,
    filtro_descricao: $get_filtro_descricao ?? null,
    ordenar_por: $ordenar_por ?? null,
    direcao: $direcao ?? null,
    filtro_cadastro:$get_filtro_cadastro ?? null,
    read_vinculados:true,
); 
    ?>
    <thead>
        <tr class="tr-header">
            <th>Data</th>
            <th>Tipo</th>
            <th>Descrição</th>
            <th>Cliente / Fornecedor</th>
            <th style="text-align: right;">Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_valor = 0;

        if (!empty($movimentacoes_pdf_vinculados)) {
            foreach ($movimentacoes_pdf_vinculados as $movimentacao_vinculada) {
                $total_valor += $movimentacao_vinculada->valor;

                $data_lancamento = DateTime::createFromFormat('Y-m-d', $movimentacao_vinculada->data)->format('d/m/Y');

                $cadastro_nome = '';
                if ($movimentacao_vinculada->id_cadastro != null) {
                    $cadastro_nome = Cadastro::read($movimentacao_vinculada->id_cadastro)[0]->nom_fant ?? '';
                }

                $descricao = $movimentacao_vinculada->descricao_comp != ''
                    ? substr(substr($movimentacao_vinculada->descricao, 0, 60) . ' - ' . substr($movimentacao_vinculada->descricao_comp, 0, 60), 0, 100)
                    : $movimentacao_vinculada->descricao;
        ?>
            <tr>
                <td><?=$data_lancamento?></td>
                <td style="text-align: left;"><?=htmlspecialchars($movimentacao_vinculada->valor > 0 ? 'C' : 'D')?></td>
                <td style="text-align: left;"><?=htmlspecialchars($cadastro_nome)?></td>
                <td style="text-align: right;">R$ <?=number_format($movimentacao_vinculada->valor, 2, ',', '.')?></td>
            </tr>
        <?php
            }
        }
        ?>
    </tbody>
    <tfoot>
        <!-- Sem colspan: as 4 células ficam alinhadas com o cabeçalho (PDF e Excel) -->
        <tr id="tr-totais" style="font-weight: bold; background-color: #dcdcdc;">
            <td></td>
            <td></td>
            <td style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">R$ <?=number_format($total_valor, 2, ',', '.')?></td>
        </tr>
    </tfoot>
</table>