<?php

// Textos que la traducción de Lunar no incluye (se combinan con el original).
return [
    'table' => [
        'created_at' => ['label' => 'Creado'],
        'coupon' => ['label' => 'Cupón'],
    ],
    'relationmanagers' => [
        'customers' => [
            'title' => 'Clientes',
            'description' => 'Elige a qué clientes se limita este descuento.',
            'actions' => ['attach' => ['label' => 'Agregar cliente']],
            'table' => ['name' => ['label' => 'Nombre']],
        ],
        'collection_conditions' => [
            'title' => 'Condiciones por colección',
            'description' => 'Elige las colecciones que debe incluir el pedido para aplicar el descuento.',
            'actions' => ['attach' => ['label' => 'Agregar condición']],
            'table' => ['name' => ['label' => 'Nombre']],
        ],
    ],
];
