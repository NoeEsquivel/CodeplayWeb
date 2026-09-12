@php $battle = $mundo['evento']['combate'] ?? null; $catalogo = collect($estado['catalogo'])->keyBy('id'); @endphp
@if(!$battle)
@if(!empty($mundo['evento']['narrativa']['resultado']))<div class="event-result"><p>{{ $mundo['evento']['narrativa']['resultado'] }}</p><ul>@foreach($mundo['evento']['narrativa']['detalle'] ?? [] as $detalle)<li>{{ $detalle }}</li>@endforeach</ul></div>@endif
<p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · ENCUENTRO HOSTIL</p><h2 id="story-title">{{ $mundo['evento']['titulo'] }}</h2><p>{{ $mundo['evento']['texto'] }}</p>
<div class="battle-intro"><span>⚔</span><p><b>Piensa antes de atacar.</b>Inspeccionar al enemigo no consume turno. Atacar, defender, lanzar un hechizo o beber una poción sí le dará oportunidad de responder.</p></div>
@if($run['escenario']===50)<div class="dragon-guide"><h3>El último desafío: Ignar</h3><p>1000 de vida, tres fases y ningún camino para rodearlo. Tus recursos actuales se conservan: entrar no te cura.</p><p>Lleva equipo avanzado y reserva pociones. El fuego pierde fuerza contra sus escamas; prueba otros hechizos. Responder bien interrumpe sus runas y abre una oportunidad para atacar. Puedes volver al campamento y reiniciar para prepararte mejor.</p></div>@endif
<button class="primary-button" type="button" data-combat-action="abrir">Entrar en combate →</button>
@else
<p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · TURNO {{ $battle['turno'] }} · {{ $region['nombre'] }}</p><h2 id="story-title">{{ $battle['estado'] === 'victoria' ? 'El camino vuelve a abrirse.' : ($run['escenario'] % 10 === 0 ? $battle['enemigos'][0]['nombre'] : 'Tu ingenio también es un arma.') }}</h2>
<div class="battle-status">{{ $battle['estado'] === 'pregunta' ? '✧ Ataque rúnico: responde para interrumpirlo' : ($battle['estado'] === 'victoria' ? '✦ VICTORIA' : 'Es tu turno · elige un objetivo y una acción') }}</div>
@include('aventura.partials.arena')
<div class="combat-hud" aria-label="Tus recursos en combate"><span>♥ Tu vida <b>{{ $run['vida_actual'] }} / {{ $personaje['vida'] }}</b></span><span>◆ Tu maná <b>{{ $run['mana_actual'] }} / {{ $personaje['mana'] }}</b></span></div>
@if($run['escenario']===50 && $battle['estado']==='jugador')
<aside class="dragon-guide" aria-label="Anticipa al Dragón"><b>{{ \App\Services\AventuraDragon::prepara($battle['enemigos'][0],$battle['turno']) ? 'Las runas se encienden: prepara un ataque especial.' : 'El Dragón se dispone a lanzar un golpe directo.' }}</b><p>El ataque puede cambiar si tu golpe lo lleva a otra fase. Defender reduce el daño; una poción también consume turno.</p>@if(!empty($battle['enemigos'][0]['expuesto']))<p>✧ Apertura disponible: tu próximo golpe causa 50% más daño.</p>@endif</aside>
@endif
<div class="enemy-grid">
@php $seleccionado = false; @endphp
@foreach($battle['enemigos'] as $indice => $enemigo)
<article data-enemy-index="{{ $indice }}" class="enemy-card {{ $enemigo['vida'] <= 0 ? 'fallen' : '' }}" data-enemy="{{ $enemigo['clave'] }}">
    @php
        $sprite = match($enemigo['clave']) {
            'lobo' => 'lobo-bosque.png',
            'slime', 'mimic' => 'slime-bosque.png',
            'ladron' => 'duende-saqueador.png',
            'avispa' => 'avispa-esmeralda.png',
            'hongo' => 'hongo-acorazado.png',
            'silvar' => ($enemigo['vida'] <= $enemigo['max'] / 2 ? 'silvar-fase-2.png' : 'silvar-fase-1.png'),
            'momia' => 'momia-archivo.png',
            'vampiro' => 'vampiro-ruinas.png',
            'espectro' => 'espectro-tinta.png',
            'centinela' => 'centinela-piedra.png',
            'nigromante' => 'nigromante-archivo.png',
            'archivista' => ($enemigo['vida'] <= $enemigo['max'] / 2 ? 'aster-archivista-fase-2.png' : 'aster-archivista-fase-1.png'),
            'sanguijuela' => 'sanguijuela-gigante.png',
            'bruja' => 'bruja-pantano.png',
            'cienago' => 'slime-cienaga.png',
            'saqueador' => 'saqueador-fango.png',
            'basilisco' => 'basilisco-musgo.png',
            'matriarca' => ($enemigo['vida'] <= $enemigo['max'] / 2 ? 'nayade-matriarca-fase-2.png' : 'nayade-matriarca-fase-1.png'),
            'yeti' => 'yeti-ventisca.png',
            'hechicero' => 'hechicero-escarcha.png',
            'golem_hielo' => 'golem-hielo.png',
            'lince' => 'lince-cristal.png',
            'alma_nieve' => 'alma-nieve.png',
            'titan' => ($enemigo['vida'] <= $enemigo['max'] / 2 ? 'boreas-titan-fase-2.png' : 'boreas-titan-fase-1.png'),
            'salamandra' => 'salamandra-ceniza.png',
            'caballero' => 'caballero-obsidiana.png',
            'cultista' => 'cultista-volcan.png',
            'devorador' => 'devorador-brasas.png',
            'acechador' => 'acechador-lava.png',
            'dragon' => match(\App\Services\AventuraDragon::fase($enemigo)) {
                1 => 'ignar-fase-1.png',
                2 => 'ignar-fase-2.png',
                default => 'ignar-fase-3.png',
            },
            default => null,
        };
    @endphp
    <div class="enemy-art {{ $sprite ? 'enemy-art-image' : '' }}" aria-hidden="true">@if($sprite)<img src="{{ asset('images/aventura/'.$sprite) }}" alt="">@else @include('aventura.partials.enemigo-arte') @endif</div>
    <label class="target-label"><input type="radio" name="enemy-target" value="{{ $indice }}" @disabled($enemigo['vida'] <= 0 || $battle['estado'] !== 'jugador') @checked(!$seleccionado && $enemigo['vida'] > 0)><b>{{ $enemigo['nombre'] }}</b></label>
    @php if($enemigo['vida'] > 0) $seleccionado = true; @endphp
    <span class="enemy-level">Nivel {{ $enemigo['nivel'] }} @if($enemigo['clave']==='dragon')· FASE {{ \App\Services\AventuraDragon::fase($enemigo) }} · {{ \App\Services\AventuraDragon::nombre(\App\Services\AventuraDragon::fase($enemigo)) }}@elseif($enemigo['habilidad'] === 'jefe')· {{ $enemigo['vida'] <= $enemigo['max']/2 ? 'FASE 2 · FURIA' : 'FASE 1 · GUARDIÁN' }}@endif</span>
    <progress max="{{ $enemigo['max'] }}" value="{{ $enemigo['vida'] }}" aria-label="Vida de {{ $enemigo['nombre'] }}"></progress><small>{{ $enemigo['vida'] }} / {{ $enemigo['max'] }} vida</small>
    <details><summary>Inspeccionar · sin gastar turno</summary><p>Ataque {{ $enemigo['ataque'] }} · Defensa {{ $enemigo['defensa'] }}<br>{{ $enemigo['clave'] === 'dragon' ? '???? Ignar es demasiado poderoso, no eres capaz de leer sus debilidades o es probable que no las tenga.' : $enemigo['descripcion'] }}</p><p>Recompensa: {{ $enemigo['xp'] }} XP · {{ $enemigo['oro'] }} oro.</p></details>
