<?php

namespace App\Console\Commands;

use App\Models\TestSesion;
use App\Services\ClassroomExamLifecycleService;
use Illuminate\Console\Command;
use Throwable;

class SincronizarExamenesClase extends Command
{
    protected $signature = 'tests:sincronizar-examenes';
    protected $description = 'Inicia y cierra automáticamente los exámenes de clase según su programación';

    public function handle(ClassroomExamLifecycleService $lifecycle): int
    {
        $processed = 0;
        TestSesion::query()->where('tipo', 'clase')->where('modalidad', 'examen')
            ->whereIn('estado', ['espera', 'activa'])->orderBy('id')
            ->chunkById(100, function ($sessions) use ($lifecycle, &$processed) {
                foreach ($sessions as $session) {
                    try {
                        $lifecycle->synchronize($session);
                        $processed++;
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            });

        $this->info("Exámenes sincronizados: {$processed}");
        return self::SUCCESS;
    }
}
