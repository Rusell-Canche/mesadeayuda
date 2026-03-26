<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use MongoDB\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf; // importante para usar DomPDF


class TicketController extends Controller
{

    public function store(Request $request)
    {
        // Validar los datos, ahora prioridad_id y categoria_id son enteros que existen en sus tablas
        $validatedData = $request->validate([
            'capturador' => 'required|string',
            'cargo' => 'nullable|string',
            'asunto' => 'required|string',
            'prioridad_id' => 'required|integer|exists:prioridades,id',
            'unidad_administrativa' => 'required|string',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'contenido' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB
        ]);

        try {
            // Guardar archivo si se cargó
            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $path = $archivo->store('tickets/archivos', 'public');
                $validatedData['archivo_path'] = $path;
            }

            // Estado inicial por defecto
            $validatedData['estado'] = 'Abierto';

            // ID del usuario autenticado
            $validatedData['user_id'] = Auth::id();

            // Crear el ticket
            $ticket = Ticket::create($validatedData);

            return response()->json([
                'message' => 'Ticket creado con éxito',
                'data' => $ticket
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $tickets = Ticket::with(['prioridad', 'categoria', 'user'])->get();
        return response()->json($tickets);
    }



    public function marcarComoAtendido(Request $request, $id)
    {
        $request->validate([
            'quien_atendio' => 'required|string|max:255',
            'solucion' => 'nullable|string|max:1000', // un poco más largo por si la solución es extensa
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->estado = 'Atendido';
        $ticket->fecha_cierre = now();
        $ticket->quien_atendio = $request->input('quien_atendio');
        $ticket->solucion = $request->input('solucion');

        // 1️⃣ Generar el PDF del oficio
        $pdf = Pdf::loadView('oficios.ticket_atendido', [
            'ticket' => $ticket
        ]);

        // 2️⃣ Guardar el PDF en storage
        $fileName = 'oficios/oficio_ticket_' . $ticket->id . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // 3️⃣ Guardar la ruta en el ticket
        $ticket->oficio_atendido = 'storage/' . $fileName;

        // 4️⃣ Guardar cambios
        $ticket->save();

        return response()->json([
            'message' => 'Ticket marcado como atendido y oficio generado',
            'fecha_cierre' => $ticket->fecha_cierre->toDateTimeString(),
            'quien_atendio' => $ticket->quien_atendio,
            'solucion' => $ticket->solucion,
            'oficio_atendido' => $ticket->oficio_atendido
        ]);
    }
}
