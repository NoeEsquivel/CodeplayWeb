@php
    $puntosPantano = [[16.8,70.9],[35.4,64.6],[49.4,77.1],[66.4,65.7],[80,52.8],[62.7,41.9],[43.2,44.9],[28.5,31.1],[50.8,21.9],[81.5,16.1]];
    $indiceDestino = max(0, min(9, $run['escenario'] - 21));
    $indiceHeroe = max(0, $indiceDestino - 1);
    [$heroeX, $heroeY] = $puntosPantano[$indiceHeroe];
    [$destinoX, $destinoY] = $puntosPantano[$indiceDestino];
    $esNayade = $run['escenario'] === 30;
@endphp
<div class="forest-3d-map swamp-3d-map" data-forest-map>
    <img src="{{ asset('images/aventura/mapa-pantano-3d.png') }}?v={{ filemtime(public_path('images/aventura/mapa-pantano-3d.png')) }}" alt="Mapa tridimensional del Pantano Maldito con el recorrido del héroe hacia Náyade.">
    <div class="forest-hero-marker swamp-hero-marker" style="--x:{{ $heroeX }}%;--y:{{ $heroeY }}%" aria-label="El héroe se encuentra en el punto {{ $indiceHeroe + 21 }} del recorrido">
        <span class="forest-position-label">TÚ</span><img src="{{ asset('images/aventura/heroe-bosque.png') }}" alt="">
    </div>
    @if($forestTieneDecision)
    <button type="button" class="forest-next-node swamp-next-node {{ $esNayade ? 'forest-silvar-node' : '' }}" style="--x:{{ $destinoX }}%;--y:{{ $destinoY }}%" data-open-forest-paths aria-haspopup="dialog">
        <span>{{ $esNayade ? 'ENFRENTAR A NÁYADE' : ($run['escenario'] === 21 ? 'COMENZAR EXPLORACIÓN' : 'CONTINUAR AVENTURA') }}</span><b>{{ $esNayade ? '♜' : $run['escenario'] }}</b>
    </button>
    @endif
</div>