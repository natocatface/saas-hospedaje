<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class LandingController extends Controller
{
    public function index()
    {
        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();
        return view('landing', compact('planes'));
    }
}
