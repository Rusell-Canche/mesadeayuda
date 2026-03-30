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
            'asignado_a' => 'nullable|integer|exists:users,id',
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
        $tickets = Ticket::with(['prioridad', 'categoria', 'user', 'asignadoA'])->get();
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

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        // (opcional) solo permitir eliminar si está atendido
        if ($ticket->estado !== 'Atendido') {
            return response()->json([
                'error' => 'Solo se pueden eliminar tickets atendidos'
            ], 400);
        }

        $ticket->delete(); // Esto hará un soft delete gracias a SoftDeletes en el modelo

        return response()->json([
            'message' => 'Ticket enviado a papelera'
        ]);
    }

    public function eliminados()
    {
        $tickets = Ticket::onlyTrashed()
            ->with(['prioridad', 'categoria', 'user'])
            ->get();

        return response()->json($tickets);
    }

    public function restore($id)
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);
        $ticket->restore();

        return response()->json([
            'message' => 'Ticket restaurado'
        ]);
    }

    public function editar(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->asunto       = $request->asunto;
        $ticket->prioridad_id = $request->prioridad_id;
        $ticket->categoria_id = $request->categoria_id;
        $ticket->contenido    = $request->contenido;

        if ($request->hasFile('archivo')) {
            // Elimina el anterior si existe
            if ($ticket->archivo_path) {
                Storage::disk('public')->delete($ticket->archivo_path);
            }
            $ticket->archivo_path = $request->file('archivo')
                ->store('tickets', 'public');
        }

        $ticket->save();

        // Retorna con relaciones para actualizar la tabla
        return response()->json($ticket->load('prioridad', 'categoria'));
    }

    public function usuariosTIC()
    {
        $usuarios = \App\Models\User::whereHas('departamento', function ($q) {
            $q->where('nombre', 'DEPARTAMENTO DE TECNOLOGÍAS DE LA INFORMACIÓN Y COMUNICACIONES');
        })
            ->where(function ($q) {
                // roles es un campo JSON, usamos operador JSON de MySQL
                $q->whereJsonContains('roles', 'administrador')
                    ->orWhereJsonContains('roles', 'root');
            })
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        return response()->json($usuarios);
    }

    public function misTickets()
    {
        $tickets = Ticket::with(['prioridad', 'categoria', 'user', 'asignadoA'])
            ->where('asignado_a', Auth::id())
            ->get();

        return response()->json($tickets);
    }
}
