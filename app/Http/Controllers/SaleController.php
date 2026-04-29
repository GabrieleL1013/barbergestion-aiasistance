<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos que el frontend (React) nos envíe la data correcta
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:users,id',
            'payment_method' => 'required|string',
            'items' => 'required|array', // Un array con lo que compró (servicios/productos)
        ]);

        // Iniciamos la transacción segura
        return DB::transaction(function () use ($request) {
            
            $client = User::findOrFail($request->client_id);
            $subtotal = 0;

            // 2. Calcular el subtotal real consultando la Base de Datos
            // (Nunca confíes en el total que envía el frontend por seguridad)
            foreach ($request->items as $item) {
                $price = $this->getItemPrice($item['type'], $item['id']);
                $subtotal += $price * $item['quantity'];
            }

            // 3. LA MAGIA DE LA FIDELIDAD
            $discount = 0;
            $promotionApplied = null;

            // Buscamos si el cliente califica para alguna promoción activa
            $promotion = Promotion::where('is_active', true)
                                  ->where('required_visits', '<=', $client->visits_count)
                                  ->orderBy('required_visits', 'desc') // Tomamos la mejor promoción posible
                                  ->first();

            if ($promotion) {
                // Aplicamos el descuento
                $discount = $subtotal * ($promotion->discount_percentage / 100);
                $promotionApplied = $promotion;
                
                // Como ya usó su beneficio, su contador vuelve a cero
                $client->visits_count = 0;
            } else {
                // Si no calificó a promo, le sumamos esta visita a su contador
                $client->visits_count += 1;
            }

            // Las visitas históricas nunca se borran (sirven para estadísticas del negocio)
            $client->total_lifetime_visits += 1;
            $client->save();

            $totalAmount = $subtotal - $discount;

            // 4. Guardar la Cabecera de la Factura (Sale)
            $sale = Sale::create([
                'client_id' => $client->id,
                'barber_id' => $request->barber_id,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            // 5. Guardar el Detalle (SaleItems)
            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $this->getItemPrice($item['type'], $item['id']),
                ]);
            }

            // Retornamos la respuesta exitosa al frontend
            return response()->json([
                'message' => 'Venta registrada con éxito',
                'sale_id' => $sale->id,
                'total_paid' => $totalAmount,
                'promotion_applied' => $promotionApplied ? $promotionApplied->name : 'Ninguna',
                'client_new_visits_count' => $client->visits_count
            ], 201);
        });
    }

    /**
     * Función auxiliar para obtener el precio real de la BD
     */
    private function getItemPrice($type, $id)
    {
        if ($type === 'product') {
            return Product::findOrFail($id)->price;
        }
        if ($type === 'service') {
            return Service::findOrFail($id)->price;
        }
        return 0;
    }
}