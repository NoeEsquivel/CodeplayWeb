<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>CODEPLAY - Pregunta {{ $numero }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root{--green:#00f5a0;--panel:#0b2429;--line:rgba(124,235,207,.38);--muted:#abc1bd}*{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:26px;color:#f4fffc;font-family:Roboto,sans-serif;background:radial-gradient(circle at 50% 15%,rgba(0,245,160,.1),transparent 35%),#041216}
        .shell{width:min(860px,100%)}.top{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:16px}.brand{font-family:'Press Start 2P';color:var(--green);font-size:16px}.topic{color:#f1d174}.timer{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:10px;background:rgba(242,206,106,.12);color:#f6d97f;font-weight:800}.timer.danger{background:rgba(255,92,104,.16);color:#ff8d96}
        .progress{height:9px;background:#18353a;border-radius:99px;overflow:hidden;margin-bottom:22px}.progress span{display:block;height:100%;width:{{ ($numero / $total) * 100 }}%;background:linear-gradient(90deg,#00c984,var(--green));transition:.3s}
        .card{border:1px solid var(--line);border-radius:22px;background:var(--panel);box-shadow:0 22px 70px rgba(0,0,0,.35);overflow:hidden}.question{padding:31px}.count{color:#8eeacb;font-size:13px;text-transform:uppercase;letter-spacing:2px}
        h1{font-size:clamp(22px,3.5vw,31px);line-height:1.45;margin:13px 0 25px}.options{display:grid;gap:11px}.option{position:relative}.option input{position:absolute;opacity:0}.option label{display:flex;gap:13px;align-items:center;padding:15px 17px;border:1px solid rgba(255,255,255,.12);border-radius:13px;background:rgba(255,255,255,.04);cursor:pointer;transition:.18s}
        .option label:hover{transform:translateX(4px);border-color:#69dcb9}.option input:checked+label{background:rgba(0,245,160,.13);border-color:var(--green);box-shadow:0 0 0 1px var(--green)}.letter{display:grid;place-items:center;width:31px;height:31px;border-radius:9px;background:#18363a;color:#9ff4d8;font-weight:700}
        .answer-label{display:block;margin-bottom:8px;color:#9ff4d8;font-weight:700}.text-answer{width:100%;padding:15px;border:1px solid var(--line);border-radius:12px;background:#071a1e;color:white;font:16px Roboto}.actions{display:flex;justify-content:flex-end;padding:18px 30px;background:rgba(0,0,0,.18);border-top:1px solid var(--line)}
        button{border:0;border-radius:12px;padding:13px 25px;background:var(--green);color:#032018;font-weight:800;font-size:15px;cursor:pointer}.error{margin-bottom:14px;padding:12px 15px;background:#672a30;border:1px solid #ff8993;border-radius:12px}
        @media(max-width:600px){body{padding:12px}.question{padding:22px 17px}.top{align-items:flex-start;flex-direction:column}.actions{padding:15px 17px}button{width:100%}}
    </style>
</head>
<body><main class="shell">
    <header class="top"><span class="brand">CODEPLAY</span><span class="topic">{{ $categoria }}</span>@if($conTiempo)<span class="timer" id="timer"><i class="fas fa-clock"></i> <span id="seconds">{{ $segundosRestantes }}</span>s</span>@endif</header>
    <div class="progress" aria-label="Progreso {{ $numero }} de {{ $total }}"><span></span></div>
    @if(session('error'))<div class="error">{{ session('error') }}</div>@endif
    <form class="card" method="POST" action="{{ route('alumno.tests.codeplay.responder', $sesion) }}">
        @csrf<div class="question"><div class="count">Pregunta {{ $numero }} de {{ $total }}</div><h1>{{ $pregunta->enunciado }}</h1>
        @if($pregunta->tipo === 'respuesta_directa')
            <label class="answer-label" for="respuesta-directa">Tu respuesta</label><input id="respuesta-directa" class="text-answer" type="text" name="respuesta" maxlength="500" autocomplete="off" placeholder="Escribe tu respuesta..." required>
        @elseif($pregunta->tipo === 'seleccion_multiple')
            <div class="options">@foreach($pregunta->opciones ?? [] as $clave => $opcion)<div class="option"><input type="checkbox" name="respuesta[]" value="{{ $clave }}" id="op-{{ $clave }}"><label for="op-{{ $clave }}"><span class="letter">{{ strtoupper($clave) }}</span><span>{{ $opcion }}</span></label></div>@endforeach</div>
        @elseif($pregunta->tipo === 'verdadero_falso')
            <div class="options">@foreach(['1'=>'Verdadero','0'=>'Falso'] as $clave=>$opcion)<div class="option"><input type="radio" name="respuesta" value="{{ $clave }}" id="op-{{ $clave }}" required><label for="op-{{ $clave }}"><span class="letter">{{ $clave === '1' ? 'V' : 'F' }}</span><span>{{ $opcion }}</span></label></div>@endforeach</div>
        @else
            <div class="options">@foreach($pregunta->opciones ?? [] as $clave => $opcion)<div class="option"><input type="radio" name="respuesta" value="{{ $clave }}" id="op-{{ $clave }}" required><label for="op-{{ $clave }}"><span class="letter">{{ strtoupper($clave) }}</span><span>{{ $opcion }}</span></label></div>@endforeach</div>
        @endif</div>
        <div class="actions"><button type="submit">{{ $numero === $total ? 'Ver resultados' : 'Responder y continuar' }} <i class="fas fa-arrow-right"></i></button></div>
    </form>
    @if($conTiempo)
    <script>
        (() => {
            const form = document.querySelector('form.card');
            const timer = document.getElementById('timer');
            const output = document.getElementById('seconds');
            let remaining = Number(@json($segundosRestantes));
            let sent = false;
            const finish = () => {
                if (sent) return;
                sent = true;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'respuesta';
                hidden.value = '__sin_respuesta__';
                form.appendChild(hidden);
                form.submit();
            };
            if (remaining <= 0) finish();
            const interval = setInterval(() => {
                remaining -= 1;
                output.textContent = Math.max(0, remaining);
                if (remaining <= 5) timer.classList.add('danger');
                if (remaining <= 0) { clearInterval(interval); finish(); }
            }, 1000);
            form.addEventListener('submit', () => { sent = true; });
        })();
    </script>
    @endif</main></body></html>
