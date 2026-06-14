<?php

use Illuminate\Support\Facades\Route;
use Webkul\Shop\Http\Controllers\CartController;
use Webkul\Shop\Http\Controllers\OnepageController;

/**
 * Cart routes.
 */
Route::controller(CartController::class)->prefix('checkout/cart')->group(function () {
    Route::get('', 'index')->name('shop.checkout.cart.index');
});

Route::controller(OnepageController::class)->prefix('checkout/onepage')->group(function () {
    Route::get('', 'index')->name('shop.checkout.onepage.index');

    Route::get('success', 'success')->name('shop.checkout.onepage.success');

    Route::get('mpesa-redirect', 'mpesaRedirect')->name('mpesa.redirect');

    Route::get('mpesa-status/{order_id}', 'mpesaStatus')->name('mpesa.status');

    Route::get('mpesa-check-status/{order_id}', 'checkMpesaStatus')->name('mpesa.check_status');

    Route::post('mpesa-retry/{order_id}', 'mpesaRetry')->name('mpesa.retry');

    Route::get('mpesa-cancel/{order_id}', 'mpesaCancel')->name('mpesa.cancel');
});
