<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Muestra la lista de citas.
     * Útil para el panel del administrador o la agenda del barbero.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['client', 'barber', 'service']);

        // Si el frontend envía un barber_id, filtramos solo las citas de ese barbero
        if ($request->has('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        // Si el frontend envía una fecha, filtramos por ese día
        if ($request->has('date')) {
            $date = Carbon::parse($request->date)->toDateString();
            $query->whereDate('scheduled_at', $date);
        }

        // Ordenamos por las más próximas primero
        $appointments = $query->orderBy('scheduled_at', 'asc')->get();

        return response()->json($appointments);
    }

    /**
     * Registra un nuevo turno (Puede ser creado por el cliente en React o por el Admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date|after:now', // No se puede reservar en el pasado
            'payment_method' => 'nullable|string', // Método de pago para la venta asociada
            'notes' => 'nullable|string'
        ]);

        $requestedTime = Carbon::parse($request->scheduled_at);
        $service = Service::findOrFail($request->service_id);
        
        // Calculamos a qué hora terminaría este nuevo servicio
        $endTime = $requestedTime->copy()->addMinutes($service->duration_minutes);

        // 1. OBTENER LAS CITAS DEL BARBERO PARA ESE DÍA
        // Cargamos la relación 'service' para saber cuánto duran sus citas ya agendadas
        $existingAppointments = Appointment::with('service')
            ->where('barber_id', $request->barber_id)
            ->whereIn('status', ['pending', 'confirmed']) // Ignoramos las canceladas
            ->whereDate('scheduled_at', $requestedTime->toDateString()) // Solo revisamos las citas de ese mismo día
            ->get();

        // 2. VERIFICAR CRUCE DE HORARIOS USANDO CARBON (Compatible con Postgres, MySQL, etc.)
        $conflict = $existingAppointments->contains(function ($appointment) use ($requestedTime, $endTime) {
            $existingStart = Carbon::parse($appointment->scheduled_at);
            
            // Obtenemos la duración de la cita existente (si por alguna razón el servicio se borró, asumimos 30 min por seguridad)
            $duration = $appointment->service ? $appointment->service->duration_minutes : 30;
            $existingEnd = $existingStart->copy()->addMinutes($duration);

            // Fórmula estándar para detectar cruce de rangos de tiempo:
            // (Inicio Nuevo < Fin Existente) Y (Inicio Existente < Fin Nuevo)
            return $requestedTime < $existingEnd && $existingStart < $endTime;
        });

        if ($conflict) {
            return response()->json([
                'message' => 'El barbero seleccionado ya tiene una cita que interfiere con este horario.'
            ], 422);
        }

        // 3. Guardamos la cita y la venta asociada en una transacción para asegurar consistencia
        return DB::transaction(function () use ($request, $requestedTime, $service) {
            $appointment = Appointment::create([
                'client_id' => $request->client_id,
                'barber_id' => $request->barber_id,
                'service_id' => $request->service_id,
                'scheduled_at' => $requestedTime,
                'status' => 'pending', // Por defecto entra como pendiente
                'notes' => $request->notes
            ]);

            $saleController = app(SaleController::class);
            $saleData = [
                [
                    'type' => 'service',
                    'id' => $request->service_id,
                    'quantity' => 1,
                ],
            ];

            $client = User::findOrFail($request->client_id);
            $saleResult = $saleController->createSaleForItems(
                $client,
                $request->barber_id,
                $saleData,
                $request->input('payment_method', 'cash')
            );

            return response()->json([
                'message' => 'Turno reservado con éxito y venta asociada creada',
                'appointment' => $appointment->load(['barber', 'service']),
                'sale_id' => $saleResult['sale']->id,
                'total_paid' => $saleResult['totalAmount'],
                'promotion_applied' => $saleResult['promotionApplied'] ? $saleResult['promotionApplied']->name : 'Ninguna'
            ], 201);
        });
    }

    /**
     * Actualiza el estado de la cita (Ej: Confirmada -> Completada)
     * ¡Aquí es donde podrías conectar con la lógica de Fidelidad si lo deseas!
     */
    public function updateStatus(Request $request, $id)
    {   // VALIDAMOS SOLO EL CAMPO DE STATUS, porque el resto de los datos de la cita no deberían cambiarse desde aquí
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,no_show'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();

        /* * NOTA PARA LA TESIS: 
         * Si marcas el status como 'completed' desde aquí (porque el cliente ya se cortó el pelo), 
         * podrías redirigir automáticamente al SaleController para cobrarle o sumar la visita.
         */

        return response()->json([
            'message' => 'Estado de la cita actualizado',
            'appointment' => $appointment
        ]);
    }
}