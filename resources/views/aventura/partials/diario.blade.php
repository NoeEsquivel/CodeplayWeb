@if(count($mundo['diario']))
@php $itemsDiario=(new \App\Services\AventuraCatalogo())->items(); $activos=count(array_filter($mundo['diario'],fn($m)=>$m['encargo']['estado']==='activo')); @endphp
<section class="guild-journal" aria-labelledby="journal-title">
    <p class="eyebrow">TU VIAJE TAMBIÉN AYUDA A OTROS</p><h3 id="journal-title">Diario de encargos <small>{{ $activos }}/3 activos</small></h3>
    @foreach($mundo['diario'] as $entrada)
        @php $d=$entrada['definicion']; $q=$entrada['encargo']; $estadoEncargo=$q['estado']; $lista=$estadoEncargo==='activo' && $entrada['progreso'] >= $d['meta']; @endphp
        <details class="guild-task" data-journal-quest="{{ $entrada['clave'] }}" data-journal-source="{{ $entrada['origen'] }}" @if($estadoEncargo==='activo') open @endif>
            <summary>{{ $d['nombre'] }} <span>{{ $lista ? 'Lista para cobrar' : ['activo'=>'En curso','cobrado'=>'Recompensa cobrada','abandonado'=>'Abandonado','cancelado'=>'Cancelado','fallido'=>'Sigue aprendiendo'][$estadoEncargo] }}</span></summary>
            <p>{{ $d['texto'] }}</p><p class="story-note">Aceptado en el escenario {{ $entrada['origen'] }}.</p>
            @if($estadoEncargo==='activo')
                <progress max="{{ $d['meta'] }}" value="{{ $entrada['progreso'] }}" aria-label="Progreso de {{ $d['nombre'] }}"></progress><p>{{ $entrada['progreso'] }} / {{ $d['meta'] }} · Recompensa: {{ $d['xp'] }} XP base y {{ $d['oro'] }} oro.</p>
                @if($d['tipo']==='acertijo' && !isset($q['correcta']))
                    @if($entrada['pregunta'])<div class="rune-question-backdrop guild-rune-question-backdrop" data-guild-rune-question data-guild-question="{{ $entrada['clave'] }}" data-guild-source="{{ $entrada['origen'] }}" role="presentation"><div class="rune-question" role="dialog" aria-modal="true" aria-labelledby="guild-rune-question-title"><div class="rune-question-timer" aria-live="assertive"><span>Tiempo para responder</span><b data-guild-rune-timer>30</b><small>segundos</small></div><p class="eyebrow">ENCARGO DEL GREMIO</p><h3 id="guild-rune-question-title">{{ $entrada['pregunta']['pregunta'] }}</h3><p>Responde antes de que termine el tiempo. Si fallas o se agota, el encargo termina y libera su espacio.</p><div class="rune-answers">@foreach(\App\Services\AventuraPreguntas::ordenOpciones(['gremio', $run['id'], $entrada['origen'], $entrada['clave'], $q['pregunta']]) as $posicion => $letra)<button type="button" data-guild-action="responder" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}" data-answer="{{ $letra }}"><b>{{ ['A','B','C','D'][$posicion] }}</b> {{ $entrada['pregunta']['opcion_'.$letra] }}</button>@endforeach</div></div></div>
                    @else<div class="rune-question"><h4>La inscripción desapareció.</h4><button type="button" class="subtle-button" data-guild-action="responder" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}" data-answer="a">Cerrar sin penalización</button></div>@endif
                @endif
                @if($lista)<button type="button" class="primary-button" data-guild-action="cobrar" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}">{{ $d['tipo']==='entrega' ? 'Entregar 1 poción y cobrar' : 'Cobrar recompensa' }}</button>@endif
                <details class="guild-abandon"><summary>Abandonar encargo</summary><p>No recibirás premio ni podrás aceptar otra vez este encargo del mismo tablón.</p><button type="button" class="subtle-button" data-guild-action="abandonar" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}">Confirmar abandono</button></details>
            @endif
            @if(isset($q['mensaje']))<p class="event-hint">{{ $q['mensaje'] }}</p>@endif
            @if(isset($q['detalle']))<ul>@foreach($q['detalle'] as $detalle)<li>{{ $detalle }}</li>@endforeach</ul>@endif
            @if($estadoEncargo==='cobrado' && !empty($q['botin']) && $itemsDiario[$q['botin']]['tipo'] !== 'pocion' && empty($q['equipado']))
                <p>{{ $itemsDiario[$q['botin']]['nombre'] }}: {{ $itemsDiario[$q['botin']]['descripcion'] }} Reemplaza su espacio de equipo, sin curarte.</p>
                <button type="button" class="subtle-button" data-guild-action="equipar" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}">Equipar recompensa</button>
            @elseif(!empty($q['equipado']))<p>Recompensa equipada.</p>@endif
        </details>
    @endforeach
</section>
@endif
