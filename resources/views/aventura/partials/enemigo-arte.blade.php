        <svg viewBox="0 0 140 110"><ellipse cx="70" cy="96" rx="48" ry="8" fill="currentColor" opacity=".15"/>
        @if($enemigo['clave'] === 'mimic')<path d="M24 52h92v37H24ZM24 42Q70 5 116 42L70 62Z"/><path class="detail" d="m35 52 8 18 9-13 11 18 10-13 10 13 10-18 10 13 6-18M49 31h8m26 0h8"/>
        @elseif(($enemigo['region'] ?? 1) > 1) @include('aventura.partials.enemigo-regional')
        @elseif($enemigo['clave'] === 'slime')<path d="M25 85Q12 58 41 43Q57 12 91 40Q125 48 117 85Q76 106 25 85Z"/><path class="detail" d="M53 60v9m34-9v9m-26 9q10 8 20 0"/>
        @elseif($enemigo['clave'] === 'lobo')<path d="m32 23 29 20h18l29-20-4 40-20 31H58L35 64Z"/><path class="detail" d="m43 53 16 7m22 0 15-7M61 77l9 8 9-8M70 85v9"/>
        @elseif($enemigo['clave'] === 'avispa')<ellipse cx="69" cy="62" rx="22" ry="29"/><path d="M48 58Q3 21 17 13Q57 9 64 44M88 58Q137 23 123 13Q84 10 75 43"/><path class="detail" d="M50 57h38M50 71h38m-23 18 5 11 5-11m-15-57-8-16m28 16 8-16"/>
        @elseif($enemigo['clave'] === 'hongo')<path d="M54 51h34l8 41H46Z"/><path d="M16 55Q23 3 72 12Q120 13 124 55Z"/><path class="detail" d="M42 38h9m20-13h8m20 16h9M60 70v6m20-6v6"/>
        @elseif($enemigo['clave'] === 'ladron')<path d="m40 44-24-9 15 28 12 2 11 23h34l11-24 12-2 14-27-24 9Q93 13 69 15Q45 15 40 44Z"/><path class="detail" d="M43 55h54M53 53v8m33-8v8m-25 17 20-1"/>
        @else<path d="m55 48-17-9-17-27m19 29-21 3m73 2 17-19 14-16m-17 18 19 5M55 41l-11 48-17 10 39-6 9-15 5 15 35 6-17-13-7-44-17-15Z"/><path class="detail" d="m58 55 9 3m13 0 9-3m-27 14 22 0M66 89l6-38"/>
        @endif
        </svg>
