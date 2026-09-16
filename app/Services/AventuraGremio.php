<?php

namespace App\Services;

use App\Models\{AventuraExpedicion,Usuario,PreguntaTest};
use Illuminate\Support\Facades\DB;

class AventuraGremio
{
    public static function tiendaCatalogo(): array
    {
        return [
            'pocion-vida'=>['nombre'=>'Poción de vida','texto'=>'Recupera 20% de tu vida máxima durante un combate.','precio'=>18,'tipo'=>'pocion','objeto'=>'pocion-vida'],
            'pocion-mana'=>['nombre'=>'Poción de maná','texto'=>'Recupera 25% de tu maná máximo durante un combate.','precio'=>18,'tipo'=>'pocion','objeto'=>'pocion-mana'],
            'pocion-mayor'=>['nombre'=>'Poción de vida mayor','texto'=>'Recupera 50% de tu vida máxima durante un combate.','precio'=>42,'tipo'=>'pocion','objeto'=>'pocion-mayor'],
            'elixir'=>['nombre'=>'Elixir del viajero','texto'=>'Recupera 40% de vida y 30% de maná durante un combate.','precio'=>55,'tipo'=>'pocion','objeto'=>'elixir'],
            'arma_dano'=>['nombre'=>'Filo calibrado','texto'=>'Tu arma gana +5 de daño base durante esta aventura.','precio'=>65,'tipo'=>'equipo','mejora'=>'arma_dano'],
            'armadura_refuerzo'=>['nombre'=>'Placas reforzadas','texto'=>'Tu armadura absorbe +2 de daño directo por golpe durante esta aventura.','precio'=>55,'tipo'=>'equipo','mejora'=>'armadura_refuerzo'],
            'dano'=>['nombre'=>'Manual ofensivo','texto'=>'Daño de ataques y hechizos: +10% durante esta aventura.','precio'=>75,'tipo'=>'mejora','mejora'=>'dano'],
            'resistencia'=>['nombre'=>'Piel de runas','texto'=>'Reduces 5% del daño directo durante esta aventura.','precio'=>65,'tipo'=>'mejora','mejora'=>'resistencia'],
            'regeneracion'=>['nombre'=>'Descanso del viajero','texto'=>'Recuperas 5% de vida al vencer durante esta aventura.','precio'=>60,'tipo'=>'mejora','mejora'=>'regeneracion'],
            'critico'=>['nombre'=>'Ojo del depurador','texto'=>'Ganas 5% de probabilidad de golpe crítico durante esta aventura.','precio'=>55,'tipo'=>'mejora','mejora'=>'critico'],
        ];
    }
    public static function catalogo(): array
    {
        return [
            'patrulla'=>['nombre'=>'Patrulla del sendero','texto'=>'Derrota a 3 enemigos después de aceptar. Los fragmentos invocados no cuentan.','tipo'=>'enemigos','meta'=>3,'xp'=>40,'oro'=>20],
            'lobo'=>['nombre'=>'Huellas entre los árboles','texto'=>'Derrota a un lobo del sendero después de aceptar.','tipo'=>'lobo','meta'=>1,'xp'=>25,'oro'=>15,'objeto'=>'pocion-vida'],
            'runas'=>['nombre'=>'La inscripción del gremio','texto'=>'Resuelve aquí una pregunta del archivo de programación. Tienes un intento.','tipo'=>'acertijo','meta'=>1,'xp'=>20,'oro'=>10],
            'mentor'=>['nombre'=>'Aprender para ayudar','texto'=>'Responde correctamente 2 preguntas de eventos, jefes o encargos después de aceptar.','tipo'=>'respuestas','meta'=>2,'xp'=>40,'oro'=>20,'mejora'=>'experiencia'],
            'botiquin'=>['nombre'=>'Un botiquín para el camino','texto'=>'Entrega 1 Poción de vida de tu mochila. Se consume al cobrar; no cambia el reembolso de tu equipo.','tipo'=>'entrega','meta'=>1,'xp'=>20,'oro'=>15,'entregar'=>'pocion-vida'],
            'reserva_arcana'=>['nombre'=>'Reserva arcana','texto'=>'Entrega 1 Poción de maná de tu mochila. Se consume al cobrar; no cambia el reembolso de tu equipo.','tipo'=>'entrega','meta'=>1,'xp'=>20,'oro'=>15,'entregar'=>'pocion-mana'],
            'explorador'=>['nombre'=>'Reconocer el terreno','texto'=>'Avanza 2 escenarios desde el lugar donde aceptes el encargo.','tipo'=>'escenario','meta'=>2,'xp'=>25,'oro'=>15,'inspiracion'=>true],
            'guardian'=>['nombre'=>'Silenciar las raíces','texto'=>'Derrota a Silvar, el jefe del escenario 10, después de aceptar.','tipo'=>'jefe','meta'=>1,'xp'=>80,'oro'=>40,'objeto'=>'hierro'],
            'hallazgo'=>['nombre'=>'Suministros perdidos','texto'=>'Encuentra una poción en un tesoro o evento después de aceptar. Las compras y premios del gremio no cuentan.','tipo'=>'hallazgo','meta'=>1,'xp'=>25,'oro'=>15,'objeto'=>'pocion-mana'],
        ];
    }

