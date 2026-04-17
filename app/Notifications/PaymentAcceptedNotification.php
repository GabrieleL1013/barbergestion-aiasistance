<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class PaymentAcceptedNotification extends Notification
{
    use Queueable;

    public $payment;

    public function __construct(Payment $payment)
    {
        // Cargamos la relación del empleado para saber quién aceptó el pago
        $this->payment = $payment->load('user');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'       => 'Pago Aceptado',
            'description' => "El barbero {$this->payment->user->name} ha aceptado y confirmado la recepción del pago de $ {$this->payment->amount}.",
            'payment_id'  => $this->payment->id,
        ];
    }
}