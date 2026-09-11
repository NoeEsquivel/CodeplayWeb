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
                    <div class="rune-question"><h4>{{ $entrada['pregunta']['pregunta'] ?? 'La inscripción desapareció.' }}</h4>
                    @if($entrada['pregunta'])<p>Responde una vez. Si fallas, no pierdes recursos; este encargo termina y libera su espacio.</p><div class="rune-answers">@foreach(['a','b','c','d'] as $letra)<button type="button" data-guild-action="responder" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}" data-answer="{{ $letra }}">{{ strtoupper($letra) }} · {{ $entrada['pregunta']['opcion_'.$letra] }}</button>@endforeach</div>
                    @else<button type="button" class="subtle-button" data-guild-action="responder" data-quest="{{ $entrada['clave'] }}" data-source="{{ $entrada['origen'] }}" data-answer="a">Cerrar sin penalización</button>@endif</div>
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
