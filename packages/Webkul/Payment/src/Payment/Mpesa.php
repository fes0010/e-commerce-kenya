<?php

namespace Webkul\Payment\Payment;

class Mpesa extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'mpesa';

    /**
     * Get redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mpesa.redirect');
    }

    /**
     * Get payment method image.
     *
     * @return string
     */
    public function getImage()
    {
        return bagisto_asset('images/mpesa.png');
    }

    /**
     * Checks if payment method is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return true;
    }
}
