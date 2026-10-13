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
    $mapaBosque3d = $activa && $actual && (int) $region['id'] === 1;
    $mapaRuinas3d = $activa && $actual && (int) $region['id'] === 2;
    $mapaPantano3d = $activa && $actual && (int) $region['id'] === 3;
    $mapaHielo3d = $activa && $actual && (int) $region['id'] === 4;
    $mapaDragon3d = $activa && $actual && (int) $region['id'] === 5;
    $mapaTerritorio3d = $mapaBosque3d || $mapaRuinas3d || $mapaPantano3d || $mapaHielo3d || $mapaDragon3d;
    $forestTieneDecision = $mapaTerritorio3d && empty($mundo['evento']);
    $caminosActuales = $mapaBosque3d
        ? \App\Services\AventuraMundo::caminosBosque((int) $run['escenario'])
        : ($mapaRuinas3d
            ? \App\Services\AventuraMundo::caminosRuinas((int) $run['escenario'])
            : ($mapaPantano3d ? \App\Services\AventuraMundo::caminosPantano((int) $run['escenario']) : ($mapaHielo3d ? \App\Services\AventuraMundo::caminosHielo((int) $run['escenario']) : ($mapaDragon3d ? \App\Services\AventuraMundo::caminosDragon((int) $run['escenario']) : $region['caminos']))));
    $musicaBatallaBosque = (int)$region['id'] === 1 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaBatallaRuinas = (int)$region['id'] === 2 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaBatallaPantano = (int)$region['id'] === 3 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaBatallaHielo = (int)$region['id'] === 4 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaBatallaDragon = (int)$region['id'] === 5 && $combateEscena && (($mundo['evento']['combate']['estado'] ?? '') !== 'victoria');
    $musicaRuinas = (int)$region['id'] === 2 && !$combateEscena;
    $musicaPantano = (int)$region['id'] === 3 && !$combateEscena;
    $musicaHielo = (int)$region['id'] === 4 && !$combateEscena;
    $musicaDragon = (int)$region['id'] === 5 && !$combateEscena;
