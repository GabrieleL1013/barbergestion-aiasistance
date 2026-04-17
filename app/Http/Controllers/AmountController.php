<?php

namespace App\Http\Controllers;

use App\Models\Amount;
use App\Models\User;
use App\Notifications\NewAmountNotification;
use Illuminate\Http\Request;
use Notification;

class AmountController extends Controller
{
    /**
     * OBTENER INGRESOS (GET)
     * Admin/Manager ven todo. Barberos ven solo lo suyo.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));

        // Preparamos la consulta con las relaciones para que React tenga todos los nombres
        $query = Amount::with(['user', 'client', 'product']);

        // Si NO es jefe, filtramos para que solo vea sus propias ventas
        if (!in_array($user->role->slug, ['admin', 'manager'])) {
            $query->where('user_id', $user->id);
        }

        // Ordenamos por los más recientes primero
        $amounts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'message' => 'Lista de ingresos obtenida correctamente',
            'amounts' => $amounts
        ], 200);
    }

    /**
     * REGISTRAR UN INGRESO/VENTA (POST)
     * Todos pueden registrar, pero los barberos solo a su propio nombre.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $isAdminOrManager = in_array($user->role->slug, ['admin', 'manager']);

        $rules = [
            'client_id'      => 'nullable|exists:clients,id',
            'product_id'     => 'required|exists:products,id',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
            'photo'          => 'nullable|string',
        ];

        if ($isAdminOrManager) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validatedData = $request->validate($rules);

        if (!$isAdminOrManager) {
            $validatedData['user_id'] = $user->id;
        }

        $amount = Amount::create($validatedData);

        // --- LÓGICA DE NOTIFICACIONES ---
        // 1. Buscamos a los destinatarios
        $recipients = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();

        // 2. Enviamos la notificación (Laravel creará una fila en la tabla por cada destinatario)
        Notification::send($recipients, new NewAmountNotification($amount));

        return response()->json([
            'message' => 'Ingreso registrado exitosamente y notificado a la administración.',
            'amount'  => $amount->load(['user', 'client', 'product'])
        ], 201);
    }

    /**
     * ACTUALIZAR UN INGRESO (PUT/PATCH)
     * Solo permitimos editar detalles estéticos (notas, fotos).
     * Si quieren cambiar el monto o el barbero, el sistema les dirá 
     * que deben anular (borrar) y crear uno nuevo para dejar evidencia.
     */
    public function update(Request $request, $id)
    {
        $amount = Amount::find($id);
        if (!$amount) {
            return response()->json(['message' => 'Registro no encontrado.'], 404);
        }

        // Bloqueo total de campos sensibles
        if ($request->hasAny(['amount', 'user_id', 'client_id', 'product_id'])) {
            return response()->json([
                'message' => 'Por seguridad y auditoría, los montos y responsables no se pueden editar. Por favor, anula este registro y crea uno nuevo para dejar constancia del error.'
            ], 422); // 422 Unprocessable Entity
        }

        $validatedData = $request->validate([
            'payment_method' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
            'photo'          => 'nullable|string',
        ]);

        $amount->update($validatedData);

        return response()->json([
            'message' => 'Campos informativos actualizados.',
            'amount'  => $amount->load(['user', 'client', 'product'])
        ], 200);
    }

    /**
     * ELIMINAR / ANULAR UN INGRESO (DELETE)
     * Aquí es donde queda la "Evidencia" que mencionas.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $amount = Amount::find($id);

        if (!$amount) {
            return response()->json(['message' => 'El registro ya no existe o ya fue anulado.'], 404);
        }

        // Validación de seguridad para que un barbero no borre lo de otro
        if (!in_array($user->role->slug, ['admin', 'manager']) && $amount->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para anular un cobro que no realizaste.'], 403);
        }

        // Al ejecutar delete(), como activamos SoftDeletes, el registro NO desaparece de la BD
        // solo se marca como anulado. Esto es la "Evidencia".
        $amount->delete();

        // TODO: Fase 5 - Notificaciones (Admin es avisado, o Barbero es avisado)
        // Esto dejará constancia de quién "apretó el botón" de borrado.

        return response()->json([
            'message' => 'Registro anulado correctamente. La evidencia ha quedado guardada en el historial del sistema.'
        ], 200);
    }


    /**
     * Helper privado
     */
    private function isAdminOrManager(Request $request)
    {
        return in_array($request->user()->role->slug ?? '', ['admin', 'manager']);
    }
}