<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
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
            ->subject("Confirmation de commande {$this->order->order_number} — SEZY")
            ->greeting("Bonjour {$notifiable->first_name} !")
            ->line("Merci pour votre commande sur SEZY. Nous avons bien reçu votre commande **{$this->order->order_number}**.")
            ->line("**Montant total :** " . number_format($this->order->total, 0, ',', ' ') . " FCFA")
            ->line("**Mode de paiement :** " . ucfirst(str_replace('_', ' ', $this->order->payment_method)))
            ->action('Suivre ma commande', url('/mes-commandes/' . $this->order->id))
            ->line('Notre équipe traite votre commande. Vous recevrez une notification à chaque étape.')
            ->salutation('L\'équipe SEZY — agencesezy@gmail.com');
    }
}
