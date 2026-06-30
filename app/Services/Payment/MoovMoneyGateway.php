<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Driver Moov Money.
 *
 * L'API officielle Moov Money diffère selon le pays/partenaire ; cette
 * classe est un squelette prêt à brancher dès réception des identifiants
 * marchands (MOOV_MONEY_API_KEY, MOOV_MONEY_MERCHANT_ID dans .env).
 * En attendant, fonctionne en mode mock (cf. AbstractGateway).
 */
class MoovMoneyGateway extends AbstractGateway
{
    public function methodKey(): string
    {
        return 'moov_money';
    }

    protected function initiateLive(Order $order, array $options): array
    {
        $config = config('payment.methods.moov_money');
        $reference = (string) Str::uuid();

        $response = Http::withToken($config['api_key'])
            ->post('https://api.moov-africa.bj/payment/v1/request', [
                'merchant_id' => $config['merchant_id'],
                'reference' => $reference,
                'amount' => (float) $order->total,
                'currency' => 'XOF',
                'phone' => preg_replace('/\D/', '', $options['phone'] ?? $order->customer_phone),
                'description' => "Commande {$order->order_number}",
            ]);

        return [
            'provider_reference' => $reference,
            'status' => $response->successful() ? 'pending' : 'failed',
            'http_status' => $response->status(),
            'raw' => $response->json(),
        ];
    }

    protected function checkStatusLive(Payment $payment): array
    {
        $config = config('payment.methods.moov_money');

        $response = Http::withToken($config['api_key'])
            ->get("https://api.moov-africa.bj/payment/v1/status/{$payment->provider_reference}");

        $data = $response->json();
        $statusMap = ['SUCCESS' => 'succeeded', 'FAILED' => 'failed', 'PENDING' => 'pending'];

        return [
            'status' => $statusMap[$data['status'] ?? ''] ?? 'pending',
            'raw' => $data,
        ];
    }
}
