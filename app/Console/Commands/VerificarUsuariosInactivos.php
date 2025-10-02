<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Carbon\Carbon;

class VerificarUsuariosInactivos extends Command
{
    protected $signature = 'usuarios:verificar-inactivos';
    protected $description = 'Verifica usuarios alumnos que no han iniciado sesión en más de 6 meses';

    public function handle()
    {
        $fechaLimite = Carbon::now()->subMonths(6);
        
        $usuariosInactivos = Usuario::where('rol', 'Alumno')
            ->where(function($query) use ($fechaLimite) {
                $query->where('ultimo_acceso', '<', $fechaLimite)
                      ->orWhere(function($q) use ($fechaLimite) {
                          $q->whereNull('ultimo_acceso')
                            ->where('created_at', '<', $fechaLimite);
                      });
            })
            ->get(['id', 'nombre', 'rol', 'ultimo_acceso', 'created_at']);

        $this->info("=== USUARIOS QUE SERÁN ELIMINADOS ===");
        $this->info("Fecha límite: " . $fechaLimite->format('Y-m-d H:i:s'));
        $this->info("Total de usuarios inactivos: " . $usuariosInactivos->count());
        
        if ($usuariosInactivos->count() > 0) {
            $this->info("\nDetalles:");
            foreach ($usuariosInactivos as $usuario) {
                // ✅ CORREGIDO: Convertir a Carbon si es string
                $ultimoAcceso = $usuario->ultimo_acceso 
                    ? Carbon::parse($usuario->ultimo_acceso)->format('Y-m-d H:i:s')
                    : 'NUNCA (creado: ' . Carbon::parse($usuario->created_at)->format('Y-m-d') . ')';
                
                $this->line("ID: {$usuario->id} | Nombre: {$usuario->nombre} | Rol: {$usuario->rol} | Último acceso: {$ultimoAcceso}");
            }
        } else {
            $this->info("No hay usuarios inactivos para eliminar.");
        }

        return Command::SUCCESS;
    }
}