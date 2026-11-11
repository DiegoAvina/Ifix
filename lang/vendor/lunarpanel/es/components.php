<?php

/*
 * Ajustes a la traducción de Lunar para el panel de IFIX. Laravel combina
 * este archivo con el original del paquete: solo se escriben las claves
 * que cambian.
 */
return [
    'activity-log' => [
        'action' => [
            'add-comment' => 'Agregar comentario',
        ],
        'partials' => [
            'orders' => [
                'order_created' => 'Pedido creado',
                // Mercado Pago no envía los últimos dígitos de la tarjeta.
                'capture' => 'Pago de :amount registrado',
                'authorized' => 'Pago de :amount autorizado',
                'refund' => 'Reembolso de :amount',
            ],
            // Lunar usa el nombre interno del modelo ("Order", "Product").
            'update' => [
                'updated' => 'Cambios guardados',
            ],
            'create' => [
                'created' => 'Registro creado',
            ],
        ],
    ],
];
