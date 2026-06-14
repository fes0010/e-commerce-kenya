<?php

namespace Webkul\Payment\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaynectaHelper
{
    /**
     * Get API credentials from config/env
     */
    protected static function getHeaders(): array
    {
        return [
            'X-API-Key' => config('services.paynecta.api_key'),
            'X-User-Email' => config('services.paynecta.user_email'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Format phone number to 254XXXXXXXXX Safaricom format
     */
    public static function formatMobileNumber(string $mobileNumber): string
    {
        // Remove non-digit characters
        $number = preg_replace('/\D/', '', $mobileNumber);

        // Convert 07XX or 01XX to 2547XX or 2541XX
        if (preg_match('/^0([17]\d{8})$/', $number, $matches)) {
            return '254'.$matches[1];
        }

        // If already starts with 254 and is correct length
        if (preg_match('/^254([17]\d{8})$/', $number)) {
            return $number;
        }

        // Default prefix fallback
        if (str_starts_with($number, '7') || str_starts_with($number, '1')) {
            return '254'.$number;
        }

        return $number;
    }

    /**
     * Initialize M-Pesa STK Push payment
     */
    public static function initializePayment(string $mobileNumber, float|int $amount): array
    {
        $code = config('services.paynecta.code');
        $formattedPhone = self::formatMobileNumber($mobileNumber);

        Log::info('Initializing Paynecta Payment', [
            'code' => $code,
            'mobile_number' => $formattedPhone,
            'amount' => $amount,
        ]);

        try {
            $response = Http::withHeaders(self::getHeaders())
                ->post('https://paynecta.co.ke/api/v1/payment/initialize', [
                    'code' => $code,
                    'mobile_number' => $formattedPhone,
                    'amount' => (int) $amount,
                ]);

            Log::info('Paynecta Initialize Response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return $response->json() ?? ['success' => false, 'message' => 'Invalid server response'];
        } catch (\Exception $e) {
            Log::error('Paynecta Initialize Exception: '.$e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Query transaction payment status
     */
    public static function queryStatus(string $transactionReference): array
    {
        try {
            $response = Http::withHeaders(self::getHeaders())
                ->get('https://paynecta.co.ke/api/v1/payment/status', [
                    'transaction_reference' => $transactionReference,
                ]);

            return $response->json() ?? ['success' => false, 'message' => 'Invalid server response'];
        } catch (\Exception $e) {
            Log::error('Paynecta Query Exception: '.$e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
