<?php

namespace App\Services;

use App\Models\{AventuraExpedicion,Usuario,PreguntaTest};
use Illuminate\Support\Facades\DB;

class AventuraEventos
{
    protected function azar(int $min,int $max): int { return random_int($min,$max); }

    public function preparar(string $tipo, array $historial = []): array
    {
        $ids = array_keys(AventuraRelatos::todos());
        $usados = array_filter(array_map(fn($e)=>$e['narrativa']['id'] ?? null,$historial));
        $disponibles = array_values(array_diff($ids,$usados));
        if (!$disponibles) $disponibles = $ids;
        return ['id'=>$tipo === 'tesoro' ? 'cofre' : $disponibles[$this->azar(0,count($disponibles)-1)],'etapa'=>'decision'];
    }

    public function actuar(int $usuario,int $id,int $version,array $accion): string
    {
        return DB::transaction(function () use ($usuario,$id,$version,$accion) {
            Usuario::whereKey($usuario)->lockForUpdate()->firstOrFail();
            $run = AventuraExpedicion::where('usuario_id',$usuario)->findOrFail($id);
            abort_unless($run->estado === 'activa' && $run->vida_actual > 0,409,'La aventura no está activa.');
            abort_if($run->version !== $version,409,'El encuentro ya cambió. Recarga para continuar.');
            $historial = $run->exploracion ?? []; $evento = $historial[$run->escenario] ?? null;
            abort_unless($evento && in_array($evento['tipo'],['narrativa','tesoro'],true) && empty($evento['combate']),409,'Este encuentro no admite esta acción.');
            $n = $evento['narrativa'] ?? null; $a = $accion['accion'];
            if ($a === 'descubrir') {
                abort_if($n !== null || $evento['estado'] !== 'pendiente',409);
                $n = $this->preparar($evento['tipo'],$historial);
            } elseif ($a === 'continuar') {
                abort_unless($evento['estado'] === 'resuelto' && ($n['etapa'] ?? '') === 'resultado',409,'Primero resuelve el encuentro.');
                abort_if(!empty($n['mejora_hechizo']),422,'Elige primero el hechizo que quieres mejorar.');
                abort_if($run->escenario >= 50,409);
                $run->escenario++; $run->version++; $run->save(); return 'mapa';
            } elseif ($a === 'equipar') {
                abort_unless($evento['estado'] === 'resuelto' && ($n['etapa'] ?? '') === 'resultado' && !empty($n['botin']) && empty($n['equipado']),409,'No hay un objeto nuevo para equipar.');
                $antes = (new AventuraService())->estado($usuario)['personaje'];
                AventuraInventario::equiparBotin($run,$n['botin'],$accion['reemplazar'] ?? null);
                $despues = (new AventuraService())->estado($usuario)['personaje'];
                // Replacing equipment cannot refill resources repeatedly.
                $run->vida_actual = min($despues['vida'],$run->vida_actual);
                $run->mana_actual = min($despues['mana'],$run->mana_actual);
                $n['equipado'] = true;
                $n['detalle'][] = 'Equipo actualizado. El objeto anterior sigue en tu mochila. Vida máxima: '.$antes['vida'].' → '.$despues['vida'].'. Maná máximo: '.$antes['mana'].' → '.$despues['mana'].'.';
            } elseif ($a === 'mejorar_hechizo') {
                abort_unless($evento['estado'] === 'resuelto' && ($n['etapa'] ?? '') === 'resultado' && !empty($n['mejora_hechizo']),409,'No hay una mejora de hechizo pendiente.');
                $hechizo = $accion['hechizo'] ?? '';
                abort_unless(in_array($hechizo,$run->equipo['hechizos'] ?? [],true),422,'Elige uno de los hechizos que llevas equipado.');
                $n['detalle'][] = AventuraMejoras::mejorarHechizo($run,$hechizo);
                unset($n['mejora_hechizo']);
            } else {
                abort_unless($evento['estado'] === 'pendiente' && $n,409,'Primero descubre el encuentro.');
                if ($a === 'responder') {
                    abort_unless($n['etapa'] === 'pregunta',409,'No hay una pregunta pendiente.');
                    abort_unless(in_array($accion['respuesta'] ?? '',['a','b','c','d'],true),422,'Elige una respuesta.');
                    $pregunta = PreguntaTest::find($n['pregunta']);
                    if (!$pregunta) $this->resolver($n,'La inscripción se ha borrado. Puedes continuar sin penalización.');
                    elseif ($pregunta->esRespuestaCorrecta($accion['respuesta'])) {
                        $this->resolver($n,'¡Correcto! Tu conocimiento abre una nueva posibilidad.');
                        $n['correcta'] = true;
                        $this->recompensar($run,$n,false);
                    } else {
                        $this->resolver($n,$n['id'] === 'cofre' ? 'El sello no se abre esta vez.' : AventuraRelatos::todos()[$n['id']][5]);
                        $n['correcta'] = false;
                        $n['detalle'][] = 'La respuesta correcta era: '.$pregunta->{'opcion_'.$pregunta->respuesta_correcta}.'. Puedes repasar este concepto en Documentación.';
                        $this->consecuencia($run,$n,$evento);
                    }
                } else {
                    abort_unless($n['etapa'] === 'decision',409,'La decisión ya está tomada.');
                    if ($a === 'preguntar') {
                        abort_if($n['id'] === 'cofre' || !empty($n['pista']),409);
                        $n['pista'] = true;
                    } elseif ($a === 'ignorar') $this->resolver($n,'Sigues tu camino. A veces, elegir tus batallas también es aprender.');
                    elseif ($a === 'ayudar') {
                        abort_if($n['id'] === 'cofre',422);
                        $this->preguntar($run,$n,$historial);
                    } elseif ($a === 'abrir') {
                        abort_unless($n['id'] === 'cofre',422);
                        $tirada = $this->azar(1,100);
                        if ($tirada <= 8 && $run->escenario < 50) {
                            $this->resolver($n,'¡El cofre tiene dientes! Un slime Mimic se escondía entre las monedas.');
                            $evento['tipo'] = 'enemigo'; $evento['variante'] = 'mimic';
                        } elseif ($tirada <= 35) $this->preguntar($run,$n,$historial);
                        else { $this->resolver($n,'El cofre se abre y revela su secreto.'); $this->recompensar($run,$n,true); }
                    } else abort(422,'Acción desconocida.');
                }
            }
            $evento['narrativa'] = $n;
            $evento['estado'] = $n['etapa'] === 'resultado' && $evento['tipo'] !== 'enemigo' ? 'resuelto' : 'pendiente';
            $historial[$run->escenario] = $evento;
            $run->exploracion = $historial; $run->version++; $run->save();
            if ($run->vida_actual <= 0) { (new AventuraService())->registrarDerrota($usuario,$id); return 'derrota'; }
            return 'mapa';
        },3);
    }

