<?php

namespace App\Notifications;

use App\Modules\Inventory\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public int $currentStock
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerte stock: ' . $this->product->name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le produit "' . $this->product->name . '" est en alerte de stock.')
            ->line('Reference: ' . $this->product->reference)
            ->line('Stock actuel: ' . $this->currentStock . ' unites')
            ->line('Seuil d\'alerte: ' . $this->product->stock_alert_threshold . ' unites')
            ->action('Voir le produit', route('inventory.products.show', $this->product))
            ->line('Une commande fournisseur devrait etre passee.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Alerte stock: ' . $this->product->name,
            'subtitle' => 'Stock: ' . $this->currentStock . ' / Seuil: ' . $this->product->stock_alert_threshold,
            'icon' => 'inventory_2',
            'color' => 'bg-red-500/20',
            'icon_color' => 'text-red-400',
            'url' => route('inventory.products.show', $this->product),
            'product_id' => $this->product->id,
        ];
    }
}
