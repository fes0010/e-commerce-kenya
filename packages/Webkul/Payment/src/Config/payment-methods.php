<?php

use Webkul\Payment\Payment\CashOnDelivery;
use Webkul\Payment\Payment\MoneyTransfer;
use Webkul\Payment\Payment\Mpesa;

return [
    'mpesa' => [
        'class' => Mpesa::class,
        'code' => 'mpesa',
        'title' => 'M-Pesa',
        'description' => 'Pay via M-Pesa',
        'active' => true,
        'sort' => 1,
    ],

    // 'cashondelivery' => [
    //     'class' => CashOnDelivery::class,
    //     'code' => 'cashondelivery',
    //     'title' => 'Cash On Delivery',
    //     'description' => 'Cash On Delivery',
    //     'active' => true,
    //     'generate_invoice' => false,
    //     'sort' => 7,
    // ],

    // 'moneytransfer' => [
    //     'class' => MoneyTransfer::class,
    //     'code' => 'moneytransfer',
    //     'title' => 'Money Transfer',
    //     'description' => 'Money Transfer',
    //     'active' => true,
    //     'generate_invoice' => false,
    //     'sort' => 8,
    // ],
];
