@php
    $n = $mundo['evento']['narrativa'] ?? null;
    $cofre = $mundo['evento']['tipo'] === 'tesoro';
    $relato = !$cofre && $n ? \App\Services\AventuraRelatos::todos()[$n['id']] : null;
    $etapa = $n['etapa'] ?? 'descubrir';
    $catalogoEvento = collect($estado['catalogo'])->keyBy('id');
@endphp
<div class="event-emblem" aria-hidden="true">
    <svg viewBox="0 0 120 90" fill="none"><circle cx="60" cy="44" r="36" stroke="currentColor" opacity=".25"/>
    @if($cofre)<path d="M24 42h72v36H24zM24 42v-9q36-24 72 0v9M55 40h10v15H55zM37 29v49m46-49v49" stroke="currentColor" stroke-width="2"/>
    @else<path d="M25 22q18-7 35 4 17-11 35-4v46q-18-7-35 4-17-11-35-4ZM60 26v46m-26-36 17 3m-17 8 17 3m18-11 17-3m-17 14 17-3" stroke="currentColor" stroke-width="2"/>
    @endif
    <path d="m101 8 2 5 5 2-5 2-2 5-2-5-5-2 5-2ZM16 11v9m-4-4h8" stroke="currentColor"/>
    </svg>
</div>
<p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · {{ $cofre ? 'UN SECRETO BAJO LLAVE' : 'HISTORIAS DEL CAMINO' }}</p>
<h2 id="story-title">{{ $cofre ? 'El cofre olvidado' : ($relato[0] ?? 'Una historia te espera') }}</h2>
@if($etapa === 'descubrir')
    <p>Tu encuentro sigue guardado. Acércate para descubrir quién te espera.</p>
    <button type="button" class="primary-button" data-event-action="descubrir">Acercarme →</button>
@elseif($etapa === 'decision')
    <p>{{ $cofre ? 'Entre las raíces hay un cofre cubierto de polvo. Podría guardar un regalo, una pregunta… o algo con demasiado apetito.' : $relato[1] }}</p>
    @if(!empty($n['pista']))<p class="event-hint">{{ $relato[4] }}</p>@endif
    <div class="event-actions">
        <button type="button" class="primary-button" data-event-action="{{ $cofre ? 'abrir' : 'ayudar' }}">{{ $cofre ? 'Abrir el cofre' : $relato[2] }}</button>
        @if(!$cofre && empty($n['pista']))<button type="button" class="subtle-button" data-event-action="preguntar">{{ $relato[3] }}</button>@endif
        <button type="button" class="subtle-button" data-event-action="ignorar">Seguir mi camino</button>
    </div>
    <p class="story-note">Puedes seguir sin recompensa ni penalización. Si aceptas el reto, acertar trae una recompensa; fallar puede costarte vida, oro o atraer un enemigo.</p>
@elseif($etapa === 'pregunta')
    <div class="rune-question">
        <p class="eyebrow">TU CONOCIMIENTO FORMA PARTE DE LA HISTORIA</p>
        <h3>{{ $mundo['pregunta_evento']['pregunta'] ?? 'La inscripción desapareció.' }}</h3>
        <p>Lee las opciones con calma. Solo tienes un intento en este encuentro.</p>
        @if($mundo['pregunta_evento'])
        <div class="rune-answers">@foreach(\App\Services\AventuraPreguntas::ordenOpciones(['evento', $run['id'], $run['escenario'], $n['pregunta']]) as $posicion => $letra)<button type="button" data-event-action="responder" data-answer="{{ $letra }}"><b>{{ ['A','B','C','D'][$posicion] }}</b> {{ $mundo['pregunta_evento']['opcion_'.$letra] }}</button>@endforeach</div>
        @else<button type="button" class="primary-button" data-event-action="responder" data-answer="a">Continuar sin penalización</button>@endif
    </div>
@else
    <div class="event-result" role="status"><p>{{ $n['resultado'] }}</p><ul>@foreach($n['detalle'] as $detalle)<li>{{ $detalle }}</li>@endforeach</ul></div>
    @if(!empty($n['mejora_hechizo']))
        <div class="event-loot"><p class="eyebrow">FOCO ARCANO · MUY RARO</p><h3>Elige un hechizo para mejorar</h3><p>La mejora dura toda esta aventura. El hechizo subirá un nivel y ganará 10% de daño, curación o protección.</p>
        <div class="event-actions">@foreach($run['equipo']['hechizos'] as $hechizo)<button type="button" class="subtle-button" data-event-action="mejorar_hechizo" data-spell="{{ $hechizo }}">Mejorar {{ $catalogoEvento[$hechizo]['nombre'] }} →</button>@endforeach</div></div>
    @endif
    @if(!empty($n['botin']))
        @php $botin = $catalogoEvento[$n['botin']]; @endphp
        <div class="event-loot"><p class="eyebrow">{{ \App\Services\AventuraCatalogo::RAREZAS[$botin['rareza']] }}</p><h3>{{ $botin['nombre'] }}</h3><p>{{ $botin['descripcion'] }}</p>
        @foreach($botin['bonus'] as $atributo => $valor)<span class="loot-bonus">+{{ $valor }} {{ ['dano'=>'daño','magia'=>'magia','armadura'=>'armadura','inteligencia'=>'inteligencia','vitalidad'=>'vitalidad'][$atributo] }}</span>@endforeach
        @if($botin['tipo'] !== 'pocion' && empty($n['equipado']))
            @if($botin['tipo'] === 'hechizo' && count($run['equipo']['hechizos']) >= $personaje['limite_hechizos'])
                <label for="replaceSpell">Ya llevas {{ $personaje['limite_hechizos'] }} hechizos. Elige cuál dejar en la mochila:</label><select id="replaceSpell">@foreach($run['equipo']['hechizos'] as $clave)<option value="{{ $clave }}">{{ $catalogoEvento[$clave]['nombre'] }}</option>@endforeach</select>
            @endif
            <button type="button" class="subtle-button" data-event-action="equipar">Equipar este objeto</button><p class="story-note">Reemplaza su espacio de equipo sin curarte. También puedes conservarlo en la mochila.</p>
        @else<p>{{ $botin['tipo'] === 'pocion' ? 'Lista para usar durante un combate.' : 'Ya está equipado.' }}</p>@endif
        </div>
    @endif
    <p class="story-note">Vida: {{ $run['vida_actual'] }}/{{ $personaje['vida'] }} · Oro de la partida: {{ $run['oro'] }} · Nivel: {{ $personaje['nivel'] }}</p>
    @if(empty($n['mejora_hechizo']))<button type="button" class="primary-button" data-event-action="continuar">Volver al mapa y continuar →</button>@endif
@endif
