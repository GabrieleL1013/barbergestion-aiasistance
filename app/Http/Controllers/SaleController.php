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

        return DB::transaction(function () use ($request) {
            $client = User::findOrFail($request->client_id);
            $result = $this->createSaleForItems(
                $client,
                $request->barber_id,
                $request->items,
                $request->payment_method
            );

            return response()->json([
                'message' => 'Venta registrada con éxito',
                'sale_id' => $result['sale']->id,
                'total_paid' => $result['totalAmount'],
                'promotion_applied' => $result['promotionApplied'] ? $result['promotionApplied']->name : 'Ninguna',
                'client_new_visits_count' => $client->visits_count
            ], 201);
        });
    }

    public function storeFromAppointment(Request $request)
    {
        // Validamos los datos necesarios para generar la venta al crear una cita
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'payment_method' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $client = User::findOrFail($request->client_id);
            $items = [
                [
                    'type' => 'service',
                    'id' => $request->service_id,
                    'quantity' => 1,
                ],
            ];

            $result = $this->createSaleForItems(
                $client,
                $request->barber_id,
                $items,
                $request->input('payment_method', 'cash')
            );

            return response()->json([
                'message' => 'Venta de cita registrada con éxito',
                'sale_id' => $result['sale']->id,
                'total_paid' => $result['totalAmount'],
                'promotion_applied' => $result['promotionApplied'] ? $result['promotionApplied']->name : 'Ninguna',
                'sale' => $result['sale']->load('items'),
            ], 201);
        });
    }

    public function createSaleForItems(User $client, int $barberId, array $items, string $paymentMethod = 'cash')
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $price = $this->getItemPrice($item['type'], $item['id']);
            $subtotal += $price * $item['quantity'];
        }

        $discount = 0;
        $promotionApplied = null;

        $promotionApplied = Promotion::where('is_active', true)
            ->where('required_visits', '<=', $client->visits_count)
            ->orderBy('required_visits', 'desc')
            ->first();

        if ($promotionApplied) {
            $discount = $subtotal * ($promotionApplied->discount_percentage / 100);
            $client->visits_count = 0;
        } else {
            $client->visits_count += 1;
        }

        $client->total_lifetime_visits += 1;
        $client->save();

        $totalAmount = $subtotal - $discount;

        $sale = Sale::create([
            'client_id' => $client->id,
            'barber_id' => $barberId,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
        ]);

        foreach ($items as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                'quantity' => $item['quantity'],
                'price_at_sale' => $this->getItemPrice($item['type'], $item['id']),
            ]);
        }

        return [
            'sale' => $sale,
            'discount' => $discount,
            'promotionApplied' => $promotionApplied,
            'totalAmount' => $totalAmount,
        ];
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