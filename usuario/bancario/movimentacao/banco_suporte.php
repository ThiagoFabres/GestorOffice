<?php 
$bancos_suporte = [
    'mercadopago' => [
        'xlsx' => [
            'start_row' => 5,
            'start_end_columns' => ['start' => 'A', 'end' => 'D'],
            'excluded_columns' => ['C'],
            'organizador' => [
                'data' => 0,
                'descricao' => 1,
                'valor' => 2,
            ],
        'suporte_data' => 'formatada(d-m-y)',
        'suporte_numero' => 'formatado(1.000,00)'
        ]
    ]
]

?>