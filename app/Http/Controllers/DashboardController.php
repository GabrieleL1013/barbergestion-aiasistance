<?php

namespace App\Http\Controllers;

use App\Models\Amount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon; // La librería mágica de fechas de Laravel

class DashboardController extends Controller
{
    /**
     * OBTENER KPIs DEL ADMINISTRADOR (GET)
     * Muestra la salud financiera del negocio en tiempo real.
     */
    public function getAdminKpis(Request $request)
    {
        // Seguridad de acceso
        if (!in_array($request->user()->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $today = Carbon::today(); // Trae la fecha de hoy a las 00:00:00

        // 1. Buscamos TODOS los cobros de HOY, trayendo sus productos (para ver el costo)
        $amountsToday = Amount::with('product')
                              ->whereDate('created_at', $today)
                              ->get();

        // 2. MATEMÁTICAS DE HOY
        $ingresosBrutos = $amountsToday->sum('amount'); // Todo el dinero físico que entró
        $cortesRealizados = $amountsToday->count();     // Cantidad de servicios
        
        // Ganancia Neta: Ingresos menos el costo de los productos
        $costoProductos = $amountsToday->sum(function ($cobro) {
            return $cobro->product ? $cobro->product->cost : 0;
        });
        $gananciaNeta = $ingresosBrutos - $costoProductos;

        // 3. MATEMÁTICAS DE DEUDAS (Nómina pendiente histórica, no solo de hoy)
        // Buscamos todos los cobros que aún no han sido pagados
        $cobrosPendientes = Amount::with(['user', 'product'])
                                  ->whereNull('payment_id')
                                  ->get();

        // Calculamos cuánto le debemos a los empleados respetando tu lógica de "Ganancia Real"
        $deudaNomina = 0;
        foreach ($cobrosPendientes as $cobro) {
            if ($cobro->user) {
                $porcentaje = $cobro->user->commission / 100;
                
                if ($cobro->product_id && $cobro->product) {
                    $gananciaReal = $cobro->amount - $cobro->product->cost;
                    if ($gananciaReal < 0) { $gananciaReal = 0; }
                    $deudaNomina += ($gananciaReal * $porcentaje);
                } else {
                    $deudaNomina += ($cobro->amount * $porcentaje);
                }
            }
        }

        // 4. Retornamos el JSON para el Dashboard de React y la futura IA
        return response()->json([
            'message' => 'KPIs de administración obtenidos',
            'data' => [
                'ingresos_brutos_hoy' => round($ingresosBrutos, 2),
                'ganancia_neta_hoy'   => round($gananciaNeta, 2),
                'cortes_hoy'          => $cortesRealizados,
                'deuda_nomina_actual' => round($deudaNomina, 2),
            ]
        ], 200);
    }

    /**
     * RANKING DE BARBEROS DEL MES ACTUAL (GET)
     * Ordena a los empleados según la Ganancia Neta que le han dejado al negocio.
     */
    public function getTopBarbers(Request $request)
    {
        if (!in_array($request->user()->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        // 1. Buscamos a los usuarios que tengan el rol de "barber"
        $barberos = User::whereHas('role', function ($query) {
            $query->where('slug', 'barber');
        })
        // 2. Cargamos TODOS sus cobros de este mes CON sus productos para ver el costo
        ->with(['amounts' => function ($query) use ($mesActual, $anioActual) {
            $query->with('product')
                  ->whereMonth('created_at', $mesActual)
                  ->whereYear('created_at', $anioActual);
        }])
        ->get();

        // 3. Mapeamos la colección para calcular la "Ganancia Neta" de cada uno
        $ranking = $barberos->map(function ($barbero) {
            
            $gananciaNetaGenerada = 0;
            $totalCortes = $barbero->amounts->count();

            // Recorremos los cortes del mes para este barbero
            foreach ($barbero->amounts as $cobro) {
                if ($cobro->product_id && $cobro->product) {
                    $gananciaNetaGenerada += ($cobro->amount - $cobro->product->cost);
                } else {
                    $gananciaNetaGenerada += $cobro->amount;
                }
            }

            return [
                'id' => $barbero->id,
                'name' => $barbero->name,
                'last_name' => $barbero->last_name,
                'avatar' => $barbero->avatar, // Para poner su fotito en el podio
                'total_cortes' => $totalCortes,
                'ganancia_neta' => round($gananciaNetaGenerada, 2)
            ];
        });

        // 4. Ordenamos de mayor a menor según la ganancia y tomamos el Top 5
        $top5 = $ranking->sortByDesc('ganancia_neta')->take(5)->values();

        return response()->json([
            'message' => 'Ranking del mes obtenido exitosamente',
            'mes' => Carbon::now()->locale('es')->monthName, // Devuelve "abril"
            'data' => $top5
        ], 200);
    }

    /**
     * TOP PRODUCTOS/SERVICIOS DEL MES (GET)
     * Qué es lo que más se vende y cuánto dinero está dejando.
     */
    public function getTopProducts(Request $request)
    {
        if (!in_array($request->user()->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        // 1. Buscamos TODOS los productos de la base de datos
        // Y cargamos SÓLO las ventas (amounts) que se hicieron este mes
        $productos = Product::with(['amounts' => function ($query) use ($mesActual, $anioActual) {
            $query->whereMonth('created_at', $mesActual)
                  ->whereYear('created_at', $anioActual);
        }])->get();

        // 2. Mapeamos la colección para calcular totales
        $ranking = $productos->map(function ($producto) {
            return [
                'id'                 => $producto->id,
                'name'               => $producto->name,
                'photo'              => $producto->photo,
                'total_vendidos'     => $producto->amounts->count(),
                'ingresos_generados' => round($producto->amounts->sum('amount'), 2)
            ];
        });

        // 3. Filtramos los que tienen 0 ventas, ordenamos por los más vendidos y sacamos el Top 5
        $top5 = $ranking->filter(function ($prod) {
                            return $prod['total_vendidos'] > 0;
                        })
                        ->sortByDesc('total_vendidos')
                        ->take(5)
                        ->values();

        return response()->json([
            'message' => 'Top productos del mes obtenidos exitosamente',
            'mes'     => Carbon::now()->locale('es')->monthName,
            'data'    => $top5
        ], 200);
    }

    /**
     * GRÁFICO DE INGRESOS DE LOS ÚLTIMOS 7 DÍAS (GET)
     * Devuelve un arreglo agrupado por días para graficar.
     */
    public function getRevenueChart(Request $request)
    {
        if (!in_array($request->user()->role->slug, ['admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        // Definimos el rango: Desde hace 6 días atrás hasta hoy (7 días en total)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);

        // 1. Buscamos TODOS los cobros de esos 7 días
        $amounts = Amount::with('product')
                         ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                         ->get();

        // 2. Preparamos un arreglo base con los 7 días en 0 (por si un día no hubo ventas)
        $chartData = [];
        for ($i = 0; $i < 7; $i++) {
            // Obtenemos el día (ej: "lunes", "martes") en español
            $date = Carbon::now()->subDays((6 - $i));
            $dayName = ucfirst($date->locale('es')->dayName); 
            $dateString = $date->format('Y-m-d');

            $chartData[$dateString] = [
                'dia' => $dayName,
                'fecha' => $dateString,
                'ingresos_brutos' => 0,
                'ganancia_neta' => 0
            ];
        }

        // 3. Llenamos el arreglo con la plata real
        foreach ($amounts as $cobro) {
            $dateString = Carbon::parse($cobro->created_at)->format('Y-m-d');
            
            // Si la fecha coincide con nuestro rango (siempre debería)
            if (isset($chartData[$dateString])) {
                // Sumamos el Bruto
                $chartData[$dateString]['ingresos_brutos'] += $cobro->amount;

                // Calculamos y sumamos el Neto
                $costo = ($cobro->product_id && $cobro->product) ? $cobro->product->cost : 0;
                $chartData[$dateString]['ganancia_neta'] += ($cobro->amount - $costo);
            }
        }

        // 4. Formateamos los números a 2 decimales y quitamos las llaves de fecha para que React lo lea como un array simple
        $finalData = array_map(function ($day) {
            return [
                'dia' => $day['dia'],
                'fecha' => $day['fecha'],
                'ingresos_brutos' => round($day['ingresos_brutos'], 2),
                'ganancia_neta' => round($day['ganancia_neta'], 2),
            ];
        }, array_values($chartData));

        return response()->json([
            'message' => 'Datos para el gráfico obtenidos exitosamente',
            'data'    => $finalData
        ], 200);
    }

    /**
     * KPIs DEL EMPLEADO / BARBERO (GET)
     * Resumen personal de su trabajo y sus comisiones.
     */
    public function getBarberKpis(Request $request)
    {
        $user = $request->user();

        // 1. OBTENEMOS LO DE HOY
        $today = Carbon::today();
        
        $misCobrosHoy = Amount::with('product')
                              ->where('user_id', $user->id)
                              ->whereDate('created_at', $today)
                              ->get();

        $misCortesHoy = $misCobrosHoy->count();
        $miComisionHoy = 0;
        $miPorcentaje = $user->commission / 100;

        // Calculamos cuánto me gané HOY (solo mi parte)
        foreach ($misCobrosHoy as $cobro) {
            if ($cobro->product_id && $cobro->product) {
                $gananciaReal = $cobro->amount - $cobro->product->cost;
                if ($gananciaReal < 0) { $gananciaReal = 0; }
                $miComisionHoy += ($gananciaReal * $miPorcentaje);
            } else {
                $miComisionHoy += ($cobro->amount * $miPorcentaje);
            }
        }

        // 2. OBTENEMOS LA DEUDA PENDIENTE
        // ¿Cuánto dinero he generado que el jefe aún no me ha pagado?
        $misCobrosPendientes = Amount::with('product')
                                     ->where('user_id', $user->id)
                                     ->whereNull('payment_id')
                                     ->get();

        $miDeudaPendiente = 0;

        foreach ($misCobrosPendientes as $cobro) {
            if ($cobro->product_id && $cobro->product) {
                $gananciaReal = $cobro->amount - $cobro->product->cost;
                if ($gananciaReal < 0) { $gananciaReal = 0; }
                $miDeudaPendiente += ($gananciaReal * $miPorcentaje);
            } else {
                $miDeudaPendiente += ($cobro->amount * $miPorcentaje);
            }
        }

        return response()->json([
            'message' => 'KPIs personales obtenidos',
            'data' => [
                'mis_cortes_hoy'         => $misCortesHoy,
                'mi_comision_hoy'        => round($miComisionHoy, 2),
                'dinero_pendiente_cobro' => round($miDeudaPendiente, 2),
                'mi_porcentaje'          => $user->commission . '%' // Para recordarle al barbero su trato
            ]
        ], 200);
    }

    /**
     * GRÁFICO DE PROGRESO DEL EMPLEADO (GET)
     * Devuelve las comisiones generadas por el empleado en los últimos 7 días.
     */
    public function getBarberChart(Request $request)
    {
        $user = $request->user();
        $miPorcentaje = $user->commission / 100;

        // Definimos el rango de 7 días
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);

        // 1. Buscamos SOLO los cobros de este usuario en la última semana
        $misCobros = Amount::with('product')
                           ->where('user_id', $user->id)
                           ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                           ->get();

        // 2. Preparamos el arreglo con los 7 días en 0
        $chartData = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays((6 - $i));
            $dayName = ucfirst($date->locale('es')->dayName);
            $dateString = $date->format('Y-m-d');

            $chartData[$dateString] = [
                'dia'         => $dayName,
                'fecha'       => $dateString,
                'mi_comision' => 0
            ];
        }

        // 3. Llenamos el arreglo con las comisiones reales
        foreach ($misCobros as $cobro) {
            $dateString = Carbon::parse($cobro->created_at)->format('Y-m-d');
            
            if (isset($chartData[$dateString])) {
                if ($cobro->product_id && $cobro->product) {
                    $gananciaReal = $cobro->amount - $cobro->product->cost;
                    if ($gananciaReal < 0) { $gananciaReal = 0; }
                    
                    // Sumamos solo la tajada del barbero
                    $chartData[$dateString]['mi_comision'] += ($gananciaReal * $miPorcentaje);
                } else {
                    $chartData[$dateString]['mi_comision'] += ($cobro->amount * $miPorcentaje);
                }
            }
        }

        // 4. Formateamos la salida para React
        $finalData = array_map(function ($day) {
            return [
                'dia'         => $day['dia'],
                'fecha'       => $day['fecha'],
                'mi_comision' => round($day['mi_comision'], 2),
            ];
        }, array_values($chartData));

        return response()->json([
            'message' => 'Gráfico de progreso personal obtenido',
            'data'    => $finalData
        ], 200);
    }
}