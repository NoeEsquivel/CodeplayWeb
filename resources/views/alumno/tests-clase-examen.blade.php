<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CODEPLAY - Examen</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root{--green:#00f5a0;--panel:#092126;--line:rgba(123,238,207,.32);--muted:#abc3be;--gold:#f1ce70;--cyan:#20d9ef;--pink:#ff6685}*{box-sizing:border-box}body{margin:0;min-height:100vh;color:#f3fffb;font-family:Roboto;background:radial-gradient(circle at 20% 0,#103b3d 0,transparent 38%),#041519}.page{width:min(900px,calc(100% - 28px));margin:auto;padding:25px 0}.top,.progress-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.back,.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:11px 16px;border-radius:10px;text-decoration:none;font:800 16px Roboto;cursor:pointer}.back{color:white;border:1px solid var(--line)}.btn{border:0;background:var(--green);color:#032019}.danger{background:linear-gradient(120deg,#ffba55,#ff6685);color:#220811}.panel{margin-top:18px;padding:24px;border:1px solid var(--line);border-radius:18px;background:linear-gradient(145deg,#0b2930,#071d23);box-shadow:0 20px 45px #0005}h1{font-family:'Press Start 2P';color:var(--green);font-size:clamp(21px,4vw,32px);line-height:1.5}.muted{color:var(--muted)}.bar{height:12px;border-radius:99px;background:#061418;overflow:hidden}.bar span{display:block;height:100%;background:linear-gradient(90deg,var(--green),var(--cyan))}.exam-clock{display:flex;align-items:center;gap:11px;padding:10px 14px;border:1px solid #f1ce706b;border-radius:12px;background:#30291680}.exam-clock i{color:var(--gold);font-size:21px}.exam-clock small,.exam-clock strong{display:block}.exam-clock small{color:#e8d9ae}.exam-clock strong{color:#fff3bd;font:700 24px monospace;letter-spacing:.05em}.exam-clock.urgent{border-color:#ff7380;background:#4d1d25}.exam-clock.urgent i,.exam-clock.urgent strong{color:#ffadb5}.deadline-local{margin-top:4px;color:var(--muted);font-size:13px}.question{font-size:23px;line-height:1.45}.options{display:grid;gap:10px}.option{padding:13px;border:1px solid var(--line);border-radius:10px}.option:has(input:checked){background:rgba(0,245,160,.1);border-color:var(--green)}input[type=text]{width:100%;padding:13px;border:1px solid var(--line);border-radius:10px;background:#06191d;color:white;font:inherit}.message{padding:13px;border-radius:10px;margin-top:14px}.success{background:#174936}.error{background:#61282e}.grade{text-align:center}.grade-score{display:block;font-size:58px;color:var(--gold)}.delivered-icon{width:82px;height:82px;display:grid;place-items:center;margin:0 auto 14px;border-radius:24px;background:linear-gradient(145deg,var(--green),var(--cyan));color:#032019;font-size:36px;transform:rotate(-4deg);box-shadow:0 13px 32px #00e6ba42}.waiting-banner{max-width:620px;margin:12px auto;color:#d8ece8;font-size:18px;line-height:1.6}.review-note{display:inline-flex;gap:8px;align-items:center;margin:13px 0;padding:9px 12px;border-radius:99px;background:#f1ce7017;color:#ffe39a}.codeplay-dialog{width:min(540px,calc(100% - 28px));padding:0;border:1px solid #76ecc8;border-radius:22px;background:#08232a;color:#f4fffc;box-shadow:0 30px 90px #000d;overflow:hidden}.codeplay-dialog::backdrop{background:#011014d9;backdrop-filter:blur(6px)}.dialog-art{padding:28px 28px 20px;text-align:center;background:radial-gradient(circle at 50% -20%,#17edb65e,transparent 65%)}.dialog-art i{width:74px;height:74px;display:grid;place-items:center;margin:auto;border-radius:22px;background:linear-gradient(145deg,#ffd65b,#ff7b78);color:#281305;font-size:31px;box-shadow:0 12px 30px #0005}.dialog-art h2{margin:18px 0 8px;font-size:27px}.dialog-art p{margin:0;color:#c5dad6;line-height:1.6}.dialog-actions{display:flex;justify-content:center;gap:11px;padding:0 28px 28px}.dialog-actions button{min-width:150px}.secondary{background:#17343b;color:#eafff9;border:1px solid #507078}.confetti-piece{position:fixed;z-index:10000;top:-20px;width:11px;height:18px;border-radius:3px;pointer-events:none;animation:confetti-fall 3.4s cubic-bezier(.12,.65,.3,1) forwards}@keyframes confetti-fall{to{transform:translate3d(var(--drift),110vh,0) rotate(900deg);opacity:.1}}@media(max-width:580px){.dialog-actions{flex-direction:column}.dialog-actions button{width:100%}}
    </style>
    <style>
        .question-map{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}.question-pill{width:42px;height:42px;display:grid;place-items:center;border:1px solid #52736c;border-radius:10px;background:#06191d;color:#dceae7;text-decoration:none;font-weight:900}.question-pill.answered{border-color:#32d9a5;background:#123c32;color:#9bffda}.question-pill.current{outline:3px solid #f1ce704d;border-color:var(--gold);color:#ffe99e}.question-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px}.question-nav .back{min-width:130px}.save-row{display:flex;justify-content:flex-end;margin-top:16px}.celebration{position:fixed;inset:0;z-index:9998;display:grid;place-items:center;pointer-events:none;background:radial-gradient(circle,#063d34b8,#020c10b8);animation:celebration-out 4.8s ease forwards}.celebration-card{position:relative;z-index:10001;width:min(470px,calc(100% - 32px));padding:32px;border:2px solid #8dffd7;border-radius:25px;background:linear-gradient(145deg,#123f3b,#092329);text-align:center;box-shadow:0 28px 100px #000d;animation:celebration-pop .65s cubic-bezier(.16,1.25,.4,1)}.celebration-card i{font-size:54px;color:#ffd65f}.celebration-card h2{margin:16px 0 8px;font-size:30px}.celebration-card p{margin:0;color:#d3e9e4;font-size:18px}@keyframes celebration-pop{from{opacity:0;transform:scale(.65) translateY(35px)}to{opacity:1;transform:scale(1) translateY(0)}}@keyframes celebration-out{0%,78%{opacity:1;visibility:visible}100%{opacity:0;visibility:hidden}}@media(max-width:580px){.question-nav{flex-wrap:wrap}.question-nav .back,.save-row .btn{width:100%}}
    </style>
</head>
<body>
<main class="page">
    <div class="top"><a class="back" href="{{ route('alumno.tests.clase') }}">← Actividades</a><span>{{ $sesion->clase->nombre }}</span></div>
    <section class="panel">
        <span class="muted">EXAMEN · {{ $sesion->anfitrion->nombre ?? 'Profesor' }}</span>
        <h1>{{ $sesion->configuracion['titulo'] ?? 'Evaluación de clase' }}</h1>
        <div class="progress-head"><span>{{ $state['respondidas'] }} de {{ $state['total'] }} respuestas guardadas</span>@if($state['limite']&&!$state['entregado'])<div class="exam-clock" id="exam-clock"><i class="fas fa-stopwatch"></i><div><small>Tiempo restante</small><strong id="exam-countdown" data-deadline="{{ $state['limite']->toIso8601String() }}" data-server-now="{{ now()->toIso8601String() }}">--:--:--</strong><div class="deadline-local">Entrega: <time id="exam-deadline-local">{{ $state['limite']->format('d/m/Y H:i:s') }}</time></div></div></div>@elseif(!$state['limite'])<span>Sin límite general</span>@endif</div>
        <div class="bar"><span style="width:{{ $state['total']?round($state['respondidas']*100/$state['total']):0 }}%"></span></div>
    </section>
    @if(session('success'))<div class="message success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="message error">{{ session('error') }}</div>@endif

    @if($state['entregado'])
        <section class="panel grade">
            <div class="delivered-icon"><i class="fas fa-paper-plane"></i></div>
            <h2>¡Examen entregado!</h2>
            @if($puedeRevisar)
                <span class="grade-score">{{ number_format((float)$state['resultado']->calificacion,2) }}</span>
                <p>Tu calificación ya está disponible porque la sesión fue cerrada.</p>
                <div class="review-note"><i class="fas fa-clock"></i> Resumen disponible hasta {{ $resumenHasta?->format('d/m/Y H:i') }}</div><br>
                <a class="btn" href="{{ route('alumno.tests.clase.examen.summary',$sesion) }}"><i class="fas fa-list-check"></i> Ver resumen de mis respuestas</a>
            @else
                <p class="waiting-banner">Tu entrega quedó guardada. La calificación y las respuestas correctas se mostrarán cuando el Profesor cierre la sesión.</p>
                <div class="review-note"><i class="fas fa-shield-halved"></i> Resultados protegidos hasta el cierre</div>
            @endif
        </section>
    @elseif($sesion->estado==='espera')
        <section class="panel grade"><i class="fas fa-hourglass-half fa-3x"></i><h2>Esperando al Profesor</h2><p>El examen comenzará automáticamente al llegar la hora programada o cuando el Profesor lo active.</p></section>
    @elseif($state['emision'])
        @php $savedValues=collect($state['respuesta_actual']??[])->map(fn($value)=>(string)$value)->all(); @endphp
        <section class="panel"><p class="muted">Pregunta {{ ($state['indice_actual']??0)+1 }} de {{ $state['total'] }} · {{ $state['emision']->puntos_maximos }} puntos</p><h2 class="question">{{ $state['pregunta']->enunciado }}</h2><form method="POST" action="{{ route('alumno.tests.clase.examen.save',[$sesion,$state['emision']]) }}">@csrf
        @if($state['pregunta']->tipo==='respuesta_directa')<input type="text" name="respuesta" value="{{ $savedValues[0]??'' }}" maxlength="1000" required autocomplete="off" placeholder="Escribe tu respuesta">
        @else @php $opts=$state['pregunta']->tipo==='verdadero_falso'?['true'=>'Verdadero','false'=>'Falso']:($state['pregunta']->opciones??[]);$multi=$state['pregunta']->tipo==='seleccion_multiple'; @endphp<div class="options">@foreach($opts as $key=>$label)<label class="option"><input type="{{ $multi?'checkbox':'radio' }}" name="respuesta{{ $multi?'[]':'' }}" value="{{ $key }}" @checked(in_array((string)$key,$savedValues,true)) @if(!$multi) required @endif> {{ $label }}</label>@endforeach</div>@endif
        @if($state['siguiente'])<input type="hidden" name="destino" value="{{ $state['siguiente']->id }}">@endif<div class="save-row"><button class="btn"><i class="fas fa-floppy-disk"></i> {{ in_array($state['emision']->id,$state['respondidas_ids'],true)?'Actualizar respuesta':'Guardar respuesta' }}{{ $state['siguiente']?' y continuar':'' }}</button></div></form>
        <div class="question-nav">@if($state['anterior'])<a class="back" href="{{ route('alumno.tests.clase.examen',['sesion'=>$sesion,'pregunta'=>$state['anterior']->id]) }}"><i class="fas fa-arrow-left"></i> Anterior</a>@else<span></span>@endif @if($state['siguiente'])<a class="back" href="{{ route('alumno.tests.clase.examen',['sesion'=>$sesion,'pregunta'=>$state['siguiente']->id]) }}">Siguiente <i class="fas fa-arrow-right"></i></a>@endif</div><p class="muted">Puedes volver a cualquier pregunta y cambiar su respuesta hasta entregar el examen.</p></section>
    @else
        <section class="panel grade"><h2>Respondiste todas las preguntas</h2><p>Puedes revisar y cambiar cualquier respuesta antes de entregar. Tu calificación permanecerá oculta hasta que el Profesor cierre la sesión.</p><a class="back" href="{{ route('alumno.tests.clase.examen',['sesion'=>$sesion,'pregunta'=>$state['emisiones']->first()?->id]) }}"><i class="fas fa-rotate-left"></i> Revisar desde la primera</a> <button class="btn danger" type="button" id="open-submit-dialog"><i class="fas fa-paper-plane"></i> Entregar examen</button></section>
    @endif
    @if(!$state['entregado']&&$state['emisiones']->isNotEmpty())<section class="panel"><strong>Navegar entre preguntas</strong><div class="question-map">@foreach($state['emisiones'] as $questionEmission)<a class="question-pill {{ in_array($questionEmission->id,$state['respondidas_ids'],true)?'answered':'' }} {{ $state['emision']?->id===$questionEmission->id?'current':'' }}" href="{{ route('alumno.tests.clase.examen',['sesion'=>$sesion,'pregunta'=>$questionEmission->id]) }}" title="{{ in_array($questionEmission->id,$state['respondidas_ids'],true)?'Respondida':'Sin responder' }}">{{ $loop->iteration }}</a>@endforeach</div><p class="muted"><i class="fas fa-circle-check"></i> Las preguntas verdes ya tienen una respuesta guardada.</p></section>@endif
</main>

@if(session('exam_submitted'))<div class="celebration" id="submission-celebration" role="status" aria-live="assertive"><div class="celebration-card"><i class="fas fa-trophy"></i><h2>¡Entrega completada!</h2><p>Tu examen fue enviado correctamente. ¡Buen trabajo!</p></div></div>@endif

<dialog class="codeplay-dialog" id="submit-exam-dialog">
    <div class="dialog-art"><i class="fas fa-flag-checkered"></i><h2>¿Listo para entregar?</h2><p>Después de entregar ya no podrás cambiar tus respuestas. Tu calificación permanecerá protegida hasta que el Profesor cierre el examen.</p></div>
    <div class="dialog-actions"><button class="btn secondary" type="button" data-close-dialog>Seguir revisando</button><form method="POST" action="{{ route('alumno.tests.clase.examen.submit',$sesion) }}">@csrf<button class="btn danger" type="submit"><i class="fas fa-paper-plane"></i> Sí, entregar</button></form></div>
</dialog>
@if(!$state['entregado']&&$state['limite'])<form id="automatic-exam-submit" method="POST" action="{{ route('alumno.tests.clase.examen.submit',$sesion) }}" hidden>@csrf</form>@endif
<script>
    (function(){
        const dialog=document.getElementById('submit-exam-dialog');
        document.getElementById('open-submit-dialog')?.addEventListener('click',()=>dialog.showModal());
        dialog?.querySelector('[data-close-dialog]')?.addEventListener('click',()=>dialog.close());
        dialog?.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});
        const countdown=document.getElementById('exam-countdown'),autoSubmit=document.getElementById('automatic-exam-submit');
        if(countdown&&autoSubmit){
            const deadline=Date.parse(countdown.dataset.deadline),serverAtRender=Date.parse(countdown.dataset.serverNow),clockOffset=serverAtRender-Date.now();
            const localDeadline=document.getElementById('exam-deadline-local'),clockBox=document.getElementById('exam-clock');let submitting=false;
            if(localDeadline)localDeadline.textContent=new Date(deadline).toLocaleString([], {dateStyle:'short',timeStyle:'medium'});
            function tick(){
                const remaining=deadline-(Date.now()+clockOffset),seconds=Math.max(0,Math.ceil(remaining/1000)),days=Math.floor(seconds/86400),hours=Math.floor((seconds%86400)/3600),minutes=Math.floor((seconds%3600)/60),secs=seconds%60;
                countdown.textContent=(days?days+'d ':'')+String(hours).padStart(2,'0')+':'+String(minutes).padStart(2,'0')+':'+String(secs).padStart(2,'0');
                clockBox?.classList.toggle('urgent',seconds<=300);
                if(remaining<=0&&!submitting){submitting=true;countdown.textContent='Entregando…';document.querySelectorAll('main input,main button,main textarea,main select').forEach(control=>control.disabled=true);autoSubmit.submit();return;}
                setTimeout(tick,250);
            }
            tick();
        }
        @if(session('exam_submitted'))
        const colors=['#00f5a0','#20d9ef','#f1ce70','#ff6685','#9b7dff'];
        for(let i=0;i<85;i++){const piece=document.createElement('i');piece.className='confetti-piece';piece.style.left=Math.random()*100+'vw';piece.style.background=colors[i%colors.length];piece.style.setProperty('--drift',(Math.random()*240-120)+'px');piece.style.animationDelay=(Math.random()*.8)+'s';piece.style.animationDuration=(2.4+Math.random()*1.8)+'s';document.body.appendChild(piece);setTimeout(()=>piece.remove(),4800);}
        @endif
        @if($state['entregado']&&!$puedeRevisar&&$sesion->estado!=='cerrada')
        setTimeout(()=>{if(!document.querySelector('dialog[open]'))location.reload();},12000);
        @endif
        @if($sesion->estado==='espera')
        setTimeout(()=>location.reload(),8000);
        @endif
        @if($sesion->estado==='activa'&&$sesion->disponible_hasta)
        (function watchScheduledClose(){const remaining=new Date(@json($sesion->disponible_hasta->toIso8601String())).getTime()-Date.now();if(remaining<=0){location.reload();return;}setTimeout(watchScheduledClose,Math.min(remaining+300,60000));})();
        @endif
        @if($puedeRevisar&&$resumenHasta)
        (function watchReviewExpiry(){const remaining=new Date(@json($resumenHasta->toIso8601String())).getTime()-Date.now();if(remaining<=0){location.replace(@json(route('alumno.tests.clase')));return;}setTimeout(watchReviewExpiry,Math.min(remaining+300,60000));})();
        @endif
    })();
</script>
</body></html>
