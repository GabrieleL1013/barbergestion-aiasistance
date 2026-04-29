<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
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
            'notes' => 'nullable|string'
        ]);

        $requestedTime = Carbon::parse($request->scheduled_at);
        $service = Service::findOrFail($request->service_id);
        
        // Calculamos a qué hora terminaría este servicio
        $endTime = $requestedTime->copy()->addMinutes($service->duration_minutes);

        // VALIDACIÓN ESTRELLA: Evitar choques de horarios para el mismo barbero
        $conflict = Appointment::where('barber_id', $request->barber_id)
            ->whereIn('status', ['pending', 'confirmed']) // Ignoramos las canceladas
            ->where(function ($query) use ($requestedTime, $endTime) {
                // Verificamos si el nuevo turno se cruza con uno existente
                $query->whereBetween('scheduled_at', [$requestedTime, $endTime->copy()->subMinute()])
                      ->orWhereRaw('DATE_ADD(scheduled_at, INTERVAL (SELECT duration_minutes FROM services WHERE services.id = appointments.service_id) MINUTE) > ? AND scheduled_at <= ?', [$requestedTime, $requestedTime]);
            })->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'El barbero seleccionado ya tiene una cita en ese horario.'
            ], 422);
        }

        // Si el horario está libre, creamos la cita
        $appointment = Appointment::create([
            'client_id' => $request->client_id,
            'barber_id' => $request->barber_id,
            'service_id' => $request->service_id,
            'scheduled_at' => $requestedTime,
            'status' => 'pending', // Por defecto entra como pendiente
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Turno reservado con éxito',
            'appointment' => $appointment->load(['barber', 'service'])
        ], 201);
    }

    /**
     * Actualiza el estado de la cita (Ej: Confirmada -> Completada)
     * ¡Aquí es donde podrías conectar con la lógica de Fidelidad si lo deseas!
     */
    public function updateStatus(Request $request, $id)
    {
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