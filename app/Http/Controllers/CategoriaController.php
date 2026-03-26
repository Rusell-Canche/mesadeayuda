<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\Ticket;

class CategoriaController extends Controller
{
    public function index()
{
    $categorias = Categoria::all();
    return response()->json($categorias);
}
    // Guardar una nueva categoría
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre',
        ]);

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Categoría creada con éxito', 'data' => $categoria], 201);
    }

    // Opcional: borrar categoría
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada']);
    }
}
