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
    ],
    'ton' => [
        'xlsx' => [
            'start_row' => 2,
            'start_end_columns' => ['start' => 'A', 'end' => 'F'],
            'excluded_columns' => ['C', 'D', 'E'],
            'organizador' => [
                'data' => 0,
                'descricao' => 2,
                'valor' => 1,
            ],
        'suporte_data' => 'formatada(d-m-y)',
        'suporte_numero' => 'formatado(1.000,00)'
        ]
    ],
    'pagbank' => [
        'xlsx' => [
            'start_row' => 10,
            'start_end_columns' => ['start' => 'A', 'end' => 'H'],
            'excluded_columns' => ['C'],
            'organizador' => [
                'data' => 0,
                'descricao' => 1,
                'valor' => 2,
                'valor_saida' => 3,
            ],
        'suporte_data' => 'formatada(d-m-y)',
        
        ]
    ]
]

?>