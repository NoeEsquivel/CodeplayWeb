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
        if ($escenario < 1 || $escenario > 50) {
            throw new \InvalidArgumentException('Escenario fuera del mundo.');
        }

        return self::regiones()[intdiv($escenario - 1, 10)];
    }

    /** Relatos ligados a cada rincón del Bosque Inicial, no a una ruta genérica. */
    public static function caminosBosque(int $escenario): array
    {
        return match ($escenario) {
            1 => [['El campamento', 'Tu primera parada: reúne valor junto al fuego antes de internarte en el bosque.']],
            2 => [['Arboleda cerrada', 'Los troncos antiguos forman un pasillo silencioso.'], ['Rama del río', 'El agua se estrecha entre raíces y flores azules.'], ['Claro de los ciervos', 'Algo se mueve donde el sol toca la hierba.']],
            3 => [['Orilla de cristales', 'Las corrientes dejan pequeñas piedras que brillan.'], ['Puente de madera', 'Un tablón viejo cruje sobre el agua viva.'], ['Sendero de la ribera', 'El cauce desaparece detrás de un velo de musgo.']],
            4 => [['Arco derrumbado', 'Una puerta de piedra cubierta de enredaderas sigue en pie.'], ['Escaleras rúnicas', 'Los peldaños hundidos conservan marcas de luz.'], ['Corredor de robles', 'Los robles rodean las ruinas como guardianes pacientes.']],
            5 => [['Cabaña vacía', 'Una lámpara apagada se balancea frente a la puerta.'], ['Carro abandonado', 'Un carro roto señala un atajo cubierto de ortigas.'], ['Cercado del cazador', 'Tras la valla caída, el bosque guarda un silencio extraño.']],
            6 => [['Puente de la cascada', 'La bruma cubre el paso y esconde el fondo del barranco.'], ['Escalones húmedos', 'Una senda de roca asciende junto al estruendo del agua.'], ['Mirador del salto', 'Desde la cornisa se ve el río dividirse en dos.']],
            7 => [['Santuario de musgo', 'Una pequeña hornacina de piedra espera entre los helechos.'], ['Raíces luminosas', 'Las raíces emiten una luz suave bajo la tierra.'], ['Rastro de luciérnagas', 'Pequeños destellos te invitan a internarte en el claro.']],
            8 => [['Monolito partido', 'Una piedra marcada por el tiempo vigila el sendero.'], ['Paso del tronco caído', 'Un gigante del bosque forma un puente sobre la quebrada.'], ['Vereda de las huellas', 'Marcas recientes avanzan hacia las tierras altas.']],
            9 => [['Tótems de raíces', 'Los símbolos tallados parecen observar tu llegada.'], ['Círculo de piedras', 'El aire vibra con una energía que no sabes nombrar.'], ['Senda del guardián', 'Las ramas se apartan como si te concedieran el paso.']],
            10 => [['Entrar al claro de Silvar', 'Solo queda un sendero. Al final, el Guardián de las Raíces aguarda.']],
            default => self::regiones()[0]['caminos'],
        };
    }

    /** Rutas inspiradas en los hitos visibles del mapa de Ruinas Antiguas. */
    public static function caminosRuinas(int $escenario): array
    {
        return match ($escenario) {
            11 => [['La entrada de los archivos', 'Una escalinata de piedra baja hacia los restos de una civilización olvidada.']],
            12 => [['Mercado sepultado', 'Telas rotas y ánforas vacías asoman entre las dunas.'], ['Pozo de las palmeras', 'Un hilo de agua protege un breve oasis entre las ruinas.'], ['Obelisco caído', 'Símbolos azules palpitan bajo la arena acumulada.']],
            13 => [['Puente del vado', 'El agua turquesa divide las ruinas frente a una pasarela antigua.'], ['Pirámide enterrada', 'Una entrada oscura se abre al pie de una pirámide menor.'], ['Columnas del mediodía', 'Las sombras de los arcos señalan un paso seguro hacia el norte.']],
            14 => [['Baños ceremoniales', 'Una alberca de piedra refleja jeroglíficos que nadie ha leído.'], ['Galería de estatuas', 'Guardianes sin rostro vigilan un corredor cubierto de arena.'], ['Escalera de basalto', 'Peldaños erosionados suben hacia una terraza derruida.']],
            15 => [['Caravana perdida', 'Un carro de carga y cofres rotos yacen junto a una puerta de piedra.'], ['Templo de la brújula', 'Un mecanismo dorado gira lentamente bajo el sol.'], ['Arco de los vigías', 'Tres columnas aún sostienen un mirador sobre el desierto.']],
            16 => [['Observatorio solar', 'Anillos de bronce apuntan al cielo como si esperaran una señal.'], ['Piramide de la luz azul', 'Una grieta brillante marca el costado de la gran pirámide.'], ['Pasaje de los obeliscos', 'Las agujas de piedra forman un corredor de destellos turquesa.']],
            17 => [['Santuario del anj', 'Una puerta iluminada guarda el símbolo de una vida que no termina.'], ['Cascada oculta', 'El agua baja por el cañón y desaparece bajo los pilares.'], ['Jardín de las palmeras', 'Un oasis silencioso esconde estatuas entre sus frondas.']],
            18 => [['Esfinge de arena', 'El rostro de piedra observa el desierto desde hace milenios.'], ['Pirámide del eco', 'Cada paso junto a sus muros regresa convertido en un susurro.'], ['Canal de piedra', 'Una zanja antigua acompaña el curso del agua hacia las alturas.']],
            19 => [['Puerta del santuario', 'Antorchas encendidas marcan la ruta hacia las ruinas más antiguas.'], ['Terraza de los faraones', 'Los monolitos cubiertos de runas rodean una plaza ceremonial.'], ['Puente del horizonte', 'Una pasarela elevada conduce al último recinto.']],
            20 => [['Entrar al santuario de Aster', 'Solo queda una ruta. El Guardián de los Archivos espera entre las llamas rituales.']],
            default => self::regiones()[1]['caminos'],
        };
    }

    /** Rutas inspiradas en los hitos visibles del mapa del Pantano Maldito. */
    public static function caminosPantano(int $escenario): array
    {
        return match ($escenario) {
            21 => [['Atravesar las marismas', 'Una única pasarela de madera se interna entre la niebla y las aguas quietas.']],
            22 => [['Bote varado', 'Una embarcación sin remos espera junto a un muelle que cruje.'], ['Muelles de las sombras', 'Las estacas hundidas forman un paso estrecho sobre el agua oscura.'], ['Hongos de luz fría', 'Un brillo azul palpita bajo las raíces de los manglares.']],
            23 => [['Puente de raíces', 'Raíces entrelazadas sostienen un paso sobre una corriente turbia.'], ['Santuario hundido', 'Un arco de piedra emerge del agua con velas aún encendidas.'], ['Canal de niebla', 'La bruma cubre una acequia antigua que conduce al interior.']],
            24 => [['Arco sumergido', 'Una puerta erosionada sobresale de la ciénaga como una advertencia.'], ['Estacas del ahogado', 'Postes inclinados señalan un recorrido que evita el fango profundo.'], ['Isla de los susurros', 'Las luciérnagas verdes bailan sobre un pequeño islote.']],
            25 => [['Embarcadero derruido', 'Tablas quebradas conducen hacia una choza abandonada entre los juncos.'], ['Osario anegado', 'Huesos antiguos asoman bajo una capa de agua inmóvil.'], ['Cabaña de las velas', 'Una luz ámbar tiembla tras una ventana rota.']],
            26 => [['Torre de vigilancia', 'Una estructura de madera vigila el pantano desde una plataforma elevada.'], ['Pantano de luciérnagas', 'Cientos de luces flotan en silencio sobre un remanso oscuro.'], ['Pasarela de los sauces', 'Las ramas muertas se cierran sobre una senda de tablones húmedos.']],
            27 => [['Capilla inundada', 'Las ruinas de un templo se reflejan bajo el agua ennegrecida.'], ['Raíces que vigilan', 'Los manglares forman siluetas que parecen seguir cada paso.'], ['Barca sin remos', 'Una vieja barca ofrece un atajo junto a los pilares rotos.']],
            28 => [['Cabeza de piedra', 'Un rostro colosal medio sumergido observa desde la maleza.'], ['Bosque de manglares', 'Los troncos retorcidos forman un laberinto de sombras y hongos azules.'], ['Escalera de la niebla', 'Peldaños de piedra ascienden hacia aguas cada vez más silenciosas.']],
            29 => [['Ruinas del rito', 'Antiguos altares y campanas oxidadas marcan el último tramo.'], ['Puente de los espectros', 'La pasarela elevada atraviesa luces que no proyectan sombra.'], ['Círculo de la Náyade', 'Una corriente luminosa rodea el santuario del guardián.']],
            30 => [['Entrar al santuario de Náyade', 'Solo queda una ruta. La Guardiana de las Aguas Sin Nombre aguarda entre las ruinas inundadas.']],
            default => self::regiones()[2]['caminos'],
        };
    }

    /** Rutas inspiradas en los hitos visibles del mapa de Montañas Heladas. */
    public static function caminosHielo(int $escenario): array
    {
        return match ($escenario) {
            31 => [['Aldea del paso blanco', 'Una única senda abandona los hogares encendidos y se interna entre la nieve.']],
            32 => [['Puente de escarcha', 'Un arco congelado cruza el río azul bajo una fina nevada.'], ['Refugio de cazadores', 'Luces cálidas parpadean junto a una cabaña aislada.'], ['Abetos del hielo', 'Los pinos cubiertos de escarcha guardan un sendero silencioso.']],
            33 => [['Portal rúnico', 'Un monolito azul emite un destello entre las piedras heladas.'], ['Cauce congelado', 'El río forma escalones de hielo que conducen hacia el norte.'], ['Estandartes del norte', 'Banderas antiguas resisten el viento junto a un paso estrecho.']],
            34 => [['Aguja de hielo', 'Una formación cristalina se alza sobre el glaciar.'], ['Puente del glaciar', 'Un puente de piedra salva una grieta de agua turquesa.'], ['Cornisa de los pinos', 'Una senda elevada avanza entre abetos cargados de nieve.']],
            35 => [['Viaducto de piedra', 'Un gran puente de arcos atraviesa el desfiladero helado.'], ['Desfiladero blanco', 'La ventisca oculta huellas recientes entre las paredes de hielo.'], ['Torre del vigía', 'Una torre abandonada permanece encendida frente a la tormenta.']],
            36 => [['Ruinas de los ancestros', 'Columnas nórdicas sobreviven entre una terraza cubierta de escarcha.'], ['Cascada helada', 'El agua azul cae entre muros congelados y forma un paso estrecho.'], ['Sendero de la aurora', 'Un resplandor verde acompaña una ruta sobre la nieve intacta.']],
            37 => [['Cabaña en la ventisca', 'Un refugio de madera ofrece humo y señales extrañas.'], ['Arco del hielo', 'Un puente antiguo cruza el cañón frente a una catarata inmóvil.'], ['Bosque nevado', 'Los abetos se cierran sobre un trayecto marcado por huellas.']],
            38 => [['Fortaleza en ruinas', 'Murallas quebradas y antorchas anuncian un enclave olvidado.'], ['Pico del cuervo', 'Un ascenso entre riscos ofrece vista a toda la corona blanca.'], ['Camino de las cascadas', 'La senda bordea saltos de agua congelada y bloques de hielo.']],
            39 => [['Templo de Bóreas', 'Un santuario de piedra y fuego espera bajo la aurora.'], ['Mirador boreal', 'Desde la cornisa se ve el trono del guardián al otro lado.'], ['Senda del trono', 'La nieve se aparta junto a los últimos pilares nórdicos.']],
            40 => [['Entrar al trono de Bóreas', 'Solo queda una ruta. El Guardián de la Tormenta aguarda entre las alturas.']],
            default => self::regiones()[3]['caminos'],
        };
    }

    /** Rutas inspiradas en los hitos visibles del mapa de las Tierras del Dragón. */
    public static function caminosDragon(int $escenario): array
    {
        return match ($escenario) {
            41 => [['Cruzar el umbral de ceniza', 'Una única ruta abandona las tierras heladas y se adentra entre la piedra ennegrecida.']],
            42 => [['Puente de obsidiana', 'Un arco de basalto cruza una grieta iluminada por lava.'], ['Osario del coloso', 'Costillas gigantes descansan entre ceniza y antorchas apagadas.'], ['Senda de las brasas', 'Pequeñas grietas rojas dibujan un paso seguro sobre la roca.']],
            43 => [['Arco fundido', 'Una puerta de piedra se abre frente a una cascada de magma.'], ['Cristales escarlata', 'Fragmentos rojos palpitan junto al camino como si respiraran.'], ['Cañón de lava', 'El calor asciende desde un río de fuego que parte el valle.']],
            44 => [['Santuario quebrado', 'Columnas ennegrecidas rodean un altar que aún conserva calor.'], ['Cornisa de basalto', 'Una senda estrecha bordea el abismo bajo una lluvia de ceniza.'], ['Caverna incandescente', 'Una luz anaranjada se filtra desde una grieta entre los riscos.']],
            45 => [['Torre de las brasas', 'Una fortaleza en ruinas vigila los puentes sobre el precipicio.'], ['Pasarela del volcán', 'Un puente de piedra avanza entre fumarolas y lava viva.'], ['Caravana carbonizada', 'Restos de carros y cofres señalan una ruta olvidada.']],
            46 => [['Observatorio de obsidiana', 'Un círculo de piedra mira el cielo rojo desde una meseta elevada.'], ['Altar de la llama', 'Las brasas se reúnen alrededor de una runa antigua.'], ['Escalera del cráter', 'Peldaños erosionados suben hacia una cresta cubierta de humo.']],
            47 => [['Camino de los huesos', 'Restos de una criatura colosal señalan el paso entre barrancos.'], ['Puente del abismo', 'Una pasarela quebrada salva un cauce de lava rugiente.'], ['Torres ennegrecidas', 'Agujas de piedra se alzan junto a murallas derruidas.']],
            48 => [['Puertas del dragón', 'Arcos antiguos marcan el último tramo hacia las alturas ardientes.'], ['Cascada de fuego', 'Una caída de magma ilumina las ruinas desde el fondo del valle.'], ['Monolitos de ceniza', 'Pilares oscuros forman un corredor hacia el norte.']],
            49 => [['Cráneo del umbral', 'La montaña-calavera guarda un camino cubierto de brasas.'], ['Trono de la caldera', 'Una plataforma ritual mira hacia la guarida del Dragón.'], ['Senda de Ignar', 'El aire tiembla donde las últimas grietas llevan al guardián final.']],
            50 => [['Entrar a la guarida de Ignar', 'Solo queda una ruta. El Dragón aguarda tras la boca de la montaña-calavera.']],
            default => self::regiones()[4]['caminos'],
        };
    }

    public static function tipo(int $escenario, int $tirada): string
    {
        self::region($escenario);
        if ($tirada < 1 || $tirada > 100) {
            throw new \InvalidArgumentException('Tirada fuera de rango.');
        }
        if ($escenario % 10 === 0) {
            return $escenario === 50 ? 'dragon' : 'jefe';
        }
        $limite = 0;
        foreach (self::PROBABILIDADES as $tipo => $peso) {
            $limite += $peso;
            if ($tirada <= $limite) {
                return $tipo;
            }
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