    private function resolver(array &$n,string $texto): void
    {
        $n['etapa'] = 'resultado'; $n['resultado'] = $texto; $n['detalle'] = [];
    }

    private function preguntar(AventuraExpedicion $run,array &$n,array $historial): void
    {
        $usadas = array_values(array_filter(array_map(fn($e)=>$e['narrativa']['pregunta'] ?? $e['combate']['pregunta'] ?? null,$historial)));
        $pregunta = (new AventuraPreguntas())->elegir($run->escenario,$usadas);
        if (!$pregunta) { $this->resolver($n,'El libro no tiene una pregunta disponible. Continúas sin perder recursos ni recibir premios.'); return; }
        $n['pregunta'] = $pregunta->id; $n['etapa'] = 'pregunta';
    }

    private function consecuencia(AventuraExpedicion $run,array &$n,array &$evento): void
    {
        $tirada = $this->azar(1,4);
        if ($tirada === 1) $n['detalle'][] = 'No pierdes recursos. El intento también cuenta para aprender.';
        elseif ($tirada === 2) {
            $stats = (new AventuraService())->estado($run->usuario_id)['personaje'];
            $perdida = min($run->vida_actual,(int)ceil($stats['vida']*.05));
            $run->vida_actual -= $perdida; $n['detalle'][] = 'Pierdes '.$perdida.' de vida (hasta 5% de tu máximo).';
        } elseif ($tirada === 3) {
            $oro = min(10,$run->oro); $run->oro -= $oro; $n['detalle'][] = $oro ? 'Pierdes '.$oro.' de oro de esta partida.' : 'Buscas una moneda para disculparte… solo tienes pelusa. No pierdes nada.';
        } elseif ($run->escenario < 50) {
            $evento['tipo'] = 'enemigo'; $n['detalle'][] = 'El ruido atrajo a un enemigo. Prepárate para combatir.';
        } else $n['detalle'][] = 'El desconocido se marcha. Tu viaje continúa.';
    }

