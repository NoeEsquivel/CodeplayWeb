@php $tablon=$mundo['evento']['gremio'] ?? null; $misiones=\App\Services\AventuraGremio::catalogo(); $objetos=(new \App\Services\AventuraCatalogo())->items(); $tienda=$tablon['tienda'] ?? null; $articulos=\App\Services\AventuraGremio::tiendaCatalogo(); @endphp
<div class="event-emblem" aria-hidden="true"><svg viewBox="0 0 120 90" fill="none" stroke="currentColor" stroke-width="2"><path d="M25 20h70v54H25zM34 29h52M34 39h24m9 0h19M34 49h24m9 0h19M35 74v10m50-10v10M60 4l5 7 8 2-5 7 1 8-9-3-9 3 1-8-5-7 8-2Z"/></svg></div>
<p class="eyebrow">ESCENARIO {{ $run['escenario'] }} · GREMIO DE AVENTUREROS</p>
<h2 id="story-title">Un encargo. Una nueva historia.</h2>
<p>Una exploradora te recibe junto a un tablón: «Nadie aprende ni viaja completamente solo. Ayúdanos y compartiremos nuestros recursos».</p>
@if(!$tablon)
    <button type="button" class="primary-button" data-guild-action="abrir">Consultar el tablón →</button>
@else
    <p class="guild-note">Lleva hasta 3 encargos activos. Los objetivos de exploración, preguntas y combate cuentan desde que aceptas; la entrega usa lo que ya tengas. Puedes cobrar desde tu diario, entre combates.</p>
    <div class="guild-board">
    @foreach($tablon['ofertas'] as $clave)
        @php $m=$misiones[$clave]; $tomado=isset($tablon['encargos'][$clave]); $entrega=$m['entregar'] ?? null; $tieneEntrega=!$entrega || collect($estado['inventario'] ?? [])->contains(fn($p)=>($p['id'] ?? '') === $entrega && (int)($p['cantidad'] ?? 0) > 0); @endphp
        <article class="guild-offer" data-offer="{{ $clave }}"><p class="eyebrow">ENCARGO DEL GREMIO</p><h3>{{ $m['nombre'] }}</h3><p>{{ $m['texto'] }}</p>
            <p class="guild-reward">{{ $m['xp'] }} XP · {{ $m['oro'] }} oro
            @if(isset($m['objeto']))<br>+ {{ $objetos[$m['objeto']]['nombre'] }}@endif
            @if(isset($m['mejora']))<br>+ Curiosidad incansable: 10% XP durante la partida (máximo 30%)@endif
            @if(!empty($m['inspiracion']))<br>+ Inspiración para el próximo combate @endif</p>
            <button type="button" class="subtle-button" data-guild-action="aceptar" data-quest="{{ $clave }}" @if($entrega && !$tieneEntrega) data-guild-requires="{{ $entrega }}" data-guild-requires-name="{{ $objetos[$entrega]['nombre'] }}" @endif @disabled($tomado)>{{ $tomado ? 'Registrado en tu diario' : 'Aceptar encargo' }}</button>
        </article>
    @endforeach
    </div>
@endif
<div class="event-actions"><button type="button" class="primary-button" data-guild-action="salir">Continuar el viaje →</button>@if($tienda)<button type="button" class="primary-button guild-store-button" data-open-guild-shop>Ver tienda ✦</button>@endif</div>
<p class="story-note">Aceptar es opcional y gratuito. Marcharte conserva tus encargos. La XP permanece; el oro, los objetos y las mejoras pertenecen a esta partida.</p>
@if($tienda)
<dialog class="guild-store-dialog" data-guild-shop aria-labelledby="guildStoreTitle">
    <div class="guild-store-header"><div><p class="eyebrow">GREMIO DE AVENTUREROS · PROVISIONES</p><h2 id="guildStoreTitle">La tienda del viajero</h2><p>Tu oro disponible: <strong>{{ $run['oro'] }}</strong></p></div><button type="button" class="dialog-close" data-close-guild-shop aria-label="Volver al gremio">×</button></div>
    <p class="guild-store-intro">Estas cuatro ofertas fueron reservadas para este encuentro. Solo cambiarán cuando encuentres otro gremio en un escenario distinto.</p>
    <p class="guild-store-error" data-guild-shop-error role="alert" aria-live="assertive" tabindex="-1" hidden></p>
    <div class="guild-store-grid">
    @foreach($tienda['ofertas'] ?? [] as $clave)
        @php $articulo=$articulos[$clave]; $comprado=$tienda['compras'][$clave] ?? null; @endphp
        <article class="guild-store-item" data-shop-item="{{ $clave }}"><p class="eyebrow">{{ strtoupper($articulo['tipo'] === 'pocion' ? 'SUMINISTRO' : 'MEJORA DE EXPEDICIÓN') }}</p><h3>{{ $articulo['nombre'] }}</h3><p>{{ $articulo['texto'] }}</p><p class="guild-store-price">{{ $articulo['precio'] }} <small>oro</small></p>
        @if($comprado)<p class="guild-store-owned">✓ Comprado<br><small>{{ $comprado['detalle'] }}</small></p>@else<button type="button" class="subtle-button" data-guild-action="comprar" data-shop-item="{{ $clave }}" @disabled($run['oro'] < $articulo['precio'])>Comprar</button>@endif</article>
    @endforeach
    </div>
    <div class="dialog-actions"><button type="button" class="subtle-button" data-close-guild-shop>Volver al gremio</button><button type="button" class="primary-button" data-guild-action="salir">Continuar el viaje →</button></div>
</dialog>
@endif
<dialog class="guild-requirement-dialog" data-guild-requirement-dialog aria-labelledby="guildRequirementTitle">
    <p class="eyebrow">ANTES DE ACEPTAR</p><h2 id="guildRequirementTitle">Te falta un recurso</h2>
    <p data-guild-requirement-text>No cuentas con la poción requerida.</p>
    <p class="dialog-note">Puedes aceptar el encargo ahora, pero no podrás cobrar la recompensa hasta conseguirla en un cofre, evento o tienda.</p>
    <div class="dialog-actions"><button type="button" class="subtle-button" data-close-guild-requirement>Volver</button><button type="button" class="primary-button" data-confirm-guild-requirement>Aceptar de todos modos</button></div>
</dialog>
