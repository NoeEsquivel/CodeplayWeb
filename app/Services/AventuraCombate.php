<?php

namespace App\Services;

use App\Models\{AventuraExpedicion, Enemigo, Combate, Usuario, PreguntaTest};
use Illuminate\Support\Facades\{DB, Schema};

class AventuraCombate
{
    private array $secuencia = [];
    public function secuencia(): array { return $this->secuencia; }
    private function escena(AventuraExpedicion $run, array $b, string $tipo, string $actor, int $desde): void
    {
        $this->secuencia[]=['tipo'=>$tipo,'actor'=>$actor,'texto'=>implode(' ',array_slice($b['registro'],$desde)),
            'vida'=>$run->vida_actual,'mana'=>$run->mana_actual,
            'enemigos'=>array_map(fn($e)=>array_intersect_key($e,array_flip(['clave','nombre','vida','max'])), $b['enemigos'])];
    }
    public const REGIONES = [1=>['lobo','slime','ladron','avispa','hongo'],2=>['momia','vampiro','espectro','centinela','nigromante'],3=>['sanguijuela','bruja','cienago','saqueador','basilisco'],4=>['yeti','hechicero','golem_hielo','lince','alma_nieve'],5=>['salamandra','caballero','cultista','devorador','acechador']];
    public const JEFES = [10=>'silvar',20=>'archivista',30=>'matriarca',40=>'titan',50=>'dragon'];
    public function disponible(): bool { return Schema::hasColumn('enemigos', 'rasgos'); }
    protected function azar(int $min, int $max): int { return random_int($min, $max); }

    private function enemigo(Enemigo $modelo, int $nivel): array
    {
        $region = $modelo->rasgos['region'];
        $extra = $modelo->rasgos['habilidad'] === 'jefe' ? 0 : max(0,$nivel - (($region-1)*10+1));
        $descripcion = $modelo->clave === 'dragon'
            ? '???? Ignar es demasiado poderoso, no eres capaz de leer sus debilidades o es probable que no las tenga.'
            : $modelo->rasgos['descripcion'];
        return ['id'=>$modelo->id,'clave'=>$modelo->clave,'nombre'=>$modelo->nombre,'nivel'=>$nivel,
            'vida'=>$modelo->vida + $extra * 3,'max'=>$modelo->vida + $extra * 3,
            'ataque'=>$modelo->{'daño'} + intdiv($extra,2),'defensa'=>$modelo->defensa,
            'xp'=>$modelo->rasgos['experiencia'] + $extra * 2, 'oro'=>$modelo->recompensa_puntos,
            'habilidad'=>$modelo->rasgos['habilidad'],'descripcion'=>$descripcion,
            'region'=>$region,'ataque_especial'=>$modelo->rasgos['ataque_especial'] ?? 'Raíces rúnicas',
            'dividido'=>false,'robo'=>false,'invocado'=>false];
    }

    private function crear(AventuraExpedicion $run): array
    {
        $boss = isset(self::JEFES[$run->escenario]);
        $region = AventuraMundo::region($run->escenario)['id'];
        $pool = Enemigo::whereIn('clave', $boss ? [self::JEFES[$run->escenario]] : self::REGIONES[$region])->orderBy('id')->get();
        abort_if($pool->count() !== ($boss ? 1 : 5), 503, 'El bestiario está en mantenimiento.');
        $cantidad = !$boss && $run->escenario >= 5 && $this->azar(1,100) <= 25 ? 2 : 1;
        $enemigos = [];
        for ($i=0; $i<$cantidad; $i++) $enemigos[] = $this->enemigo($pool[$this->azar(0,$pool->count()-1)], $run->escenario);
        if (($run->exploracion[$run->escenario]['variante'] ?? '') === 'mimic') {
            // A Mimic is a disguised slime; retain its existing species ID in combat history.
            $modelo = Enemigo::where('clave','slime')->firstOrFail();
            $enemigo = $this->enemigo($modelo,$run->escenario);
            if ($region > 1) {
                $enemigo['max']=$enemigo['vida']=32+($region-1)*24+(($run->escenario-1)%10)*3;
                $enemigo['ataque']=5+($region-1)*6; $enemigo['region']=$region;
            }
            $enemigo['clave'] = 'mimic'; $enemigo['nombre'] = 'Slime Mimic';
            $enemigo['descripcion'] = 'Un slime disfrazado de cofre. Se divide bajo media vida; sus fragmentos no duplican el botín.';
            $enemigo['oro'] += 15; $enemigos = [$enemigo];
        }
        return ['turno'=>1, 'estado'=>'jugador', 'enemigos'=>$enemigos, 'registro'=>['El combate comienza. Observa al enemigo antes de actuar.'], 'robado'=>0, 'pregunta'=>null, 'defendiendo'=>false];
    }

