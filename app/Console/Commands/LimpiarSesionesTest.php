<?php

namespace App\Console\Commands;

use App\Services\LiveTestCleanupService;
use Illuminate\Console\Command;

class LimpiarSesionesTest extends Command
{
    protected $signature = 'tests:limpiar-sesiones
        {--inactividad=24 : Horas sin actividad antes de cerrar una sala abandonada}
        {--retencion=365 : Días antes de caducar códigos y tokens de una sesión terminada}';

    protected $description = 'Cierra sesiones de test abandonadas y caduca credenciales antiguas sin borrar el historial';

    public function handle(LiveTestCleanupService $cleanup): int
    {
        $result = $cleanup->clean((int) $this->option('inactividad'), (int) $this->option('retencion'));
        $this->info("Sesiones abandonadas cerradas: {$result['cerradas']}");
        $this->info("Códigos antiguos caducados: {$result['codigos_caducados']}");
        return self::SUCCESS;
    }
}
