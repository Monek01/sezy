<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

/**
 * Driver Wave (Checkout API).
 * Documentation : https://docs.wave.com
 * Renseigner WAVE_API_KEY + WAVE_MERCHANT_ID dans .env puis PAYMENT_MODE=live.
 */
class WaveGateway extends AbstractGateway
{
    public function methodKey(): string
    {
        return 'wave';
    }

    protected function initiateLive(Order $order, array $options): array
    {
        $config = config('payment.methods.wave');

        $response = Http::withToken($config['api_key'])
            ->post('https://api.wave.com/v1/checkout/sessions', [
                'amount' => (string) $order->total,
                'currency' => 'XOF',
                'error_url' => route('checkout.payment.failed', $order),
                'success_url' => route('checkout.payment.success', $order),
                'client_reference' => $order->order_number,
            ]);

        $data = $response->json();

        return [
            'provider_reference' => $data['id'] ?? null,
            'status' => $response->successful() ? 'pending' : 'failed',
            'checkout_url' => $data['wave_launch_url'] ?? null,
            'raw' => $data,
        ];
    }

    protected function checkStatusLive(Payment $payment): array
    {
        $config = config('payment.methods.wave');

        $response = Http::withToken($config['api_key'])
            ->get("https://api.wave.com/v1/checkout/sessions/{$payment->provider_reference}");

        $data = $response->json();
        $statusMap = ['complete' => 'succeeded', 'expired' => 'failed', 'processing' => 'pending'];

        return [
            'status' => $statusMap[$data['payment_status'] ?? ''] ?? 'pending',
            'raw' => $data,
        ];
    }
}
