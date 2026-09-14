@php $tablon=$mundo['evento']['gremio'] ?? null; $misiones=\App\Services\AventuraGremio::catalogo(); $objetos=(new \App\Services\AventuraCatalogo())->items(); $tienda=$tablon['tienda'] ?? null; $articulos=\App\Services\AventuraGremio::tiendaCatalogo(); @endphp
<div class="event-emblem" aria-hidden="true"><svg viewBox="0 0 120 90" fill="none" stroke="currentColor" stroke-width="2"><path d="M25 20h70v54H25zM34 29h52M34 39h24m9 0h19M34 49h24m9 0h19M35 74v10m50-10v10M60 4l5 7 8 2-5 7 1 8-9-3-9 3 1-8-5-7 8-2Z"/></svg></div>
<p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · GREMIO DE AVENTUREROS</p>
<h2 id="story-title">Un encargo. Una nueva historia.</h2>
<p>Una exploradora te recibe junto a un tablón: «Nadie aprende ni viaja completamente solo. Ayúdanos y compartiremos nuestros recursos».</p>
@if(!$tablon)
    <button type="button" class="primary-button" data-guild-action="abrir">Consultar el tablón →</button>
@else
    <p class="guild-note">Lleva hasta 3 encargos activos. Los objetivos de exploración, preguntas y combate cuentan desde que aceptas; la entrega usa lo que ya tengas. Puedes cobrar desde tu diario, entre combates.</p>
    @if(empty($tienda['abierta']))
        <div class="event-actions"><button type="button" class="subtle-button" data-guild-action="tienda">Visitar tienda →</button></div>
    @else
        <section class="guild-board" aria-label="Tienda del gremio"><div class="guild-offer"><p class="eyebrow">TIENDA DEL GREMIO</p><h3>Suministros para la expedición</h3><p>Estas cuatro ofertas se reservaron para este encuentro. Solo cambian al encontrar otro gremio en un escenario distinto.</p></div>
        @foreach($tienda['ofertas'] ?? [] as $clave)
            @php $articulo=$articulos[$clave]; $comprado=$tienda['compras'][$clave] ?? null; @endphp
            <article class="guild-offer" data-shop-item="{{ $clave }}"><p class="eyebrow">{{ strtoupper($articulo['tipo'] === 'pocion' ? 'SUMINISTRO' : 'MEJORA DE EXPEDICIÓN') }}</p><h3>{{ $articulo['nombre'] }}</h3><p>{{ $articulo['texto'] }}</p><p class="guild-reward">{{ $articulo['precio'] }} oro · {{ $run['oro'] }} disponible</p>
            @if($comprado)<p class="story-note">Comprado: {{ $comprado['detalle'] }}</p>@else<button type="button" class="subtle-button" data-guild-action="comprar" data-shop-item="{{ $clave }}" @disabled($run['oro'] < $articulo['precio'])>Comprar por {{ $articulo['precio'] }} oro</button>@endif</article>
        @endforeach
        </section>
        <div class="event-actions"><button type="button" class="subtle-button" data-guild-action="cerrar_tienda">Volver al tablón</button></div>
    @endif
    <div class="guild-board">
    @foreach($tablon['ofertas'] as $clave)
        @php $m=$misiones[$clave]; $tomado=isset($tablon['encargos'][$clave]); @endphp
        <article class="guild-offer" data-offer="{{ $clave }}"><p class="eyebrow">ENCARGO DEL GREMIO</p><h3>{{ $m['nombre'] }}</h3><p>{{ $m['texto'] }}</p>
            <p class="guild-reward">{{ $m['xp'] }} XP · {{ $m['oro'] }} oro
            @if(isset($m['objeto']))<br>+ {{ $objetos[$m['objeto']]['nombre'] }}@endif
            @if(isset($m['mejora']))<br>+ Curiosidad incansable: 10% XP durante la partida (máximo 30%)@endif
            @if(!empty($m['inspiracion']))<br>+ Inspiración para el próximo combate @endif</p>
            <button type="button" class="subtle-button" data-guild-action="aceptar" data-quest="{{ $clave }}" @disabled($tomado)>{{ $tomado ? 'Registrado en tu diario' : 'Aceptar encargo' }}</button>
        </article>
    @endforeach
    </div>
@endif
<div class="event-actions"><button type="button" class="primary-button" data-guild-action="salir">Continuar el viaje →</button></div>
<p class="story-note">Aceptar es opcional y gratuito. Marcharte conserva tus encargos. La XP permanece; el oro, los objetos y las mejoras pertenecen a esta partida.</p>
