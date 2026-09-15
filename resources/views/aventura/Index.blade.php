<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campamento · Aventura CodePlay</title>
    <link rel="stylesheet" href="{{ asset('css/aventura.css') }}?v={{ filemtime(public_path('css/aventura.css')) }}">
</head>
<body>
<svg class="icon-definitions" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <symbol id="i-sword" viewBox="0 0 64 64"><path d="M45 5 59 5 59 19 28 48 17 37Z"/><path d="m21 43-12 12m1-23 23 23M7 53l4 4"/><path d="M27 36 50 13"/></symbol>
        <symbol id="i-staff" viewBox="0 0 64 64"><path d="m14 57 29-35M36 7 49 3l12 13-13 12-14-9Z"/><path d="m41 10 10 9M15 45l8 7"/></symbol>
        <symbol id="i-axe" viewBox="0 0 64 64"><path d="m12 57 35-46M23 12C36 18 46 9 48 4l12 24c-17 1-22 12-23 17Z"/><path d="m12 47 9 6"/></symbol>
        <symbol id="i-armor" viewBox="0 0 64 64"><path d="m22 8 10 8 10-8 16 12-9 13-5-3 5 24H15l5-24-5 3-9-13Z"/><path d="M23 34h18M21 43h22M32 16v37"/></symbol>
        <symbol id="i-robe" viewBox="0 0 64 64"><path d="M24 8h16l9 18-8-3 12 33H11l12-33-8 3Z"/><path d="m24 8 8 16 8-16M32 24v32M23 38h18"/></symbol>
        <symbol id="i-fire" viewBox="0 0 64 64"><path d="M32 5c5 18 23 23 22 36-1 22-43 23-44 0-1-10 7-20 13-25-2 11 0 14 5 16 7-9 5-18 4-27Z"/><path d="M30 36c-8 12-7 20 3 20 12-1 10-12-3-20Z"/></symbol>
        <symbol id="i-heal" viewBox="0 0 64 64"><path d="M22 9h20v13h13v20H42v13H22V42H9V22h13Z"/></symbol>
        <symbol id="i-ice" viewBox="0 0 64 64"><path d="M32 5v54M9 18l46 28M9 46l46-28M24 10l8 8 8-8M24 54l8-8 8 8M10 27l10-3-2-11M46 51l-2-11 10-3M10 37l10 3-2 11M46 13l-2 11 10 3"/></symbol>
        <symbol id="i-shield" viewBox="0 0 64 64"><path d="m32 6 23 9v18c0 13-23 25-23 25S9 46 9 33V15Z"/><path d="m20 30 8 8 17-18"/></symbol>
        <symbol id="i-bolt" viewBox="0 0 64 64"><path d="M36 4 12 36h18l-3 24 26-36H34Z"/></symbol>
        <symbol id="i-potion" viewBox="0 0 64 64"><path d="M24 6h16v8h-3v10c26 22 15 33-5 33S1 46 27 24V14h-3Z"/><path d="M17 38h30M26 46h12M32 40v12"/></symbol>
        <symbol id="i-mana" viewBox="0 0 64 64"><path d="M24 6h16v8h-3v10c26 22 15 33-5 33S1 46 27 24V14h-3Z"/><path d="m33 33-9 13h8l-1 8 10-15h-8Z"/></symbol>
        <symbol id="i-elixir" viewBox="0 0 64 64"><path d="M24 6h16v8h-3v10c26 22 15 33-5 33S1 46 27 24V14h-3Z"/><path d="m32 33 4 7 8 4-8 4-4 7-4-7-8-4 8-4Z"/></symbol>
    </defs>
</svg>
<header class="topbar">
    <a href="{{ route('inicio.alumno') }}" class="back">← Inicio</a>
    <a class="brand" href="{{ route('inicio.alumno') }}">CODE<span>PLAY</span><small>AVENTURA</small></a>
    <span class="player-name">{{ session('usuario_nombre', 'Explorador') }}</span>
