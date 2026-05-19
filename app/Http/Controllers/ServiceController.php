<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Listar servicios (públicamente solo activos, admin/manager pueden ver todos)
     * Soporta búsqueda por nombre (case-insensitive, sin importar orden en la palabra),
     * paginación configurable y filtrado por estado.
     * GET /api/services?search=barba&per_page=20&include_inactive=false
     * Público para servicios activos, requiere auth+permisos para inactivos.
     */
    public function index(Request $request)
    {
        // Si es público (sin autenticación), solo mostramos activos
        $isAdmin = $request->user() && in_array($request->user()->role->slug ?? '', ['admin', 'manager']);
        $includeInactive = $request->query('include_inactive', false) == 'true' || $request->query('include_inactive') == 1;

        // Validar que si quiere inactivos, esté autenticado y sea admin/manager
        if ($includeInactive && !$isAdmin) {
            return response()->json(['message' => 'Acceso denegado. No tienes permisos para ver servicios inactivos.'], 403);
        }

        $query = Service::query();

        // Si no es admin o no pide incluir inactivos, solo activos
        if (!$includeInactive) {
            $query->where('is_active', true);
        }

        // Búsqueda por nombre (case-insensitive, en cualquier parte de la palabra)
        if ($request->has('search') && $request->query('search') !== '') {
            $search = $request->query('search');
            $query->where('name', 'ilike', "%{$search}%"); // ILIKE en PostgreSQL, LIKE en MySQL
        }

        // Paginación configurable (entre 1 y 100)
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
        $services = $query->paginate($perPage);

        return response()->json([
            'message' => 'Servicios obtenidos correctamente',
            'services' => $services
        ], 200);
    }

    /**
     * Crear un nuevo servicio (solo admin/manager autenticados)
     * POST /api/services
     */
    public function store(Request $request)
    {
        // Validar autenticación y permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden crear servicios.'], 403);
        }

        // Validar datos
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'photo' => 'nullable|string', // URL o base64
            'details' => 'nullable|array', // Array con detalles/características
            'is_active' => 'boolean',
        ]);

        // Crear el servicio
        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'photo' => $request->photo,
            'details' => $request->details,
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'service' => $service
        ], 201);
    }

    /**
     * Actualizar un servicio (solo admin/manager autenticados)
     * PUT/PATCH /api/services/{id}
     */
    public function update(Request $request, $id)
    {
        // Validar autenticación y permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden actualizar servicios.'], 403);
        }

        // Buscar el servicio
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Servicio no encontrado.'], 404);
        }

        // Validar datos (todo es opcional menos los campos requeridos)
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_minutes' => 'sometimes|required|integer|min:1',
            'photo' => 'nullable|string',
            'details' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        // Actualizar los campos proporcionados
        $service->update($request->only(['name', 'description', 'price', 'duration_minutes', 'photo', 'details', 'is_active']));

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'service' => $service
        ], 200);
    }

    /**
     * Eliminar un servicio (solo admin/manager autenticados) - Soft Delete
     * DELETE /api/services/{id}
     */
    public function destroy(Request $request, $id)
    {
        // Validar autenticación y permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden eliminar servicios.'], 403);
        }

        // Buscar el servicio
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Servicio no encontrado o ya fue eliminado.'], 404);
        }

        // Soft delete
        $service->delete();

        return response()->json([
            'message' => 'Servicio eliminado correctamente.'
        ], 200);
    }

    /**
     * Función auxiliar privada para validar admin/manager
     */
    private function isAdminOrManager(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }
        $userRoleSlug = $user->role->slug ?? '';
        return in_array($userRoleSlug, ['admin', 'manager']);
    }
}