    public function preparar(AventuraExpedicion $run): array
    {
        $pool = array_keys(self::catalogo());
        if ($run->escenario >= 10) $pool = array_values(array_diff($pool,['lobo','guardian']));
        if ($run->escenario > 48) $pool = array_values(array_diff($pool,['explorador']));
        // Stored once; reloading never rerolls the board.
        shuffle($pool);
        $tienda = array_keys(self::tiendaCatalogo());
        shuffle($tienda);
        return ['ofertas'=>array_slice($pool,0,4),'encargos'=>[],
            'tienda'=>['ofertas'=>array_slice($tienda,0,4),'compras'=>[],'abierta'=>false]];
    }

    private function metricas(AventuraExpedicion $run): array
    {
        $m=['enemigos'=>0,'lobo'=>0,'jefe'=>0,'respuestas'=>0,'hallazgo'=>0,'escenario'=>$run->escenario];
        foreach ($run->exploracion ?? [] as $e) {
            $b=$e['combate'] ?? [];
            if (($b['estado'] ?? '') === 'victoria') foreach ($b['enemigos'] as $enemigo) {
                if ($enemigo['xp'] <= 0) continue;
                $m['enemigos']++;
                if ($enemigo['clave'] === 'lobo') $m['lobo']++;
                if ($enemigo['clave'] === 'silvar') $m['jefe']++;
            }
            $m['respuestas'] += (int)($b['aciertos'] ?? 0) + (int)($e['narrativa']['correcta'] ?? false);
            $item=(new AventuraCatalogo())->items()[$e['narrativa']['botin'] ?? ''] ?? null;
            if ($item && $item['tipo'] === 'pocion') $m['hallazgo']++;
            foreach ($e['gremio']['encargos'] ?? [] as $encargo) $m['respuestas'] += (int)($encargo['correcta'] ?? false);
        }
        return $m;
    }

