@php
    $puntosDragon = [[17.7,68.9],[35.8,62.5],[50.0,75.0],[65.6,64.5],[80.6,51.8],[64.1,40.2],[44.2,43.9],[29.2,31],[52.0,21.4],[82.0,16]];
    $indiceDestino = max(0, min(9, $run['escenario'] - 41));
    $indiceHeroe = max(0, $indiceDestino - 1);
    [$heroeX, $heroeY] = $puntosDragon[$indiceHeroe];
    [$destinoX, $destinoY] = $puntosDragon[$indiceDestino];
    $esIgnar = $run['escenario'] === 50;
@endphp
<div class="forest-3d-map dragon-3d-map" data-forest-map>
    <img src="{{ asset('images/aventura/mapa-tierras-dragon-3d.png') }}?v={{ filemtime(public_path('images/aventura/mapa-tierras-dragon-3d.png')) }}" alt="Mapa tridimensional de las Tierras del Dragón con el recorrido del héroe hacia Ignar.">
    <div class="forest-hero-marker dragon-hero-marker" style="--x:{{ $heroeX }}%;--y:{{ $heroeY }}%" aria-label="El héroe se encuentra en el punto {{ $indiceHeroe + 41 }} del recorrido">
        <span class="forest-position-label">TÚ</span><img src="{{ asset('images/aventura/heroe-bosque.png') }}" alt="">
    </div>
    @if($forestTieneDecision)
    <button type="button" class="forest-next-node dragon-next-node {{ $esIgnar ? 'forest-silvar-node' : '' }}" style="--x:{{ $destinoX }}%;--y:{{ $destinoY }}%" data-open-forest-paths aria-haspopup="dialog">
        <span>{{ $esIgnar ? 'ENFRENTAR A IGNAR' : ($run['escenario'] === 41 ? 'COMENZAR EXPLORACIÓN' : 'CONTINUAR AVENTURA') }}</span><b>{{ $esIgnar ? '♜' : $run['escenario'] }}</b>
    </button>
    @endif
</div>