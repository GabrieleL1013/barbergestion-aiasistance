<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class NewPaymentNotification extends Notification
{
    use Queueable;

    public $payment;

    public function __construct(Payment $payment)
    {
        // Cargamos la relación del admin para saber quién le envió el pago (ej: Gabriele)
        $this->payment = $payment->load('admin');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'       => 'Nuevo Pago Pendiente',
            'admin_name'  => $this->payment->admin->name,
            'total'       => $this->payment->amount,
            'payment_id'  => $this->payment->id,
            // Mensaje amigable para el frontend
            'description' => "{$this->payment->admin->name} {$this->payment->admin->last_name} ha generado tu pago por $ {$this->payment->amount}. Por favor, revísalo y acéptalo.",
        ];
    }
}