    public function actuar(int $usuario, int $id, int $version, array $accion): string
    {
        return DB::transaction(function () use ($usuario,$id,$version,$accion) {
            $this->secuencia=[];
            abort_unless($this->disponible(),503,'El combate está en mantenimiento.');
            Usuario::whereKey($usuario)->lockForUpdate()->firstOrFail();
            $run = AventuraExpedicion::where('usuario_id',$usuario)->findOrFail($id);
            abort_unless($run->estado === 'activa' && $run->vida_actual > 0,409,'La partida no está activa.');
            abort_if($run->version !== $version,409,'Este turno ya cambió. Recarga para continuar.');
            $historial = $run->exploracion ?? [];
            $evento = $historial[$run->escenario] ?? null;
            $tipoEsperado = $run->escenario === 50 ? 'dragon' : ($run->escenario % 10 === 0 ? 'jefe' : 'enemigo');
            abort_unless($evento && $run->escenario <= 50 && $evento['tipo'] === $tipoEsperado,409,'Este encuentro aún no admite combate.');
            if ($accion['accion'] === 'continuar') {
                abort_if($run->escenario === 50,409,'Este es el último escenario. Tu resultado ya está guardado.');
                abort_unless($evento['estado'] === 'resuelto' && ($evento['combate']['estado'] ?? '') === 'victoria',409,'Primero debes ganar el combate.');
                $run->escenario++; $run->version++; $run->save();
                return 'mapa';
            }
            abort_unless($evento['estado'] === 'pendiente',409,'Este encuentro ya terminó.');
            $battle = $evento['combate'] ?? null;
            if ($accion['accion'] === 'abrir') {
                abort_if($battle !== null,409,'El combate ya está preparado.');
                $battle = $this->crear($run);
                $this->escena($run,$battle,'entrada','jugador',0);
            } else {
                abort_if($battle === null,409,'Primero entra al combate.');
                $estado = (new AventuraService())->estado($usuario);
                $stats = $estado['personaje'];
                if ($battle['estado'] === 'pregunta') {
                    abort_unless($accion['accion'] === 'responder',409,'Responde a las runas antes de actuar.');
                    $this->responder($run,$battle,$accion,$stats);
                } else {
                    abort_unless($battle['estado'] === 'jugador',409,'No es tu turno.');
                    $this->jugador($run,$battle,$accion,$stats);
                    if ($this->vivos($battle)) $this->turnoEnemigos($run,$battle,$stats);
                }
                if ($run->vida_actual <= 0) $this->finalizar($run,$battle,false);
                elseif (!$this->vivos($battle)) $this->finalizar($run,$battle,true);
            }
            $battle['registro'] = array_slice($battle['registro'], -18);
            $evento['combate'] = $battle;
            if (in_array($battle['estado'], ['victoria','derrota'],true)) $evento['estado'] = 'resuelto';
            $historial[$run->escenario] = $evento;
            $run->exploracion = $historial; $run->version++; $run->save();
            if ($battle['estado'] === 'derrota') {
                (new AventuraService())->registrarDerrota($usuario,$id);
                return 'derrota';
            }
            return 'mapa';
        },3);
    }

    private function vivos(array $battle): bool
    {
        foreach ($battle['enemigos'] as $enemy) if ($enemy['vida'] > 0) return true;
        return false;
    }

