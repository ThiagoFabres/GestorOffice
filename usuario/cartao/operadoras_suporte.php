<?php 
$operadoras = [
    'stone' => [
        'xlsx' =>[
            'excluded_columns' => [
                'F', 'J', 'K', 'L', 'M', 'N', 'O'
            ],
            'start_row' => 2,
            'start_end_columns' => ['start' => 'C', 'end' => 'P'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 1,
                'tipo' => 2,
                'parcela' => 3,
                'valor_b' => 4,
                'valor_l' => 5,
                'estado' => 6
            ],
            'suporte_data' => 'hora',
        ],
    ],


    'getnet' => [
        'xlsx' => [
            'excluded_columns' => [
                'E', 'J', 'K', 'L', 'M', 'N', 'O', 'Q'
            ],
            'start_row' => 9,
            'start_end_columns' => ['start' => 'D', 'end' => 'R'],
            'organizador' => [
                'data' => 2,
                'bandeira' => 0,
                'tipo' => 1,
                'parcela' => 4,
                'valor_b' => 5,
                'valor_l' => 6,
                'estado' => 3
            ],
            'suporte_data' => 'hora',
        ],
        'xls' => [
            'excluded_columns' => [
                'E', 'J', 'K', 'L', 'M', 'N', 'O', 'Q'
            ],
            'start_row' => 2,
            'start_end_columns' => ['start' => 'D', 'end' => 'R'],
            'organizador' => [
                'data' => 2,
                'bandeira' => 0,
                'tipo' => 1,
                'parcela' => 4,
                'valor_b' => 5,
                'valor_l' => 6,
                'estado' => 3
            ],
            'suporte_data' => 'hora',
        ]

    ],

    
    'rede' => [
        'xlsx' =>[
            'excluded_columns' => [
                'B', 'E', 'G', 'H', 'K', 'L', 'M', 'N', 'O', 'P'
            ],
            'start_row' => 3,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'Q'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 5,
                'tipo' => 3,
                'parcela' => 4,
                'valor_b' => 2,
                'valor_l' => 6,
                'estado' => 1
            ],
            'suporte_data' => false,
        ],
    ],


    'sicredi' => [
        'xlsx' =>[
            'excluded_columns' => [
                'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I','K', 'L'
            ],
            'start_row' => 6,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'Q'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 3,
                'tipo' => 1,
                'parcela' => 2,
                'valor_b' => 5,
                'valor_l' => 6,
                'estado' => 4
            ],
            'suporte_data' => 'formatado',
        ],
    ],
    'fazpay' => [
        'xlsx' => [
            'excluded_columns' => [
                'F'
            ],
            'start_row' => 2,
            'start_end_columns' => [ 'start' => 'B', 'end' => 'H'],
            'organizador' => [
                'data' => 1,
                'bandeira' => 2,
                'tipo' => 3,
                'parcela' => 3,
                'valor_b' => 4,
                'valor_l' => 5,
                'estado' => 0 
            ],
            'suporte_data' => 'hora'
        ],
    ],
    
    'infinite pay' => [
        'csv' => [
            true
        ]
    ]
]
?>