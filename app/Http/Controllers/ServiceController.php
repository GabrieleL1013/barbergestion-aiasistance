<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        // Devolvemos todos los servicios que estén activos
        $services = Service::where('is_active', true)->get();
        return response()->json($services);
    }
}