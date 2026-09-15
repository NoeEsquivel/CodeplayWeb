<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Atlas de los cinco territorios · CodePlay</title>
    <link rel="stylesheet" href="{{ asset('css/aventura.css') }}?v={{ filemtime(public_path('css/aventura.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/aventura-mapa.css') }}?v={{ filemtime(public_path('css/aventura-mapa.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/aventura-combate.css') }}?v={{ filemtime(public_path('css/aventura-combate.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/aventura-arena.css') }}?v={{ filemtime(public_path('css/aventura-arena.css')) }}">
</head>
<body class="world-page" data-theme="{{ $mundo['region']['tema'] }}">
@php
    $bossIntroduction = match((int)$estado['expedicion']['escenario']) {
        10 => ['name' => 'Silvar', 'url' => config('aventura-cinematics.silvar_url')],
        20 => ['name' => 'Aster', 'url' => config('aventura-cinematics.aster_url')],
        30 => ['name' => 'Náyade', 'url' => config('aventura-cinematics.nayade_url')],
        40 => ['name' => 'Bóreas', 'url' => config('aventura-cinematics.boreas_url')],
        50 => ['name' => 'Ignar', 'url' => config('aventura-cinematics.ignar_url')],
        default => null,
    };
    $run = $estado['expedicion']; $personaje = $estado['personaje']; $region = $mundo['region'];
    $activa = $run['estado'] === 'activa'; $actual = $region['id'] === $mundo['actual']['id'];
    $combateEscena = $activa && $actual && !empty($mundo['evento']['combate']);
    $eventoEscena = $activa && $actual && $mundo['evento'] && !$combateEscena;
    $musicaBatallaBosque = (int)$region['id'] === 1 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaRuinas = (int)$region['id'] === 2 && !$combateEscena;
