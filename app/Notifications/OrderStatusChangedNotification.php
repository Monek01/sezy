<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    private static array $labels = [
        'pending'   => 'En attente',
        'confirmed' => 'Confirmée',
        'preparing' => 'En préparation',
        'shipped'   => 'Expédiée',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
        'refunded'  => 'Remboursée',
    ];

    public function __construct(public Order $order, public string $newStatus) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $label = self::$labels[$this->newStatus] ?? $this->newStatus;

        $message = (new MailMessage)
            ->subject("Commande {$this->order->order_number} — {$label}")
            ->greeting("Bonjour {$notifiable->first_name} !")
            ->line("Le statut de votre commande **{$this->order->order_number}** a été mis à jour.");

        match ($this->newStatus) {
            'confirmed' => $message->line('✅ Votre commande a été **confirmée**. Nous préparons vos articles.'),
            'preparing' => $message->line('📦 Votre commande est **en cours de préparation**.'),
            'shipped'   => $message->line('🚚 Votre commande a été **expédiée** et est en route vers vous !'),
            'delivered' => $message->line('🎉 Votre commande a été **livrée**. Nous espérons que vous êtes satisfait(e) !'),
            'cancelled' => $message->line('❌ Votre commande a été **annulée**. Contactez-nous pour plus d\'informations.'),
            'refunded'  => $message->line('💸 Votre remboursement est en cours de traitement.'),
            default     => $message->line("Nouveau statut : **{$label}**"),
        };

        return $message
            ->action('Voir ma commande', url('/mes-commandes/' . $this->order->id))
            ->salutation('L\'équipe SEZY — agencesezy@gmail.com');
    }
}
