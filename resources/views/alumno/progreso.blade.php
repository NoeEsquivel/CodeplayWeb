<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Progreso - CODEPLAY</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root { --green:#00f5a0; --green-soft:#76ffd0; --cyan:#42cfff; --gold:#ffd166; --panel:rgba(5,16,23,.92); --line:rgba(118,255,208,.3); }
        * { box-sizing:border-box; }
        body { min-height:100vh; margin:0; color:#eefcf8; font-family:'Roboto',sans-serif; background:linear-gradient(rgba(1,8,13,.78),rgba(1,8,13,.9)),url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') center/cover fixed; }
        .page { width:min(1240px,calc(100% - 32px)); margin:auto; padding:24px 0 60px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .back { display:inline-flex; align-items:center; gap:9px; padding:10px 15px; border:1px solid var(--line); border-radius:12px; color:#dffff5; background:rgba(7,26,32,.85); text-decoration:none; }
        .back:hover { color:#071b18; background:var(--green-soft); }
        .identity { text-align:right; color:#b9d3cf; font-size:14px; }
        .identity strong { display:block; color:white; font-size:18px; }
        .hero { display:grid; grid-template-columns:180px 1fr; align-items:center; gap:28px; padding:28px; border:1px solid var(--line); border-radius:24px; background:linear-gradient(135deg,rgba(7,35,36,.96),rgba(8,20,31,.95)); box-shadow:0 20px 55px rgba(0,0,0,.35); }
        .progress-ring { --value:0; width:150px; aspect-ratio:1; margin:auto; display:grid; place-items:center; border-radius:50%; background:conic-gradient(var(--green) calc(var(--value)*1%),rgba(255,255,255,.09) 0); position:relative; }
        .progress-ring::before { content:""; position:absolute; inset:13px; border-radius:50%; background:#092126; }
        .progress-ring span { position:relative; text-align:center; font-size:31px; font-weight:700; color:var(--green-soft); }
        .progress-ring small { display:block; font-size:11px; letter-spacing:1px; color:#b9d3cf; text-transform:uppercase; }
        .eyebrow { color:var(--gold); letter-spacing:3px; text-transform:uppercase; font-size:12px; font-weight:700; }
        h1 { margin:8px 0; font-size:clamp(29px,4vw,48px); }
        .hero p { margin:0; max-width:720px; color:#c3d7d4; font-size:17px; line-height:1.6; }
        .quick-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:18px 0 28px; }
        .stat { padding:18px; border:1px solid rgba(255,255,255,.1); border-radius:16px; background:var(--panel); }
        .stat i { color:var(--green); margin-right:7px; }
        .stat strong { display:block; margin-top:8px; font-size:25px; color:white; }
        .stat small { color:#a9c3bf; }
        .section-title { margin:32px 0 15px; display:flex; justify-content:space-between; align-items:end; gap:15px; }
        .section-title h2 { margin:0; font-size:25px; }
        .section-title p { margin:3px 0 0; color:#9fbab6; }
        .modules { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
        .module { min-width:0; padding:18px; border:1px solid var(--line); border-radius:17px; background:var(--panel); color:white; text-decoration:none; transition:.2s ease; }
        .module:hover { transform:translateY(-4px); border-color:var(--green); color:white; box-shadow:0 10px 28px rgba(0,245,160,.12); }
        .module-head { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
        .module-head i { width:36px; height:36px; display:grid; place-items:center; border-radius:10px; color:#061815; background:var(--green-soft); }
        .module-head strong { font-size:16px; }
        .bar { height:8px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,.1); }
        .bar span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--green),var(--cyan)); }
        .module-meta { display:flex; justify-content:space-between; gap:10px; margin-top:9px; color:#b9d3cf; font-size:13px; }
        .available-note { padding:10px 14px; border:1px solid rgba(0,245,160,.35); border-radius:12px; color:var(--green-soft); background:rgba(0,245,160,.08); }
        .tests { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .test-card { padding:18px; border:1px solid rgba(255,255,255,.12); border-radius:18px; background:var(--panel); }
        .test-top { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; }
        .test-card h3 { margin:0 0 5px; font-size:17px; }
        .test-card .document { color:#9db8b5; font-size:13px; }
        .badge-state { flex:0 0 auto; padding:6px 9px; border-radius:20px; font-size:12px; font-weight:700; }
        .badge-ready { color:#062319; background:var(--green-soft); }
        .badge-wait { color:#332600; background:var(--gold); }
        .doc-state { display:flex; align-items:center; gap:7px; margin:14px 0; font-size:13px; color:#c7d9d6; }
        .doc-state.done i { color:var(--green); }
        .doc-state.pending i { color:#7f9995; }
        .test-data { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:14px; }
        .test-data div { padding:8px; border-radius:10px; background:rgba(255,255,255,.055); text-align:center; }
        .test-data strong { display:block; color:white; }
        .test-data small { color:#8fa9a5; }
        .action { display:inline-flex; justify-content:center; align-items:center; gap:8px; width:100%; min-height:42px; border-radius:11px; font-weight:700; text-decoration:none; }
        .action-ready { color:#062319; background:var(--green-soft); }
        .action-ready:hover { color:#062319; background:white; }
        .action-wait { color:#dacfa9; border:1px solid rgba(255,209,102,.25); background:rgba(255,209,102,.07); }
        .empty { grid-column:1/-1; padding:28px; text-align:center; border:1px dashed var(--line); border-radius:18px; color:#abc4c0; }
        @media (max-width:900px) { .quick-stats,.modules { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:680px) { .page{width:min(100% - 20px,1240px)} .hero{grid-template-columns:1fr;text-align:center;padding:22px 16px}.hero p{margin:auto}.tests{grid-template-columns:1fr}.quick-stats{grid-template-columns:1fr 1fr}.section-title{align-items:flex-start;flex-direction:column}.identity{display:none} }
    </style>
</head>
<body>
@include('partials.alumno-sidebar')
@php
    $moduleInfo = [
        'documentacion' => ['Documentación','fa-book',route('alumno.documentacion')],
        'tutoriales' => ['Tutoriales','fa-graduation-cap',route('alumno.tutoriales')],
        'desafios' => ['Desafíos','fa-gamepad',route('alumno.desafios')],
        'aventura' => ['Aventura','fa-shield-halved',route('alumno.aventura')],
    ];
@endphp
<main class="page">
    <nav class="topbar">
        <a class="back" href="{{ route('inicio.alumno') }}"><i class="fas fa-arrow-left"></i> Menú principal</a>
        <div class="identity"><span>Progreso de</span><strong>{{ $student->nombre }}</strong></div>
    </nav>

    <section class="hero">
        <div class="progress-ring" style="--value:{{ $overall }}"><span>{{ number_format($overall, 0) }}%<small>avance general</small></span></div>
        <div><span class="eyebrow">Panel del estudiante</span><h1>Tu recorrido en CODEPLAY</h1><p>Aquí puedes ver cuánto has avanzado, qué contenido ya revisaste y cuáles tests están listos para darte puntos nuevamente.</p></div>
    </section>

    <section class="quick-stats" aria-label="Resumen del progreso">
        <div class="stat"><small><i class="fas fa-star"></i>Puntos disponibles</small><strong>{{ number_format($pointsAvailable) }}</strong></div>
        <div class="stat"><small><i class="fas fa-clipboard-check"></i>Tests realizados</small><strong>{{ $testAttempts }}</strong></div>
        <div class="stat"><small><i class="fas fa-bolt"></i>Tests disponibles</small><strong>{{ $availableTests }}</strong></div>
        <div class="stat"><small><i class="fas fa-trophy"></i>Promedio en tests</small><strong>{{ $testAverage !== null ? number_format($testAverage, 1).' pts' : 'Sin intentos' }}</strong></div>
    </section>

    <div class="section-title"><div><h2>Avance por módulo</h2><p>Selecciona un módulo para continuar aprendiendo.</p></div></div>
    <section class="modules">
        @foreach($moduleInfo as $key => [$label,$icon,$url])
            <a class="module" href="{{ $url }}">
                <div class="module-head"><i class="fas {{ $icon }}"></i><strong>{{ $label }}</strong></div>
                <div class="bar"><span style="width:{{ $modules[$key]['porcentaje'] }}%"></span></div>
                <div class="module-meta"><span>{{ $modules[$key]['completados'] }} de {{ $modules[$key]['total'] }}</span><strong>{{ number_format($modules[$key]['porcentaje'], 0) }}%</strong></div>
                @if($key === 'aventura')<div class="module-meta"><span>Nivel del héroe {{ $adventureLevel }}</span><span>{{ $adventureVictories }} victoria(s)</span></div>@endif
            </a>
        @endforeach
    </section>

    <div class="section-title">
        <div><h2>Documentación y tests</h2><p>Los tests vuelven a otorgar puntos 24 horas después de cada intento.</p></div>
        @if($availableTests > 0)<div class="available-note"><i class="fas fa-bolt"></i> Tienes {{ $availableTests }} {{ $availableTests === 1 ? 'test disponible' : 'tests disponibles' }}</div>@endif
    </div>
    <section class="tests">
        @forelse($tests as $test)
            <article class="test-card">
                <div class="test-top">
                    <div><h3>{{ $test['titulo'] }}</h3><div class="document">{{ $test['documento_titulo'] ?? 'Documento de estudio' }}</div></div>
                    <span class="badge-state {{ $test['disponible'] ? 'badge-ready' : 'badge-wait' }}">{{ $test['disponible'] ? 'Disponible' : 'En espera' }}</span>
                </div>
                <div class="doc-state {{ $test['documento_revisado'] ? 'done' : 'pending' }}">
                    <i class="fas {{ $test['documento_revisado'] ? 'fa-circle-check' : 'fa-circle' }}"></i>
                    {{ $test['documento_revisado'] ? 'Documentación revisada' : 'Documentación pendiente de revisar' }}
                </div>
                <div class="test-data">
                    <div><strong>{{ $test['intentos'] }}</strong><small>intentos</small></div>
                    <div><strong>{{ $test['mejor_puntaje'] !== null ? number_format($test['mejor_puntaje'],0) : '—' }}</strong><small>mejor puntaje</small></div>
                    <div><strong>{{ $test['promedio'] !== null ? number_format($test['promedio'],1) : '—' }}</strong><small>promedio</small></div>
                </div>
                @if($test['disponible'])
                    <a class="action action-ready" href="{{ route('test.show', $test['modulo']) }}"><i class="fas fa-play"></i> Realizar test y ganar puntos</a>
                @else
                    <div class="action action-wait" data-countdown="{{ $test['disponible_desde']->toIso8601String() }}" data-test-url="{{ route('test.show', $test['modulo']) }}"><i class="fas fa-clock"></i><span>Disponible en breve</span></div>
                @endif
            </article>
        @empty
            <div class="empty"><i class="fas fa-clipboard-list fa-2x"></i><p>Aún no hay tests configurados.</p></div>
        @endforelse
    </section>
</main>
<script>
    function formatRemaining(totalSeconds) {
        const seconds = Math.max(0, Math.floor(totalSeconds));
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const rest = seconds % 60;
        return `${hours} h ${String(minutes).padStart(2,'0')} min ${String(rest).padStart(2,'0')} s`;
    }
    function updateCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(box => {
            const remaining = Math.ceil((new Date(box.dataset.countdown).getTime() - Date.now()) / 1000);
            if (remaining <= 0) {
                const link = document.createElement('a');
                link.className = 'action action-ready';
                link.href = box.dataset.testUrl;
                link.innerHTML = '<i class="fas fa-play"></i> Realizar test y ganar puntos';
                box.replaceWith(link);
                return;
            }
            box.querySelector('span').textContent = 'Podrás ganar puntos en '+formatRemaining(remaining);
        });
    }
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
</script>
</body>
</html>
