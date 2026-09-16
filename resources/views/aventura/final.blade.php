<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>El último horizonte · CodePlay</title>
<link rel="stylesheet" href="{{ asset('css/aventura-final.css') }}?v={{ filemtime(public_path('css/aventura-final.css')) }}">
</head>
<body>
@php($victoryVideo = config('aventura-cinematics.ignar_victory_url'))
@if($victoryVideo)
<div id="victoryCinematic" data-run="{{ $partida->id }}" role="dialog" aria-modal="true" aria-label="La última batalla: Supernova">
    <video playsinline preload="auto" aria-label="El héroe derrota a Ignar con Supernova"><source src="{{ asset($victoryVideo) }}" type="video/mp4"></video>
    <div class="victory-controls"><p data-victory-status role="status">Preparando el último combate…</p><button type="button" data-victory-retry hidden>Reproducir batalla final →</button></div>
</div>
@endif
<div class="final-stars" aria-hidden="true"></div>
<audio id="finalEpilogueMusic" preload="auto">
    <source src="{{ asset('audio/aventura-epilogo-final.mp3') }}" type="audio/mpeg">
</audio>
<main class="epilogue" @if($victoryVideo) hidden inert @endif>
    <p class="overline">CODEPLAY · EL ÚLTIMO HORIZONTE</p>
    <div class="creator" aria-hidden="true"><svg viewBox="0 0 200 180"><circle cx="100" cy="68" r="32"/><path d="M38 180v-25q0-52 62-52t62 52v25Z"/><path class="rune" d="m80 132-15 12 15 12m40-24 15 12-15 12m-13-30-14 42"/></svg></div>
    <section class="chapter" id="encuentro" aria-labelledby="encuentro-title">
        <p class="overline">EL FUEGO SE APAGA. UNA FIGURA APARECE.</p>
        <h1 id="encuentro-title" tabindex="-1">«Así que realmente llegaste hasta aquí…»</h1>
        <p class="voice">???</p><p>Las cenizas se convierten en luces. La silueta da un paso y, por primera vez, el mundo guarda silencio.</p>
        <button type="button" data-next="mensaje" hidden>Escuchar a la figura →</button>
    </section>
    <section class="chapter" id="mensaje" aria-labelledby="mensaje-title">
        <p class="overline">DETRÁS DE CADA MUNDO HAY UNA IDEA</p>
        <h2 id="mensaje-title" tabindex="-1">Soy Noé Esquivel.</h2>
        <p class="voice">El programador que creó este mundo.</p>
        <p>Gracias por jugar. Llegar hasta aquí significa que dedicaste tiempo, esfuerzo y curiosidad a aprender. Te equivocaste, volviste a intentarlo y encontraste tu camino.</p>
        <p>Eso también es programar: transformar una idea en algo que antes no existía. Hoy completaste esta aventura. Quizá mañana seas tú quien construya un mundo para los demás.</p>
        <p class="farewell">Sigue creando. Tu próxima gran historia puede comenzar con una línea de código.</p>
        <button type="button" data-next="logro" hidden>Contemplar lo conseguido →</button>
    </section>
    <section class="chapter" id="logro" aria-labelledby="logro-title">
        <div class="trophy" aria-hidden="true">🏆</div>
        <p class="overline">LOGRO PERMANENTE</p>
        <h2 id="logro-title" tabindex="-1">Conquistador de la Aventura</h2>
        <p>Derrotaste al Dragón y completaste Aprender Jugando.</p>
        <p>El trofeo ya está asociado a tu cuenta. Permanecerá en el menú principal, incluso si cierras sesión o vuelves a jugar.</p>
        @if($partida->estado==='activa')
        <p class="return-note">Al regresar recuperarás los {{ $partida->inversion }} puntos invertidos en equipo. Esta expedición se cerrará: sus objetos, oro y efectos se despedirán contigo; tu nivel, experiencia y trofeo permanecerán.</p>
        <form method="POST" action="{{ route('alumno.aventura.completar') }}">@csrf
            <input type="hidden" name="expedicion_id" value="{{ $partida->id }}"><input type="hidden" name="version" value="{{ $partida->version }}">
            <button type="submit">Terminar aventura y volver al campamento →</button>
        </form>
        @else
        <a class="button" href="{{ route('alumno.aventura') }}">Preparar otra aventura →</a>
        @endif
        <a class="home-link" href="{{ route('inicio.alumno') }}" data-stop-epilogue-music>Ver mi trofeo en el menú principal</a>
    </section>
    <button class="skip" type="button" data-next="logro" hidden>Ir al logro</button>
</main>
<script src="{{ asset('js/aventura-final.js') }}?v={{ filemtime(public_path('js/aventura-final.js')) }}" defer></script>
<script src="{{ asset('js/aventura-victory.js') }}?v={{ filemtime(public_path('js/aventura-victory.js')) }}" defer></script>
</body></html>
