@php
    $puntosBosque = [[17.5,70.1],[35.3,64.6],[49.5,77],[66.5,65.4],[81.7,51.7],[64.9,39.9],[44.3,43.8],[29.2,30.5],[52.3,20.5],[82.9,15.2]];
    $indiceDestino = max(0, min(9, $run['escenario'] - 1));
    $indiceHeroe = max(0, $indiceDestino - 1);
    [$heroeX, $heroeY] = $puntosBosque[$indiceHeroe];
    [$destinoX, $destinoY] = $puntosBosque[$indiceDestino];
    $esSilvar = $run['escenario'] === 10;
@endphp
<div class="forest-3d-map" data-forest-map>
    <img src="{{ asset('images/aventura/mapa-bosque-3d.png') }}?v={{ filemtime(public_path('images/aventura/mapa-bosque-3d.png')) }}" alt="Mapa tridimensional del Bosque Inicial con el recorrido del héroe hacia Silvar.">
    <div class="forest-hero-marker" style="--x:{{ $heroeX }}%;--y:{{ $heroeY }}%" aria-label="El héroe se encuentra en el punto {{ $indiceHeroe + 1 }} del recorrido">
        <span class="forest-position-label">TÚ</span><img src="{{ asset('images/aventura/heroe-bosque.png') }}" alt="">
    </div>
    @if($forestTieneDecision)
    <button type="button" class="forest-next-node {{ $esSilvar ? 'forest-silvar-node' : '' }}" style="--x:{{ $destinoX }}%;--y:{{ $destinoY }}%" data-open-forest-paths aria-haspopup="dialog">
        <span>{{ $esSilvar ? 'ENFRENTAR A SILVAR' : ($run['escenario'] === 1 ? 'COMENZAR AVENTURA' : 'CONTINUAR AVENTURA') }}</span><b>{{ $esSilvar ? '♜' : $run['escenario'] }}</b>
    </button>
    @endif
</div>
