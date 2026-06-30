<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🛍️ Nouvelle commande {$this->order->order_number}")
            ->greeting('Nouvelle commande reçue !')
            ->line("**Commande :** {$this->order->order_number}")
            ->line("**Client :** {$this->order->customer_name}")
            ->line("**Montant :** " . number_format($this->order->total, 0, ',', ' ') . " FCFA")
            ->line("**Paiement :** " . ucfirst(str_replace('_', ' ', $this->order->payment_method)))
            ->action('Voir la commande', url('/admin/commandes/' . $this->order->id))
            ->salutation('SEZY Back-office');
    }
}
