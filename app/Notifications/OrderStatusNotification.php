<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status = 'created',
        public ?string $message = null,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $orderNumber = $this->order->order_number;
        $statusLabel = Str::title(str_replace('_', ' ', $this->status));
        $generic = "Order #{$orderNumber} status updated to {$statusLabel}.";

        $message = $this->message ?? match ($this->status) {
            'processing' => "Payment received for Order #{$orderNumber}. We're preparing it for shipment.",
            'paid' => "Payment confirmed for Order #{$orderNumber}. Thank you!",
            'shipped' => "Order #{$orderNumber} is on the way.",
            'cancelled' => "Order #{$orderNumber} has been cancelled.",
            default => $generic,
        };

        return [
            'type' => 'orders',
            'title' => 'Order update',
            'message' => $message,
            'order_number' => $orderNumber,
            'status' => $this->status,
            'url' => route('account.orders.show', $orderNumber),
        ];
    }
}
