<section class="battle-theatre" aria-label="Escenario de combate" data-theatre @if(in_array($battle['answer_feedback'] ?? null, ['correct','incorrect'], true)) data-answer-feedback="{{ $battle['answer_feedback'] }}" @endif>
    <div class="arena" data-region="{{ $region['tema'] }}">
        <div class="arena-land" aria-hidden="true">
            @if($region['id']===1)<img class="arena-background-image" src="{{ asset('images/aventura/bosque-inicial-arena.png') }}" alt="">@elseif($region['id']===2)<img class="arena-background-image" src="{{ asset('images/aventura/ruinas-antiguas-arena.png') }}" alt="">@elseif($region['id']===3)<img class="arena-background-image" src="{{ asset('images/aventura/pantano-maldito-arena.png') }}" alt="">@elseif($region['id']===4)<img class="arena-background-image" src="{{ asset('images/aventura/montanas-heladas-arena.png') }}" alt="">@elseif($region['id']===5)<img class="arena-background-image" src="{{ asset('images/aventura/tierras-dragon-arena.png') }}" alt="">@endif
            <svg viewBox="0 0 1000 450" preserveAspectRatio="xMidYMid slice"><circle class="arena-moon" cx="735" cy="75" r="44"/><path class="far-ridge" d="M0 190 110 110 185 185 340 50 480 180 670 105 800 190 910 60 1000 180V450H0Z"/><path class="near-ridge" d="M0 245Q200 170 360 240T700 220T1000 235V450H0Z"/>
            @if($region['id']===1 || $region['id']===3)
            <g class="arena-trees"><path d="M70 50 15 220h110ZM185 95l-45 140h90ZM930 65l-60 180h120Z"/><path d="M63 210h14v60H63m115-50h14v55h-14m744 15h16v50h-16"/></g>
            @elseif($region['id']===2)
            <g class="arena-ruins"><path d="M45 95h45v175H45m100-130h45v145h-45m680-170h50v195h-50M25 80h180v30H25m780-80h95v35h-95"/></g>
            @elseif($region['id']===4)
            <path class="arena-snow" d="m235 135 105-85 96 108-74-28-26 12-34-24ZM856 129l54-69 63 90-55-30-20 15Z"/>
            @else
            <path class="arena-lava" d="m660 450 90-65-50-35 120-110-70 111 50 33-52 66ZM300 135l40-85 45 95-45-28Z"/>
            @endif
        </svg>
    </div>
    <div class="opponents" style="--opponents:{{ count($battle['enemigos']) }}">
    @foreach($battle['enemigos'] as $indice=>$enemigo)
        <button type="button" class="arena-enemy {{ $enemigo['vida']<=0?'is-fallen':'' }}" data-actor="enemigo-{{ $indice }}" data-arena-target="{{ $indice }}" data-hp="{{ $enemigo['vida'] }}" @disabled($enemigo['vida']<=0 || $battle['estado']!=='jugador') aria-label="Seleccionar {{ $enemigo['nombre'] }}">
            <span class="arena-enemy-label">{{ $enemigo['nombre'] }} <small>Nv. {{ $enemigo['nivel'] }}</small><em>{{ $enemigo['clave'] === 'dragon' ? '???? Ignar es demasiado poderoso, no eres capaz de leer sus debilidades o es probable que no las tenga.' : $enemigo['descripcion'] }}</em></span>
            <span class="visual-meter"><span data-hp-bar style="width:{{ 100*$enemigo['vida']/$enemigo['max'] }}%"></span></span>
            <span class="arena-hp" data-hp-label>{{ $enemigo['vida'] }} / {{ $enemigo['max'] }}</span>
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
            @php
                $spriteTheme = match($region['id']) { 1 => 'forest', 2 => 'ruins', 3 => 'swamp', 4 => 'ice', 5 => 'fire', default => null };
                $isSpriteBoss = in_array($enemigo['clave'], ['silvar', 'archivista', 'matriarca', 'titan', 'dragon'], true);
                $bossPhase = $enemigo['clave'] === 'dragon'
                    ? \App\Services\AventuraDragon::fase($enemigo)
                    : ($enemigo['vida'] <= $enemigo['max'] / 2 ? 2 : 1);
                $phaseClass = $isSpriteBoss ? $spriteTheme.'-boss '.$spriteTheme.'-boss-phase-'.$bossPhase : '';
            @endphp
            @if($sprite)<span class="fighter enemy-art {{ $spriteTheme }}-enemy {{ $spriteTheme }}-enemy-{{ $enemigo['clave'] }} {{ $phaseClass }}" aria-hidden="true"><img class="{{ $spriteTheme }}-enemy-image" src="{{ asset('images/aventura/'.$sprite) }}" alt=""></span>@elseif($region['id']===1)<span class="fighter enemy-art forest-enemy forest-enemy-{{ $indice }}" aria-hidden="true"></span>@else<span class="fighter enemy-art">@include('aventura.partials.enemigo-arte')</span>@endif @unless($isSpriteBoss)<span class="platform"></span>@endunless
        </button>
    @endforeach
    </div>
    <div class="arena-player" data-actor="jugador" data-hp="{{ $run['vida_actual'] }}" data-level="{{ $personaje['nivel'] }}">
        <div class="arena-player-hud"><b>{{ session('usuario_nombre','Explorador') }} <small data-player-level>Nv. {{ $personaje['nivel'] }}</small></b><span>VIDA <strong data-player-hp>{{ $run['vida_actual'] }} / {{ $personaje['vida'] }}</strong></span><span class="visual-meter"><span data-player-bar style="width:{{ 100*$run['vida_actual']/$personaje['vida'] }}%"></span></span><span>MANÁ <strong data-player-mana>{{ $run['mana_actual'] }} / {{ $personaje['mana'] }}</strong></span><span class="visual-meter mana"><span data-mana-bar style="width:{{ 100*$run['mana_actual']/$personaje['mana'] }}%"></span></span><span>EXP <strong>{{ $personaje['experiencia'] }} / {{ $personaje['siguiente'] }}</strong></span><span class="visual-meter exp"><span style="width:{{ $personaje['siguiente'] > 0 ? 100*$personaje['experiencia']/$personaje['siguiente'] : 0 }}%"></span></span></div>
        @php
            $heroInicial = $run['vida_actual'] / max(1, $personaje['vida']) < .25 ? 'heroe-herido.png' : 'heroe-bosque.png';
        @endphp
        <span class="fighter hero" aria-hidden="true"><img class="hero-image" data-hero-sprite data-hero-default="{{ asset('images/aventura/heroe-bosque.png') }}" data-hero-attack="{{ asset('images/aventura/heroe-ataque.png') }}" data-hero-defense="{{ asset('images/aventura/heroe-defensa.png') }}" data-hero-potion="{{ asset('images/aventura/heroe-pocion.png') }}" data-hero-wounded="{{ asset('images/aventura/heroe-herido.png') }}" src="{{ asset('images/aventura/'.$heroInicial) }}" alt=""></span><span class="platform"></span>
    </div>
    <div class="arena-fx" aria-hidden="true"></div>
    </div>
    <div class="battle-console"><div class="battle-console-dialog" data-continue-zone tabindex="0" role="button" aria-label="Continuar narración"><p class="battle-narration" role="status" aria-live="polite">{{ $battle['estado']==='pregunta'?'Las runas esperan tu respuesta.':($battle['estado']==='victoria'?'¡Victoria! El camino está despejado.':'¿Cuál será tu siguiente movimiento?') }}</p><p class="battle-hint" data-continue-hint hidden>Pulsa aquí para continuar la aventura.</p>@if($battle['estado']==='victoria' && $run['escenario']!==50)<button type="button" class="dialog-continue" data-combat-action="continuar">{{ $run['escenario'] % 10 === 0 ? 'Cruzar hacia '.\App\Services\AventuraMundo::region($run['escenario']+1)['nombre'].' →' : 'Continuar aventura →' }}</button>@endif</div><div class="playback-tools"><button type="button" data-sound-toggle aria-pressed="true">🔊 Sonido</button></div></div>
    @if($battle['estado']==='jugador')
    <nav class="command-menu" aria-label="Acciones del turno"><button type="button" data-combat-action="atacar">⚔ Atacar</button><button type="button" data-panel="arenaSpells">✧ Hechizos</button><button type="button" data-panel="arenaBag">♜ Mochila</button><button type="button" data-combat-action="defender">◇ Defender</button></nav>
    <div class="arena-toolbox" data-arena-toolbox>
        <section id="arenaSpells" hidden><h3>Hechizos disponibles</h3>@forelse($run['equipo']['hechizos'] as $id)@php($nivelHechizo = \App\Services\AventuraMejoras::nivelHechizo($run, $id))<button class="spell-button" type="button" data-combat-action="hechizo" data-item="{{ $id }}" @disabled($run['mana_actual'] < $catalogo[$id]['mana'])>{{ $catalogo[$id]['nombre'] }}@if($nivelHechizo > 1) <small>Nv. {{ $nivelHechizo }}</small>@endif <small>{{ $catalogo[$id]['mana'] }} maná</small><span>{{ $catalogo[$id]['descripcion'] }}@if($nivelHechizo > 1) · Potencia +{{ \App\Services\AventuraMejoras::valorHechizo($run, $id) }}%@endif</span></button>@empty<p>No equipaste hechizos para este viaje.</p>@endforelse</section>
        <section id="arenaBag" hidden><h3>Mochila de combate</h3>@forelse($run['equipo']['pociones'] as $pocion)<button class="spell-button" type="button" data-combat-action="pocion" data-item="{{ $pocion['id'] }}">{{ $catalogo[$pocion['id']]['nombre'] }} <small>×{{ $pocion['cantidad'] }}</small><span>{{ $catalogo[$pocion['id']]['descripcion'] }}</span></button>@empty<p>No quedan pociones.</p>@endforelse</section>
    </div>
    @endif
</section>
