<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tickets';

    protected $fillable = [
        'capturador',
        'cargo',
        'asunto',
        'prioridad_id',
        'unidad_administrativa',
        'categoria_id',
        'contenido',
        'archivo_path',
        'estado',
        'user_id',
    ];

    // Si tienes relación con usuarios:
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function prioridad()
    {
        return $this->belongsTo(Prioridad::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
}
