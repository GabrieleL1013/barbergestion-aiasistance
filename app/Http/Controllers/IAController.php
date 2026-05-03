<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IAController extends Controller
{
public function sugerirCorte(Request $request)
{
    $request->validate(['question' => 'required|string']);
    $apiKey = env('GROQ_API_KEY');

    try {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                // MODELO ACTUALIZADO (Llama 3.3 Versatile)
                'model' => 'llama-3.3-70b-versatile', 
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'Eres Roger M, barbero experto de RS Barber Studio. Das consejos de estilo modernos, breves y con mucha actitud.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $request->input('question')
                    ],
                ],
                'temperature' => 0.7,
            ]);

        if ($response->successful()) {
            return response()->json([
                'respuesta' => $response->json()['choices'][0]['message']['content']
            ]);
        }

        return response()->json([
            'error' => 'Error de Groq',
            'detalle' => $response->json()
        ], $response->status());

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}