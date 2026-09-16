@php
    $puntosRuinas = [[17.1,65.3],[35.6,58.7],[50.5,71.1],[66,59],[80.6,47.3],[63.5,36.5],[44.1,40],[30,27.5],[52.3,18],[82.1,13]];
    $indiceDestino = max(0, min(9, $run['escenario'] - 11));
    $indiceHeroe = max(0, $indiceDestino - 1);
    [$heroeX, $heroeY] = $puntosRuinas[$indiceHeroe];
    [$destinoX, $destinoY] = $puntosRuinas[$indiceDestino];
    $esAster = $run['escenario'] === 20;
@endphp
<div class="forest-3d-map ruins-3d-map" data-forest-map>
    <img src="{{ asset('images/aventura/mapa-ruinas-3d.png') }}?v={{ filemtime(public_path('images/aventura/mapa-ruinas-3d.png')) }}" alt="Mapa tridimensional de las Ruinas Antiguas con el recorrido del héroe hacia Aster.">
    <div class="forest-hero-marker ruins-hero-marker" style="--x:{{ $heroeX }}%;--y:{{ $heroeY }}%" aria-label="El héroe se encuentra en el punto {{ $indiceHeroe + 11 }} del recorrido">
        <span class="forest-position-label">TÚ</span><img src="{{ asset('images/aventura/heroe-bosque.png') }}" alt="">
    </div>
    @if($forestTieneDecision)
    <button type="button" class="forest-next-node ruins-next-node {{ $esAster ? 'forest-silvar-node' : '' }}" style="--x:{{ $destinoX }}%;--y:{{ $destinoY }}%" data-open-forest-paths aria-haspopup="dialog">
        <span>{{ $esAster ? 'ENFRENTAR A ASTER' : ($run['escenario'] === 11 ? 'COMENZAR EXPLORACIÓN' : 'CONTINUAR AVENTURA') }}</span><b>{{ $esAster ? '♜' : $run['escenario'] }}</b>
    </button>
    @endif
</div>
