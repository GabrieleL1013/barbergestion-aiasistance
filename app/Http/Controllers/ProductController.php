<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * OBTENER PRODUCTOS (GET)
     * Todos los usuarios autenticados pueden verlo.
     */
    public function index(Request $request)
    {
        // Paginación segura dinámica (entre 1 y 100)
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));

        $products = Product::paginate($perPage);

        return response()->json([
            'message'  => 'Lista de productos obtenida correctamente',
            'products' => $products
        ], 200);
    }

    /**
     * CREAR PRODUCTO (POST)
     * Solo Admin y Manager.
     */
    public function store(Request $request)
    {
        // 1. Validar permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden crear productos.'], 403);
        }

        // 2. Validar datos
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost'        => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'measure'     => 'nullable|string|max:255',
            'unit'        => 'nullable|string|max:255',
            'photo'       => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        // 3. Crear el producto
        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ], 201);
    }

    /**
     * ACTUALIZAR PRODUCTO (PUT/PATCH)
     * Solo Admin y Manager.
     */
    public function update(Request $request, $id)
    {
        // 1. Validar permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden modificar productos.'], 403);
        }

        // 2. Buscar producto
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        // 3. Validar datos
        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cost'        => 'sometimes|required|numeric|min:0',
            'price'       => 'sometimes|required|numeric|min:0',
            'measure'     => 'nullable|string|max:255',
            'unit'        => 'nullable|string|max:255',
            'photo'       => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
        ]);

        // 4. Actualizar
        $product->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'product' => $product
        ], 200);
    }

    /**
     * ELIMINAR PRODUCTO (DELETE)
     * Solo Admin y Manager.
     */
    public function destroy(Request $request, $id)
    {
        // 1. Validar permisos
        if (!$this->isAdminOrManager($request)) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores pueden eliminar productos.'], 403);
        }

        // 2. Buscar y eliminar
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado o ya fue eliminado.'], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.'
        ], 200);
    }

    /**
     * Función auxiliar (privada) para no repetir código de validación de roles.
     */
    private function isAdminOrManager(Request $request)
    {
        $userRoleSlug = $request->user()->role->slug ?? '';
        return in_array($userRoleSlug, ['admin', 'manager']);
    }
}