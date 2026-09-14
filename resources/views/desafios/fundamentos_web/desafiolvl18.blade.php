<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Desafío 18: Dashboard Responsivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        :root { --accent:#00ff9d; --purple:#b026ff; --blue:#2575fc; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:#fff; font-family:Roboto,sans-serif; background:url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') center/cover fixed no-repeat; }
        body::before { content:''; position:fixed; inset:0; background:rgba(0,0,0,.72); z-index:-1; }
        .navbar-custom { background:rgba(0,0,0,.86); border-bottom:3px solid var(--accent); }
        .game-title { color:var(--accent); font:1.15rem 'Press Start 2P',cursive; }
        .btn-home,.player-name { color:#fff; border:2px solid #fff; border-radius:999px; padding:.5rem 1rem; text-decoration:none; background:rgba(255,255,255,.08); }
        .challenge-container { max-width:1300px; margin:2rem auto; padding:1.5rem; border-left:5px solid var(--purple); border-radius:18px; background:rgba(0,0,0,.78); box-shadow:0 0 28px rgba(176,38,255,.18); }
        .challenge-header { border-bottom:2px solid var(--purple); padding-bottom:1rem; }
        .challenge-title { color:var(--accent); font:1.25rem 'Press Start 2P',cursive; }
        .badge-level { background:var(--purple); padding:.4rem .75rem; border-radius:8px; }
        .hint-box { padding:1rem; margin:1rem 0; border-left:4px solid #ffcc00; border-radius:0 10px 10px 0; background:rgba(255,204,0,.13); }
        .objectives-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:.65rem; padding:0; list-style:none; }
        .objectives-list li { padding:.75rem; border-radius:12px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.16); }
        .objectives-list i { color:var(--accent); margin-right:.35rem; }
        .reference-card { margin:1rem 0 1.5rem; padding:1rem; border:2px solid rgba(0,255,157,.45); border-radius:16px; background:rgba(15,23,42,.78); }
        .reference-card h2 { color:var(--accent); font-size:1.1rem; }
        .reference-card .mini-dashboard { display:grid; grid-template-columns:150px 1fr; gap:.75rem; min-height:150px; padding:.75rem; border-radius:12px; background:#111a30; }
        .mini-dashboard aside { border-radius:8px; padding:.7rem; background:#26375a; color:#9fffd5; }
        .mini-dashboard main { display:grid; gap:.5rem; grid-template-columns:repeat(3,1fr); }
        .mini-dashboard header { grid-column:1/-1; padding:.6rem; border-radius:8px; background:#405b91; }
        .mini-dashboard .mini-card { padding:.7rem; border-radius:8px; background:#20365b; }
        .editor-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .editor-panel,.preview-panel { min-height:510px; border-radius:12px; overflow:hidden; }
        .CodeMirror { height:510px !important; border:3px solid var(--accent); border-radius:12px; font-size:14px; }
        .preview-panel { background:#fff; border:3px solid var(--accent); }
        .preview-panel iframe { width:100%; height:100%; min-height:504px; border:0; background:#fff; }
        .validation-panel { margin-top:1rem; padding:1rem; border-radius:14px; background:rgba(15,23,42,.92); border:1px solid rgba(255,255,255,.16); }
        .validation-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.5rem; }
        .validation-item { padding:.55rem .7rem; border-radius:10px; background:rgba(255,255,255,.08); }
        .validation-item.ok { color:#86efac; } .validation-item.missing { color:#fca5a5; }
        .action-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.75rem; margin:1.2rem 0; padding:.85rem; border:2px solid rgba(0,255,157,.5); border-radius:16px; background:rgba(2,6,23,.94); }
        .btn-run,.btn-submit { border:0; border-radius:999px; padding:.75rem 1.35rem; font-weight:700; }
        .btn-run { color:#fff; background:linear-gradient(45deg,#6a11cb,var(--blue)); }
        .btn-submit { color:#07131f; background:linear-gradient(45deg,#16a34a,var(--accent)); }
        .feedback { display:none; margin-top:1rem; padding:1rem; border-radius:10px; }
        .feedback.show { display:block; } .feedback.success { background:rgba(40,167,69,.2); border-left:4px solid #28a745; } .feedback.error { background:rgba(220,53,69,.2); border-left:4px solid #dc3545; }
        @media (max-width:800px) { .editor-grid { grid-template-columns:1fr; } .game-title { font-size:.8rem; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom py-3"><div class="container-fluid d-flex align-items-center justify-content-between gap-2">
        <a class="btn-home" href="{{ route('alumno.desafios.programacion') }}"><i class="fas fa-arrow-left me-2"></i>Volver</a>
        <div class="game-title">CODEPLAY</div><div class="player-name"><i class="fas fa-user me-2"></i>{{ session('usuario_nombre', 'Jugador') }}</div>
    </div></nav>
    <main class="container-fluid px-3 px-lg-5"><section class="challenge-container">
        <header class="challenge-header"><h1 class="challenge-title">Desafío 18: Dashboard Responsivo</h1>
            <p class="text-white-50 mb-2">Diseña un panel de control adaptable con información, tarjetas y una interacción.</p>
            <span class="badge-level">NIVEL 18</span><span class="badge bg-success ms-2">{{ $infoRecompensa['puntos_base'] ?? 400 }} puntos</span>
        </header>
        <div class="hint-box"><strong>Piensa como diseñador:</strong> organiza la información con un menú lateral, un encabezado y tarjetas que sigan siendo legibles en una pantalla pequeña.</div>
        <div class="reference-card"><h2><i class="fas fa-chart-line me-2"></i>Referencia visual</h2><div class="mini-dashboard"><aside>Menú<br><small>Inicio<br>Reportes</small></aside><main><header>Panel de control</header><div class="mini-card">Usuarios<br><b>128</b></div><div class="mini-card">Ventas<br><b>64</b></div><div class="mini-card">Meta<br><b>82%</b></div></main></div><p class="text-white-50 mt-2 mb-0">Puedes cambiar colores y contenido. La referencia solo muestra la idea de distribución esperada.</p></div>
        <h2 class="h4 mt-4"><i class="fas fa-bullseye me-2"></i>Objetivos del proyecto</h2>
        <ul class="objectives-list mt-3"><li><i class="fas fa-circle-check"></i>Usa <code>&lt;header&gt;</code>, <code>&lt;aside&gt;</code> o <code>&lt;nav&gt;</code> y una zona <code>&lt;main&gt;</code> o <code>&lt;section&gt;</code>.</li><li><i class="fas fa-circle-check"></i>Crea al menos tres tarjetas con clases <code>card</code>, <code>stat</code>, <code>metric</code> o <code>tarjeta</code>.</li><li><i class="fas fa-circle-check"></i>Organiza el panel con <code>display: grid</code> o <code>display: flex</code>.</li><li><i class="fas fa-circle-check"></i>Incluye adaptación con <code>@media</code>, columnas responsivas o <code>flex-wrap</code>.</li><li><i class="fas fa-circle-check"></i>Aplica fondo y <code>border-radius</code> para darle identidad visual.</li><li><i class="fas fa-circle-check"></i>Añade un botón y conéctalo a un evento usando <code>addEventListener</code>.</li><li><i class="fas fa-circle-check"></i>Modifica un texto de la vista con <code>textContent</code>.</li></ul>
        <div class="editor-grid mt-4"><div class="editor-panel"><textarea id="codeEditor"><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Mi dashboard</title><style>
body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
/* Crea aquí el layout, el fondo y tus tarjetas */
</style></head><body>
<header><h1>Mi panel</h1></header>
<!-- Agrega un menú, una zona principal y al menos tres tarjetas -->
<button id="actualizar">Actualizar panel</button>
<script>
// Conecta el botón y cambia un texto con textContent.
</script></body></html></textarea></div><div class="preview-panel"><iframe id="previewFrame" title="Vista previa del dashboard" sandbox="allow-scripts allow-modals"></iframe></div></div>
        <section class="validation-panel"><h2 class="h5"><i class="fas fa-list-check me-2"></i>Progreso del desafío</h2><div id="validationGrid" class="validation-grid"></div></section>
        <div class="action-toolbar"><button id="runButton" class="btn-run"><i class="fas fa-play me-2"></i>Ejecutar código</button><button id="submitButton" class="btn-submit"><i class="fas fa-check me-2"></i>Entregar solución</button><span class="text-white-50">Ejecuta la vista y entrega cuando todos los objetivos estén completos.</span></div><div id="feedback" class="feedback"></div>
    </section></main>
    <script>
        const editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), { mode:'htmlmixed', theme:'dracula', lineNumbers:true, lineWrapping:true, indentUnit:4, tabSize:4, autoCloseTags:true });
        const previewFrame = document.getElementById('previewFrame'), validationGrid = document.getElementById('validationGrid'), feedback = document.getElementById('feedback');
        const labels = { structure:'Estructura del dashboard', cards:'Tres tarjetas', layout:'Grid o Flexbox', responsive:'Diseño adaptable', style:'Fondo y bordes', interaction:'Botón con evento', dynamic:'Texto dinámico' };
        function evaluateCode(code) { const lower=code.toLowerCase(), compact=lower.replace(/\s+/g,''); const cards=(code.match(/class\s*=\s*["'][^"']*\b(card|stat|metric|tarjeta)\b[^"']*["']/gi)||[]).length; return { structure:(lower.includes('<main')||lower.includes('<section'))&&(lower.includes('<aside')||lower.includes('<nav'))&&lower.includes('<header'), cards:cards>=3, layout:compact.includes('display:grid')||compact.includes('display:flex'), responsive:lower.includes('@media')||lower.includes('grid-template-columns')||lower.includes('flex-wrap'), style:(lower.includes('background:')||lower.includes('background-color'))&&lower.includes('border-radius'), interaction:lower.includes('<button')&&lower.includes('addeventlistener'), dynamic:lower.includes('textcontent') }; }
        function renderValidation(showFeedback=false) { const checks=evaluateCode(editor.getValue()); validationGrid.innerHTML=Object.entries(checks).map(([key,ok])=>`<div class="validation-item ${ok?'ok':'missing'}"><i class="fas ${ok?'fa-check':'fa-circle'} me-2"></i>${labels[key]}</div>`).join(''); const count=Object.values(checks).filter(Boolean).length; if(showFeedback&&!Object.values(checks).every(Boolean)){ feedback.className='feedback error show'; feedback.textContent=`Aún faltan objetivos (${count}/${Object.keys(checks).length}). Revisa los elementos marcados.`; } return Object.values(checks).every(Boolean); }
        function runCode(){ previewFrame.srcdoc=editor.getValue(); renderValidation(false); }
        async function submitSolution(){ runCode(); if(!renderValidation(true)) return; try { const response=await fetch('{{ route("desafios.procesar-recompensa") }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({nivel:18,codigo:editor.getValue()})}); const data=await response.json(); if(!response.ok||!data.success){feedback.className='feedback error show';feedback.textContent=data.message||data.error||'No se pudo validar la solución.';return;} feedback.className='feedback success show';feedback.textContent=`¡Desafío completado! Ganaste ${data.puntos_otorgados} puntos.`; if(typeof confetti==='function') confetti({particleCount:160,spread:85,origin:{y:.6}}); } catch(e){ feedback.className='feedback error show'; feedback.textContent='No se pudo registrar la recompensa. Intenta de nuevo.'; } }
        document.getElementById('runButton').addEventListener('click',runCode); document.getElementById('submitButton').addEventListener('click',submitSolution); window.addEventListener('load',()=>{runCode();const info=@json($infoRecompensa);if(info.ya_completado&&!info.puede_reclamar){feedback.className='feedback show';feedback.textContent=`Ya completaste este desafío. Podrás reclamar nuevamente ${info.tiempo_restante}.`;}});
    </script>
</body></html>
