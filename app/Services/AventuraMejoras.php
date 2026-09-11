<?php

namespace App\Services;

use App\Models\AventuraExpedicion;

class AventuraMejoras
{
    public const TIPOS = [
        'dano'=>['Impulso del ingenio',10,30,'Daño de ataques y hechizos'],
        'vida'=>['Corazón del bosque',10,30,'Vida máxima'],
        'robo_vida'=>['Vínculo vital',5,15,'Recuperas vida según el daño efectivo'],
        'critico'=>['Ojo del depurador',5,15,'Probabilidad de crítico: daño ×1.5'],
        'regeneracion'=>['Descanso del viajero',5,15,'Recuperas un porcentaje de vida tras vencer'],
        'experiencia'=>['Curiosidad incansable',10,30,'Experiencia adicional'],
        'resistencia'=>['Piel de runas',5,20,'Reducción del daño directo recibido'],
    ];

    public static function valor(AventuraExpedicion $run, string $tipo): int
    {
        return min(self::TIPOS[$tipo][2], array_sum(array_map(fn($m)=>($m['tipo'] ?? '') === $tipo ? (int)$m['valor'] : 0,$run->mejoras ?? [])));
    }

    public static function agregar(AventuraExpedicion $run, string $tipo): string
    {
        [$nombre,$paso,$limite,$descripcion] = self::TIPOS[$tipo];
        $valor = self::valor($run,$tipo);
        if ($valor >= $limite) { $run->oro += 15; return 'Ya dominabas '.$nombre.'. Recibes 15 de oro en su lugar.'; }
        $mejoras = array_values(array_filter($run->mejoras ?? [],fn($m)=>$m['tipo'] !== $tipo));
        $mejoras[] = ['tipo'=>$tipo,'nombre'=>$nombre,'valor'=>min($limite,$valor+$paso),'descripcion'=>$descripcion];
        $run->mejoras = $mejoras;
        return $nombre.': +'.min($paso,$limite-$valor).'% · '.$descripcion.'. Dura hasta terminar esta partida.';
    }

    public static function vida(AventuraExpedicion $run, int $base): int
    {
        return (int)ceil($base*(100+self::valor($run,'vida'))/100);
    }

    public static function experiencia(AventuraExpedicion $run, int $base): int
    {
        return (int)ceil($base*(100+self::valor($run,'experiencia'))/100);
    }
}
