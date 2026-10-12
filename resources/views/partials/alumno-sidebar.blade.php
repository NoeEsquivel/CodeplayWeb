@if(session('usuario_rol') === 'Alumno')
@php
    $rutaActual = request()->route()?->getName() ?? '';
    $enTests = str_starts_with($rutaActual, 'alumno.tests') || str_starts_with($rutaActual, 'alumno.bancos') || str_starts_with($rutaActual, 'alumno.codigo-amigo');
    $enDocumentacion = str_starts_with($rutaActual, 'alumno.documentacion');
@endphp
<style>
    :root{--student-nav-width:236px;--student-nav-compact:72px;--student-nav-current:var(--student-nav-width);--student-mint:#00efa4;--student-gold:#ffd157;--student-ink:#031116}
    body.student-sidebar-enabled{padding-left:var(--student-nav-current)!important;transition:padding-left .22s ease}body.student-sidebar-collapsed{--student-nav-current:var(--student-nav-compact)}
    .student-sidebar{position:fixed;inset:0 auto 0 0;z-index:2140;width:var(--student-nav-width);display:flex;flex-direction:column;padding:18px 13px 14px;border-right:1px solid #23e9b56b;background:linear-gradient(180deg,#03151ced,#061f27f5 55%,#031218fa);box-shadow:18px 0 45px #0008;backdrop-filter:blur(18px);color:#edfff9;font-family:"Trebuchet MS","Segoe UI",sans-serif}
    .student-sidebar *{box-sizing:border-box}.student-nav-brand{display:flex;align-items:center;gap:10px;padding:5px 7px 17px;color:#fff;text-decoration:none;border-bottom:1px solid #315159}.student-nav-brand:hover{color:#fff}.student-nav-logo{flex:0 0 38px;width:38px;height:38px;display:grid;place-items:center;border:1px solid var(--student-mint);border-radius:11px;background:#06251f;color:var(--student-mint);font:900 16px Consolas,monospace}.student-nav-brand strong{font-size:18px;letter-spacing:.08em}.student-nav-brand strong span{color:var(--student-mint)}.student-nav-brand small{display:block;margin-top:2px;color:#7ba29f;font-size:9px;letter-spacing:.16em;text-transform:uppercase}.student-nav-collapse{position:absolute;right:-14px;top:72px;z-index:2;width:29px;height:29px;display:grid;place-items:center;border:1px solid #43dcb5;border-radius:50%;background:#08252d;color:#8dffe0;box-shadow:0 5px 18px #0009;cursor:pointer;transition:transform .22s,background .2s}.student-nav-collapse:hover{background:#125043}.student-nav-collapse i{transition:transform .22s}
    .student-nav-profile{display:grid;grid-template-columns:36px 1fr auto;align-items:center;gap:9px;margin:14px 2px 10px;padding:10px;border:1px solid #315861;border-radius:12px;background:#0a2931}.student-nav-avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#00efa417;color:var(--student-mint)}.student-nav-profile strong{display:block;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px}.student-nav-profile small{color:#95b3b0;font-size:10px}.student-nav-points{color:var(--student-gold);font-size:11px;font-weight:900;white-space:nowrap}
    .student-nav-scroll{flex:1;overflow-y:auto;padding:2px 1px 10px;scrollbar-width:thin;scrollbar-color:#27705e transparent}.student-nav-label{margin:13px 8px 6px;color:#698f8d;font-size:9px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.student-nav-link,.student-nav-group>summary{display:flex;align-items:center;gap:10px;width:100%;min-height:42px;padding:9px 11px;border:1px solid transparent;border-radius:10px;color:#cde1dd;text-decoration:none;font-size:12px;font-weight:800;cursor:pointer;transition:.17s}.student-nav-link i,.student-nav-group>summary i{width:18px;color:#6bdabb;text-align:center}.student-nav-link:hover,.student-nav-link:focus-visible,.student-nav-link.active{border-color:#2e7664;background:#104034;color:#7dffda;outline:0}.student-nav-link.active i{color:var(--student-mint)}
    .student-nav-group{margin:2px 0}.student-nav-group>summary{list-style:none}.student-nav-group>summary::-webkit-details-marker{display:none}.student-nav-group>summary:after{content:'+';margin-left:auto;color:#739591;font-size:17px}.student-nav-group[open]>summary:after{content:'−'}.student-nav-group.active>summary{color:#7dffda;background:#0c312a}.student-nav-sub{display:grid;gap:2px;margin:2px 0 5px;padding-left:13px;border-left:1px solid #28594d}.student-nav-sub .student-nav-link{min-height:35px;padding:7px 9px;font-size:11px}
    .student-nav-bottom{padding-top:10px;border-top:1px solid #315159}.student-nav-logout{width:100%;border:1px solid #5f454b;background:#26171c;color:#ffbec6}.student-nav-logout:hover{border-color:#dc6475;background:#422029;color:#fff}.student-nav-toggle{display:none;position:fixed;left:12px;top:12px;z-index:2160;width:44px;height:44px;border:1px solid var(--student-mint);border-radius:11px;background:#061a21ee;color:var(--student-mint);box-shadow:0 8px 24px #0008}.student-nav-overlay{display:none;position:fixed;inset:0;z-index:2130;background:#01080bc7;backdrop-filter:blur(3px)}
    body.student-sidebar-enabled .navbar-custom.fixed-top{left:var(--student-nav-current)}.student-motivation{left:calc(50% + var(--student-nav-current)/2)!important;transition:left .22s ease}
    body.student-documentation-view .navbar-custom>.container,body.student-documentation-view .navbar-custom>.container-fluid{position:relative;min-height:48px}body.student-documentation-view .navbar-custom .game-title{position:absolute!important;top:50%;left:50%;margin:0!important;transform:translate(-50%,-50%);white-space:nowrap}body.student-documentation-view .navbar-custom .player-name-display,body.student-documentation-view .navbar-custom .ms-auto{display:none!important}body.student-documentation-view .navbar-custom .btn-home{position:relative;z-index:1;display:inline-grid;place-items:center;min-width:44px;min-height:44px;padding:8px!important;margin-right:8px!important}body.student-documentation-view .navbar-custom .btn-home i{margin:0!important}
    body.student-sidebar-collapsed .student-sidebar{width:var(--student-nav-compact);padding-inline:9px}body.student-sidebar-collapsed .student-nav-brand{justify-content:center;padding-inline:0}body.student-sidebar-collapsed .student-nav-brand>span:last-child,body.student-sidebar-collapsed .student-nav-profile>span:not(.student-nav-avatar),body.student-sidebar-collapsed .student-nav-label,body.student-sidebar-collapsed .student-nav-text{display:none}body.student-sidebar-collapsed .student-nav-profile{display:flex;justify-content:center;padding:8px 0}body.student-sidebar-collapsed .student-nav-link,body.student-sidebar-collapsed .student-nav-group>summary{justify-content:center;padding-inline:7px}body.student-sidebar-collapsed .student-nav-link i,body.student-sidebar-collapsed .student-nav-group>summary i{width:auto;font-size:16px}body.student-sidebar-collapsed .student-nav-group>summary:after,body.student-sidebar-collapsed .student-nav-sub{display:none}body.student-sidebar-collapsed .student-nav-collapse i{transform:rotate(180deg)}
    @media(max-width:880px){:root{--student-nav-current:0px!important}body.student-sidebar-enabled{padding-left:0!important;padding-top:62px!important}.student-sidebar,body.student-sidebar-collapsed .student-sidebar{width:var(--student-nav-width);padding:18px 13px 14px;transform:translateX(-105%);transition:transform .22s}.student-nav-toggle{display:grid;place-items:center}.student-nav-collapse{display:none}.student-sidebar-open .student-sidebar{transform:translateX(0)}.student-sidebar-open .student-nav-overlay{display:block}.student-sidebar-open{overflow:hidden}body.student-sidebar-collapsed .student-nav-brand{justify-content:flex-start;padding:5px 7px 17px}body.student-sidebar-collapsed .student-nav-brand>span:last-child,body.student-sidebar-collapsed .student-nav-profile>span:not(.student-nav-avatar),body.student-sidebar-collapsed .student-nav-label,body.student-sidebar-collapsed .student-nav-text{display:initial}body.student-sidebar-collapsed .student-nav-profile{display:grid;justify-content:initial;padding:10px}body.student-sidebar-collapsed .student-nav-link,body.student-sidebar-collapsed .student-nav-group>summary{justify-content:flex-start;padding:9px 11px}body.student-sidebar-collapsed .student-nav-link i,body.student-sidebar-collapsed .student-nav-group>summary i{width:18px;font-size:inherit}body.student-sidebar-collapsed .student-nav-group>summary:after{display:block}body.student-sidebar-collapsed .student-nav-sub{display:grid}body.student-sidebar-enabled .navbar-custom.fixed-top{left:0}.student-motivation{left:50%!important}}
    @media print{.student-sidebar,.student-nav-toggle,.student-nav-overlay{display:none!important}body.student-sidebar-enabled{padding-left:0!important;padding-top:0!important}}
</style>
<button class="student-nav-toggle" id="student-nav-toggle" type="button" aria-label="Abrir navegación" aria-controls="student-sidebar" aria-expanded="false"><i class="fas fa-bars"></i></button>
<div class="student-nav-overlay" id="student-nav-overlay" aria-hidden="true"></div>
<aside class="student-sidebar" id="student-sidebar" aria-label="Navegación del alumno">
    <a class="student-nav-brand" href="{{ route('inicio.alumno') }}"><span class="student-nav-logo">&lt;/&gt;</span><span><strong>CODE<span>PLAY</span></strong><small>Panel del alumno</small></span></a>
    <button class="student-nav-collapse" id="student-nav-collapse" type="button" aria-label="Replegar navegación" aria-expanded="true" title="Replegar navegación"><i class="fas fa-chevron-left"></i></button>
    <div class="student-nav-profile"><span class="student-nav-avatar"><i class="fas fa-user-graduate"></i></span><span><strong title="{{ session('usuario_nombre') }}">{{ session('usuario_nombre') }}</strong><small>Alumno</small></span><span class="student-nav-points"><i class="fas fa-star"></i> <span id="studentNavPointsValue">{{ session('puntos_disponibles', 0) }}</span></span></div>
    <nav class="student-nav-scroll">
        <p class="student-nav-label">Navegación</p>
        <a class="student-nav-link @if(request()->routeIs('inicio.alumno')) active @endif" href="{{ route('inicio.alumno') }}" title="Inicio"><i class="fas fa-house"></i><span class="student-nav-text">Inicio</span></a>
        <a class="student-nav-link @if(request()->routeIs('alumno.documentacion*')) active @endif" href="{{ route('alumno.documentacion') }}" title="Documentación"><i class="fas fa-book-open"></i><span class="student-nav-text">Documentación</span></a>
        <a class="student-nav-link @if(request()->routeIs('alumno.tutoriales*')) active @endif" href="{{ route('alumno.tutoriales') }}" title="Tutoriales"><i class="fas fa-graduation-cap"></i><span class="student-nav-text">Tutoriales</span></a>
        <a class="student-nav-link @if(request()->routeIs('alumno.desafios*')) active @endif" href="{{ route('alumno.desafios') }}" title="Desafíos"><i class="fas fa-gamepad"></i><span class="student-nav-text">Desafíos</span></a>
        <details class="student-nav-group @if($enTests) active @endif" @if($enTests) open @endif>
            <summary title="Tests y evaluaciones"><i class="fas fa-clipboard-check"></i><span class="student-nav-text">Tests y evaluaciones</span></summary>
            <div class="student-nav-sub">
                <a class="student-nav-link @if(request()->routeIs('alumno.tests')) active @endif" href="{{ route('alumno.tests') }}"><span class="student-nav-text">Centro de tests</span></a>
                <a class="student-nav-link @if(request()->routeIs('alumno.tests.clase')) active @endif" href="{{ route('alumno.tests.clase') }}"><span class="student-nav-text">Actividades de clase</span></a>
                <a class="student-nav-link @if(request()->routeIs('alumno.bancos.*')) active @endif" href="{{ route('alumno.bancos.index') }}"><span class="student-nav-text">Mis bancos</span></a>
                <a class="student-nav-link @if(request()->routeIs('alumno.codigo-amigo.*')) active @endif" href="{{ route('alumno.codigo-amigo.index') }}"><span class="student-nav-text">Código amigo</span></a>
                <a class="student-nav-link @if(request()->routeIs('alumno.tests.amigos.index')) active @endif" href="{{ route('alumno.tests.amigos.index') }}"><span class="student-nav-text">Test con amigos</span></a>
            </div>
        </details>
        <a class="student-nav-link @if(request()->routeIs('alumno.progreso')) active @endif" href="{{ route('alumno.progreso') }}" title="Mi progreso"><i class="fas fa-chart-line"></i><span class="student-nav-text">Mi progreso</span></a>
        <p class="student-nav-label">Modo inmersivo</p>
        <a class="student-nav-link" href="{{ route('alumno.aventura') }}" title="Aventura"><i class="fas fa-dragon"></i><span class="student-nav-text">Aventura</span></a>
    </nav>
    <div class="student-nav-bottom"><form action="{{ route('logout') }}" method="POST">@csrf<button class="student-nav-link student-nav-logout" type="submit" title="Cerrar sesión"><i class="fas fa-right-from-bracket"></i><span class="student-nav-text">Cerrar sesión</span></button></form></div>
</aside>
<script>
(()=>{const body=document.body,nav=document.getElementById('student-sidebar'),toggle=document.getElementById('student-nav-toggle'),collapse=document.getElementById('student-nav-collapse'),overlay=document.getElementById('student-nav-overlay');body.classList.add('student-sidebar-enabled');const setOpen=open=>{body.classList.toggle('student-sidebar-open',open);toggle?.setAttribute('aria-expanded',open?'true':'false');overlay?.setAttribute('aria-hidden',open?'false':'true')};const setCollapsed=collapsed=>{body.classList.toggle('student-sidebar-collapsed',collapsed);collapse?.setAttribute('aria-label',collapsed?'Desplegar navegación':'Replegar navegación');collapse?.setAttribute('aria-expanded',collapsed?'false':'true');collapse?.setAttribute('title',collapsed?'Desplegar navegación':'Replegar navegación');try{localStorage.setItem('codeplay.studentNavCollapsed',collapsed?'1':'0')}catch(error){}};try{setCollapsed(localStorage.getItem('codeplay.studentNavCollapsed')==='1')}catch(error){setCollapsed(false)}toggle?.addEventListener('click',()=>setOpen(!body.classList.contains('student-sidebar-open')));collapse?.addEventListener('click',()=>setCollapsed(!body.classList.contains('student-sidebar-collapsed')));overlay?.addEventListener('click',()=>setOpen(false));nav?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setOpen(false)));document.addEventListener('keydown',event=>{if(event.key==='Escape')setOpen(false)})})();
</script>
@if($enDocumentacion)
<script>
(()=>{
    document.body.classList.add('student-documentation-view');
    const claveRetorno='codeplay.documentacion.regresando';
    const marcarRetorno=()=>{try{sessionStorage.setItem(claveRetorno,'1')}catch(error){}};
    const hayRetornoMarcado=()=>{try{return sessionStorage.getItem(claveRetorno)==='1'}catch(error){return false}};
    const limpiarRetorno=()=>{
        const overlay=document.getElementById('loadingOverlay');
        if(overlay){
            overlay.style.opacity='0';
            overlay.style.pointerEvents='none';
        }
        const animacion=document.getElementById('bookAnimation');
        const contenido=document.getElementById('docContent');
        if(animacion)animacion.style.display='none';
        if(contenido)contenido.style.display='block';
        try{sessionStorage.removeItem(claveRetorno)}catch(error){}
    };
    const prepararEncabezado=()=>{
        document.querySelectorAll('.navbar-custom .btn-home').forEach(boton=>{
            boton.removeAttribute('onclick');
            boton.setAttribute('href','#');
            boton.setAttribute('aria-label','Regresar a la pantalla anterior');
            boton.setAttribute('title','Regresar');
            boton.innerHTML='<i class="fas fa-arrow-left" aria-hidden="true"></i>';
            boton.addEventListener('click',evento=>{
                evento.preventDefault();
                if(window.history.length>1){
                    marcarRetorno();
                    window.history.back();
                    return;
                }
                window.location.href=@json($rutaActual === 'alumno.documentacion' ? route('inicio.alumno') : route('alumno.documentacion'));
            });
        });
    };
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',prepararEncabezado,{once:true});else prepararEncabezado();
    window.addEventListener('pageshow',evento=>{
        if(evento.persisted||hayRetornoMarcado())limpiarRetorno();
    });
})();
</script>
@endif
@endif
