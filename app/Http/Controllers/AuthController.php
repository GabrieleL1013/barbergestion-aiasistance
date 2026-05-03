<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validación corregida para coincidir con la migración
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'ci' => 'required|string|size:10|unique:users,ci', // Ajustado a 10 caracteres exactos
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'ci' => $request->ci,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'avatar' => $request->avatar ?? null,
            'password' => $request->password, 
            'role_id' => Role::where('slug', 'user')->first()->id, 
        ]);

        return response()->json([
            'message' => 'Registro exitoso. Ahora puedes iniciar sesión.',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = User::with('role')->where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true, // Añadido para mantener compatibilidad con tu lógica de React
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'ci' => $user->ci,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->slug ?? 'user', // Fallback por seguridad
                'avatar' => $user->avatar
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }
}