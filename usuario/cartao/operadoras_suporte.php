<?php 
$operadoras_suporte = [
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
            'suporte_multi' => true
        ],
        'xlsx2' => [
            'excluded_columns' => [
                'E', 'F', 'G', 'H', 'I', 'J', 'O', 'Q'
            ],
            'start_row' => 9,
            'start_end_columns' => ['start' => 'D', 'end' => 'N'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 4,
                'tipo' => 4,
                'parcela' => 4,
                'valor_b' => 2,
                'valor_l' => 1,
                'estado' => 3
            ],
            'suporte_data' => 'hora',
            'suporte_pix' => true,
            'suporte_valor_taxa' => true
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
        'csv' => [
            'colunas' => [
                'data' =>'data da venda',
                'status' => 'status da venda',
                'valor_b' => 'valor da venda original',
                'valor_l' => 'valor líquido',
                'tipo' => 'modalidade',
                'bandeira' => 'bandeira',
                'parcela' => 'número de parcelas'
            ],
            'suporte_encoding' => false,
            'linha_inicial' => null,
            'suporte_status' => true,
            'suporte_parcela' => true,
            'suporte_data' => 'formatada',
            
            'separator' => ';',
            'encoding' => 'UTF-8',
        ]
    ],


    'sicredi' => [
        'xlsx' =>[
            'excluded_columns' => [
                'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'L', 'O', 'Q'
            ],
            'start_row' => 6,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'R'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 3,
                'tipo' => 1,
                'parcela' => 2,
                'valor_b' => 5,
                'valor_l' => 6,
                'estado' => 4
            ],
            'suporte_data' => 'formatada',
            'suporte_numero' => 'formatado',
        ],
        'xlsx2' =>[
            'excluded_columns' => [
                'B', 'F', 'H', 'J',
            ],
            'start_row' => 17,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'K'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 3,
                'tipo' => 1,
                'parcela' => 2,
                'valor_b' => 4,
                'valor_l' => 5,
                'estado' => 6
            ],
            'suporte_data' => 'hora',
            'suporte_numero' => 'formatado',
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
    
    'infinitepay' => [
        'csv' => [
            'colunas' => [
                'data' =>'Data e hora',
                'status' => 'Status',
                'valor_b' => 'Valor (R$)',
                'valor_l' => 'Líquido (R$)',
                'tipo' => 'Meio - Meio',
                'bandeira' => 'Meio - Bandeira',
                'parcela' => 'Meio - Parcelas'
            ],
            'suporte_encoding' => false,
            'linha_inicial' => null,
            'suporte_status' => true,
            'suporte_parcela' => true,
            'suporte_data' => 'hora',
            'separator' => ',',
            'encoding' => 'UTF-8',
        ]
    ],
    'safrapay' => [
        'csv' => [
            'colunas' => [
                'data' => 'DATA VENDA',
                'status' => null,
                'valor_b' => 'VALOR BRUTO',
                'valor_l' => 'VALOR LIQUIDO',
                'tipo' => 'MODALIDADE',
                'bandeira' => 'PRODUTO',
                'parcela' => 'PL'
            ],
            'suporte_encoding' => false,
            'linha_inicial' => null,
            'suporte_status' => false,
            'suporte_parcela' => true,
            'suporte_data' => 'formatada',
            'separator' => ';',
            'encoding' => 'UTF-8',
        ]
    ],
    'cielo' => [
        'csv' => [
            'colunas' => [
                'data' => 'Data da venda',
                'status' => 'Status da venda',
                'valor_b' => 'Valor bruto',
                'valor_l' => 'Valor líquido',
                'tipo' => 'Forma de pagamento',
                'bandeira' => 'Bandeira',
                'parcela' => null
            ],
            'suporte_encoding' => true,
            'linha_inicial' => 22,
            'suporte_status' => true,
            'suporte_parcela' => false,
            'suporte_data' => 'formatada',
            'separator' => ';',
            'encoding' => 'ISO-8859-1',
        ],
        'xlsx' => [
            'excluded_columns' => [
                'B', 'C', 'D', 'I'
            ],
            'start_row' => 11,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'K'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 3,
                'tipo' => 1,
                'parcela' => 2,
                'valor_b' => 4,
                'valor_l' => 5,
                'estado' => 6
            ],
            'suporte_data' => 'formatada',
            'suporte_numero' => 'formatado',
        ],
    ],
    'pagbank' => [
        'csv' => [
            'colunas' => [
                'data' => 'Data da Transação',
                'status' => 'Status',
                'valor_b' => 'Valor Bruto',
                'valor_l' => 'Valor Líquido',
                'tipo' => 'Forma de Pagamento',
                'bandeira' => 'Bandeira',
                'parcela' => 'Parcela'
            ],
            'separator' => ';',
            'suporte_parcela' => true,
            'linha_inicial' => null,
            'suporte_data' => 'hora',
            'suporte_numero' => 'formatado',
            'encoding' => 'UTF-8',
        ],
    ],
    'capim' => [
        'xlsx' => [
            'excluded_columns' => [
                'A', 'C', 'D', 'J'
            ],
            'start_row' => 6,
            'start_end_columns' => [ 'start' => 'A', 'end' => 'J'],
            'organizador' => [
                'data' => 0,
                'bandeira' => 1,
                'tipo' => 2,
                'parcela' => 3,
                'valor_b' => 4,
                'valor_l' => 5,
                'estado' => null
            ],
            'suporte_estado' => true,
            'suporte_data' => 'formatada(Y-m-d)',

        ],
    ],
]
?>