    private function jugador(AventuraExpedicion $run, array &$b, array $a, array $stats): void
    {
        $desde=count($b['registro']);
        $tipo = $a['accion']; $dano = 0; $elemento = 'fisico';
        $equipo = $run->equipo;
        $b['defendiendo'] = false;
        if ($tipo === 'atacar') $dano = 8 + 2 * $stats['total']['dano'] + AventuraMejoras::valor($run,'arma_dano');
        elseif ($tipo === 'defender') {
            $b['defendiendo'] = true;
            $run->mana_actual = min($stats['mana'],$run->mana_actual+4);
            $b['registro'][] = 'Te defiendes: mitad de daño directo este turno y recuperas hasta 4 de maná.';
        } elseif ($tipo === 'hechizo') {
            $id = $a['objeto'] ?? '';
            abort_unless(in_array($id,$equipo['hechizos'],true),422,'No tienes ese hechizo equipado.');
            $item = (new AventuraCatalogo())->items()[$id];
            abort_if($run->mana_actual < $item['mana'],422,'No tienes suficiente maná. Puedes defenderte o usar una poción.');
            $run->mana_actual -= $item['mana'];
            $magia = $stats['total']['magia'];
            $ataques = ['brasa'=>8,'escarcha'=>16,'relampago'=>28,'meteorito'=>45,'supernova'=>70];
            $curas = ['alivio'=>15,'renuevo'=>35,'luz-fenix'=>80];
            $maestria = AventuraMejoras::valorHechizo($run,$id);
            if (isset($ataques[$id])) { $dano = (int)ceil(($ataques[$id]+$magia)*(100+$maestria)/100); $elemento = in_array($id,['brasa','meteorito'],true) ? 'fuego' : 'magia'; }
            elseif (isset($curas[$id])) $run->vida_actual = min($stats['vida'],$run->vida_actual+(int)ceil(($curas[$id]+$magia)*(100+$maestria)/100));
            else {
                $efectos = array_values(array_filter($run->efectos ?? [],fn ($e) => $e['tipo'] !== 'barrera'));
                $efectos[] = ['tipo'=>'barrera','nombre'=>'Barrera protectora','valor'=>(int)ceil((($id === 'barrera' ? 18 : 50)+$magia)*(100+$maestria)/100)];
                $run->efectos = $efectos;
            }
            $nivel = AventuraMejoras::nivelHechizo($run,$id);
            $b['registro'][] = 'Usas '.$item['nombre'].($nivel > 1 ? ' nivel '.$nivel : '').': '.$item['mana'].' de maná.';
        } elseif ($tipo === 'pocion') {
            $id = $a['objeto'] ?? '';
            $valores = ['pocion-vida'=>[30,0],'pocion-mana'=>[0,20],'pocion-mayor'=>[75,0],'elixir'=>[40,25]];
            abort_unless(isset($valores[$id]),422,'Poción desconocida.');
            $fila = $run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo',$id))->where('cantidad','>',0)->first();
            abort_unless($fila,422,'Ya no tienes esa poción.');
            [$vida,$mana] = $valores[$id];
            abort_if(($vida === 0 || $run->vida_actual >= $stats['vida']) && ($mana === 0 || $run->mana_actual >= $stats['mana']),422,'No necesitas esta poción todavía.');
            $fila->cantidad--; $fila->save();
            $run->vida_actual = min($stats['vida'],$run->vida_actual+$vida);
            $run->mana_actual = min($stats['mana'],$run->mana_actual+$mana);
            $b['registro'][] = 'Consumes '.$fila->item->nombre.'. Recuperas hasta '.$vida.' de vida y '.$mana.' de maná.';
        } else abort(422,'Acción desconocida.');
        if ($dano > 0) {
            $inspiracion = array_sum(array_map(fn($e)=>$e['tipo'] === 'inspiracion' ? $e['valor'] : 0,$run->efectos ?? []));
            $dano = (int)ceil($dano*(100+AventuraMejoras::valor($run,'dano')+$inspiracion)/100);
            if (AventuraMejoras::valor($run,'critico') > 0 && $this->azar(1,100) <= AventuraMejoras::valor($run,'critico')) {
                $dano = (int)ceil($dano*1.5); $b['registro'][] = '¡Golpe crítico! Tu ojo del depurador encuentra una debilidad.';
            }
            $target = $a['objetivo'] ?? -1;
            abort_unless(isset($b['enemigos'][$target]) && $b['enemigos'][$target]['vida'] > 0,422,'Selecciona un enemigo que siga en pie.');
            $enemy = &$b['enemigos'][$target];
            $dano = max(1,$dano - ($elemento === 'fisico' ? $enemy['defensa'] : intdiv($enemy['defensa'],2)));
            if ($enemy['habilidad'] === 'resiste_magia' && $elemento !== 'fisico') $dano=max(1,(int)ceil($dano*.6));
            if ($enemy['habilidad'] === 'resiste_fisico' && $elemento === 'fisico') $dano=max(1,(int)ceil($dano*.65));
            if ($enemy['clave'] === 'archivista' && $elemento === 'fisico' && $b['turno']%2===0) { $dano=max(1,(int)ceil($dano*.75)); $b['registro'][]='El sello del Archivista amortigua tu ataque físico.'; }
            if ($enemy['clave'] === 'titan' && $elemento === 'fuego') $dano=(int)ceil($dano*1.5);
            if ($enemy['habilidad'] === 'coraza' && $elemento === 'fuego') $dano = (int) ceil($dano*1.5);
            if ($enemy['clave'] === 'dragon') {
                if ($elemento === 'fuego') $dano=max(1,(int)ceil($dano*.5));
                elseif ($elemento === 'fisico' && AventuraDragon::fase($enemy)===1) $dano=max(1,(int)ceil($dano*.7));
                if (!empty($enemy['expuesto'])) { $dano=(int)ceil($dano*1.5); $enemy['expuesto']=false; $b['registro'][]='¡Aprovechas la apertura de las runas! Tu golpe causa 50% más daño.'; }
                $faseAnterior=AventuraDragon::fase($enemy);
            }
            $efectivo = min($enemy['vida'],$dano);
            $enemy['vida'] = max(0,$enemy['vida']-$dano);
            if ($enemy['clave']==='dragon' && $enemy['vida']>0 && AventuraDragon::fase($enemy)!==$faseAnterior) $b['registro'][]='Ignar entra en fase '.AventuraDragon::fase($enemy).': '.AventuraDragon::nombre(AventuraDragon::fase($enemy)).'.';
            $curacion = (int)floor($efectivo*AventuraMejoras::valor($run,'robo_vida')/100);
            if ($curacion > 0) { $run->vida_actual = min($stats['vida'],$run->vida_actual+$curacion); $b['registro'][] = 'Tu vínculo vital recupera hasta '.$curacion.' de vida.'; }
            $b['registro'][] = 'Golpeas a '.$enemy['nombre'].' por '.$dano.' de daño.';
            if ($enemy['vida'] === 0) $b['registro'][] = $enemy['nombre'].' cae derrotado.';
            elseif ($enemy['habilidad'] === 'division' && !$enemy['dividido'] && $enemy['vida'] <= $enemy['max']/2) {
                $enemy['dividido'] = true;
                $fragmento = $enemy; $fragmento['nombre'] = 'Fragmento de rocío'; $fragmento['habilidad'] = 'ninguna';
                $fragmento['max'] = $fragmento['vida'] = max(1,intdiv($enemy['vida'],2));
                $enemy['vida'] -= $fragmento['vida']; $fragmento['xp'] = 0; $fragmento['oro'] = 0;
                $b['enemigos'][] = $fragmento; $b['registro'][] = '¡El slime se divide! Ahora hay dos objetivos, pero no duplica su vida ni sus recompensas.';
            }
            unset($enemy);
        }
        $this->escena($run,$b,$tipo,'jugador',$desde);
    }

