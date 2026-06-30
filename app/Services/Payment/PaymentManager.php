<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    /**
     * Retourne le driver associé à une méthode de paiement
     * (mtn_momo, moov_money, wave, card).
     */
    public function driver(string $method): PaymentGatewayInterface
    {
        $config = config("payment.methods.{$method}");

        if (! $config || ! ($config['enabled'] ?? false)) {
            throw new InvalidArgumentException("Méthode de paiement inconnue ou désactivée : {$method}");
        }

        return app($config['driver']);
    }

    /** Liste des méthodes actives, pour affichage au checkout (§4.4). */
    public function availableMethods(): array
    {
        return collect(config('payment.methods'))
            ->filter(fn ($m) => $m['enabled'] ?? false)
            ->map(fn ($m, $key) => [
                'key' => $key,
                'label' => $m['label'],
                'logo' => $m['logo'],
            ])
            ->values()
            ->all();
    }
}
