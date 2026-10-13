<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class AventuraCatalogo
{
    public const STATS = ['dano', 'magia', 'armadura', 'inteligencia', 'vitalidad'];

    public const RAREZAS = ['normal' => 'Normal', 'raro' => 'Raro', 'super_raro' => 'Super raro', 'ultra_raro' => 'Ultra raro', 'legendario' => 'Legendario'];

    public function items(): array
    {
        $items = [];
        $add = function ($id, $nombre, $tipo, $rareza, $precio, $bonus, $descripcion, $icono, $desafio = null, $mana = 0) use (&$items) {
            $items[$id] = compact('id', 'nombre', 'tipo', 'rareza', 'precio', 'bonus', 'descripcion', 'icono', 'desafio', 'mana');
        };
        $add('espada-viajero', 'Espada del viajero', 'arma', 'normal', 30, ['dano' => 2], 'Ligera y fiable para dar tus primeros pasos.', 'sword');
        $add('baston-chispa', 'Bastón de chispa', 'arma', 'normal', 35, ['magia' => 2], 'Una pequeña reserva de poder arcano.', 'staff');
        $add('hacha-roble', 'Hacha de roble', 'arma', 'raro', 100, ['dano' => 5], 'Un golpe contundente para quien prefiere la fuerza.', 'axe');
        $add('vara-lunar', 'Vara lunar', 'arma', 'raro', 110, ['magia' => 4, 'inteligencia' => 2], 'La luz de la luna acompaña tus hechizos.', 'staff');
        $add('sable-aurora', 'Sable de aurora', 'arma', 'super_raro', 240, ['dano' => 8, 'vitalidad' => 2], 'Un filo que brilla al amanecer.', 'sword');
        $add('cetro-niebla', 'Cetro de niebla', 'arma', 'super_raro', 260, ['magia' => 8, 'inteligencia' => 3], 'La niebla guarda secretos para quien aprende.', 'staff');
        $add('hacha-cometa', 'Hacha del cometa', 'arma', 'ultra_raro', 500, ['dano' => 13, 'armadura' => 2], 'Forjada con metal caído de las estrellas.', 'axe');
        $add('cetro-cosmos', 'Cetro del cosmos', 'arma', 'ultra_raro', 540, ['magia' => 13, 'inteligencia' => 4], 'Un fragmento del firmamento en tus manos.', 'staff');
        $add('filo-fenix', 'Filo del fénix', 'arma', 'legendario', 900, ['dano' => 20, 'vitalidad' => 4], 'Cada nuevo intento es una oportunidad de renacer.', 'sword');
        $add('oraculo', 'Oráculo estelar', 'arma', 'legendario', 950, ['magia' => 20, 'inteligencia' => 6], 'Para exploradores que nunca dejan de preguntar.', 'staff');

        $add('cuero', 'Chaleco de explorador', 'armadura', 'normal', 30, ['armadura' => 2], 'Protección sencilla, lista para el camino.', 'armor');
        $add('tunica', 'Túnica de aprendiz', 'armadura', 'normal', 35, ['armadura' => 1, 'inteligencia' => 2], 'Cómoda para estudiar y practicar magia.', 'robe');
        $add('hierro', 'Coraza de hierro', 'armadura', 'raro', 100, ['armadura' => 5], 'Un refugio resistente frente a los golpes.', 'armor');
        $add('manto-musgo', 'Manto del bosque', 'armadura', 'raro', 110, ['armadura' => 3, 'vitalidad' => 3], 'El bosque cuida a sus visitantes.', 'robe');
        $add('guardian', 'Coraza del guardián', 'armadura', 'super_raro', 240, ['armadura' => 8, 'vitalidad' => 3], 'La mejor defensa también necesita paciencia.', 'armor');
        $add('manto-astral', 'Manto astral', 'armadura', 'super_raro', 260, ['armadura' => 5, 'magia' => 4, 'inteligencia' => 3], 'Tejido con hilos de luz.', 'robe');
        $add('bastion', 'Bastión de cristal', 'armadura', 'ultra_raro', 500, ['armadura' => 13, 'vitalidad' => 4], 'Una fortaleza que puedes llevar contigo.', 'armor');
        $add('velo-eclipse', 'Velo del eclipse', 'armadura', 'ultra_raro', 540, ['armadura' => 8, 'magia' => 7, 'inteligencia' => 5], 'Equilibra protección y poder arcano.', 'robe');
        $add('coraza-dragon', 'Coraza del dragón', 'armadura', 'legendario', 900, ['armadura' => 20, 'vitalidad' => 6], 'Escamas legendarias para un largo viaje.', 'armor');
        $add('manto-infinito', 'Manto del infinito', 'armadura', 'legendario', 950, ['armadura' => 12, 'magia' => 10, 'inteligencia' => 8], 'Un cielo de posibilidades sobre tus hombros.', 'robe');

        $add('brasa', 'Brasa', 'hechizo', 'normal', 25, [], 'Ataque de fuego: 8 + tu magia de daño.', 'fire', null, 6);
        $add('alivio', 'Alivio', 'hechizo', 'normal', 30, [], 'Recupera 15 + tu magia de vida.', 'heal', null, 8);
        $add('escarcha', 'Escarcha', 'hechizo', 'raro', 90, [], 'Ataque de hielo: 16 + tu magia de daño.', 'ice', null, 10);
        $add('barrera', 'Barrera rúnica', 'hechizo', 'raro', 95, [], 'Absorbe 18 + tu magia de daño en el siguiente golpe.', 'shield', null, 10);
        $add('relampago', 'Relámpago', 'hechizo', 'super_raro', 220, [], 'Ataque eléctrico: 28 + tu magia de daño.', 'bolt', null, 16);
        $add('renuevo', 'Renuevo', 'hechizo', 'super_raro', 230, [], 'Recupera 35 + tu magia de vida.', 'heal', null, 18);
        $add('quebrarrocas', 'Quebrarrocas', 'hechizo', 'super_raro', 250, [], 'Magia de tierra: 34 + tu magia de daño.', 'axe', null, 18);
        $add('raiz-vital', 'Raíz vital', 'hechizo', 'super_raro', 270, [], 'Recupera 28 + tu magia de vida y regenera 5 de vida durante 3 turnos.', 'heal', null, 20);
        $add('ritual-doble', 'Ritual de doble filo', 'hechizo', 'super_raro', 290, [], 'Tus próximos 2 ataques físicos golpean dos veces.', 'sword', null, 22);
        $add('meteorito', 'Meteorito', 'hechizo', 'ultra_raro', 460, [], 'Ataque de fuego: 45 + tu magia de daño.', 'fire', null, 24);
        $add('aurora', 'Aurora protectora', 'hechizo', 'ultra_raro', 480, [], 'Absorbe 50 + tu magia de daño en el siguiente golpe.', 'shield', null, 24);
        $add('tormenta-cadena', 'Tormenta en cadena', 'hechizo', 'ultra_raro', 540, [], 'Ataque eléctrico: 58 + tu magia de daño.', 'bolt', null, 30);
        $add('furia-runica', 'Furia rúnica', 'hechizo', 'ultra_raro', 560, [], 'Todos tus ataques y hechizos causan 35% más daño durante 3 turnos.', 'flame', null, 28);
        $add('imbuicion-ignea', 'Imbuición ígnea', 'hechizo', 'ultra_raro', 580, [], 'Durante 3 turnos, tus ataques físicos se vuelven mágicos de fuego y causan 25% más daño.', 'fire', null, 30);
        $add('terremoto', 'Terremoto', 'hechizo', 'ultra_raro', 600, [], 'Magia de tierra: 64 + tu magia de daño y una barrera de 20.', 'axe', null, 32);
        $add('supernova', 'Supernova', 'hechizo', 'legendario', 1500, [], 'Explosión cósmica: 150 + tu magia y 50% de tu vida faltante. Cuesta 75 de maná y 15% de tu vida máxima. Ignora 75% de la defensa y resistencia mágica.', 'bolt', null, 75);
        $add('luz-fenix', 'Luz del fénix', 'hechizo', 'legendario', 850, [], 'Recupera 80 + tu magia de vida; no revive al personaje.', 'heal', null, 32);
        $add('pocion-vida', 'Poción de vida', 'pocion', 'normal', 15, [], 'Recupera 20% de vida máxima sin superar el máximo.', 'potion');
        $add('pocion-mana', 'Poción de maná', 'pocion', 'normal', 15, [], 'Recupera 25% de maná máximo sin superar el máximo.', 'mana');
        $add('pocion-mayor', 'Poción de vida mayor', 'pocion', 'raro', 35, [], 'Recupera 50% de vida máxima sin superar el máximo.', 'potion');
        $add('elixir', 'Elixir del viajero', 'pocion', 'raro', 45, [], 'Recupera 40% de vida máxima y 30% de maná máximo.', 'elixir');

        $add('coraza-caja', 'Coraza del modelo de caja', 'armadura', 'raro', 80, ['armadura' => 5, 'vitalidad' => 2], 'Sus capas de protección celebran tu dominio de CSS.', 'armor', 5);
        $add('filo-validacion', 'Filo de la validación', 'arma', 'raro', 80, ['dano' => 5, 'inteligencia' => 2], 'Un filo preciso para quien comprueba antes de actuar.', 'sword', 10);
        $add('manto-memoria', 'Manto de la memoria', 'armadura', 'super_raro', 190, ['armadura' => 8, 'inteligencia' => 4], 'Un homenaje a tus primeras sesiones y cookies.', 'robe', 15);
        $add('compilador-origen', 'Compilador del origen', 'arma', 'legendario', 700, ['dano' => 16, 'magia' => 16, 'inteligencia' => 4], 'Reliquia única del proyecto final. Tu conocimiento abre su sello.', 'sword', 20);

        return $items;
    }

    public static function vacio(): array
    {
        return ['arma' => null, 'armadura' => null, 'hechizos' => [], 'pociones' => []];
    }

    /** Cada diez niveles completos abre un espacio adicional para hechizos. */
    public static function limiteHechizos(int $nivel): int
    {
        return 2 + intdiv(max(1, $nivel) - 1, 10);
    }

    public function cotizar(array $equipo, array $completados, int $nivel = 1): array
    {
        $items = $this->items();
        $costo = 0;
        $bonus = array_fill_keys(self::STATS, 0);
        $usar = function ($id, $tipo, $cantidad = 1) use ($items, $completados, &$costo, &$bonus) {
            $item = $items[$id] ?? null;
            if (! $item || $item['tipo'] !== $tipo) {
                throw ValidationException::withMessages(['equipo' => 'Ese objeto no pertenece a esta categoría.']);
            }
            if ($item['desafio'] && ! in_array($item['desafio'], $completados, true)) {
                throw ValidationException::withMessages(['equipo' => 'Completa el desafío '.$item['desafio'].' para desbloquear '.$item['nombre'].'.']);
            }
            $costo += $item['precio'] * $cantidad;
            foreach ($item['bonus'] as $stat => $valor) {
                $bonus[$stat] += $valor;
            }
        };
        foreach (['arma', 'armadura'] as $slot) {
            if ($equipo[$slot] !== null) {
                $usar($equipo[$slot], $slot);
            }
        }
        $limiteHechizos = self::limiteHechizos($nivel);
        if (count($equipo['hechizos']) > $limiteHechizos || count(array_unique($equipo['hechizos'])) !== count($equipo['hechizos'])) {
            throw ValidationException::withMessages(['equipo' => 'Puedes equipar hasta '.$limiteHechizos.' hechizos diferentes con tu nivel actual.']);
        }
        foreach ($equipo['hechizos'] as $id) {
            $usar($id, 'hechizo');
        }
        $cantidad = 0;
        $vistos = [];
        foreach ($equipo['pociones'] as $pocion) {
            if (in_array($pocion['id'], $vistos, true) || ! is_int($pocion['cantidad']) || $pocion['cantidad'] < 1 || $pocion['cantidad'] > 20) {
                throw ValidationException::withMessages(['equipo' => 'Revisa las cantidades de pociones.']);
            }
            $vistos[] = $pocion['id'];
            $cantidad += $pocion['cantidad'];
            $usar($pocion['id'], 'pocion', $pocion['cantidad']);
        }
        if ($cantidad > 20) {
            throw ValidationException::withMessages(['equipo' => 'La mochila admite hasta 20 pociones en total.']);
        }

        return ['costo' => $costo, 'bonus' => $bonus];
    }
}
