<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'ci' => 'nullable|string|size:10|unique:users,ci,' . $user->id,
            'avatar' => 'nullable|string',
        ]);

        $user->update($request->only('name', 'last_name', 'phone', 'ci', 'avatar'));

        // Formateamos la respuesta para que sea EXACTAMENTE igual a la del Login
        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'ci' => $user->ci,
                'phone' => $user->phone,
                'role' => $user->role->slug ?? 'user', // Aquí evitamos el objeto
                'avatar' => $user->avatar
            ]
        ], 200);
    }

    public function index(Request $request)
    {
        $authUser = $request->user();

        // 1. VERIFICACIÓN DE PERMISOS POR NIVEL JERÁRQUICO
        // Exigimos que el usuario tenga un nivel de permisos alto (ej. 99 para admin/manager)
        if ($authUser->role->level_permissions < 99) {
            return response()->json([
                'message' => 'Acceso denegado. No tienes el nivel de permisos necesario para listar a los usuarios.'
            ], 403);
        }

        // 2. CONFIGURAR LA PAGINACIÓN
        // Permite al frontend definir cuántos usuarios por página quiere, limitando entre 1 y 100.
        $requestedPerPage = (int) $request->query('per_page', 10);
        $perPage = min(100, max(1, $requestedPerPage));

        // 3. OBTENER LOS USUARIOS
        // Usamos whereHas para excluir a los empleados/administradores si solo quieres listar clientes.
        // O simplemente User::with('role')->paginate($perPage) si quieres traer absolutamente a todos.
        
        // Opción A: Traer a todos los usuarios que tengan un nivel de permiso INFERIOR al que consulta.
        // Esto evita que un Manager edite o vea detalles internos de un Admin (Programador).
        $users = User::whereHas('role', function ($query) use ($authUser) {
            $query->where('level_permissions', '<', $authUser->role->level_permissions)
                  ->orWhere('slug', 'user'); // Aseguramos que siempre traiga a los clientes (nivel 0)
        })
        ->with('role') // Cargamos la relación para ver el nombre/color del rol en el frontend
        ->paginate($perPage);

        /* 
        // Opción B: Traer ABSOLUTAMENTE a todos (descomenta esto y borra la Opción A si prefieres este comportamiento)
        // $users = User::with('role')->paginate($perPage);
        */

        return response()->json([
            'message' => 'Lista de usuarios obtenida exitosamente.',
            'users' => $users
        ], 200);
    }

}