<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\AdminNewOrderNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Notify client
        if ($order->user) {
            $order->user->notify(new OrderPlacedNotification($order));
        }

        // Notify admin
        $settings = Cache::get('sezy_settings', []);
        $adminEmail = $settings['admin_notif_email'] ?? config('mail.from.address');
        $notifNewOrder = $settings['notif_new_order'] ?? true;

        if ($notifNewOrder && $adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new AdminNewOrderNotification($order));
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->user) {
            $order->user->notify(new OrderStatusChangedNotification($order, $order->status));
        }
    }
}
