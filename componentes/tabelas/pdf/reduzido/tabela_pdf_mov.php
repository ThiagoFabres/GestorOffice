<table id="tabela-pdf">
    <thead>
        <tr class="tr-header">
            <th>Data</th>
            <th>Descrição</th>
            <th>Cliente / Fornecedor</th>
            <th style="text-align: right;">Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_valor = 0;

        if (!empty($movimentacoes_pdf)) {
            foreach ($movimentacoes_pdf as $movimentacao) {
                $total_valor += $movimentacao->valor;

                $link = $caminho . (empty($filtros) ? '?' : '&');
                $link .= 'acao=conciliar&id=' . $movimentacao->id;

                $data_lancamento = DateTime::createFromFormat('Y-m-d', $movimentacao->data)->format('d/m/Y');

                $cadastro_nome = '';
                if ($movimentacao->id_cadastro != null) {
                    $cadastro_nome = Cadastro::read($movimentacao->id_cadastro)[0]->nom_fant ?? '';
                }

                $descricao = $movimentacao->descricao_comp != ''
                    ? substr(substr($movimentacao->descricao, 0, 60) . ' - ' . substr($movimentacao->descricao_comp, 0, 60), 0, 100)
                    : $movimentacao->descricao;
        ?>
            <tr>
                <td onclick="window.location.href='<?=$link?>'"><?=$data_lancamento?></td>
                <td style="text-align: left;" onclick="window.location.href='<?=$link?>'"><?=htmlspecialchars($descricao)?></td>
                <td style="text-align: left;" onclick="window.location.href='<?=$link?>'"><?=htmlspecialchars($cadastro_nome)?></td>
                <td style="text-align: right;" onclick="window.location.href='<?=$link?>'">R$ <?=number_format($movimentacao->valor, 2, ',', '.')?></td>
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