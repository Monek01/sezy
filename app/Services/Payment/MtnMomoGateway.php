<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Driver MTN Mobile Money Bénin (Collection API).
 *
 * Documentation officielle : https://momodeveloper.mtn.com
 * Tant que MTN_MOMO_* n'est pas renseigné dans .env, le système
 * fonctionne en mode mock (voir AbstractGateway::isMockMode()).
 *
 * Pour passer en production :
 *   1. Créer un compte sur momodeveloper.mtn.com, souscrire au produit
 *      "Collection", récupérer la Subscription Key.
 *   2. Générer un API User + API Key (sandbox puis production).
 *   3. Renseigner MTN_MOMO_SUBSCRIPTION_KEY, MTN_MOMO_API_USER,
 *      MTN_MOMO_API_KEY, MTN_MOMO_TARGET_ENV=production dans .env.
 *   4. PAYMENT_MODE=live dans .env.
 */
class MtnMomoGateway extends AbstractGateway
{
    public function methodKey(): string
    {
        return 'mtn_momo';
    }

    protected function baseUrl(): string
    {
        return config('payment.methods.mtn_momo.target_environment') === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    protected function initiateLive(Order $order, array $options): array
    {
        $referenceId = (string) Str::uuid();
        $config = config('payment.methods.mtn_momo');

        $token = $this->getAccessToken($config);

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $config['subscription_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl().'/collection/v1_0/requesttopay', [
                'amount' => (string) $order->total,
                'currency' => 'EUR', // 'XOF' uniquement disponible en production MTN
                'externalId' => $order->order_number,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => preg_replace('/\D/', '', $options['phone'] ?? $order->customer_phone),
                ],
                'payerMessage' => "Paiement commande {$order->order_number}",
                'payeeNote' => 'SEZY',
            ]);

        return [
            'provider_reference' => $referenceId,
            'status' => $response->successful() ? 'pending' : 'failed',
            'http_status' => $response->status(),
            'raw' => $response->json(),
        ];
    }

    protected function checkStatusLive(Payment $payment): array
    {
        $config = config('payment.methods.mtn_momo');
        $token = $this->getAccessToken($config);

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Target-Environment' => $config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $config['subscription_key'],
            ])
            ->get($this->baseUrl()."/collection/v1_0/requesttopay/{$payment->provider_reference}");

        $data = $response->json();
        $statusMap = [
            'SUCCESSFUL' => 'succeeded',
            'FAILED' => 'failed',
            'PENDING' => 'pending',
        ];

        return [
            'status' => $statusMap[$data['status'] ?? ''] ?? 'pending',
            'raw' => $data,
        ];
    }

    private function getAccessToken(array $config): string
    {
        // Authentification OAuth2 Basic -> Bearer token (cache recommandé en prod)
        $response = Http::withBasicAuth($config['api_user'], $config['api_key'])
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $config['subscription_key']])
            ->post($this->baseUrl().'/collection/token/');

        return $response->json('access_token', '');
    }
}
