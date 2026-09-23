<table class="" id="tabela-pdf-canceladas" >
    <thead>
        <tr class="tr-clientes-header">
            <th>DATA</th>
            <th>BANDEIRA</th>
            <th>TIPO</th>
            <th>STATUS</th>
            <th>VALOR</th>
            <th>COMPROVANTE</th>
        </tr>
    </thead>
    <tbody class="avoid-page-break">
        <?php
        $linhas = Cancelada::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            filtro_data_inicial: $get_filtro_data_inicial ?? null,
            filtro_data_final: $get_filtro_data_final ?? null,
            estado: $get_filtro_status ?? null,
        );
        if (!empty($linhas)) {
            $total_valor = 0;
            if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                $recebimentos_pagos = []; 
            ?>
            <?php foreach ($linhas as $linha) {
                $data = new DateTime($linha->data);
                $data = $data->format('d/m/Y');

                $valor_formatado = number_format($linha->valor, 2, ',', '.')
            ?>
                <div class="avoid-page-break">
                    <tr class="avoid-page-break">
                        <td><?= $data ?></td>
                        <td><?= $linha->bandeira ?></td>
                        <td><?= $linha->tipo ?></td>
                        <td><?= $linha->estado ?></td>
                        <td style="text-align: end; "><?= $valor_formatado ?></td>
                        <td><?= $linha->comprovante ?></td>
                    </tr>
                </div>
            <?php 
                $total_valor += $linha->valor;
            }?> 
            <tr id="tr-totais">
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: end; ">Total:</td>
                <td style="text-align: end; "><?= number_format($total_valor, 2 , ',', '.') ?></td>
                <td></td>

            </tr>
            <?php }  else { ?>
            <tr >
                <td>Nenhum Lançamento encontrado</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
                <?php } ?>
    </tbody>
</table>