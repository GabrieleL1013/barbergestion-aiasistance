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
}
