@php $puntos = [[140,470],[280,430],[390,510],[520,435],[640,355],[510,280],[350,305],[230,220],[410,155],[650,120]]; @endphp
<svg class="territory-art" viewBox="0 0 800 610" role="img" aria-labelledby="map-description">
<title id="map-description">Mapa de {{ $region['nombre'] }}. Diez etapas conectadas por senderos. El escenario {{ $region['hasta'] }} contiene un guardián obligatorio.</title>
<defs>
<radialGradient id="land-light"><stop stop-color="var(--land-light)"/><stop offset="1" stop-color="var(--land)"/></radialGradient>
<pattern id="contours" width="160" height="130" patternUnits="userSpaceOnUse"><path d="M-20 80Q40 0 100 60T190 70M-20 95Q40 15 100 75T190 85M-20 110Q40 30 100 90T190 100" fill="none" stroke="var(--contour)" stroke-width="1"/></pattern>
<symbol id="atlas-tree" viewBox="0 0 40 65"><path d="M20 63V37" stroke="#9b8056" stroke-width="4"/><path d="M20 0 4 26h8L0 45h40L28 26h8Z" fill="var(--tree)" stroke="var(--tree-edge)"/><path d="M20 3v41L3 45" fill="none" stroke="var(--tree-edge)" opacity=".5"/></symbol>
<symbol id="atlas-mountain" viewBox="0 0 100 85"><path d="M0 80 45 0 100 80Z" fill="var(--mountain)" stroke="var(--tree-edge)"/><path d="m45 0 0 80h55Z" fill="#000" opacity=".2"/><path d="m45 0-17 30 17-8 12 10 6-5Z" fill="var(--snow)"/></symbol>
<symbol id="atlas-ruin" viewBox="0 0 75 65"><path d="M5 60V15h17v45M45 60V15h17v45M0 15V5h70v10" fill="var(--stone)" stroke="var(--tree-edge)" stroke-width="2"/><path d="M10 20v35M50 20v35M0 62h70" stroke="var(--snow)" opacity=".4"/></symbol>
</defs>
<path d="M76 91Q172 18 281 54T467 40Q645 10 737 94L772 230 734 336 771 457Q706 590 542 566L425 595 303 571Q91 603 46 496L64 355 24 237Z" fill="url(#land-light)" stroke="var(--coast)" stroke-width="3"/>
<path d="M76 91Q172 18 281 54T467 40Q645 10 737 94L772 230 734 336 771 457Q706 590 542 566L425 595 303 571Q91 603 46 496L64 355 24 237Z" fill="url(#contours)"/>
<path class="river-bank" d="M300 43Q310 120 365 200T440 357Q470 413 405 442T325 584"/><path class="river" d="M300 43Q310 120 365 200T440 357Q470 413 405 442T325 584"/>
@foreach([[80,140],[125,280],[65,345],[175,355],[290,100],[460,390],[580,465],[650,460],[715,275],[620,200],[520,100],[160,520],[280,520],[545,535]] as $index => [$x,$y])
@if(in_array($region['tema'], ['hielo','dragon']) || ($region['tema'] === 'ruinas' && $index % 3 === 0))
<use href="#atlas-mountain" x="{{ $x-12 }}" y="{{ $y-20 }}" width="80" height="68" opacity=".8"/>
@elseif($region['tema'] === 'ruinas')
<use href="#atlas-ruin" x="{{ $x }}" y="{{ $y }}" width="55" height="48" opacity=".8"/>
@else
<use href="#atlas-tree" x="{{ $x }}" y="{{ $y }}" width="34" height="55"/><use href="#atlas-tree" x="{{ $x+23 }}" y="{{ $y+12 }}" width="30" height="49"/><use href="#atlas-tree" x="{{ $x-18 }}" y="{{ $y+15 }}" width="27" height="45"/>
@endif
@endforeach
@foreach($puntos as $index => [$x,$y])
@if($index > 0)
@php [$px,$py] = $puntos[$index-1]; @endphp
@foreach([-22,0,22] as $bifurcacion)<path d="M{{ $px }} {{ $py }} Q{{ ($px+$x)/2 + $bifurcacion }} {{ ($py+$y)/2 + $bifurcacion }} {{ $x }} {{ $y }}" class="map-trail"/>@endforeach
@endif
@endforeach
@foreach($puntos as $index => [$x,$y])
@php $nivel = $region['desde']+$index; $aqui = $actual && $nivel === $run['escenario']; $visitado = isset($mundo['historial'][$nivel]); @endphp
<g class="map-stop {{ $aqui ? 'here' : '' }} {{ $visitado ? 'visited' : '' }}" transform="translate({{ $x }},{{ $y }})">
@if($aqui)<circle class="position-halo" r="29"/>@endif
<circle r="{{ $index === 9 ? 23 : 17 }}"/><text text-anchor="middle" dy="5">{{ $index === 9 ? '♜' : $nivel }}</text>
@if($aqui)<g class="position-label"><rect x="-43" y="-55" width="86" height="21" rx="10"/><text y="-41" text-anchor="middle">ESTÁS AQUÍ</text></g>@endif
@if($index === 9)<text class="boss-label" x="0" y="42" text-anchor="middle">{{ $nivel === 50 ? 'EL DRAGÓN · 50' : 'GUARDIÁN · '.$nivel }}</text>@endif
</g>
@endforeach
<g class="compass" transform="translate(706 540)"><path d="m0-28 7 21 21 7-21 7-7 21-7-21-21-7 21-7Z"/><path d="M0-28V28M-28 0H28"/><text y="-36" text-anchor="middle">N</text></g>
<text class="map-place" x="65" y="110">{{ ['bosque'=>'EL SUSURRO VERDE','ruinas'=>'ARCHIVOS DEL OLVIDO','pantano'=>'AGUAS SIN NOMBRE','hielo'=>'LA CORONA BLANCA','dragon'=>'EL UMBRAL DE CENIZA'][$region['tema']] }}</text>
<text class="map-place" x="72" y="572">{{ $region['desde'] === 1 ? 'EL CAMPAMENTO' : 'DESDE LA REGIÓN ANTERIOR' }}</text>
</svg>
