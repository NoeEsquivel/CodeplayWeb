@php
    $puntosHielo = [[17.6,71.1],[35.2,65.5],[49.3,79.3],[66.3,65.7],[80,52.3],[62.4,40.6],[43.1,42.4],[28.8,29.9],[50.4,20.3],[81.3,16.2]];
    $indiceDestino = max(0, min(9, $run['escenario'] - 31));
    $indiceHeroe = max(0, $indiceDestino - 1);
    [$heroeX, $heroeY] = $puntosHielo[$indiceHeroe];
    [$destinoX, $destinoY] = $puntosHielo[$indiceDestino];
    $esBoreas = $run['escenario'] === 40;
@endphp
<div class="forest-3d-map hielo-3d-map" data-forest-map>
    <img src="{{ asset('images/aventura/mapa-montanas-heladas-3d.png') }}?v={{ filemtime(public_path('images/aventura/mapa-montanas-heladas-3d.png')) }}" alt="Mapa tridimensional de las Montañas Heladas con el recorrido del héroe hacia Bóreas.">
    <div class="forest-hero-marker hielo-hero-marker" style="--x:{{ $heroeX }}%;--y:{{ $heroeY }}%" aria-label="El héroe se encuentra en el punto {{ $indiceHeroe + 31 }} del recorrido">
        <span class="forest-position-label">TÚ</span><img src="{{ asset('images/aventura/heroe-bosque.png') }}" alt="">
    </div>
    @if($forestTieneDecision)
    <button type="button" class="forest-next-node hielo-next-node {{ $esBoreas ? 'forest-silvar-node' : '' }}" style="--x:{{ $destinoX }}%;--y:{{ $destinoY }}%" data-open-forest-paths aria-haspopup="dialog">
        <span>{{ $esBoreas ? 'ENFRENTAR A BÓREAS' : ($run['escenario'] === 31 ? 'COMENZAR EXPLORACIÓN' : 'CONTINUAR AVENTURA') }}</span><b>{{ $esBoreas ? '♜' : $run['escenario'] }}</b>
    </button>
    @endif
</div>