@endphp
<header class="topbar"><a class="back" href="{{ route('alumno.aventura') }}">← Campamento</a><a class="brand" href="{{ route('inicio.alumno') }}">CODE<span>PLAY</span><small>EL ATLAS</small></a><span class="player-name">{{ $personaje['nombre'] }}</span></header>
<main class="world-shell {{ $combateEscena ? 'combat-stage' : ($eventoEscena ? 'event-stage' : '') }}" id="world" data-forest-music="{{ (int)$region['id'] === 1 && !$combateEscena ? 'true' : 'false' }}" data-ruins-music="{{ $musicaRuinas ? 'true' : 'false' }}" data-swamp-music="{{ $musicaPantano ? 'true' : 'false' }}" data-ice-music="{{ $musicaHielo ? 'true' : 'false' }}" data-dragon-music="{{ $musicaDragon ? 'true' : 'false' }}" data-forest-battle-music="{{ $musicaBatallaBosque ? 'true' : 'false' }}" data-ruins-battle-music="{{ $musicaBatallaRuinas ? 'true' : 'false' }}" data-swamp-battle-music="{{ $musicaBatallaPantano ? 'true' : 'false' }}" data-ice-battle-music="{{ $musicaBatallaHielo ? 'true' : 'false' }}" data-dragon-battle-music="{{ $musicaBatallaDragon ? 'true' : 'false' }}" data-id="{{ $run['id'] }}" data-version="{{ $run['version'] }}" data-start="{{ route('alumno.aventura.iniciar') }}" data-path="{{ route('alumno.aventura.camino') }}" data-combat="{{ route('alumno.aventura.combate') }}" data-event="{{ route('alumno.aventura.evento') }}" data-guild="{{ route('alumno.aventura.gremio') }}">
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
            @if($mapaBosque3d) @include('aventura.partials.territorio-bosque-3d') @elseif($mapaRuinas3d) @include('aventura.partials.territorio-ruinas-3d') @elseif($mapaPantano3d) @include('aventura.partials.territorio-pantano-3d') @elseif($mapaHielo3d) @include('aventura.partials.territorio-hielo-3d') @elseif($mapaDragon3d) @include('aventura.partials.territorio-dragon-3d') @else @include('aventura.partials.territorio') @endif
            <div class="atlas-legend"><span><i class="legend-current"></i>Tu posición</span><span>◇ Camino por descubrir</span><span>♜ Guardián obligatorio</span></div>
            <p class="atlas-caption">Los senderos guardan sus secretos. Sus señales orientan; su destino se descubre al elegir.</p>
        </section>
        @endif
        <aside class="traveler" aria-label="Estado del personaje" @if($eventoEscena || $combateEscena) inert aria-hidden="true" @endif>
            <div class="traveler-title"><img src="{{ asset('Imagenes/aventura/explorador.svg') }}" alt=""><div><p class="eyebrow">TU EXPLORADOR</p><h2>{{ $personaje['nombre'] }}</h2><span>Nivel {{ $personaje['nivel'] }} · permanente</span></div></div>
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
                @if($mapaTerritorio3d)
                    <p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · PRÓXIMO DESTINO</p><h2 id="story-title">{{ $mapaBosque3d ? ($run['escenario'] === 10 ? 'El claro de Silvar te espera.' : 'El sendero se abre ante ti.') : ($mapaRuinas3d ? ($run['escenario'] === 20 ? 'El santuario de Aster te espera.' : 'Las ruinas señalan tu siguiente paso.') : ($mapaPantano3d ? ($run['escenario'] === 30 ? 'El santuario de Náyade te espera.' : 'El pantano señala tu siguiente paso.') : ($mapaHielo3d ? ($run['escenario'] === 40 ? 'El trono de Bóreas te espera.' : 'Las montañas señalan tu siguiente paso.') : ($run['escenario'] === 50 ? 'La guarida de Ignar te espera.' : 'Las tierras del Dragón señalan tu siguiente paso.')))) }}</h2><p>{{ $mapaBosque3d ? ($run['escenario'] === 10 ? 'El bosque guarda silencio. Solo queda seguir el resplandor de las raíces hasta el Guardián de la región.' : 'El siguiente punto del mapa parpadea. Púlsalo para acercarte al bosque y elegir cómo continuar.') : ($mapaRuinas3d ? ($run['escenario'] === 20 ? 'Las antorchas del santuario arden a lo lejos. Solo queda enfrentar al Guardián de los Archivos.' : 'El siguiente punto del mapa parpadea. Púlsalo para acercarte a las ruinas y elegir cómo continuar.') : ($mapaPantano3d ? ($run['escenario'] === 30 ? 'Las aguas se aquietan frente al santuario. Solo queda enfrentar a la Guardiana de las Aguas Sin Nombre.' : 'El siguiente punto del mapa parpadea. Púlsalo para acercarte al pantano y elegir cómo continuar.') : ($mapaHielo3d ? ($run['escenario'] === 40 ? 'La tormenta se abre frente al trono. Solo queda enfrentar al Guardián de la Tormenta.' : 'El siguiente punto del mapa parpadea. Púlsalo para acercarte a las montañas y elegir cómo continuar.') : ($run['escenario'] === 50 ? 'La boca de la montaña-calavera arde frente a ti. Solo queda enfrentar a Ignar, el Dragón.' : 'El siguiente punto del mapa parpadea. Púlsalo para acercarte a las tierras del Dragón y elegir cómo continuar.')))) }}</p>
                @else
                    <p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · ELIGE TU RUMBO</p><h2 id="story-title">El camino se divide frente a ti.</h2><p>{{ $region['relato'] }}</p>
                    <div class="path-choices">@foreach($caminosActuales as $indice => $camino)<button type="button" data-path-choice="{{ $indice }}"><span class="path-index">0{{ $indice + 1 }} <i>↗</i></span><b>{{ $camino[0] }}</b><small>{{ $camino[1] }}</small></button>@endforeach</div>
                @endif
            @endif
            @if($activa && $actual && (!$combateEscena || ($mundo['evento']['combate']['estado'] ?? '') === 'victoria'))
                @include('aventura.partials.diario')
            @endif
            <p id="worldError" role="alert" hidden></p>
        </section>
    </div>