</article>
@endforeach
</div>
@if($battle['estado'] === 'victoria')
    <div class="battle-victory"><b>+{{ $battle['recompensa']['xp'] }} XP</b><b>+{{ $battle['recompensa']['oro'] }} oro</b><p>Tu experiencia permanece. El oro pertenece a esta expedición.</p></div>
    @if($run['escenario']===50)
    <div class="dragon-guide"><h3>El Dragón ha caído. Llegaste al último horizonte.</h3><p>Tu victoria está guardada. Cada intento, cada decisión y cada respuesta te ayudaron a superar este desafío.</p></div>
    <a class="primary-button" href="{{ route('alumno.aventura') }}">Volver al campamento →</a>
    @endif
@elseif($battle['estado'] === 'pregunta')
    <div class="rune-question-backdrop" role="presentation"><div class="rune-question" role="dialog" aria-modal="true" aria-labelledby="rune-question-title"><div class="rune-question-timer" aria-live="assertive"><span>Tiempo para responder</span><b data-rune-timer>15</b><small>segundos</small></div><p class="eyebrow">{{ $battle['enemigos'][0]['ataque_especial'] ?? 'RAÍCES RÚNICAS' }}</p><h3 id="rune-question-title">{{ $mundo['pregunta']['pregunta'] }}</h3><p>Una respuesta correcta interrumpe el ataque especial. Si fallas, el guardián ejecutará el ataque. La defensa y la barrera que preparaste aún reducen su daño directo.</p><div class="rune-answers">@foreach(['a','b','c','d'] as $letra)<button type="button" data-combat-action="responder" data-answer="{{ $letra }}"><b>{{ strtoupper($letra) }}</b> {{ $mundo['pregunta']['opcion_'.$letra] }}</button>@endforeach</div></div></div>
@else
    <div class="battle-resources"><section><h3>Tus hechizos</h3></section><section><h3>Tus pociones</h3></section></div>
    <p class="story-note">Cada acción permite responder a todos los enemigos que sigan en pie. La defensa reduce a la mitad sus golpes de este turno; una barrera absorbe el siguiente golpe.</p>
@endif
<details class="battle-log" open><summary>Lo que acaba de ocurrir</summary><ol>@foreach($battle['registro'] as $linea)<li>{{ $linea }}</li>@endforeach</ol></details>
@endif