    private function recompensar(AventuraExpedicion $run,array &$n,bool $tesoro): void
    {
        $tirada = $this->azar(1,6);
        if ($tirada === 1) {
            $oro = ($tesoro ? 25 : 12)+$run->escenario;
            $run->oro += $oro; $n['detalle'][] = '+'.$oro.' de oro para esta partida. Tus puntos educativos no cambian.';
        } elseif ($tirada === 2) {
            $xp = AventuraMejoras::experiencia($run,15+$run->escenario*2);
            (new AventuraService())->otorgarExperiencia($run->usuario_id,'expedicion:'.$run->id.':evento:'.$run->escenario,$xp);
            $n['detalle'][] = '+'.$xp.' de experiencia permanente.';
        } elseif ($tirada === 3) {
            $clave = $this->botin($run);
            if ($clave && AventuraInventario::recibir($run,$clave)) {
                $item = (new AventuraCatalogo())->items()[$clave];
                $n['botin'] = $clave;
                $n['detalle'][] = '+1 '.$item['nombre'].' · '.AventuraCatalogo::RAREZAS[$item['rareza']].'. Guardado en tu mochila.';
            } else { $run->oro += 15; $n['detalle'][] = 'Tu mochila ya tiene ese objeto o alcanzó su límite. Recibes 15 de oro.'; }
        } elseif ($tirada === 4) {
            $opciones = [];
            if (!empty($run->equipo['arma'])) $opciones[] = 'arma_dano';
            if (!empty($run->equipo['armadura'])) $opciones[] = 'armadura_refuerzo';
            if ($opciones) $n['detalle'][] = AventuraMejoras::mejorarEquipo($run,$opciones[$this->azar(0,count($opciones)-1)]);
            else { $run->oro += 15; $n['detalle'][] = 'No llevas arma ni armadura para reforzar. Recibes 15 de oro.'; }
        } elseif ($tirada === 5) {
            $tipos = array_keys(AventuraMejoras::TIPOS); $tipo = $tipos[$this->azar(0,count($tipos)-1)];
            $base = (new AventuraService())->estado($run->usuario_id)['personaje']['vida'];
            $n['detalle'][] = AventuraMejoras::agregar($run,$tipo);
            if ($tipo === 'vida') {
                $run->save();
                $max = (new AventuraService())->estado($run->usuario_id)['personaje']['vida'];
                $run->vida_actual += max(0,$max-$base);
            }
        } else {
            if (!empty($run->equipo['hechizos'])) {
                $n['mejora_hechizo'] = true;
                $n['detalle'][] = 'Hallaste un foco arcano muy raro. Elige un hechizo equipado para elevarlo de nivel durante esta aventura.';
            } else {
                $n['detalle'][] = AventuraMejoras::agregar($run,'critico');
            }
        }
    }

    private function botin(AventuraExpedicion $run): ?string
    {
        $completados = DB::table('desafio_recompensas')->where('usuario_id',$run->usuario_id)->where('completado_primera_vez',true)->pluck('nivel_desafio')->all();
        $pool = array_filter((new AventuraCatalogo())->items(),fn($i)=>$i['tipo'] === 'pocion' && (!$i['desafio'] || in_array($i['desafio'],$completados)));
        $keys = array_keys($pool);
        return $keys ? $keys[$this->azar(0,count($keys)-1)] : null;
    }
}