@if($forestTieneDecision)
<dialog id="forestPathDialog" class="forest-path-dialog" aria-labelledby="forest-path-title">
    <form method="dialog" class="forest-path-close"><button type="submit" aria-label="Volver al mapa" data-close-forest-paths>×</button></form>
    <p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · ELIGE TU RUMBO</p>
    <h2 id="forest-path-title">{{ $mapaBosque3d ? ($run['escenario'] === 1 ? 'El campamento te recibe.' : ($run['escenario'] === 10 ? 'Solo queda una senda.' : 'El bosque revela tres caminos.')) : ($mapaRuinas3d ? ($run['escenario'] === 11 ? 'El umbral de los archivos se abre.' : ($run['escenario'] === 20 ? 'Solo queda una ruta hacia Aster.' : 'Las ruinas revelan tres caminos.')) : ($mapaPantano3d ? ($run['escenario'] === 21 ? 'Las marismas se abren.' : ($run['escenario'] === 30 ? 'Solo queda una ruta hacia Náyade.' : 'El pantano revela tres caminos.')) : ($mapaHielo3d ? ($run['escenario'] === 31 ? 'El paso de la corona se abre.' : ($run['escenario'] === 40 ? 'Solo queda una ruta hacia Bóreas.' : 'Las montañas revelan tres caminos.')) : ($run['escenario'] === 41 ? 'El umbral de ceniza se abre.' : ($run['escenario'] === 50 ? 'Solo queda una ruta hacia Ignar.' : 'Las tierras del Dragón revelan tres caminos.'))))) }}</h2>
    <p>{{ $mapaBosque3d ? ($run['escenario'] === 1 ? 'La aventura comienza junto al fuego. Desde aquí partirás hacia el Bosque Inicial.' : ($run['escenario'] === 10 ? 'Las raíces se apartan y el claro del guardián queda abierto ante ti.' : 'Observa el terreno antes de decidir. Cada camino guarda su propio encuentro.')) : ($mapaRuinas3d ? ($run['escenario'] === 11 ? 'Tras las dunas, una sola escalera conduce a la ciudad enterrada.' : ($run['escenario'] === 20 ? 'Las llamas rituales iluminan el último paso antes del Guardián de los Archivos.' : 'Observa las ruinas antes de decidir. Cada ruta conduce a un descubrimiento diferente.')) : ($mapaPantano3d ? ($run['escenario'] === 21 ? 'La primera pasarela atraviesa las aguas quietas. Al cruzarla comenzará la exploración del Pantano Maldito.' : ($run['escenario'] === 30 ? 'Las luces del agua rodean el último paso antes de la Guardiana de las Aguas Sin Nombre.' : 'Observa la ciénaga antes de decidir. Cada sendero guarda un peligro distinto.')) : ($mapaHielo3d ? ($run['escenario'] === 31 ? 'Una única senda abandona la aldea y conduce al corazón de las Montañas Heladas.' : ($run['escenario'] === 40 ? 'Las luces de la aurora rodean el último paso antes del Guardián de la Tormenta.' : 'Observa la montaña antes de decidir. Cada senda guarda un desafío distinto.')) : ($run['escenario'] === 41 ? 'Una única senda atraviesa las primeras brasas y conduce al Umbral de Ceniza.' : ($run['escenario'] === 50 ? 'La montaña-calavera abre su boca de fuego. El último paso conduce al Dragón.' : 'Observa el terreno antes de decidir. Cada ruta atraviesa una amenaza distinta.'))))) }}</p>
    <div class="path-choices {{ count($caminosActuales) === 1 ? 'one-path' : '' }}">@foreach($caminosActuales as $indice => $camino)<button type="button" data-path-choice="{{ $indice }}"><span class="path-index">0{{ $indice + 1 }} <i>↗</i></span><b>{{ $camino[0] }}</b><small>{{ $camino[1] }}</small></button>@endforeach</div>
