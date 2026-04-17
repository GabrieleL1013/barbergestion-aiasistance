<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Client;

class ClientCreatedNotification extends Notification
{
    use Queueable;

    public $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Obtenemos el nombre del empleado que hizo la petición
        $creatorName = auth()->user() ? auth()->user()->name : 'El sistema';

        return [
            'title'       => 'Nuevo Cliente Registrado',
            'description' => "{$creatorName} ha registrado a un nuevo cliente en el directorio: {$this->client->name} {$this->client->last_name}.",
            'client_id'   => $this->client->id,
        ];
    }
}