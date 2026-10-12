<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>CODEPLAY - Resultado</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root{--green:#00f5a0;--panel:#0a2227;--line:rgba(127,237,208,.3);--muted:#abc2bd}*{box-sizing:border-box}body{margin:0;min-height:100vh;color:#f3fffb;font-family:Roboto,sans-serif;background:#041216}
        .page{width:min(940px,calc(100% - 28px));margin:auto;padding:35px 0 60px}.summary{text-align:center;padding:30px;border:1px solid var(--line);border-radius:22px;background:linear-gradient(145deg,#0d2c31,#081c20)}
        .eyebrow{color:#f2ce69;letter-spacing:3px;text-transform:uppercase;font-size:12px}h1{font-family:'Press Start 2P';color:var(--green);font-size:clamp(23px,4vw,36px);line-height:1.4}.score{font:700 clamp(52px,9vw,82px) Roboto;color:white}.score span{color:var(--muted);font-size:.45em}
        .stats{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin:18px 0}.stat{min-width:145px;padding:12px;border-radius:12px;background:rgba(255,255,255,.055)}.stat strong{display:block;font-size:23px}.correct strong{color:#79f3ba}.wrong strong{color:#ff8c96}
        .note{color:#b8cfca}.actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:22px}.btn{display:inline-flex;gap:9px;align-items:center;padding:12px 18px;border-radius:12px;text-decoration:none;font-weight:700}.primary{background:var(--green);color:#032019}.secondary{border:1px solid var(--line);color:#dffcf3}
        .review-title{margin:35px 0 15px}.answer{padding:19px;margin-bottom:12px;border-radius:15px;background:var(--panel);border:1px solid var(--line)}.answer.ok{border-left:5px solid #36d996}.answer.bad{border-left:5px solid #ff6673}.answer h3{font-size:17px;line-height:1.45;margin:0 0 12px}.answer p{margin:6px 0;color:#c0d4d0}.answer .expected{color:#84ecc8}
    </style>
</head>
<body>
@include('partials.alumno-sidebar')<main class="page">
    <section class="summary"><span class="eyebrow">Práctica completada</span><h1>Resultado del Test</h1><div class="score">{{ number_format($resultado->calificacion,0) }}<span>/100</span></div>
    <div class="stats"><div class="stat correct"><strong>{{ $resultado->correctas }}</strong>Aciertos</div><div class="stat wrong"><strong>{{ $resultado->incorrectas }}</strong>Errores</div><div class="stat"><strong>{{ $resultado->total_preguntas }}</strong>Preguntas</div></div>
    <p class="note"><i class="fas fa-circle-info"></i> Este resultado es de práctica y no modificó tus puntos CodePlay.</p>
    <div class="actions">
    @if($bancoPersonalId)
        <a class="btn primary" href="{{ route('alumno.bancos.edit', $bancoPersonalId) }}"><i class="fas fa-pen"></i> Volver a editar el banco</a>
        <a class="btn secondary" href="{{ route('alumno.bancos.index') }}"><i class="fas fa-layer-group"></i> Mis bancos</a>
    @else
        <a class="btn primary" href="{{ route('alumno.tests.codeplay') }}"><i class="fas fa-rotate-right"></i> Practicar otra vez</a>
        <a class="btn secondary" href="{{ route('alumno.tests') }}"><i class="fas fa-grid-2"></i> Tests y Evaluaciones</a>
    @endif
</div></section>
    <h2 class="review-title">Revisión de respuestas</h2>
    @foreach($detalles as $indice=>$detalle)<article class="answer {{ $detalle['correcta'] ? 'ok' : 'bad' }}"><h3>{{ $indice+1 }}. {{ $detalle['pregunta'] }}</h3><p><strong>Tu respuesta:</strong> {{ $detalle['respuesta'] }}</p>@if(!$detalle['correcta'])<p class="expected"><strong>Respuesta correcta:</strong> {{ $detalle['esperada'] }}</p>@endif @if($detalle['explicacion'])<p>{{ $detalle['explicacion'] }}</p>@endif</article>@endforeach
</main></body></html>