    public function progreso(AventuraExpedicion $run,string $clave,array $encargo): int
    {
        $d=self::catalogo()[$clave];
        if ($d['tipo'] === 'acertijo') return (int)($encargo['correcta'] ?? false);
        if ($d['tipo'] === 'entrega') return min(1,(int)$run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo',$d['entregar']))->sum('cantidad'));
        return min($d['meta'],max(0,$this->metricas($run)[$d['tipo']] - $encargo['base'][$d['tipo']]));
    }

    public function diario(AventuraExpedicion $run): array
    {
        $lista=[];
        foreach ($run->exploracion ?? [] as $origen=>$evento) foreach ($evento['gremio']['encargos'] ?? [] as $clave=>$encargo) {
            $d=self::catalogo()[$clave];
            $lista[]=['origen'=>(int)$origen,'clave'=>$clave,'definicion'=>$d,'encargo'=>$encargo,
                'progreso'=>$encargo['estado']==='cobrado'?$d['meta']:$this->progreso($run,$clave,$encargo),
                'pregunta'=>($encargo['estado']==='activo' && isset($encargo['pregunta'])) ? (new AventuraPreguntas())->publica($encargo['pregunta']) : null];
        }
        return $lista;
    }

    public function actuar(int $usuario,int $id,int $version,array $a): void
    {
        DB::transaction(function () use($usuario,$id,$version,$a) {
            Usuario::whereKey($usuario)->lockForUpdate()->firstOrFail();
            $run=AventuraExpedicion::where('usuario_id',$usuario)->findOrFail($id);
            abort_unless($run->estado==='activa' && $run->vida_actual>0,409,'La partida no está activa.');
            abort_if($run->version!==$version,409,'El diario cambió. Recarga para continuar.');
            $h=$run->exploracion ?? []; $actual=$h[$run->escenario] ?? null;
            abort_if(isset($actual['combate']) && !in_array($actual['combate']['estado'],['victoria'],true),409,'Termina el combate antes de gestionar encargos.');
            $accion=$a['accion'];
            if (in_array($accion,['abrir','aceptar','salir','tienda','cerrar_tienda'],true)) {
                abort_unless(($actual['tipo'] ?? '')==='gremio' && $actual['estado']==='pendiente',409,'No estás ante un tablón del gremio.');
                if ($accion==='abrir') {
                    abort_if(isset($actual['gremio']),409);
                    $actual['gremio']=$this->preparar($run);
                } elseif ($accion === 'tienda') {
                    $actual['gremio']['tienda'] ??= ['ofertas'=>array_slice(array_keys(self::tiendaCatalogo()),0,4),'compras'=>[],'abierta'=>false];
                    $actual['gremio']['tienda']['abierta'] = true;
                } elseif ($accion === 'cerrar_tienda') {
                    $actual['gremio']['tienda']['abierta'] = false;
                } elseif ($accion==='salir') {
                    abort_if($run->escenario>=50,409);
                    $actual['estado']='resuelto'; $h[$run->escenario]=$actual;
                    $run->escenario++; $run->exploracion=$h; $run->version++; $run->save(); return;
                } else {
                    $clave=$a['encargo'] ?? '';
                    abort_unless(in_array($clave,$actual['gremio']['ofertas'] ?? [],true),422,'Ese encargo no está en este tablón.');
                    abort_if(isset($actual['gremio']['encargos'][$clave]),409,'Ya decidiste sobre ese encargo.');
                    $activos=count(array_filter($this->diario($run),fn($m)=>$m['encargo']['estado']==='activo'));
                    abort_if($activos>=3,422,'Puedes llevar hasta 3 encargos activos. Cobra o abandona uno antes de aceptar otro.');
                    $encargo=['estado'=>'activo','base'=>$this->metricas($run)];
                    if (self::catalogo()[$clave]['tipo']==='acertijo') {
                        $pregunta=(new AventuraPreguntas())->elegir($run->escenario);
                        abort_unless($pregunta,422,'El archivo no tiene preguntas disponibles. Puedes aceptar otro encargo.');
                        $encargo['pregunta']=$pregunta->id;
                    }
                    $actual['gremio']['encargos'][$clave]=$encargo;
                }
                $h[$run->escenario]=$actual;
            } elseif ($accion === 'comprar') {
                // The storefront is now a client-side modal. Its offers are
                // already persisted with this guild encounter, so purchases
                // must not depend on the old screen-transition flag.
                abort_unless(($actual['tipo'] ?? '')==='gremio' && $actual['estado']==='pendiente' && !empty($actual['gremio']['tienda']['ofertas']),409,'La tienda no está disponible en este encuentro.');
                $oferta = $a['oferta'] ?? '';
                $tienda = &$actual['gremio']['tienda'];
                abort_unless(in_array($oferta,$tienda['ofertas'] ?? [],true),422,'Esa oferta no pertenece a esta tienda.');
                abort_if(!empty($tienda['compras'][$oferta]),409,'Ya compraste esta oferta.');
                $articulo = self::tiendaCatalogo()[$oferta] ?? null;
                abort_unless($articulo,422,'Ese artículo ya no está disponible.');
                abort_unless($run->oro >= $articulo['precio'],422,'No tienes suficiente oro para esta compra.');
                if (($articulo['tipo'] ?? '') === 'pocion') {
                    abort_unless(AventuraInventario::recibir($run,$articulo['objeto']),422,'Tu mochila está llena o ya tiene este objeto.');
                    $detalle = 'Compras '.(new AventuraCatalogo())->items()[$articulo['objeto']]['nombre'].'.';
                } elseif (($articulo['tipo'] ?? '') === 'equipo') {
                    $limite = $articulo['mejora'] === 'arma_dano' ? 25 : 12;
                    abort_if(AventuraMejoras::valor($run,$articulo['mejora']) >= $limite,422,'Esta mejora ya alcanzó su límite para la aventura.');
                    $detalle = AventuraMejoras::mejorarEquipo($run,$articulo['mejora']);
                } else {
                    abort_if(AventuraMejoras::valor($run,$articulo['mejora']) >= AventuraMejoras::TIPOS[$articulo['mejora']][2],422,'Esta mejora ya alcanzó su límite para la aventura.');
                    $detalle = AventuraMejoras::agregar($run,$articulo['mejora']);
                }
                $run->oro -= $articulo['precio'];
                $tienda['compras'][$oferta] = ['detalle'=>$detalle,'precio'=>$articulo['precio']];
                unset($tienda);
                $h[$run->escenario]=$actual;
            } else {
                $origen=$a['origen'] ?? 0; $clave=$a['encargo'] ?? '';
                abort_unless(isset($h[$origen]['gremio']['encargos'][$clave]),404,'No existe ese encargo en tu partida.');
                $q=&$h[$origen]['gremio']['encargos'][$clave]; $d=self::catalogo()[$clave];
                if ($accion==='equipar') {
                    abort_unless($q['estado']==='cobrado' && !empty($q['botin']) && empty($q['equipado']),409);
                    AventuraInventario::equiparBotin($run,$q['botin'],$a['reemplazar'] ?? null);
                    $stats=(new AventuraService())->estado($usuario)['personaje'];
                    $run->vida_actual=min($run->vida_actual,$stats['vida']); $run->mana_actual=min($run->mana_actual,$stats['mana']); $q['equipado']=true;
                } else {
                    abort_unless($q['estado']==='activo',409,'Este encargo ya terminó.');
                    if ($accion==='abandonar') $q['estado']='abandonado';
                    elseif ($accion==='responder') {
                        abort_unless($d['tipo']==='acertijo' && !isset($q['correcta']),409);
                        $agotado = ($a['tiempo_agotado'] ?? false) === true;
                        abort_unless($agotado || in_array($a['respuesta'] ?? '',['a','b','c','d'],true),422);
                        $pregunta=PreguntaTest::find($q['pregunta']);
                        if (!$pregunta) { $q['estado']='cancelado'; $q['mensaje']='La inscripción desapareció. El encargo se cancela sin penalización.'; }
                        else {
                            $q['correcta']=!$agotado && $pregunta->esRespuestaCorrecta($a['respuesta']);
                            $q['mensaje']=$q['correcta']?'¡Correcto! Ya puedes cobrar este encargo.':($agotado ? 'El tiempo se agotó. ' : '').'La respuesta correcta era: '.$pregunta->{'opcion_'.$pregunta->respuesta_correcta}.'. Sigue aprendiendo; encontrarás nuevos encargos.';
                            if (!$q['correcta']) $q['estado']='fallido';
                        }
                    } elseif ($accion==='cobrar') {
                        abort_unless($this->progreso($run,$clave,$q)>=$d['meta'],422,'Aún no cumples el objetivo.');
                        if ($d['tipo']==='entrega') {
                            $fila=$run->inventario()->whereHas('item',fn($query)=>$query->where('clave_catalogo',$d['entregar']))->where('cantidad','>',0)->firstOrFail();
                            $fila->cantidad--; $fila->save();
                        }
                        $xp=AventuraMejoras::experiencia($run,$d['xp']);
                        (new AventuraService())->otorgarExperiencia($usuario,'expedicion:'.$id.':gremio:'.$origen.':'.$clave,$xp);
                        $run->oro+=$d['oro']; $q['detalle']=['+'.$xp.' XP permanente y +'.$d['oro'].' oro de esta partida.'];
                        if (isset($d['objeto'])) {
                            if (AventuraInventario::recibir($run,$d['objeto'])) { $q['botin']=$d['objeto']; $q['detalle'][]='Recibes '.(new AventuraCatalogo())->items()[$d['objeto']]['nombre'].'.'; }
                            else { $run->oro+=15; $q['detalle'][]='Objeto repetido o mochila llena: +15 oro en su lugar.'; }
                        }
                        if (isset($d['mejora'])) $q['detalle'][]=AventuraMejoras::agregar($run,$d['mejora']);
                        if (!empty($d['inspiracion'])) {
                            $efectos=array_values(array_filter($run->efectos ?? [],fn($e)=>$e['tipo']!=='inspiracion'));
                            $efectos[]=['tipo'=>'inspiracion','nombre'=>'Inspiración: +20% de daño en el próximo combate','valor'=>20]; $run->efectos=$efectos;
                            $q['detalle'][]='Inspiración: +20% de daño en tu próximo combate.';
                        }
                        $q['estado']='cobrado';
                    } else abort(422,'Acción desconocida.');
                }
                unset($q);
            }
            $run->exploracion=$h; $run->version++; $run->save();
        },3);
    }
}
