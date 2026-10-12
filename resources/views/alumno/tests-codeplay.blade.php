<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>CODEPLAY - Test CodePlay</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root{--green:#00f5a0;--panel:rgba(5,20,25,.94);--line:rgba(129,235,207,.3);--muted:#a9bfbd}*{box-sizing:border-box}
        body{margin:0;min-height:100vh;color:#f2fffb;font-family:Roboto,sans-serif;background:linear-gradient(rgba(1,12,16,.8),rgba(1,12,16,.94)),url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') center/cover fixed}
        .page{width:min(1120px,calc(100% - 30px));margin:auto;padding:28px 0 60px}.back{display:inline-flex;gap:9px;align-items:center;color:#dfffee;text-decoration:none;padding:10px 14px;border:1px solid var(--line);border-radius:12px}
        .hero{text-align:center;margin:30px auto}.eyebrow{color:#f4ce6a;letter-spacing:3px;text-transform:uppercase;font-size:12px}h1{font-family:'Press Start 2P';color:var(--green);font-size:clamp(24px,4vw,38px);line-height:1.35;margin:14px 0}
        .hero p{color:var(--muted);font-size:17px}.notice{max-width:780px;margin:0 auto 25px;padding:13px 16px;border:1px solid rgba(244,206,106,.35);border-radius:12px;background:rgba(244,206,106,.08);color:#f9e6ae;text-align:center}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.category{padding:0;border:1px solid var(--line);border-radius:18px;background:var(--panel);overflow:hidden;color:inherit;text-align:left}
        .category:last-child{grid-column:1/-1;width:min(545px,100%);justify-self:center}.inside{padding:23px}.head{display:flex;justify-content:space-between;gap:15px}.icon{display:grid;place-items:center;width:52px;height:52px;border-radius:14px;background:rgba(0,245,160,.11);color:var(--green);font-size:23px}
        h2{margin:14px 0 7px;font-size:21px}.category p{color:var(--muted);line-height:1.5;min-height:46px}.meta{display:flex;justify-content:space-between;color:#b8cdc8;font-size:13px;margin:17px 0}
        button{width:100%;border:0;border-top:1px solid var(--line);padding:15px;background:rgba(0,245,160,.09);color:#91ffda;font:700 15px Roboto;cursor:pointer;transition:.2s}button:hover{background:var(--green);color:#032018}
        .error{padding:13px 16px;margin:0 auto 20px;max-width:760px;background:#61272d;border:1px solid #ff8490;border-radius:12px}
        @media(max-width:720px){.grid{grid-template-columns:1fr}.category:last-child{grid-column:auto}.page{width:min(100% - 20px,1120px)}}
    </style>
</head>
<body>
@include('partials.alumno-sidebar')<main class="page">
    <a class="back" href="{{ route('alumno.tests') }}"><i class="fas fa-arrow-left"></i> Tests y Evaluaciones</a>
    <section class="hero"><span class="eyebrow">Práctica individual</span><h1>Test CodePlay</h1><p>Escoge un tema. Cada intento mezcla diez preguntas diferentes.</p></section>
    <div class="notice"><i class="fas fa-circle-info"></i> Esta práctica muestra tus aciertos y errores, pero no suma ni descuenta puntos CodePlay.</div>
    @if(session('error'))<div class="error">{{ session('error') }}</div>@endif
    <section class="grid">
        @foreach($categorias as $categoria)
        <form class="category" method="POST" action="{{ route('alumno.tests.codeplay.iniciar') }}">
            @csrf<input type="hidden" name="categoria" value="{{ $categoria['clave'] }}">
            <div class="inside"><div class="head"><span class="icon"><i class="fas {{ $categoria['icono'] }}"></i></span><span style="color:#f4ce6a;font-size:13px">{{ $categoria['preguntas'] }} preguntas</span></div>
            <h2>{{ $categoria['nombre'] }}</h2><p>{{ $categoria['descripcion'] }}</p><div class="meta"><span><i class="fas fa-list-ol"></i> 10 por intento</span><span><i class="fas fa-shuffle"></i> Orden aleatorio</span></div></div>
            <button type="submit" @disabled($categoria['preguntas'] < 10)><i class="fas fa-play"></i> {{ $categoria['preguntas'] >= 10 ? 'Comenzar práctica' : 'Contenido insuficiente' }}</button>
        </form>
        @endforeach
    </section>
</main></body></html>

