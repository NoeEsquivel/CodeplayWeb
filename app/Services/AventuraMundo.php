<?php

namespace App\Services;

class AventuraMundo
{
    public const PROBABILIDADES = ['tesoro' => 3, 'narrativa' => 27, 'gremio' => 20, 'enemigo' => 50];

    public static function regiones(): array
    {
        return [
            ['id' => 1, 'nombre' => 'Bosque Inicial', 'tema' => 'bosque', 'subtitulo' => 'Donde nace el valor', 'desde' => 1, 'hasta' => 10,
                'relato' => 'La luz se filtra entre las hojas. Un sendero olvidado despierta bajo tus pasos y, a lo lejos, las ramas parecen guardar una conversación.',
                'caminos' => [['Helechos luminosos', 'Una luz suave tiembla entre las hojas.'], ['Sendero del arroyo', 'El agua acompaña tus pasos, hasta que deja de oírse.'], ['Colina de los robles', 'Desde lo alto llega un crujido de madera.']]],
            ['id' => 2, 'nombre' => 'Ruinas Antiguas', 'tema' => 'ruinas', 'subtitulo' => 'La memoria de la piedra', 'desde' => 11, 'hasta' => 20,
                'relato' => 'Las columnas sostienen un cielo vacío. Bajo la arena, las inscripciones de una civilización perdida esperan a quien sepa interpretarlas.',
                'caminos' => [['Galería del eco', 'Tus pasos regresan con un sonido diferente.'], ['Plaza de las runas', 'Una inscripción aún brilla sobre la piedra.'], ['Acueducto roto', 'El viento atraviesa los arcos derrumbados.']]],
            ['id' => 3, 'nombre' => 'Pantano Maldito', 'tema' => 'pantano', 'subtitulo' => 'Nada es lo que parece', 'desde' => 21, 'hasta' => 30,
                'relato' => 'La niebla dibuja siluetas sobre el agua. Aquí conviene observar antes de actuar: incluso las luces más amables pueden esconder un secreto.',
                'caminos' => [['Pasarela hundida', 'Quedan algunas tablas firmes sobre el agua.'], ['Islote de las luces', 'Pequeños destellos se apartan al acercarte.'], ['Raíces retorcidas', 'Entre las raíces hay huellas recientes.']]],
            ['id' => 4, 'nombre' => 'Montañas Heladas', 'tema' => 'hielo', 'subtitulo' => 'Más allá de la tormenta', 'desde' => 31, 'hasta' => 40,
                'relato' => 'El aire se vuelve cristal. Cada paso deja una marca en la nieve; cada decisión te acerca a una cumbre que parecía imposible desde el valle.',
                'caminos' => [['Paso del glaciar', 'Una corriente de aire recorre el hielo azul.'], ['Refugio de piedra', 'Hay marcas en la nieve junto a un muro.'], ['Cresta del viento', 'Las nubes ocultan el final de la subida.']]],
            ['id' => 5, 'nombre' => 'Tierras del Dragón', 'tema' => 'dragon', 'subtitulo' => 'El último horizonte', 'desde' => 41, 'hasta' => 50,
                'relato' => 'La ceniza cae despacio sobre un mundo encendido. Detrás de las montañas negras respira el dragón. Todo lo aprendido te ha traído hasta aquí.',
                'caminos' => [['Puente de obsidiana', 'Bajo tus pies, la roca conserva el calor.'], ['Valle de ceniza', 'El silencio se interrumpe con un rumor lejano.'], ['Ladera escarlata', 'Una sombra cruza el resplandor de la cima.']]],
        ];
    }

    public static function region(int $escenario): array
    {
        if ($escenario < 1 || $escenario > 50) throw new \InvalidArgumentException('Escenario fuera del mundo.');
        return self::regiones()[intdiv($escenario - 1, 10)];
    }

    public static function tipo(int $escenario, int $tirada): string
    {
        self::region($escenario);
        if ($tirada < 1 || $tirada > 100) throw new \InvalidArgumentException('Tirada fuera de rango.');
        if ($escenario % 10 === 0) return $escenario === 50 ? 'dragon' : 'jefe';
        $limite = 0;
        foreach (self::PROBABILIDADES as $tipo => $peso) {
            $limite += $peso;
            if ($tirada <= $limite) return $tipo;
        }
        throw new \LogicException('Probabilidades incompletas.');
    }

    public static function encuentro(string $tipo): array
    {
        return match ($tipo) {
            'tesoro' => ['titulo' => 'Un brillo entre las sombras', 'texto' => 'Un cofre descansa junto al sendero. Te acercas con cuidado: todavía no sabes qué guarda.'],
            'narrativa' => ['titulo' => 'Una historia en tu camino', 'texto' => 'Alguien necesita ayuda. Te detienes a escuchar: a veces, la mejor herramienta es lo que has aprendido.'],
            'gremio' => ['titulo' => 'Bajo el emblema del gremio', 'texto' => 'Una bandera señala el refugio de los aventureros. En la entrada hay un tablón cubierto de encargos.'],
            'enemigo' => ['titulo' => 'No estás solo', 'texto' => 'Algo se mueve frente a ti. Aseguras tu equipo y observas, esperando el momento de actuar.'],
            'jefe' => ['titulo' => 'El guardián de la región', 'texto' => 'Los caminos se unen ante una presencia imponente. Para cruzar esta frontera tendrás que enfrentarte a su guardián.'],
            'dragon' => ['titulo' => 'Ante el Dragón', 'texto' => 'Dos ojos se abren entre el fuego. El último guardián despliega sus alas y el mundo parece contener la respiración.'],
            default => throw new \InvalidArgumentException('Encuentro desconocido.'),
        };
    }
}
