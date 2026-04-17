<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * 1. OBTENER NOTIFICACIONES (GET)
     * Trae las notificaciones del usuario logueado (Admin, Manager o Barbero)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Laravel mágicamente busca donde notifiable_id sea el ID de este usuario
        return response()->json([
            // Para pintar el numerito rojo en la campana del frontend
            'unread_count'  => $user->unreadNotifications->count(), 
            
            // Traemos todas paginadas para que no colapse si hay miles
            'notifications' => $user->notifications()->paginate(15) 
        ], 200);
    }

    /**
     * 2. MARCAR UNA NOTIFICACIÓN COMO LEÍDA (PATCH)
     * Cuando el usuario hace clic en una notificación específica.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        // Buscamos la notificación específica DENTRO de las notificaciones de este usuario
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notificación no encontrada.'], 404);
        }

        // Si ya está leída, no hacemos nada
        if ($notification->read_at) {
            return response()->json(['message' => 'La notificación ya estaba leída.']);
        }

        $notification->markAsRead(); // Laravel le pone la fecha y hora actual en 'read_at'

        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'unread_count' => $user->unreadNotifications->count() // Devolvemos el nuevo total para actualizar el frontend
        ], 200);
    }

    /**
     * 3. MARCAR TODAS COMO LEÍDAS (POST)
     * El clásico botón de "Marcar todo como leído"
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        // Marca todas las que tienen read_at en NULL de un solo golpe
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Todas las notificaciones han sido marcadas como leídas.',
            'unread_count' => 0
        ], 200);
    }
}