</header>
<main id="campamento" data-save-url="{{ route('alumno.aventura.equipamiento') }}">
    <div class="page-heading"><div><p class="eyebrow">TU CONOCIMIENTO ABRE CAMINOS</p><h1>Antes del primer paso.</h1><p>Prepara tu equipo. Elige tu estilo. Lleva contigo todo lo que has aprendido.</p></div><span class="chapter">01 <span>EL CAMPAMENTO</span></span></div>
    <div class="journey-tabs" aria-label="Opciones de aventura">
        <a class="selected" href="#armeria">⚔ Equiparme <small>Prepara tu próxima expedición</small></a>
        <button type="button" id="adventureButton" class="adventure-launch">✦ Iniciar aventura <small>{{ $estado['expedicion']['estado'] === 'activa' ? 'Continuar tu expedición' : 'Cuando termines de Equiparte preciona aquí para iniciar la aventura' }}</small><span aria-hidden="true">→</span></button>
    </div>
    <div class="camp-grid">
        <aside class="character-panel" aria-labelledby="characterTitle">
            <div class="panel-heading"><span class="eyebrow">TU PERSONAJE</span><span class="level-pill">NV. <b id="characterLevel">{{ $estado['personaje']['nivel'] }}</b></span></div>
            <div class="portrait"><div class="portrait-orbit"></div><img src="{{ asset('Imagenes/aventura/explorador.svg') }}" alt="Explorador con capa, espada y escudo ante un portal luminoso"><span class="portrait-caption">EL CONOCIMIENTO ES TU PODER</span></div>
            <h2 id="characterTitle">{{ session('usuario_nombre', 'Explorador') }}</h2><p class="character-subtitle">Aprendiz de los caminos</p>
            <div class="xp-label"><span>Experiencia permanente</span><b>{{ $estado['personaje']['experiencia'] }} / {{ $estado['personaje']['siguiente'] }} XP</b></div>
            <progress class="xp" max="{{ $estado['personaje']['siguiente'] }}" value="{{ $estado['personaje']['experiencia'] }}" aria-label="Experiencia para subir de nivel"></progress>
            <p class="xp-note">Faltan {{ $estado['personaje']['siguiente'] - $estado['personaje']['experiencia'] }} XP para el nivel {{ $estado['personaje']['nivel'] + 1 }}.</p>
            <div class="resources"><div class="health"><span>♥ VIDA MÁX.</span><strong id="maxHealth"></strong></div><div class="mana"><span>◆ MANÁ MÁX.</span><strong id="maxMana"></strong></div></div>
            <div class="stats-heading"><span>ATRIBUTO</span><span>BASE <i>+</i> EQUIPO</span></div>
            <div id="characterStats" class="stats"></div>
            <details class="growth-help"><summary>¿Cómo crece mi personaje?</summary><p>Comienza con 1 en todos sus atributos. Cada nivel añade al azar entre 0 y 2 a cada uno. Cada diez niveles, ese máximo aumenta en 1: desde el nivel 11 puedes ganar hasta 3 por atributo, desde el 21 hasta 4, y así sucesivamente. El resultado se guarda: no cambia al recargar.</p><p>También obtienes un espacio adicional para hechizos cada diez niveles: {{ $estado['personaje']['limite_hechizos'] }} disponibles actualmente. Vida inicial: 100. Cada punto de vitalidad por encima del inicial añade 5. Maná inicial: 50. Cada punto extra de inteligencia añade 2. Los bonos del equipo también cuentan.</p><p>La XP se ganará en los eventos de Aventura. El nivel y sus atributos se conservan aunque pierdas. Comprar no da XP.</p>
                @if($estado['personaje']['ultimo_aumento'])<p>Última subida: @foreach($estado['personaje']['ultimo_aumento'] as $atributo => $valor) {{ $atributo }} +{{ $valor }}{{ !$loop->last ? ' ·' : '' }} @endforeach</p>@endif
            </details>
        </aside>
        <div class="equipment-column">
            <section class="wallet" aria-label="Presupuesto de aventura"><div><span>PUNTOS DISPONIBLES</span><strong id="walletAvailable"></strong></div><div><span>EN TU EQUIPO GUARDADO</span><strong id="walletReserved"></strong></div><p><span class="refund-icon">↺</span> Si pierdes, recuperas lo invertido.<br><small>Tu nivel y tus desbloqueos permanecen.</small></p></section>
            <section class="loadout-panel" aria-labelledby="loadoutTitle"><div class="section-heading"><div><p class="eyebrow">TU PRÓXIMA EXPEDICIÓN</p><h2 id="loadoutTitle">Una mochila, muchas posibilidades.</h2></div><span id="savedBadge" class="saved-badge">GUARDADO</span></div><div id="loadout" class="loadout"></div><div class="loadout-tools"><span>1 arma · 1 armadura · {{ $estado['personaje']['limite_hechizos'] }} hechizos · hasta 20 pociones</span><button id="clearDraft" type="button" class="text-button">Vaciar selección</button></div></section>
            <section id="armeria" class="shop" aria-labelledby="shopTitle">
                <div class="section-heading"><div><p class="eyebrow">LA ARMERÍA DEL CAMPAMENTO</p><h2 id="shopTitle">Encuentra tu estilo.</h2></div><button type="button" id="starterKit" class="subtle-button">✧ Sugerir equipo inicial</button></div>
                <p class="shop-intro">Fuerza, magia o resistencia: combina tus ventajas. Puedes cambiar tu elección antes de confirmar.</p>
                <div class="category-tabs" role="tablist" aria-label="Categorías de equipo">
                    <button type="button" role="tab" aria-selected="true" aria-controls="catalogue" data-category="arma" id="cat-arma">Armas</button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="catalogue" data-category="armadura" id="cat-armadura" tabindex="-1">Armaduras</button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="catalogue" data-category="hechizo" id="cat-hechizo" tabindex="-1">Hechizos</button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="catalogue" data-category="pocion" id="cat-pocion" tabindex="-1">Pociones</button>
                </div>
                <div class="shop-filters"><label>Rareza <select id="rarityFilter"><option value="">Todas las rarezas</option>@foreach(\App\Services\AventuraCatalogo::RAREZAS as $valor => $nombre)<option value="{{ $valor }}">{{ $nombre }}</option>@endforeach</select></label><label class="available-filter"><input type="checkbox" id="unlockedFilter"> Solo desbloqueados</label><span id="itemCount"></span></div>
                <p id="shopNotice" class="shop-notice" role="status" hidden></p>
                <div id="catalogue" class="catalogue" role="tabpanel" aria-labelledby="cat-arma"></div>
            </section>
            <section class="checkout" aria-labelledby="checkoutTitle"><div><span class="eyebrow" id="checkoutTitle">RESUMEN DE TU SELECCIÓN</span><p><strong id="draftCost"></strong> <span>puntos en equipo</span><span class="budget-left">Te quedan <b id="draftRemaining"></b></span></p><small id="priceChange"></small></div><button type="button" id="reviewButton" class="primary-button">Revisar y confirmar →</button></section>
            <p id="saveMessage" class="save-message" role="status">Los puntos solo se comprometen cuando confirmas. Puedes reequiparte antes de iniciar.</p>
            <details class="rules"><summary>Tu aventura, sin perder lo que aprendiste</summary><div class="rule-grid"><p><b>01 · Aprende</b>Documentación, tutoriales y desafíos te dan los puntos que puedes destinar al equipo.</p><p><b>02 · Elige</b>Las compras se guardan en tu cuenta. Al cambiar de equipo solo pagas o recuperas la diferencia.</p><p><b>03 · Vuelve a intentar</b>Cuando el futuro modo Aventura registre una derrota, se vaciará tu equipo y volverán los puntos invertidos, incluso los de pociones consumidas.</p><p><b>04 · Conserva tu progreso</b>El nivel y los desbloqueos son permanentes. Las reliquias requieren completar el desafío indicado, no solo desbloquear su acceso.</p></div></details>
        </div>
    </div>
    <footer class="camp-footer">Cada línea que aprendes hoy puede abrir un camino mañana. <span>CODEPLAY / AVENTURA</span></footer>
