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
        // 1. Validar los datos que envía React
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'ci' => 'required|string|max:20|unique:users,ci',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
            'password' => 'required|string|min:6',
        ]);

        // 2. Crear el usuario con rol de "user" por defecto
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'ci' => $request->ci,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'avatar' => $request->avatar ?? null,
            'password' => $request->password, // El mutator en el modelo se encargará de hashear la contraseña
            'role_id' => Role::where('slug', 'user')->first()->id, // Asignamos el rol de "user" por defecto
        ]);

        // 3. Devolver una respuesta al frontend
        return response()->json([
            'message' => 'Registro exitoso. Ahora puedes iniciar sesión.',
            'user' => $user
        ], 201);
    }
    public function login(Request $request)
    {
        // 1. Validar los datos que envía React
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // 3. Si es correcto, obtener el usuario y su rol
        $user = User::with('role')->where('email', $request->email)->firstOrFail();

        // 4. Generar el token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolver la respuesta al frontend
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role->slug ?? null, // Útil para que React sepa qué vistas mostrar
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Revocar el token actual del usuario
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Devuelve los datos del usuario autenticado (para la interfaz de perfil).
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }
}