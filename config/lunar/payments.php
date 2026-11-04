<?php

return [

    'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),

    'types' => [
        'cash-in-hand' => [
            'driver' => 'offline',
            'authorized' => 'payment-offline',
        ],

        // Checkout Pro (Preferences API). El pago lo confirma el webhook o la
        // conciliación, que pasan el pedido a este estado (ver
        // App\PaymentTypes\MercadoPagoPayment).
        'mercadopago' => [
            'driver' => 'mercadopago',
            'authorized' => 'payment-received',
        ],
    ],

];