    private function recibir(AventuraExpedicion $run, array &$b, int $dano, string $nombre): void
    {
        $dano = (int)ceil($dano*(100-AventuraMejoras::valor($run,'resistencia'))/100);
        $dano = max(0,$dano-AventuraMejoras::valor($run,'armadura_refuerzo'));
        if ($b['defendiendo']) $dano = (int) ceil($dano/2);
        $efectos = $run->efectos ?? [];
        foreach ($efectos as $i=>$efecto) if ($efecto['tipo'] === 'barrera') {
            $absorbido = min($dano,$efecto['valor']); $dano -= $absorbido;
            unset($efectos[$i]); $b['registro'][] = 'La barrera absorbe '.$absorbido.' de daño y se disipa.'; break;
        }
        $run->efectos = array_values($efectos);
        $run->vida_actual = max(0,$run->vida_actual-$dano);
        $b['registro'][] = $nombre.' te causa '.$dano.' de daño.';
    }

    private function turnoEnemigos(AventuraExpedicion $run, array &$b, array $stats): void
    {
        $invocaciones=[];
        foreach ($b['enemigos'] as $indice=>&$enemy) {
            if ($enemy['vida'] <= 0 || $run->vida_actual <= 0) continue;
            $desde=count($b['registro']);
            if ($enemy['habilidad'] === 'jefe' && ($enemy['clave']==='dragon' ? AventuraDragon::prepara($enemy,$b['turno']) : $b['turno'] % 3 === 0)) {
                if ($enemy['clave']==='dragon') $enemy['ataque_especial']=AventuraDragon::especial($enemy);
                $pregunta = (new AventuraPreguntas())->elegir($run->escenario,$b['preguntas_usadas'] ?? []);
                if ($pregunta) {
                    $b['preguntas_usadas'][]=$pregunta->id;
                    $b['pregunta'] = $pregunta->id; $b['estado'] = 'pregunta';
                    $b['registro'][] = $enemy['nombre'].' prepara '.($enemy['ataque_especial'] ?? 'Raíces rúnicas').'. ¡Responde para interrumpirlo!';
                    $this->escena($run,$b,'runas','enemigo-'.$indice,$desde);
                    return;
                }
                $b['registro'][] = 'Las runas están apagadas. El guardián usa un ataque normal.';
            }
            $dano = $enemy['ataque'];
            if ($enemy['clave']==='dragon') $dano=(int)ceil($dano*[1=>1,2=>1.15,3=>1.35][AventuraDragon::fase($enemy)]);
            elseif ($enemy['habilidad'] === 'jefe' && $enemy['vida'] <= $enemy['max']/2) $dano = (int) ceil($dano*1.4);
            if ($enemy['habilidad'] === 'embestida' && $b['turno'] % 3 === 0) { $dano *= 2; $b['registro'][] = '¡'.$enemy['nombre'].' se abalanza!'; }
            $vidaAnterior=$run->vida_actual;
            $this->recibir($run,$b,max(1,$dano-$stats['total']['armadura']),$enemy['nombre']);
            if ($enemy['habilidad'] === 'vampiro' || $enemy['clave'] === 'matriarca') {
                $cura=(int)floor(($vidaAnterior-$run->vida_actual)*($enemy['clave']==='matriarca' ? .25 : .35));
                $cura=min($cura,$enemy['max']-$enemy['vida']); $enemy['vida']+=$cura;
                $b['registro'][]=$enemy['nombre'].' absorbe '.$cura.' de vida del daño real causado.';
            }
            if (($enemy['habilidad']==='drenar' && $b['turno']%2===0) || $enemy['clave']==='titan') {
                $drenaje=$b['defendiendo'] ? 0 : min($run->mana_actual,1+($enemy['region'] ?? 1));
                $run->mana_actual-=$drenaje;
                $b['registro'][]=$drenaje ? 'El enemigo drena '.$drenaje.' de maná.' : 'Tu defensa impide el drenaje, o ya no queda maná que drenar.';
            }
            if ($enemy['habilidad']==='robo_item' && !$enemy['robo']) {
                $pociones=$run->inventario()->where('posicion','pocion')->where('cantidad','>',0)->get();
                if ($pociones->isNotEmpty()) {
                    $pocion=$pociones[$this->azar(0,$pociones->count()-1)]; $pocion->cantidad--; $pocion->save();
                    $b['objetos_robados'][$pocion->id]=($b['objetos_robados'][$pocion->id] ?? 0)+1;
                    $b['registro'][]='El enemigo esconde una '.$pocion->item->nombre.'. La recuperarás al vencer.';
                } else $b['registro'][]='El saqueador busca una poción… y encuentra un bolsillo vacío.';
                $enemy['robo']=true;
            }
            if ($enemy['habilidad']==='invocar' && empty($enemy['invocado']) && $b['turno']%3===0 && count($b['enemigos'])+count($invocaciones)<3) {
                $eco=$enemy; $eco['nombre']='Eco de '.$enemy['nombre']; $eco['habilidad']='ninguna';
                $eco['max']=$eco['vida']=20+($enemy['region'] ?? 1)*5; $eco['ataque']=max(1,intdiv($enemy['ataque'],2));
                $eco['defensa']=0; $eco['xp']=0; $eco['oro']=0; $eco['invocado']=true;
                $invocaciones[]=$eco; $enemy['invocado']=true;
                $b['registro'][]='Un eco aparece. Atacará desde el próximo turno y no entrega recompensas.';
            }
            if ($enemy['habilidad'] === 'robo' && !$enemy['robo']) {
                $robado = min(8,$run->oro); $run->oro -= $robado; $b['robado'] += $robado; $enemy['robo'] = true;
                $b['registro'][] = $robado ? 'El duende esconde '.$robado.' de oro. Lo recuperarás al ganar.' : 'El duende busca oro en tus bolsillos… y solo encuentra pelusa.';
            }
            if ($enemy['habilidad'] === 'veneno' && $b['turno'] % 3 === 0) {
                $valor=1+($enemy['region'] ?? 1);
                $this->envenenar($run,$valor);
                $b['registro'][] = 'El veneno causa '.$valor.' de daño al final de cada turno durante tres turnos.';
            }
            $this->escena($run,$b,'ataque','enemigo-'.$indice,$desde);
        }
        unset($enemy);
        foreach($invocaciones as $eco) $b['enemigos'][]=$eco;
        if($invocaciones) $this->escena($run,$b,'invocacion','ambiente',count($b['registro'])-1);
        $this->cerrarTurno($run,$b);
    }

