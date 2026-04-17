<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Amount;

class NewAmountNotification extends Notification
{
    use Queueable;

    public $amount;

    public function __construct(Amount $amount)
    {
        // Cargamos las relaciones para tener los nombres en la notificación
        $this->amount = $amount->load(['user', 'product']);
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Se guarda en la tabla 'notifications' que creaste
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nueva Venta Registrada',
            'barber_name' => $this->amount->user->name,
            'service' => $this->amount->product->name,
            'total' => $this->amount->amount,
            'amount_id' => $this->amount->id,
            'description' => "{$this->amount->user->name} registró: {$this->amount->product->name} por $ {$this->amount->amount}",
        ];
    }
}