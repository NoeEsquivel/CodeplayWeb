<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EliminarUsuariosInactivos extends Command
{
    protected $signature = 'usuarios:eliminar-inactivos';

    protected $description = 'Elimina usuarios alumnos que no han iniciado sesión en más de 6 meses';

    public function handle()
    {
        $fechaLimite = Carbon::now()->subMonths(6);

        // SOLO eliminamos alumnos que no han accedido en 6 meses
        $usuariosEliminados = Usuario::where('rol', 'Alumno')
            ->where(function ($query) use ($fechaLimite) {
                // Usuarios que SI han accedido pero hace más de 6 meses
                $query->where('ultimo_acceso', '<', $fechaLimite)
                      // O usuarios que NUNCA han accedido y fueron creados hace más de 6 meses
                    ->orWhere(function ($q) use ($fechaLimite) {
                        $q->whereNull('ultimo_acceso')
                            ->where('created_at', '<', $fechaLimite);
                    });
            })
            ->delete();

        $mensaje = "Limpieza automática ejecutada: {$usuariosEliminados} alumnos eliminados (sin acceso en 6 meses)";

        $this->info($mensaje);
        Log::info($mensaje);

        return Command::SUCCESS;
    }
}