    private function envenenar(AventuraExpedicion $run,int $valor): void
    {
        $efectos=array_values(array_filter($run->efectos ?? [],fn($e)=>$e['tipo']!=='veneno'));
        $efectos[]=['tipo'=>'veneno','nombre'=>'Veneno: '.$valor.' de daño por turno','turnos'=>3,'valor'=>$valor];
        $run->efectos=$efectos;
    }

    private function cerrarTurno(AventuraExpedicion $run, array &$b): void
    {
        $desde=count($b['registro']);
        $efectos = $run->efectos ?? [];
        foreach ($efectos as $i=>&$e) if ($e['tipo'] === 'veneno') {
            $valor=$e['valor'] ?? 2;
            $run->vida_actual = max(0,$run->vida_actual-$valor); $e['turnos']--;
            $b['registro'][] = 'El veneno te quita '.$valor.' de vida.';
            if ($e['turnos'] <= 0) unset($efectos[$i]);
        }
        unset($e); $run->efectos = array_values($efectos);
        $b['turno']++; $b['defendiendo'] = false;
        if(count($b['registro'])>$desde) $this->escena($run,$b,'efecto','ambiente',$desde);
    }

    private function responder(AventuraExpedicion $run, array &$b, array $a, array $stats): void
    {
        $desde=count($b['registro']);
        $agotado = ($a['tiempo_agotado'] ?? false) === true;
        abort_unless($agotado || in_array($a['respuesta'] ?? '',['a','b','c','d'],true),422,'Selecciona una respuesta.');
        $pregunta = PreguntaTest::find($b['pregunta']);
        if (!$pregunta) $b['registro'][] = 'La inscripción desapareció. El ataque rúnico se disipa.';
        elseif (!$agotado && $pregunta->esRespuestaCorrecta($a['respuesta'])) {
            $b['aciertos'] = ($b['aciertos'] ?? 0) + 1;
            $b['registro'][] = '¡Correcto! Entiendes las runas e interrumpes el ataque del guardián.';
            if ($b['enemigos'][0]['clave']==='dragon') {
                $b['enemigos'][0]['expuesto']=true;
                $run->mana_actual=min($stats['mana'],$run->mana_actual+6);
                $b['registro'][]='El Dragón queda expuesto: tu próximo golpe gana 50% de daño y recuperas hasta 6 de maná. La apertura no se acumula.';
            }
        }
        else {
            $b['registro'][] = ($agotado ? 'El tiempo se agotó. ' : '').'La respuesta correcta era '.$pregunta->{'opcion_'.$pregunta->respuesta_correcta}.'. El guardián libera su ataque.';
            $enemy = $b['enemigos'][0];
            $multiplicador=$enemy['clave']==='dragon' ? [1=>2,2=>2.3,3=>2.6][AventuraDragon::fase($enemy)] : ($enemy['vida'] <= $enemy['max']/2 ? 2.6 : 2);
            $this->recibir($run,$b,max(1,(int)ceil($enemy['ataque']*$multiplicador)-$stats['total']['armadura']),$enemy['ataque_especial'] ?? 'Raíces rúnicas');
            if ($enemy['clave']==='archivista') { $perdida=min(6,$run->mana_actual); $run->mana_actual-=$perdida; $b['registro'][]='El sello consume '.$perdida.' de maná.'; }
            if ($enemy['clave']==='matriarca') { $this->envenenar($run,4); $b['registro'][]='La marea deja veneno durante tres turnos.'; }
        }
        $b['pregunta'] = null; $b['estado'] = 'jugador';
        $this->escena($run,$b,'respuesta','enemigo-0',$desde);
        $this->cerrarTurno($run,$b);
    }