</dialog>
@endif
    <footer class="camp-footer">No necesitas conocer todas las respuestas para comenzar a explorar.<span>CODEPLAY / APRENDER JUGANDO</span></footer>
</main>
<div id="worldEntrance" class="world-entrance" hidden role="status" aria-live="polite"><video class="world-entrance-video" playsinline preload="auto" role="presentation"><source src="{{ asset('videos/aventura/inicio-codeplay2.mp4') }}?v={{ filemtime(public_path('videos/aventura/inicio-codeplay2.mp4')) }}" type="video/mp4"></video><p class="eyebrow">TU HISTORIA ESTÁ POR COMENZAR</p><h2>El bosque reconoce tus pasos.</h2><p id="entranceStatus">Atravesando el portal…</p></div>
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
<div class="forest-music-controls" id="swampMusicControls" hidden role="group" aria-label="Música del Pantano Maldito">
    <button type="button" aria-label="Reproducir música del Pantano Maldito">▶ Música</button>
    <label for="swampMusicVolume">Volumen</label>
    <input id="swampMusicVolume" type="range" min="0" max="100" value="35" aria-label="Volumen de la música del Pantano Maldito">
    <output for="swampMusicVolume">35%</output>
</div>
<audio id="swampMusic" loop preload="metadata" src="{{ asset('audio/aventura/pantano-maldito.mp3') }}"></audio>
<div class="forest-music-controls" id="iceMusicControls" hidden role="group" aria-label="Música de Montañas Heladas">
    <button type="button" aria-label="Reproducir música de Montañas Heladas">▶ Música</button>
    <label for="iceMusicVolume">Volumen</label>
    <input id="iceMusicVolume" type="range" min="0" max="100" value="35" aria-label="Volumen de la música de Montañas Heladas">
    <output for="iceMusicVolume">35%</output>
</div>
<audio id="iceMusic" loop preload="metadata" src="{{ asset('audio/aventura/montanas-heladas.mp3') }}"></audio>
<div class="forest-music-controls" id="dragonMusicControls" hidden role="group" aria-label="Música de Tierras del Dragón">
    <button type="button" aria-label="Reproducir música de Tierras del Dragón">▶ Música</button>
    <label for="dragonMusicVolume">Volumen</label>
    <input id="dragonMusicVolume" type="range" min="0" max="100" value="35" aria-label="Volumen de la música de Tierras del Dragón">
    <output for="dragonMusicVolume">35%</output>
</div>
<audio id="dragonMusic" loop preload="metadata" src="{{ asset('audio/aventura/tierras-dragon.mp3') }}"></audio>
<audio id="forestBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-bosque.mp3') }}"></audio>
<audio id="ruinsBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-ruinas.mp3') }}"></audio>
<audio id="swampBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-pantano.mp3') }}"></audio>
<audio id="iceBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-montanas-heladas.mp3') }}"></audio>
<audio id="dragonBattleMusic" loop preload="metadata" src="{{ asset('audio/aventura/batalla-tierras-dragon.mp3') }}"></audio>
<audio id="answerCorrectSound" preload="auto" src="{{ asset('audio/aventura/respuesta-correcta.mp3') }}"></audio>
<audio id="answerIncorrectSound" preload="auto" src="{{ asset('audio/aventura/respuesta-incorrecta.mp3') }}"></audio>
<script src="{{ asset('js/aventura-musica.js') }}?v={{ filemtime(public_path('js/aventura-musica.js')) }}" defer></script>
</body>
</html>
