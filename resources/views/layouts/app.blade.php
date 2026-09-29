<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CodePlay - Aprender Programación Jugando')</title>

    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <style>
        :root{--primary-color:#6a11cb;--secondary-color:#2575fc;--codeplay-mint:#00efa4;--codeplay-gold:#f2c867;--codeplay-ink:#06161d}
        body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,#6a11cb 0%,#2575fc 100%);min-height:100vh}
        .navbar-brand{font-family:'Press Start 2P',cursive;color:var(--codeplay-mint)!important;text-shadow:2px 2px 0 #000}
        .game-card{background:rgba(255,255,255,.1);backdrop-filter:blur(10px);border:2px solid var(--codeplay-mint);border-radius:15px;transition:all .3s}
        .game-card:hover{box-shadow:0 10px 25px rgba(0,255,157,.3)}
        .codeplay-navbar{position:relative;z-index:1200;overflow:visible}.codeplay-navbar .container,.codeplay-navbar .navbar-nav{overflow:visible}.account-dropdown{position:relative;z-index:1210}
        .account-dropdown summary{list-style:none;display:flex;align-items:center;gap:10px;min-height:46px;padding:7px 12px;border:1px solid #557078;border-radius:12px;background:#10252dcc;color:#fff;cursor:pointer;transition:border-color .2s,background .2s,transform .2s}
        .account-dropdown summary::-webkit-details-marker{display:none}
        .account-dropdown summary:hover,.account-dropdown[open] summary{border-color:var(--codeplay-mint);background:#16343d;transform:translateY(-1px)}
        .account-avatar{width:31px;height:31px;display:grid;place-items:center;border-radius:9px;background:#00efa41c;color:var(--codeplay-mint)}
        .account-copy{display:grid;text-align:left;line-height:1.05}.account-copy strong{font-size:16px}.account-copy small{margin-top:4px;color:var(--codeplay-gold);font-size:14px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .account-chevron{margin-left:3px;color:#8ea8ad;font-size:14px;transition:transform .2s}.account-dropdown[open] .account-chevron{transform:rotate(180deg)}
        .account-menu{position:absolute;right:0;top:calc(100% + 9px);z-index:1080;width:235px;padding:8px;border:1px solid #547078;border-radius:13px;background:#081a21f7;box-shadow:0 18px 45px #0009;backdrop-filter:blur(16px)}
        .account-menu a,.account-menu button{display:flex;align-items:center;gap:10px;width:100%;padding:11px 12px;border:0;border-radius:9px;background:transparent;color:#eafff8;text-decoration:none;text-align:left;font-size:15px;transition:background .2s,color .2s}
        .account-menu a:hover,.account-menu button:hover{background:#173139;color:var(--codeplay-mint)}.account-menu .logout-action:hover{background:#4a2226;color:#ffbdc0}
        .teacher-nav-brand{display:flex;align-items:center;gap:10px}.teacher-nav-toggle{width:44px;height:44px;display:grid;place-items:center;border:1px solid #527078;border-radius:11px;background:#10272f;color:#eafff8;font-size:19px;transition:.2s}.teacher-nav-toggle:hover,.teacher-nav-toggle:focus-visible{border-color:var(--codeplay-mint);background:#173840;color:var(--codeplay-mint);outline:0}.teacher-nav-overlay{position:fixed;inset:0;z-index:1280;background:#01070bbd;opacity:0;visibility:hidden;transition:opacity .22s,visibility .22s}.teacher-nav-drawer{position:fixed;left:0;top:0;bottom:0;z-index:1290;width:min(365px,92vw);padding:20px;background:linear-gradient(155deg,#071a22,#0c242c);border-right:1px solid #5ba98f80;box-shadow:22px 0 60px #000a;transform:translateX(-105%);transition:transform .25s;overflow-y:auto;color:#effbf8}.teacher-nav-open .teacher-nav-overlay{opacity:1;visibility:visible}.teacher-nav-open .teacher-nav-drawer{transform:translateX(0)}.teacher-nav-open{overflow:hidden}.teacher-drawer-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding-bottom:17px;margin-bottom:18px;border-bottom:1px solid #3d5d64}.teacher-drawer-head strong{font-family:Georgia,serif;font-size:24px;font-weight:500}.teacher-drawer-close{width:42px;height:42px;border:1px solid #4c686f;border-radius:10px;background:#102a32;color:#fff;font-size:18px}.teacher-drawer-group{margin:13px 0;border:1px solid #36565d;border-radius:12px;background:#0a2028}.teacher-drawer-group>summary{display:flex;align-items:center;gap:11px;padding:13px 14px;list-style:none;cursor:pointer;color:#f2c867;font-size:15px;font-weight:900;letter-spacing:.04em}.teacher-drawer-group>summary::-webkit-details-marker{display:none}.teacher-drawer-group>summary::after{content:'+';margin-left:auto;font-size:20px}.teacher-drawer-group[open]>summary::after{content:'−'}.teacher-drawer-links{display:grid;gap:4px;padding:0 8px 9px}.teacher-drawer-links a{display:flex;align-items:center;gap:11px;min-height:45px;padding:10px 12px;border-radius:9px;color:#dbeae7;text-decoration:none;font-size:15px;font-weight:700}.teacher-drawer-links a:hover,.teacher-drawer-links a:focus-visible{background:#174039;color:#7af0c8;outline:0}.teacher-drawer-links a.is-primary{background:#00efa414;color:#78e9c5}.teacher-drawer-links i{width:20px;text-align:center;color:#67dfb9}.teacher-drawer-foot{margin-top:18px;padding-top:17px;border-top:1px solid #3d5d64;color:#9eb3b5;font-size:14px}
        @media(max-width:575px){.account-copy small{display:none}.account-menu{right:-2px}.navbar-brand{font-size:15px}.teacher-nav-brand{gap:6px}.teacher-nav-toggle{width:41px;height:41px}}
        @media print{.teacher-nav-drawer,.teacher-nav-overlay,.teacher-nav-toggle{display:none!important}}
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark codeplay-navbar">
        <div class="container">
            @php
                $brandRoute = match(session('usuario_rol')) {
                    'Alumno' => 'inicio.alumno',
                    'Profesor' => 'inicio.profesor',
                    'AdministradorColegio' => 'inicio.administrador-colegio',
                    'Master' => 'inicio.master',
                    default => 'home',
                };
                $hasRoleDrawer = in_array(session('usuario_rol'), ['Profesor', 'AdministradorColegio', 'Master'], true);
                $isSchoolAdmin = session('usuario_rol') === 'AdministradorColegio';
                $isMaster = session('usuario_rol') === 'Master';
                $drawerTitle = $isMaster ? 'Abrir consola Master' : ($isSchoolAdmin ? 'Abrir navegación del Administrador de Colegio' : 'Abrir navegación del Profesor');
            @endphp
            @if($hasRoleDrawer)
                <div class="teacher-nav-brand">
                    <button class="teacher-nav-toggle" type="button" id="teacher-nav-toggle" aria-controls="teacher-nav-drawer" aria-expanded="false" title="{{ $drawerTitle }}"><i class="fas {{ $isMaster ? 'fa-terminal' : 'fa-bars' }}"></i></button>
                    <a class="navbar-brand" href="{{ route($brandRoute) }}" title="Volver al menú principal"><i class="fas fa-gamepad me-2"></i>CODEPLAY</a>
                </div>
            @else
                <a class="navbar-brand" href="{{ route($brandRoute) }}" title="Volver al menú principal"><i class="fas fa-gamepad me-2"></i>CODEPLAY</a>
            @endif

            <div class="navbar-nav ms-auto align-items-center">
                @if($hasRoleDrawer)
                    <details class="account-dropdown" id="teacher-account-menu">
                        <summary aria-label="Abrir opciones de la cuenta">
                            <span class="account-avatar"><i class="fas {{ $isMaster ? 'fa-crown' : ($isSchoolAdmin ? 'fa-user-shield' : 'fa-user-tie') }}"></i></span>
                            <span class="account-copy"><strong>{{ session('usuario_nombre') }}</strong><small>{{ $isMaster ? 'Master' : ($isSchoolAdmin ? 'Administrador de Colegio' : 'Profesor') }}</small></span>
                            <i class="fas fa-chevron-down account-chevron"></i>
                        </summary>
                        <div class="account-menu">
                            <a href="{{ route('cuenta.clave.editar') }}"><i class="fas fa-key"></i><span>Actualizar contraseña</span></a>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">@csrf
                                <button type="submit" class="logout-action"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></button>
                            </form>
                        </div>
                    </details>
                @else
                    <span class="navbar-text text-light me-3"><i class="fas fa-user me-1"></i>{{ session('usuario_nombre') }}<span class="badge bg-warning ms-2">{{ session('usuario_rol') }}</span></span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf
                        <button type="submit" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Salir</button>
                    </form>
                @endif
            </div>
        </div>
    </nav>

    @if(in_array(session('usuario_rol'), ['Profesor', 'AdministradorColegio', 'Master'], true))
        <div class="teacher-nav-overlay" id="teacher-nav-overlay" aria-hidden="true"></div>
        <aside class="teacher-nav-drawer" id="teacher-nav-drawer" aria-label="Navegación de {{ session('usuario_rol') }}" aria-hidden="true">
            <div class="teacher-drawer-head"><div><small class="text-warning d-block">CODEPLAY // ROOT</small><strong>{{ $isMaster ? 'Consola Master' : ($isSchoolAdmin ? 'Menú del Colegio' : 'Menú del Profesor') }}</strong></div><button class="teacher-drawer-close" type="button" id="teacher-nav-close" aria-label="Cerrar menú"><i class="fas fa-times"></i></button></div>
            @if($isMaster)
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-satellite-dish"></i>Control del sistema</summary>
                <div class="teacher-drawer-links">
                    <a class="is-primary" href="{{ route('inicio.master') }}#dashboard"><i class="fas fa-gauge-high"></i>Telemetría global</a>
                    <a href="{{ route('inicio.master') }}#colegios"><i class="fas fa-building-shield"></i>Colegios</a>
                    <a href="{{ route('inicio.master') }}#profesores"><i class="fas fa-chalkboard-user"></i>Profesores</a>
                    <a href="{{ route('inicio.master') }}#administradores-colegio"><i class="fas fa-user-shield"></i>Administradores</a>
                    <a href="{{ route('inicio.master') }}#alumnos"><i class="fas fa-user-graduate"></i>Alumnos</a>
                </div>
            </details>
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-network-wired"></i>Observabilidad</summary>
                <div class="teacher-drawer-links">
                    <a href="{{ route('inicio.master') }}#jerarquia"><i class="fas fa-sitemap"></i>Jerarquías e impresión</a>
                    <a href="{{ route('inicio.master') }}#auditoria"><i class="fas fa-shield-halved"></i>Auditoría</a>
                </div>
            </details>
            <p class="teacher-drawer-foot">Acceso root a la configuración multiinstitucional de CodePlay.</p>
            @elseif($isSchoolAdmin)
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-school"></i>Administración institucional</summary>
                <div class="teacher-drawer-links">
                    <a class="is-primary" href="{{ route('inicio.administrador-colegio') }}#profesores"><i class="fas fa-chalkboard-teacher"></i>Gestión de profesores</a>
                    <a href="{{ route('inicio.administrador-colegio') }}#alumnos"><i class="fas fa-user-graduate"></i>Gestión de alumnos</a>
                    <a href="{{ route('inicio.administrador-colegio') }}#clases"><i class="fas fa-users-rectangle"></i>Clases y asignaciones</a>
                </div>
            </details>
            <details class="teacher-drawer-group">
                <summary><i class="fas fa-user-gear"></i>Mi cuenta</summary>
                <div class="teacher-drawer-links"><a href="{{ route('cuenta.clave.editar') }}"><i class="fas fa-key"></i>Actualizar contraseña</a></div>
            </details>
            <p class="teacher-drawer-foot">Accesos directos para administrar únicamente los usuarios y las clases de tu colegio.</p>
            @else
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-chalkboard-teacher"></i>Gestión académica</summary>
                <div class="teacher-drawer-links">
                    <a class="is-primary" href="{{ route('inicio.profesor') }}"><i class="fas fa-house"></i>Panel principal</a>
                    <a href="{{ route('inicio.profesor') }}#alumnos"><i class="fas fa-user-graduate"></i>Gestión de estudiantes</a>
                    <a href="{{ route('inicio.profesor') }}#progreso"><i class="fas fa-chart-line"></i>Seguimiento académico</a>
                </div>
            </details>
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-clipboard-question"></i>Tests y evaluaciones</summary>
                <div class="teacher-drawer-links">
                    <a href="{{ route('profesor.tests.index') }}"><i class="fas fa-table-cells-large"></i>Panel de Tests</a>
                    <a href="{{ route('profesor.tests.index') }}#mis-bancos"><i class="fas fa-folder-open"></i>Mis bancos</a>
                    <a href="{{ route('profesor.tests.index') }}#bancos-sistema"><i class="fas fa-database"></i>Bancos del sistema</a>
                    <a href="{{ route('profesor.tests.banks.create') }}"><i class="fas fa-circle-plus"></i>Crear banco</a>
                    <a href="{{ route('profesor.tests.index') }}#crear-trivia"><i class="fas fa-bolt"></i>Abrir trivia</a>
                    <a href="{{ route('profesor.tests.index') }}#crear-examen"><i class="fas fa-file-pen"></i>Abrir examen</a>
                    <a href="{{ route('profesor.tests.index') }}#sesiones-recientes"><i class="fas fa-clock-rotate-left"></i>Sesiones recientes</a>
                </div>
            </details>
            <details class="teacher-drawer-group" open>
                <summary><i class="fas fa-chart-column"></i>Resultados</summary>
                <div class="teacher-drawer-links"><a href="{{ route('profesor.calificaciones.index') }}"><i class="fas fa-file-signature"></i>Calificaciones e historial</a></div>
            </details>
            <details class="teacher-drawer-group">
                <summary><i class="fas fa-user-gear"></i>Mi cuenta</summary>
                <div class="teacher-drawer-links"><a href="{{ route('cuenta.clave.editar') }}"><i class="fas fa-key"></i>Actualizar contraseña</a></div>
            </details>
            <p class="teacher-drawer-foot">Accesos directos disponibles desde todas las pantallas del Profesor.</p>
            @endif
        </aside>
    @endif

    <main class="py-4" style="position:relative;z-index:1">@yield('content')</main>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/fontawesome/js/all.min.js') }}"></script>
    <script>
        (function(){
            var body=document.body,toggle=document.getElementById('teacher-nav-toggle'),drawer=document.getElementById('teacher-nav-drawer'),overlay=document.getElementById('teacher-nav-overlay'),close=document.getElementById('teacher-nav-close');
            function setTeacherNav(open){if(!drawer)return;body.classList.toggle('teacher-nav-open',open);drawer.setAttribute('aria-hidden',open?'false':'true');overlay?.setAttribute('aria-hidden',open?'false':'true');toggle?.setAttribute('aria-expanded',open?'true':'false');if(open)close?.focus();}
            toggle?.addEventListener('click',function(){setTeacherNav(!body.classList.contains('teacher-nav-open'));});close?.addEventListener('click',function(){setTeacherNav(false);toggle?.focus();});overlay?.addEventListener('click',function(){setTeacherNav(false);});drawer?.querySelectorAll('a').forEach(function(link){link.addEventListener('click',function(){setTeacherNav(false);});});
            document.addEventListener('keydown',function(event){if(event.key==='Escape')setTeacherNav(false);});
            document.addEventListener('click',function(event){var menu=document.getElementById('teacher-account-menu');if(menu&&menu.open&&!menu.contains(event.target))menu.removeAttribute('open');});
        })();
    </script>
    @stack('scripts')
</body>
</html>
