<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ClientUpdatedNotification;
use App\Notifications\ClientCreatedNotification;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * OBTENER TODOS LOS CLIENTES (GET)
     */
    public function index(Request $request)
    {
        // Paginación dinámica y segura
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
        $clients = Client::paginate($perPage);

        return response()->json([
            'message' => 'Directorio de clientes obtenido correctamente',
            'clients' => $clients
        ], 200);
    }

    /**
     * REGISTRAR UN NUEVO CLIENTE (POST)
     * TODOS los usuarios autenticados (incluyendo barberos) pueden hacer esto.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos que envía el frontend
        $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|string|email|max:255',
            'notes'     => 'nullable|string',
        ]);

        // 2. Crear el cliente
        $client = Client::create($request->all());

        // --- LÓGICA DE NOTIFICACIONES ---
        $adminsAndManagers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();
        Notification::send($adminsAndManagers, new ClientCreatedNotification($client));

        return response()->json([
            'message' => 'Cliente registrado exitosamente en el directorio.',
            'client'  => $client
        ], 201);
    }

    /**
     * ACTUALIZAR CLIENTE (PUT/PATCH)
     * Cualquier empleado puede actualizar a cualquier cliente.
     * Mantiene un historial en las notas.
     */
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }

        // Validamos la data que llega
        $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'ci'        => 'nullable|string|max:10|min:10', // Validamos que la cédula tenga exactamente 10 caracteres si se proporciona
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|string|email|max:255',
            'notes'     => 'nullable|string', // El frontend envía la nueva nota aquí
        ]);

        // Obtenemos todos los datos validados listos para actualizar
        $dataToUpdate = $request->all();

        // Lógica de la Bitácora de Notas
        if ($request->filled('notes')) {
            $user = $request->user();
            // Obtenemos la fecha y hora actual, ej: "31/03/2026 15:30"
            $date = now()->format('d/m/Y H:i'); 
            
            // Construimos la nueva nota identificando quién la hizo y cuándo
            $newNote = "[Actualizado el {$date} por {$user->name}]:\n" . $request->notes;

            // Si el cliente ya tenía notas previas, le pegamos la nueva abajo
            if (!empty($client->notes)) {
                $dataToUpdate['notes'] = $client->notes . "\n\n" . $newNote;
            } else {
                // Si estaba vacío, simplemente ponemos la nota nueva
                $dataToUpdate['notes'] = $newNote;
            }
        }

        // Actualizamos al cliente en la base de datos
        $client->update($dataToUpdate);

        // --- LÓGICA DE NOTIFICACIONES --- (Reemplazo de la Fase 5)
        $adminsAndManagers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();
        Notification::send($adminsAndManagers, new ClientUpdatedNotification($client));

        return response()->json([
            'message' => 'Datos del cliente actualizados correctamente.',
            'client'  => $client
        ], 200);
    }

    /**
     * ELIMINAR CLIENTE (DELETE)
     * Estrictamente para Admin y Manager. (Usa SoftDeletes)
     */
    public function destroy(Request $request, $id)
    {
        $userRoleSlug = $request->user()->role->slug ?? '';

        if (!in_array($userRoleSlug, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Acceso denegado. Solo la administración puede eliminar clientes.'
            ], 403);
        }

        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente no encontrado o ya eliminado.'], 404);
        }

        $client->delete();

        return response()->json([
            'message' => 'Cliente eliminado del directorio exitosamente.'
        ], 200);
    }
}