    private function finalizar(AventuraExpedicion $run, array &$b, bool $victoria): void
    {
        $desde=count($b['registro']);
        $b['estado'] = $victoria ? 'victoria' : 'derrota';
        $xp = $victoria ? AventuraMejoras::experiencia($run,array_sum(array_column($b['enemigos'],'xp'))) : 0;
        $oro = $victoria ? array_sum(array_column($b['enemigos'],'oro')) : 0;
        if ($victoria) {
            foreach($b['objetos_robados'] ?? [] as $fila=>$cantidad) $run->inventario()->whereKey($fila)->increment('cantidad',$cantidad);
            if (!empty($b['objetos_robados'])) { $b['registro'][]='Recuperas todas las pociones escondidas por los enemigos.'; $b['objetos_robados']=[]; }
            (new AventuraService())->otorgarExperiencia($run->usuario_id,'expedicion:'.$run->id.':combate:'.$run->escenario,$xp);
            $run->oro += $oro + $b['robado'];
            $b['registro'][] = 'Victoria: +'.$xp.' XP y +'.$oro.' de oro. Recuperas '.$b['robado'].' de oro robado.';
            $b['recompensa'] = ['xp'=>$xp,'oro'=>$oro];
            $max = (new AventuraService())->estado($run->usuario_id)['personaje']['vida'];
            $curacion = (int)ceil($max*AventuraMejoras::valor($run,'regeneracion')/100);
            if ($curacion > 0) { $run->vida_actual = min($max,$run->vida_actual+$curacion); $b['registro'][] = 'Descansas y recuperas hasta '.$curacion.' de vida.'; }
        } else $b['registro'][] = 'Has caído. Conservas el aprendizaje, la experiencia y tu nivel.';
        $run->efectos = array_values(array_filter($run->efectos ?? [],fn($e)=>!in_array($e['tipo'],['veneno','barrera','inspiracion'],true)));
        $record = new Combate();
        $record->usuario_id = $run->usuario_id; $record->enemigo_id = $b['enemigos'][0]['id'];
        $record->resultado = $b['estado']; $record->vida_restante_usuario = $run->vida_actual;
        $record->vida_restante_enemigo = array_sum(array_column($b['enemigos'],'vida'));
        $record->expedicion_id = $run->id; $record->escenario = $run->escenario;
        $record->resumen = ['turnos'=>$b['turno'],'enemigos'=>$b['enemigos'],'xp'=>$xp,'oro'=>$oro];
        $record->save();
        $this->escena($run,$b,$victoria?'victoria':'derrota','ambiente',$desde);
    }

    public function preguntaPublica(?array $battle): ?array
    {
        if (($battle['estado'] ?? '') !== 'pregunta') return null;
        $pregunta = PreguntaTest::find($battle['pregunta']);
        return $pregunta ? $pregunta->only(['pregunta','opcion_a','opcion_b','opcion_c','opcion_d']) : ['pregunta'=>'La inscripción desapareció. Elige una opción para continuar sin penalización.','opcion_a'=>'Continuar','opcion_b'=>'Continuar','opcion_c'=>'Continuar','opcion_d'=>'Continuar'];
    }
}
