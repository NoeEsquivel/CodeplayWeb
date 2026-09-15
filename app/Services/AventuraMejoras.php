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

    private static function lista(AventuraExpedicion|array $run): array
    {
        return $run instanceof AventuraExpedicion ? ($run->mejoras ?? []) : ($run['mejoras'] ?? []);
    }

    public static function valor(AventuraExpedicion|array $run, string $tipo): int
    {
        $limites = ['arma_dano'=>25, 'armadura_refuerzo'=>12];
        $limite = self::TIPOS[$tipo][2] ?? $limites[$tipo] ?? 0;
        return min($limite, array_sum(array_map(fn($m)=>($m['tipo'] ?? '') === $tipo ? (int)$m['valor'] : 0,self::lista($run))));
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

    public static function mejorarEquipo(AventuraExpedicion $run, string $tipo): string
    {
        $definiciones = [
            'arma_dano'=>['Filo calibrado', 5, 25, 'Tu arma gana +%d de daño base en cada ataque físico.'],
            'armadura_refuerzo'=>['Placas reforzadas', 2, 12, 'Tu armadura absorbe %d de daño directo adicional por golpe.'],
        ];
        [$nombre,$paso,$limite,$texto] = $definiciones[$tipo];
        $actual = self::valor($run,$tipo);
        if ($actual >= $limite) { $run->oro += 15; return 'Tu '.$nombre.' ya alcanzó su límite. Recibes 15 de oro en su lugar.'; }
        $nuevo = min($limite,$actual+$paso);
        $mejoras = array_values(array_filter($run->mejoras ?? [],fn($m)=>($m['tipo'] ?? '') !== $tipo));
        $mejoras[] = ['tipo'=>$tipo,'nombre'=>$nombre,'valor'=>$nuevo,'descripcion'=>sprintf($texto,$nuevo)];
        $run->mejoras = $mejoras;
        return $nombre.': '.sprintf($texto,$nuevo).' Dura hasta terminar esta partida.';
    }

    public static function valorHechizo(AventuraExpedicion|array $run, string $hechizo): int
    {
        return min(30, array_sum(array_map(fn($m)=>(($m['tipo'] ?? '') === 'maestria_hechizo' && ($m['hechizo'] ?? '') === $hechizo) ? (int)$m['valor'] : 0,self::lista($run))));
    }

    public static function nivelHechizo(AventuraExpedicion|array $run, string $hechizo): int
    {
        return 1 + intdiv(self::valorHechizo($run,$hechizo),10);
    }

    public static function mejorarHechizo(AventuraExpedicion $run, string $hechizo): string
    {
        $actual = self::valorHechizo($run,$hechizo);
        $catalogo = (new AventuraCatalogo())->items();
        $nombre = $catalogo[$hechizo]['nombre'] ?? 'Hechizo';
        if ($actual >= 30) { $run->oro += 15; return $nombre.' ya alcanzó el nivel máximo. Recibes 15 de oro en su lugar.'; }
        $nuevo = min(30,$actual+10);
        $mejoras = array_values(array_filter($run->mejoras ?? [],fn($m)=>!(($m['tipo'] ?? '') === 'maestria_hechizo' && ($m['hechizo'] ?? '') === $hechizo)));
        $nivel = 1 + intdiv($nuevo,10);
        $mejoras[] = ['tipo'=>'maestria_hechizo','hechizo'=>$hechizo,'nombre'=>$nombre.' Nv. '.$nivel,'valor'=>$nuevo,'descripcion'=>'Daño, curación o protección del hechizo: +'.$nuevo.'%.'];
        $run->mejoras = $mejoras;
        return $nombre.' sube a nivel '.$nivel.': sus efectos ganan +10% durante esta aventura.';
    }
}
