<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initie un paiement auprès de l'opérateur / agrégateur.
     * Retourne le Payment créé, avec son statut mis à jour.
     *
     * @param  array{phone?: string}  $options
     */
    public function initiate(Order $order, array $options = []): Payment;

    /**
     * Interroge le statut réel d'un paiement déjà initié (polling ou webhook).
     */
    public function checkStatus(Payment $payment): Payment;
}