@endphp
<header class="topbar"><a class="back" href="{{ route('alumno.aventura') }}">← Campamento</a><a class="brand" href="{{ route('inicio.alumno') }}">CODE<span>PLAY</span><small>EL ATLAS</small></a><span class="player-name">{{ session('usuario_nombre', 'Explorador') }}</span></header>
<main class="world-shell {{ $combateEscena ? 'combat-stage' : ($eventoEscena ? 'event-stage' : '') }}" id="world" data-forest-music="{{ (int)$region['id'] === 1 && !$combateEscena ? 'true' : 'false' }}" data-ruins-music="{{ $musicaRuinas ? 'true' : 'false' }}" data-forest-battle-music="{{ $musicaBatallaBosque ? 'true' : 'false' }}" data-id="{{ $run['id'] }}" data-version="{{ $run['version'] }}" data-start="{{ route('alumno.aventura.iniciar') }}" data-path="{{ route('alumno.aventura.camino') }}" data-combat="{{ route('alumno.aventura.combate') }}" data-event="{{ route('alumno.aventura.evento') }}" data-guild="{{ route('alumno.aventura.gremio') }}">
    @if(!$combateEscena)
    <div @if($eventoEscena) inert aria-hidden="true" @endif>
    <div class="world-heading"><div><p class="eyebrow">CINCO TERRITORIOS · UNA HISTORIA POR ESCRIBIR</p><h1>El conocimiento abre caminos.</h1><p>Observa. Elige. Descubre qué te espera al otro lado.</p></div><div class="journey-number"><span>ESCENARIO</span><strong>{{ $run['escenario'] }}<small> / 50</small></strong><span>{{ $activa ? 'EXPEDICIÓN EN CURSO' : 'LISTO PARA COMENZAR' }}</span></div></div>
    <nav class="region-nav" aria-label="Regiones del atlas">
        @foreach($mundo['regiones'] as $r)
            <a href="{{ route('alumno.aventura.mapa', ['region' => $r['id']]) }}" @if($r['id'] === $region['id']) aria-current="page" @endif><span>0{{ $r['id'] }} <small>{{ $r['desde'] }}–{{ $r['hasta'] }}</small></span><b>{{ $r['nombre'] }}</b>@if($r['id'] === $mundo['actual']['id'])<i>Tu región</i>@else<i>Consultar territorio</i>@endif</a>
        @endforeach
    </nav>
    </div>
    @endif
    @if($eventoEscena)<div class="event-shade" aria-hidden="true"></div>@endif
    <div class="world-grid">
        @if(!$combateEscena)
        <section class="atlas" aria-labelledby="region-title" @if($eventoEscena) inert aria-hidden="true" @endif>
            <div class="atlas-title"><p class="eyebrow">REGIÓN {{ $region['id'] }} · {{ $region['subtitulo'] }}</p><h2 id="region-title">{{ $region['nombre'] }}</h2><span>Escenarios {{ $region['desde'] }}–{{ $region['hasta'] }}</span></div>
            @include('aventura.partials.territorio')
            <div class="atlas-legend"><span><i class="legend-current"></i>Tu posición</span><span>◇ Camino por descubrir</span><span>♜ Guardián obligatorio</span></div>
            <p class="atlas-caption">Los senderos guardan sus secretos. Sus señales orientan; su destino se descubre al elegir.</p>
        </section>
        @endif
        <aside class="traveler" aria-label="Estado del personaje" @if($eventoEscena || $combateEscena) inert aria-hidden="true" @endif>
            <div class="traveler-title"><img src="{{ asset('Imagenes/aventura/explorador.svg') }}" alt=""><div><p class="eyebrow">TU EXPLORADOR</p><h2>{{ session('usuario_nombre', 'Explorador') }}</h2><span>Nivel {{ $personaje['nivel'] }} · permanente</span></div></div>
            <div class="meter"><label for="world-health">Vida <b>{{ $run['vida_actual'] }} / {{ $personaje['vida'] }}</b></label><progress id="world-health" max="{{ $personaje['vida'] }}" value="{{ $run['vida_actual'] }}"></progress></div>
            <div class="meter mana-meter"><label for="world-mana">Maná <b>{{ $run['mana_actual'] }} / {{ $personaje['mana'] }}</b></label><progress id="world-mana" max="{{ $personaje['mana'] }}" value="{{ $run['mana_actual'] }}"></progress></div>
            <div class="meter xp-meter"><label for="world-xp">Experiencia <b>{{ $personaje['experiencia'] }} / {{ $personaje['siguiente'] }}</b></label><progress id="world-xp" max="{{ $personaje['siguiente'] }}" value="{{ $personaje['experiencia'] }}"></progress></div>
            <div class="travel-money"><span>Oro de la partida <b>{{ $run['oro'] }}</b></span><span>Puntos en equipo <b>{{ $run['inversion'] }}</b></span></div>
            <details open><summary>Equipo y mochila <span>{{ count($estado['inventario']) }}</span></summary><ul class="pack">@forelse($estado['inventario'] as $item)<li><span>{{ $item['nombre'] }}<small>{{ \App\Services\AventuraCatalogo::RAREZAS[$item['rareza']] ?? 'Objeto' }}</small></span><b>×{{ $item['cantidad'] }}</b></li>@empty<li>Viajas con la mochila vacía.</li>@endforelse</ul></details>
            <details><summary>Efectos activos <span>{{ count($run['efectos'] ?? []) }}</span></summary>@forelse($run['efectos'] ?? [] as $efecto)<p>{{ $efecto['nombre'] ?? $efecto['tipo'] ?? 'Efecto' }} @if(isset($efecto['turnos']))· {{ $efecto['turnos'] }} turnos @endif</p>@empty<p>No tienes efectos activos.</p>@endforelse</details>
            <details><summary>Mejoras de esta partida <span>{{ count($run['mejoras'] ?? []) }}</span></summary>@forelse($run['mejoras'] ?? [] as $mejora)<p>{{ $mejora['nombre'] ?? $mejora['tipo'] ?? 'Mejora' }} @if(isset($mejora['valor']))+{{ $mejora['valor'] }}{{ in_array($mejora['tipo'] ?? '', ['arma_dano','armadura_refuerzo'], true) ? '' : '%' }} @endif<br>{{ $mejora['descripcion'] ?? '' }}</p>@empty<p>Las decisiones del viaje podrán darte nuevas ventajas.</p>@endforelse</details>
            <p class="travel-save">◇ El viaje se guarda con cada decisión.</p>
        </aside>
        <section class="story" aria-labelledby="story-title">
            @if(!$mundo['disponible'])
                <p class="eyebrow">TU EQUIPO ESTÁ A SALVO</p><h2 id="story-title">El atlas se está preparando.</h2><p>Vuelve al campamento mientras terminamos de abrir los caminos.</p>
            @elseif(!$actual)
                <p class="eyebrow">UNA MIRADA AL HORIZONTE</p><h2 id="story-title">{{ $region['subtitulo'] }}</h2><p>{{ $region['relato'] }}</p><a class="primary-button" href="{{ route('alumno.aventura.mapa') }}">Volver a mi región →</a><p class="story-note">Consultar este territorio no mueve a tu personaje ni salta escenarios.</p>
            @elseif(!$activa)
                <p class="eyebrow">EL PRIMER PASO ES TUYO</p><h2 id="story-title">Un mundo espera al otro lado.</h2><p>{{ $region['relato'] }}</p><p>Llevarás tu equipo confirmado. Al iniciar, la selección quedará cerrada para esta expedición.</p>
                <button type="button" id="beginJourney" class="primary-button">Abrir los caminos →</button><a class="adjust-link" href="{{ route('alumno.aventura') }}">Revisar mi equipo</a>
                <p class="story-note">Explora cinco regiones, descubre tesoros y acepta encargos. Supera a los cuatro guardianes y reúne equipo, pociones y conocimientos para enfrentarte al Dragón del escenario 50.</p>
            @elseif($mundo['evento'] && $mundo['combate_disponible'] && in_array($mundo['evento']['tipo'], ['enemigo','jefe','dragon']))
                @include('aventura.partials.combate')
            @elseif($mundo['evento'] && in_array($mundo['evento']['tipo'], ['narrativa','tesoro']))
                @include('aventura.partials.evento')
            @elseif($mundo['evento'] && $mundo['evento']['tipo'] === 'gremio')
                @include('aventura.partials.gremio')
            @elseif($mundo['evento'])
                <p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · ENCUENTRO GUARDADO</p><h2 id="story-title">{{ $mundo['evento']['titulo'] }}</h2><p>{{ $mundo['evento']['texto'] }}</p><div class="pending-event"><span>◇</span><p><b>Tu siguiente capítulo está a salvo.</b>La resolución de este encuentro estará disponible próximamente. Puedes salir y volver: conservarás el mismo encuentro, equipo y recursos.</p></div>
            @else
                <p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · ELIGE TU RUMBO</p><h2 id="story-title">El camino se divide frente a ti.</h2><p>{{ $region['relato'] }}</p>
                <div class="path-choices">@foreach($region['caminos'] as $indice => $camino)<button type="button" data-path-choice="{{ $indice }}"><span class="path-index">0{{ $indice + 1 }} <i>↗</i></span><b>{{ $camino[0] }}</b><small>{{ $camino[1] }}</small></button>@endforeach</div>
            @endif
            @if($activa && $actual && (!$combateEscena || ($mundo['evento']['combate']['estado'] ?? '') === 'victoria'))
                @include('aventura.partials.diario')
            @endif
            <p id="worldError" role="alert" hidden></p>
        </section>
    </div>
    <footer class="camp-footer">No necesitas conocer todas las respuestas para comenzar a explorar.<span>CODEPLAY / APRENDER JUGANDO</span></footer>
