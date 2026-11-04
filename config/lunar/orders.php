<?php

use Lunar\Base\OrderReferenceGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Order Reference Format
    |--------------------------------------------------------------------------
    |
    | Specify the format for the order reference generator to use.
    |
    */
    'reference_format' => [
        /**
         * Optional prefix for the order reference
         */
        'prefix' => null,

        /**
         * STR_PAD_LEFT: 00001965
         * STR_PAD_RIGHT: 19650000
         * STR_PAD_BOTH: 00196500
         */
        'padding_direction' => STR_PAD_LEFT,

        /**
         * 00001965
         * AAAA1965
         */
        'padding_character' => '0',

        /**
         * If the length specified below is smaller than the length
         * of the Order ID, then no padding will take place.
         */
        'length' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Reference Generator
    |--------------------------------------------------------------------------
    |
    | Here you can specify how you want your order references to be generated
    | when you create an order from a cart.
    |
    */
    'reference_generator' => OrderReferenceGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Draft Status
    |--------------------------------------------------------------------------
    |
    | When a draft order is created from a cart, we need an initial status for
    | the order that's created. Define that here, it can be anything that would
    | make sense for the store you're building.
    |
    */
    'draft_status' => 'awaiting-payment',

    'statuses' => [

        'awaiting-payment' => [
            'label' => 'Esperando pago',
            'color' => '#848a8c',
            'mailers' => [App\Mail\Orders\OrderAwaitingPayment::class],
            'notifications' => [],
            'favourite' => true,
        ],

        'payment-offline' => [
            'label' => 'Pago por confirmar',
            'color' => '#0A81D7',
            'mailers' => [App\Mail\Orders\OrderPaymentOffline::class],
            'notifications' => [],
            'favourite' => true,
        ],

        'payment-received' => [
            'label' => 'Pago recibido',
            'color' => '#6a67ce',
            'mailers' => [App\Mail\Orders\OrderPaymentReceived::class],
            'notifications' => [],
            'favourite' => true,
        ],

        // Pagado pero sin stock al confirmarse el pago: surtir o reembolsar.
        'payment-review' => [
            'label' => 'Revisión de pago',
            'color' => '#d97706',
            'mailers' => [],
            'notifications' => [],
            'favourite' => true,
        ],

        'dispatched' => [
            'label' => 'Enviado',
            'mailers' => [App\Mail\Orders\OrderDispatched::class],
            'notifications' => [],
            'favourite' => true,
        ],

        // Sin pagar dentro del plazo (ver orders:expire-unpaid).
        'cancelled' => [
            'label' => 'Cancelado',
            'color' => '#dc2626',
            'mailers' => [],
            'notifications' => [],
            'favourite' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Order Pipelines
    |--------------------------------------------------------------------------
    |
    | Define which pipelines should be run throughout an order's lifecycle.
    | The default ones provided should suit most needs, however you are
    | free to add your own as you see fit.
    |
    | Each pipeline class will be run from top to bottom.
    |
    */
    'pipelines' => [
        'creation' => [
            Lunar\Pipelines\Order\Creation\FillOrderFromCart::class,
            Lunar\Pipelines\Order\Creation\CreateOrderLines::class,
            Lunar\Pipelines\Order\Creation\CreateOrderAddresses::class,
            Lunar\Pipelines\Order\Creation\CreateShippingLine::class,
            Lunar\Pipelines\Order\Creation\CleanUpOrderLines::class,
            Lunar\Pipelines\Order\Creation\MapDiscountBreakdown::class,
        ],
    ],

];
