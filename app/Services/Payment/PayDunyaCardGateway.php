<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

/**
 * Driver carte bancaire (Visa/Mastercard) via l'agrégateur PayDunya,
 * qui gère aussi MTN/Moov/Wave au Bénin si on préfère un point d'entrée
 * unique plutôt que les API directes des opérateurs.
 * Documentation : https://paydunya.com/developers
 */
class PayDunyaCardGateway extends AbstractGateway
{
    public function methodKey(): string
    {
        return 'card';
    }

    protected function baseUrl(): string
    {
        $mode = config('payment.methods.card.paydunya_mode', 'test');

        return $mode === 'live'
            ? 'https://app.paydunya.com/api/v1'
            : 'https://app.paydunya.com/sandbox-api/v1';
    }

    protected function headers(): array
    {
        $config = config('payment.methods.card');

        return [
            'PAYDUNYA-MASTER-KEY' => $config['master_key'],
            'PAYDUNYA-PRIVATE-KEY' => $config['private_key'],
            'PAYDUNYA-PUBLIC-KEY' => $config['public_key'],
            'PAYDUNYA-TOKEN' => $config['token'],
            'Content-Type' => 'application/json',
        ];
    }

    protected function initiateLive(Order $order, array $options): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl().'/checkout-invoice/create', [
                'invoice' => [
                    'total_amount' => (float) $order->total,
                    'description' => "Commande SEZY {$order->order_number}",
                ],
                'store' => ['name' => 'SEZY'],
                'actions' => [
                    'callback_url' => route('checkout.payment.webhook'),
                    'return_url' => route('checkout.payment.success', $order),
                    'cancel_url' => route('checkout.payment.failed', $order),
                ],
                'custom_data' => ['order_number' => $order->order_number],
            ]);

        $data = $response->json();

        return [
            'provider_reference' => $data['token'] ?? null,
            'status' => ($data['response_code'] ?? null) === '00' ? 'pending' : 'failed',
            'checkout_url' => $data['response_text'] ?? null,
            'raw' => $data,
        ];
    }

    protected function checkStatusLive(Payment $payment): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl().'/checkout-invoice/confirm/'.$payment->provider_reference);

        $data = $response->json();
        $status = ($data['status'] ?? '') === 'completed' ? 'succeeded' : 'pending';

        return ['status' => $status, 'raw' => $data];
    }
}
