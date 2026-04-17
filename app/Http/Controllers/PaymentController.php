<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Amount;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\NewPaymentNotification;
use App\Notifications\PaymentAcceptedNotification;

class PaymentController extends Controller
{
    /**
     * OBTENER PAGOS PAGINADOS (GET)
     * Admin/Manager ven toda la nómina. Barberos ven solo sus propios pagos recibidos.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));

        // Traemos las relaciones principales
        $query = Payment::with(['user', 'admin']);

        // Filtro de privacidad: Si no es jefe, solo ve sus pagos
        if (!in_array($user->role->slug, ['admin', 'manager'])) {
            $query->where('user_id', $user->id);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'message'  => 'Lista de pagos obtenida correctamente',
            'payments' => $payments
        ], 200);
    }

    /**
     * EMITIR UN PAGO (POST)
     * Calcula la comisión respetando el costo de los productos (Ganancia Real).
     */
    public function store(Request $request)
    {
        $admin = $request->user();

        if (!in_array($admin->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'amount_ids' => 'required|array|min:1', 
            'amount_ids.*' => 'exists:amounts,id',
            'notes'      => 'nullable|string',
        ]);

        $employee = User::find($request->user_id);

        $cobros = Amount::with('product')
                        ->whereIn('id', $request->amount_ids)
                        ->where('user_id', $employee->id)
                        ->whereNull('payment_id') 
                        ->get();

        if ($cobros->isEmpty()) {
            return response()->json(['message' => 'Los cobros seleccionados no son válidos o ya fueron pagados.'], 400);
        }

        $pagoFinal = 0;
        $porcentaje = $employee->commission / 100; 

        foreach ($cobros as $cobro) {
            if ($cobro->product_id && $cobro->product) {
                $gananciaReal = $cobro->amount - $cobro->product->cost;
                if ($gananciaReal < 0) { $gananciaReal = 0; } 

                $pagoFinal += ($gananciaReal * $porcentaje);
            } else {
                $pagoFinal += ($cobro->amount * $porcentaje);
            }
        }

        $payment = Payment::create([
            'user_id'  => $employee->id,
            'admin_id' => $admin->id,
            'amount'   => $pagoFinal,
            'status'   => 'pending',
            'notes'    => $request->notes,
        ]);

        Amount::whereIn('id', $cobros->pluck('id'))->update(['payment_id' => $payment->id]);

        // --- LÓGICA DE NOTIFICACIONES ---
        // Le enviamos la notificación únicamente al empleado dueño de este pago
        $employee->notify(new NewPaymentNotification($payment));

        return response()->json([
            'message' => "Se ha generado un pago pendiente por $$pagoFinal (Calculado sobre ganancia neta).",
            'payment' => $payment
        ], 201);
    }

    /**
     * EMPLEADO ACEPTA EL PAGO (PATCH)
     */
    /**
     * EMPLEADO ACEPTA EL PAGO (PATCH)
     */
    public function acceptPayment(Request $request, $id)
    {
        $user = $request->user();
        
        // CAMBIO AQUÍ: Añadimos with('admin') para poder enviarle la notificación al jefe
        $payment = Payment::with('admin')->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado.'], 404);
        }

        // Solo el empleado dueño de este pago puede aceptarlo
        if ($payment->user_id !== $user->id) {
            return response()->json(['message' => 'No puedes aprobar un pago que no es tuyo.'], 403);
        }

        if ($payment->status === 'accepted') {
            return response()->json(['message' => 'Este pago ya fue aceptado anteriormente.'], 400);
        }

        $payment->update(['status' => 'accepted']);

        // --- LÓGICA DE NOTIFICACIONES ---
        // Le notificamos al administrador que generó este pago
        if ($payment->admin) {
            $payment->admin->notify(new PaymentAcceptedNotification($payment));
        }

        return response()->json(['message' => 'Has aceptado el pago exitosamente.']);
    }

    /**
     * VER DETALLES DE UN PAGO (GET)
     * Ideal para mostrar el "Recibo Desglosado" con todos los cortes que incluye.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        // Buscamos el pago y traemos toda su descendencia de golpe (Nested Eager Loading)
        $payment = Payment::with(['user', 'admin', 'amounts.client', 'amounts.product'])->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Recibo de pago no encontrado.'], 404);
        }

        // Seguridad: Un barbero solo puede ver sus propios recibos
        if (!in_array($user->role->slug, ['admin', 'manager']) && $payment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para ver este recibo de pago.'], 403);
        }

        return response()->json([
            'message' => 'Detalle del recibo obtenido correctamente.',
            'payment' => $payment
        ], 200);
    }

    /**
     * ANULAR UN PAGO (DELETE)
     * Solo para Admin/Manager. 
     * Libera los cobros asociados para que puedan ser pagados nuevamente.
     */
    public function destroy(Request $request, $id)
    {
        if (!in_array($request->user()->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Solo la administración puede anular pagos.'], 403);
        }

        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'El registro ya no existe o fue anulado.'], 404);
        }

        // IMPORTANTE: Antes de borrar el pago, ponemos en NULL el payment_id de sus cobros
        // Esto permite que esos cobros aparezcan de nuevo como "Pendientes de pago"
        Amount::where('payment_id', $payment->id)->update(['payment_id' => null]);

        $payment->delete(); // SoftDelete

        return response()->json([
            'message' => 'Pago anulado correctamente. Los cobros asociados han sido liberados para un nuevo registro.'
        ], 200);
    }
}