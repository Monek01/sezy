<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    abstract public function methodKey(): string;

    /**
     * En mode "live", chaque sous-classe implémente l'appel réel à l'API
     * de l'opérateur (MTN MoMo Collection API, Moov, Wave Checkout, PayDunya).
     * Tant que les identifiants marchands ne sont pas configurés dans .env,
     * le système reste en mode "mock" et ne fait aucun appel réseau.
     */
    abstract protected function initiateLive(Order $order, array $options): array;

    abstract protected function checkStatusLive(Payment $payment): array;

    public function initiate(Order $order, array $options = []): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $this->methodKey(),
            'amount' => $order->total,
            'currency' => 'XOF',
            'payer_phone' => $options['phone'] ?? $order->customer_phone,
            'status' => 'initiated',
        ]);

        if ($this->isMockMode()) {
            return $this->initiateMock($payment);
        }

        $result = $this->initiateLive($order, $options);

        $payment->update([
            'provider_reference' => $result['provider_reference'] ?? null,
            'status' => $result['status'] ?? 'pending',
            'gateway_payload' => $result,
        ]);

        return $payment;
    }

    public function checkStatus(Payment $payment): Payment
    {
        if ($this->isMockMode()) {
            return $this->checkStatusMock($payment);
        }

        $result = $this->checkStatusLive($payment);

        $payment->update([
            'status' => $result['status'] ?? $payment->status,
            'gateway_payload' => $result,
            'paid_at' => ($result['status'] ?? null) === 'succeeded' ? now() : $payment->paid_at,
        ]);

        return $payment;
    }

    protected function isMockMode(): bool
    {
        return config('payment.mode', 'mock') === 'mock';
    }

    /**
     * Simulation locale : le paiement passe immédiatement en "pending"
     * (en attente de confirmation opérateur), comme une vraie demande
     * Mobile Money (l'utilisateur doit valider sur son téléphone).
     * Le statut "succeeded" est obtenu ensuite via checkStatus() après
     * le délai configuré (config('payment.mock_delay_seconds')).
     */
    protected function initiateMock(Payment $payment): Payment
    {
        $payment->update([
            'provider_reference' => 'MOCK-'.strtoupper(Str::random(10)),
            'status' => 'pending',
            'gateway_payload' => [
                'mode' => 'mock',
                'message' => 'Paiement simulé initié. Statut "succeeded" après le délai configuré.',
                'initiated_at' => now()->toIso8601String(),
            ],
        ]);

        return $payment;
    }

    protected function checkStatusMock(Payment $payment): Payment
    {
        if ($payment->status === 'succeeded') {
            return $payment;
        }

        $initiatedAt = $payment->created_at;
        $delay = (int) config('payment.mock_delay_seconds', 5);

        if ($initiatedAt && $initiatedAt->addSeconds($delay)->isPast()) {
            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);
        }

        return $payment->fresh();
    }
}