</main>
<div id="worldEntrance" class="world-entrance" hidden role="status" aria-live="polite"><video class="world-entrance-video" playsinline preload="auto" aria-hidden="true"><source src="{{ asset('videos/aventura/inicio-codeplay2.mp4') }}?v={{ filemtime(public_path('videos/aventura/inicio-codeplay2.mp4')) }}" type="video/mp4"></video><p class="eyebrow">TU HISTORIA ESTÁ POR COMENZAR</p><h2>El bosque reconoce tus pasos.</h2><p id="entranceStatus">Atravesando el portal…</p></div>
@if(!empty($bossIntroduction['url']))
<div id="bossIntroduction" data-boss-name="{{ $bossIntroduction['name'] }}" class="world-entrance boss-cinematic" hidden role="dialog" aria-modal="true" aria-label="Presentación de {{ $bossIntroduction['name'] }}">
    <video class="world-entrance-video" playsinline preload="auto" aria-label="{{ $bossIntroduction['name'] }} aparece ante el héroe">
        <source src="{{ asset($bossIntroduction['url']) }}" type="video/mp4">
    </video>
    <p data-cinematic-status role="status">Preparando el encuentro con {{ $bossIntroduction['name'] }}…</p>
</div>
@endif
<div id="supernovaCinematic" class="world-entrance supernova-cinematic" hidden role="dialog" aria-modal="true" aria-label="El héroe canaliza Supernova">
    <video class="world-entrance-video" playsinline preload="metadata" aria-label="El héroe canaliza y libera Supernova">
        <source src="{{ asset('videos/aventura/supernova-cinematica.mp4') }}?v={{ filemtime(public_path('videos/aventura/supernova-cinematica.mp4')) }}" type="video/mp4">
    </video>
    <p data-cinematic-status role="status">Canalizando Supernova…</p>
</div>
<script src="{{ asset('js/aventura-arena.js') }}?v={{ filemtime(public_path('js/aventura-arena.js')) }}" defer></script>
<script src="{{ asset('js/aventura-mapa.js') }}?v={{ filemtime(public_path('js/aventura-mapa.js')) }}" defer></script>

<div class="forest-music-controls" id="forestMusicControls" hidden role="group" aria-label="Música del bosque">
    <button type="button" aria-label="Reproducir música del bosque">▶ Música</button>
    <label for="forestMusicVolume">Volumen</label>
    <input id="forestMusicVolume" type="range" min="0" max="100" value="35" aria-label="Volumen de la música del bosque">
    <output for="forestMusicVolume">35%</output>
</div>
<audio id="forestMusic" loop preload="metadata" src="{{ asset('audio/aventura/bosque-inicio.mp3') }}"></audio>
<div class="forest-music-controls" id="ruinsMusicControls" hidden role="group" aria-label="Música de Ruinas Antiguas">
    <button type="button" aria-label="Reproducir música de Ruinas Antiguas">▶ Música</button>
    <label for="ruinsMusicVolume">Volumen</label>
    <input id="ruinsMusicVolume" type="range" min="0" max="100" value="35" aria-label="Volumen de la música de Ruinas Antiguas">
    <output for="ruinsMusicVolume">35%</output>
</div>
<audio id="ruinsMusic" loop preload="metadata" src="{{ asset('audio/aventura/ruinas-antiguas.mp3') }}"></audio>
<audio id="forestBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-bosque.mp3') }}"></audio>
<script src="{{ asset('js/aventura-musica.js') }}?v={{ filemtime(public_path('js/aventura-musica.js')) }}" defer></script>
</body>
</html>
