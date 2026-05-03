<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    public function show()
    {
        
        $profile = BusinessProfile::first();

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        return response()->json($profile, 200);
    }
}