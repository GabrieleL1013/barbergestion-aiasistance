<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // El middleware auth:sanctum ya debió validar que esté logueado,
        // pero igual obtenemos el usuario directamente del request.
        $user = $request->user();

        $request->validate([
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'client_id' => $user->id, // Usamos el ID del request
            'barber_id' => $request->barber_id,
            'service_id' => $request->service_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Reseña creada exitosamente',
            'review' => $review
        ], 201);
    }

    /**
     * Lista reseñas públicas con filtros opcionales:
     * - barber_id: filtra reseñas de un barbero específico
     * - service_id: filtra reseñas de un servicio específico
     * - sort: 'rating_asc' o 'rating_desc' para ordenar por calificación
     * No requiere autenticación.
     */
    public function index(Request $request)
    {
        $query = Review::with(['client', 'barber', 'service']);

        if ($request->has('barber_id')) {
            $query->where('barber_id', $request->query('barber_id'));
        }

        if ($request->has('service_id')) {
            $query->where('service_id', $request->query('service_id'));
        }

        // Ordenamiento por rating
        $sort = $request->query('sort');
        if ($sort === 'rating_asc') {
            $query->orderBy('rating', 'asc');
        } elseif ($sort === 'rating_desc') {
            $query->orderBy('rating', 'desc');
        } else {
            // Por defecto orden cronológico descendente
            $query->orderBy('created_at', 'desc');
        }

        // Paginación simple (opcional)
        $perPage = (int) $request->query('per_page', 20);
        $reviews = $query->paginate(max(1, min(100, $perPage)));

        return response()->json([
            'message' => 'Reseñas obtenidas',
            'reviews' => $reviews
        ], 200);
    }

    /**
     * Obtener una reseña específica por su id (pública)
     */
    public function show($id)
    {
        $review = Review::with(['client', 'barber', 'service'])->find($id);

        if (! $review) {
            return response()->json(['message' => 'Reseña no encontrada'], 404);
        }

        return response()->json(['review' => $review], 200);
    }
}
