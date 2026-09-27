<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Tests y Evaluaciones</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root{--green:#00f5a0;--ink:#06181d;--panel:rgba(5,20,25,.93);--line:rgba(123,240,205,.35);--muted:#a9bfbd}
        *{box-sizing:border-box} body{margin:0;min-height:100vh;color:#eefcf8;font-family:Roboto,sans-serif;background:linear-gradient(rgba(1,12,16,.78),rgba(1,12,16,.9)),url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') center/cover fixed}
        .page{width:min(1180px,calc(100% - 32px));margin:auto;padding:28px 0 56px}.top{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:32px}
        .back{display:inline-flex;align-items:center;gap:9px;color:#d9fff3;text-decoration:none;border:1px solid var(--line);padding:10px 15px;border-radius:12px;background:rgba(0,0,0,.28)}
        .identity{color:var(--muted)}.hero{text-align:center;margin:22px auto 36px}.eyebrow{color:#f2cf6b;text-transform:uppercase;letter-spacing:3px;font-size:12px;font-weight:700}
        h1{font-family:'Press Start 2P',cursive;color:var(--green);font-size:clamp(25px,4vw,42px);line-height:1.35;margin:15px 0 12px;text-shadow:0 3px #001}
        .hero p{max-width:720px;margin:auto;color:#c3d7d3;font-size:18px;line-height:1.6}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
        .card{position:relative;display:flex;gap:19px;align-items:flex-start;min-height:170px;padding:24px;border:1px solid var(--line);border-radius:20px;background:var(--panel);color:inherit;text-decoration:none;box-shadow:0 18px 45px rgba(0,0,0,.2);overflow:hidden}
        .card.active{border:2px solid var(--green);transition:.22s}.card.active:hover{transform:translateY(-5px);box-shadow:0 18px 42px rgba(0,245,160,.16)}
        .card:last-child{grid-column:1/-1;width:min(570px,100%);justify-self:center}.icon{display:grid;place-items:center;width:58px;height:58px;flex:0 0 58px;border-radius:15px;background:rgba(0,245,160,.1);color:var(--green);font-size:26px}
        .card h2{font-size:21px;margin:2px 0 9px}.card p{color:var(--muted);line-height:1.5;margin:0}.badge{display:inline-block;margin-top:15px;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:700;letter-spacing:.5px}
        .ready{background:rgba(0,245,160,.14);color:#83ffd4}.soon{background:rgba(242,207,107,.1);color:#f2cf6b}.number{position:absolute;right:18px;bottom:10px;font:700 44px/1 'Press Start 2P';color:rgba(255,255,255,.035)}
        @media(max-width:760px){.grid{grid-template-columns:1fr}.card:last-child{grid-column:auto}.identity{display:none}.page{width:min(100% - 20px,1180px)}.card{padding:20px}}
    </style>
</head>
<body><main class="page">
    <header class="top"><a class="back" href="{{ route('inicio.alumno') }}"><i class="fas fa-arrow-left"></i> Menú principal</a><div class="identity"><i class="fas fa-user"></i> {{ session('usuario_nombre') }}</div></header>
    <section class="hero"><span class="eyebrow">Centro de aprendizaje</span><h1>Tests y Evaluaciones</h1><p>Practica a tu ritmo, crea material de estudio y participa en actividades con compañeros o con tu profesor.</p></section>
    @if(session('error'))<div style="padding:14px 18px;margin-bottom:18px;border-radius:12px;background:#5e252a;border:1px solid #ff7b86">{{ session('error') }}</div>@endif
    <section class="grid">
        <a class="card active" href="{{ route('alumno.tests.codeplay') }}"><span class="icon"><i class="fas fa-shapes"></i></span><div><h2>Test CodePlay</h2><p>Practica con diez preguntas aleatorias del banco oficial, por tema o con todo el contenido.</p><span class="badge ready">Disponible ahora <i class="fas fa-arrow-right"></i></span></div><span class="number">01</span></a>
        <a class="card active" href="{{ route('alumno.bancos.index') }}"><span class="icon"><i class="fas fa-pen-ruler"></i></span><div><h2>Estudia con ayuda de CodePlay</h2><p>Crea, organiza, prueba y comparte tus propios bancos de preguntas.</p><span class="badge ready">Disponible ahora <i class="fas fa-arrow-right"></i></span></div><span class="number">02</span></a>
        <a class="card active" href="{{ route('alumno.codigo-amigo.index') }}"><span class="icon"><i class="fas fa-key"></i></span><div><h2>Tengo un Código Amigo</h2><p>Consulta códigos, guarda bancos de compañeros y practica desde tu biblioteca.</p><span class="badge ready">Disponible ahora <i class="fas fa-arrow-right"></i></span></div><span class="number">03</span></a>
        <a class="card active" href="{{ route('alumno.tests.amigos.index') }}"><span class="icon"><i class="fas fa-users"></i></span><div><h2>Test con amigos</h2><p>Crea una mesa de estudio o únete a una trivia sincronizada de hasta 30 alumnos.</p><span class="badge ready">Disponible ahora <i class="fas fa-arrow-right"></i></span></div><span class="number">04</span></a>
        <a class="card active" href="{{ route('alumno.tests.clase') }}"><span class="icon"><i class="fas fa-chalkboard-user"></i></span><div><h2>CodePlay en clase</h2><p>Participa en trivias o evaluaciones creadas y dirigidas por tus profesores.</p><span class="badge ready">Ver actividades <i class="fas fa-arrow-right"></i></span></div><span class="number">05</span></a>
    </section>
</main></body></html>

