<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prioridad;


class PrioridadController extends Controller
{

     public function index()
    {
        $prioridades = Prioridad::all();
        return response()->json($prioridades);
    }


public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:100',
        'color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'], // validar color hexadecimal
    ]);

    Prioridad::create([
        'nombre' => $request->nombre,
        'color' => $request->color,
    ]);

    return response()->json(['message' => 'Prioridad creada con éxito']);
}

}
