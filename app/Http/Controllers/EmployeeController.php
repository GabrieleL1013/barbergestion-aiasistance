<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // 1. VERIFICACIÓN DE PERMISOS
        // Obtenemos el slug del rol del usuario autenticado que hace la petición
        $userRoleSlug = $request->user()->role->slug;

        // Comprobamos si el slug NO está en el arreglo de roles permitidos
        if (!in_array($userRoleSlug, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Acceso denegado. Solo los administradores o programadores pueden registrar nuevos usuarios.'
            ], 403); // 403 Forbidden
        }

        // 2. VALIDACIÓN DE DATOS
        $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'ci'        => 'required|string|max:20|unique:users',
            'phone'     => 'nullable|string|max:20|unique:users',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id', // Verifica que el ID del rol exista en la BD
        ]);

        // 3. CREACIÓN DEL USUARIO
        $employee = User::create([
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'ci'        => $request->ci,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'password'  => $request->password, // Se encripta solo gracias a los casts() del modelo
            'role_id'   => $request->role_id,
        ]);

        // Cargamos la relación del rol para devolver la información completa en la respuesta
        $employee->load('role');

        return response()->json([
            'message'  => 'Usuario registrado exitosamente',
            'user'     => $employee
        ], 201);
    }

    public function index(Request $request)
    {
        // 1. VERIFICACIÓN DE PERMISOS
        $userRoleSlug = $request->user()->role->slug;

        if (!in_array($userRoleSlug, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Acceso denegado. Solo los administradores o programadores pueden ver la lista de empleados.'
            ], 403);
        }

        // 2. CONFIGURAR LA PAGINACIÓN
        $requestedPerPage = (int) $request->query('per_page', 10);
        $perPage = min(100, max(1, $requestedPerPage));

        // 3. OBTENER SOLO LOS BARBEROS PAGINADOS
        // whereHas() permite filtrar usuarios dependiendo de una condición en su tabla relacionada (roles)
        $users = User::whereHas('role', function ($query) {
            $query->where('slug', 'barber');
        })->with('role')->paginate($perPage);

        return response()->json([
            'message' => 'Lista de barberos obtenida correctamente',
            'users'   => $users 
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        // 1. VERIFICACIÓN DE PERMISOS
        $userRoleSlug = $request->user()->role->slug;

        if (!in_array($userRoleSlug, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Acceso denegado. Solo los administradores o programadores pueden eliminar usuarios.'
            ], 403);
        }

        // 2. BUSCAR AL USUARIO
        // Usamos find() en lugar de findOrFail() para poder devolver un JSON limpio si no existe
        $employee = User::find($id);

        if (!$employee) {
            return response()->json([
                'message' => 'El usuario especificado no existe o ya fue eliminado.'
            ], 404);
        }

        // 3. SEGURIDAD EXTRA: Prevenir el auto-borrado
        if ($request->user()->id === $employee->id) {
            return response()->json([
                'message' => 'Operación denegada. No puedes eliminar tu propia cuenta de administrador.'
            ], 400); // 400 Bad Request
        }

        // 4. ELIMINAR (Esto ejecuta el Soft Delete gracias a tu modelo)
        $employee->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente del sistema.',
            'deleted_user_id' => $id
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $authUser = $request->user();
        
        // 1. BUSCAR AL USUARIO QUE SE VA A EDITAR
        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json([
                'message' => 'El usuario especificado no existe.'
            ], 404);
        }

        // 2. VERIFICACIÓN DE PERMISOS (Doble validación)
        $isAdminOrManager = in_array($authUser->role->slug, ['admin', 'manager']);
        $isOwnProfile = $authUser->id === $targetUser->id;

        // Si NO es admin/manager y TAMPOCO es su propio perfil, lo bloqueamos
        if (!$isAdminOrManager && !$isOwnProfile) {
            return response()->json([
                'message' => 'Acceso denegado. Solo puedes actualizar tus propios datos.'
            ], 403);
        }

        // 3. REGLAS DE VALIDACIÓN
        // Usamos 'sometimes' para que solo valide el campo si el frontend lo envía.
        // En el email, concatenamos el $id para que Laravel ignore el email actual del usuario 
        // y no lance el error de "este correo ya está en uso" si el usuario no lo cambió.
        $rules = [
            'name'      => 'sometimes|required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:6',
            'avatar'    => 'nullable|string', // Acepta el base64
            'commission' => 'sometimes|required|integer|min:0|max:100',
        ];

        // Solo si es admin o manager, le permitimos enviar y validar un cambio de rol
        if ($isAdminOrManager) {
            $rules['role_id'] = 'sometimes|required|exists:roles,id';
        }

        $validatedData = $request->validate($rules);

        // 4. ACTUALIZAR LOS DATOS MASIVAMENTE
        // fill() reemplaza los datos actuales con los nuevos, pero solo los que pasaron la validación
        $targetUser->fill($request->except(['password', 'role_id']));

        // 5. ACTUALIZAR CONTRASEÑA (Solo si la enviaron)
        if ($request->filled('password')) {
            // Como tu modelo User tiene 'password' => 'hashed' en los casts, 
            // esto se encriptará automáticamente al guardarse en la BD.
            $targetUser->password = $request->password;
        }

        // 6. ACTUALIZAR ROL (Solo si es admin/manager y si enviaron el campo)
        if ($isAdminOrManager && $request->filled('role_id')) {
            $targetUser->role_id = $request->role_id;
        }

        // 7. GUARDAR EN LA BASE DE DATOS
        $targetUser->save();

        // Recargamos la relación del rol para devolver la respuesta completa al frontend de React
        $targetUser->load('role');

        return response()->json([
            'message' => 'Datos actualizados correctamente.',
            'user'    => $targetUser
        ], 200);
    }
}