</main>
<dialog id="purchaseDialog" aria-labelledby="purchaseTitle"><form method="dialog"><button class="dialog-close" aria-label="Cerrar" value="cancel">×</button></form><p class="eyebrow">ANTES DE PARTIR</p><h2 id="purchaseTitle">¿Guardamos este equipo?</h2><div id="purchaseSummary"></div><p class="dialog-note">Puedes cambiarlo mientras estés en el campamento. Si pierdes la aventura, los puntos invertidos regresan a tu cuenta.</p><div class="dialog-actions"><button type="button" id="cancelPurchase" class="subtle-button">Seguir eligiendo</button><button type="button" id="confirmPurchase" class="primary-button">Confirmar equipo</button></div></dialog>
<dialog id="adventureDialog" aria-labelledby="adventureTitle">
    <form method="dialog"><button class="dialog-close" aria-label="Cerrar" value="cancel">×</button></form>
    <p class="eyebrow">EL MUNDO TE ESPERA</p><h2 id="adventureTitle">Cada camino comienza contigo.</h2>
    <p id="journeyNotice">Llevarás el equipo que hayas confirmado. Consultar el mapa no inicia la partida.</p>
    <p>Te esperan cinco regiones con enemigos distintos, cuatro guardianes, tesoros y encargos del gremio. Tu diario conserva los encargos mientras exploras. El Dragón del escenario 50 llegará en la siguiente etapa.</p>
    <a class="primary-button" href="{{ route('alumno.aventura.mapa') }}">{{ $estado['expedicion']['estado'] === 'activa' ? 'Continuar aventura →' : 'Consultar el mapa →' }}</a>
    @if($estado['expedicion']['estado'] === 'activa')
        <details class="restart-choice"><summary>↺ Reiniciar desde el escenario 1</summary>
            <p>Terminarás esta partida y volverás a elegir equipo. Conservarás tu nivel y experiencia; recuperarás los {{ $estado['expedicion']['inversion'] }} puntos invertidos. Se perderán el equipo, oro, efectos y mejoras de esta partida.</p>
            <button type="button" id="restartJourney" class="primary-button" data-url="{{ route('alumno.aventura.reiniciar') }}">Confirmar reinicio y elegir equipo</button>
            <p id="restartError" role="alert" hidden></p>
        </details>
    @endif
</dialog>
<script id="adventureState" type="application/json">{!! json_encode($estado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/aventura.js') }}?v={{ filemtime(public_path('js/aventura.js')) }}" defer></script>
</body>
</html>
