@php($modoVoluntario = $modoVoluntario ?? false)
<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $modoVoluntario ? 'Actualizar contraseña' : 'Protege tu cuenta' }} · CodePlay</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        :root{--ink:#061217;--panel:#0a2027f2;--panel-soft:#102c33e8;--line:#315b5d;--mint:#00efa4;--mint-soft:#8bf4ce;--gold:#f3c969;--text:#effff9;--muted:#a8c0bc;--danger:#ff7c80;--success:#72efbd}
        *{box-sizing:border-box}html,body{min-height:100%}body{margin:0;color:var(--text);font-family:"Trebuchet MS","Segoe UI",sans-serif;background:#030d12 url("{{ asset('Imagenes/fondos/seguridad-codeplay.png') }}") center/cover fixed no-repeat;overflow-x:hidden}
        body::before{content:"";position:fixed;inset:0;z-index:-1;background:radial-gradient(circle at 50% 25%,#0626302b 0 18%,#02090dd1 76%),linear-gradient(90deg,#01080db3,#06181b70 50%,#01080db3)}
        .security-shell{width:min(1050px,calc(100% - 32px));min-height:100vh;margin:auto;padding:20px 0;display:grid;grid-template-columns:minmax(260px,.72fr) minmax(520px,1.28fr);align-items:center;gap:clamp(28px,4vw,52px)}
        .brand-panel{padding:18px 4px;text-shadow:0 3px 20px #000}.brand{display:inline-flex;align-items:center;gap:11px;color:var(--text);text-decoration:none;font-size:clamp(27px,3.3vw,40px);font-weight:900;letter-spacing:.07em}.brand span{color:var(--mint)}.brand-mark{width:48px;height:48px;border:1px solid #69e5bd;border-radius:13px;display:grid;place-items:center;background:#07191ed9;box-shadow:0 0 28px #00efa438;color:var(--mint);font-family:Consolas,monospace;font-size:19px;letter-spacing:-5px;padding-right:5px}
        .brand-panel .eyebrow,.card-eyebrow{margin:25px 0 8px;color:var(--gold);font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase}.brand-panel h1{max-width:420px;margin:0;font-family:Georgia,serif;font-size:clamp(34px,4.25vw,52px);line-height:1.02;font-weight:500}.brand-panel>p:not(.eyebrow){max-width:430px;margin:16px 0;color:#d3e3df;font-size:15px;line-height:1.5}.security-note{display:flex;gap:11px;max-width:420px;padding:12px 14px;border:1px solid #63c5a94a;border-radius:12px;background:#061a20b8;backdrop-filter:blur(9px)}.security-note i{color:var(--mint);font-size:17px;margin-top:2px}.security-note strong,.security-note span{display:block}.security-note strong{font-size:13px}.security-note span{margin-top:2px;color:var(--muted);font-size:11px;line-height:1.4}
        .security-card{position:relative;border:1px solid #84d8bd78;border-radius:20px;background:linear-gradient(150deg,var(--panel),#07181ef5);box-shadow:0 24px 65px #000a,inset 0 1px #d8fff321;backdrop-filter:blur(18px);overflow:hidden}.security-card::before{content:"";position:absolute;inset:0 0 auto;height:3px;background:linear-gradient(90deg,transparent,var(--mint),var(--gold),transparent)}.card-inner{padding:26px 30px}
        .card-header{display:flex;align-items:flex-start;gap:13px;margin-bottom:20px}.shield{flex:0 0 46px;width:46px;height:46px;display:grid;place-items:center;border:1px solid #e7c76c80;border-radius:13px;background:linear-gradient(145deg,#55441666,#172f2b);color:var(--gold);font-size:19px;box-shadow:0 0 24px #e8bd3d19}.card-eyebrow{margin:0 0 4px}.card-header h2{margin:0;font-family:Georgia,serif;font-size:clamp(25px,2.5vw,34px);font-weight:500}.card-header p{margin:5px 0 0;color:var(--muted);font-size:12px;line-height:1.4}
        .alert{border-radius:12px;border-width:1px;background:#14242be8;color:var(--text)}.alert-danger{border-color:#ff7c8075}.alert-success{border-color:#72efbd75}.alert-warning{border-color:#f3c96975}.alert ul{margin-top:7px}
        .field{margin-bottom:14px}.fields-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field label{display:flex;justify-content:space-between;gap:8px;margin-bottom:6px;color:#ddf2eb;font-size:12px;font-weight:800;letter-spacing:.02em}.field label span{color:#789994;font-size:10px;font-weight:500}.password-wrap{position:relative}.password-wrap input{width:100%;min-height:46px;padding:11px 43px 11px 13px;border:1px solid #3a6667;border-radius:10px;outline:0;background:#05151bd6;color:#fff;font:inherit;font-size:14px;transition:border-color .2s,box-shadow .2s,background .2s}.password-wrap input::placeholder{color:#708b88}.password-wrap input:focus{border-color:var(--mint);background:#071c22;box-shadow:0 0 0 3px #00efa420}.toggle-password{position:absolute;right:5px;top:50%;width:36px;height:36px;transform:translateY(-50%);border:0;border-radius:8px;background:transparent;color:#91aca7}.toggle-password:hover,.toggle-password:focus-visible{background:#153139;color:var(--mint)}
        .strength{margin:1px 0 15px;padding:12px 14px;border:1px solid #315052;border-radius:11px;background:#081a20}.strength-head{display:flex;justify-content:space-between;margin-bottom:7px;color:var(--muted);font-size:11px}.strength-head b{color:var(--gold)}.strength-track{height:4px;margin-bottom:10px;border-radius:99px;background:#1c3539;overflow:hidden}.strength-bar{display:block;width:0;height:100%;border-radius:inherit;background:var(--danger);transition:width .25s,background .25s}.requirements{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px}.requisito{display:flex;align-items:center;gap:6px;color:#a8b9b6;font-size:11px;transition:color .2s}.requisito i{width:16px;height:16px;display:grid;place-items:center;border:1px solid #6d4144;border-radius:50%;color:var(--danger);font-size:7px}.requisito.cumplido{color:#d7f8eb}.requisito.cumplido i{border-color:#5ad5a5;background:#174b3b;color:var(--success)}
        .submit-button{width:100%;min-height:48px;border:1px solid #91ffd7;border-radius:10px;background:linear-gradient(110deg,#00d995,#16b986);color:#032018;font-size:14px;font-weight:900;letter-spacing:.02em;box-shadow:0 11px 26px #00d99520;transition:transform .2s,filter .2s,opacity .2s}.submit-button:not(:disabled):hover{transform:translateY(-2px);filter:brightness(1.08)}.submit-button:disabled{border-color:#3e5a57;background:#1d3938;color:#78928e;box-shadow:none;cursor:not-allowed}.privacy-copy{display:flex;align-items:center;justify-content:center;gap:8px;margin:10px 0 0;color:#78948f;font-size:10px}.privacy-copy i{color:var(--gold)}.secondary-link{display:flex;align-items:center;justify-content:center;gap:7px;margin-top:9px;color:#a8c0bc;font-size:11px;text-decoration:none}.secondary-link:hover{color:var(--mint)}
        @media(max-width:900px){body{background-position:68% center}.security-shell{grid-template-columns:1fr;width:min(650px,calc(100% - 28px));padding:18px 0 28px}.brand-panel{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:4px 8px}.brand-panel h1,.brand-panel>p:not(.eyebrow),.security-note{display:none}.brand-panel .eyebrow{margin:0;text-align:right}.security-card{margin-top:0}}
        @media(max-width:620px){.security-shell{width:min(100% - 18px,650px);padding:10px 0 20px}.brand{font-size:23px}.brand-mark{width:42px;height:42px}.brand-panel .eyebrow{font-size:9px}.card-inner{padding:21px 17px}.card-header{gap:10px}.shield{flex-basis:42px;width:42px;height:42px}.card-header h2{font-size:25px}.fields-grid,.requirements{grid-template-columns:1fr}.field label span{display:none}}
        @media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
<main class="security-shell">
    <section class="brand-panel" aria-label="CodePlay">
        <div class="brand"><span class="brand-mark" aria-hidden="true">&lt;&gt;</span>CODE<span>PLAY</span></div>
        <p class="eyebrow">{{ $modoVoluntario ? 'Seguridad de tu cuenta' : 'Punto de control de seguridad' }}</p>
        <h1>{{ $modoVoluntario ? 'Renueva tu llave cuando lo necesites.' : 'Tu aventura merece una llave propia.' }}</h1>
        <p>{{ $modoVoluntario ? 'Actualiza tu contraseña sin perder el acceso a tus clases, alumnos ni reportes.' : 'Antes de continuar, reemplaza la contraseña temporal. Tu progreso, tus clases y cada logro seguirán exactamente donde los dejaste.' }}</p>
        <div class="security-note"><i class="fas fa-shield-alt" aria-hidden="true"></i><div><strong>{{ $modoVoluntario ? 'Mantén protegida tu cuenta de Profesor.' : 'Un cambio rápido. Una cuenta mejor protegida.' }}</strong><span>La nueva contraseña se guarda de forma segura y solo tú debes conocerla.</span></div></div>
    </section>

    <section class="security-card" aria-labelledby="security-title">
        <div class="card-inner">
            <header class="card-header">
                <span class="shield" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
                <div><p class="card-eyebrow">{{ $modoVoluntario ? 'Cuenta del Profesor' : 'Acceso inicial' }}</p><h2 id="security-title">{{ $modoVoluntario ? 'Actualiza tu contraseña' : 'Crea tu nueva contraseña' }}</h2><p>{{ $modoVoluntario ? 'Confirma tu contraseña actual y elige una nueva.' : 'Completa los requisitos para desbloquear tu cuenta de CodePlay.' }}</p></div>
            </header>

            @if(session('error'))<div class="alert alert-danger" role="alert"><i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}</div>@endif
            @if(session('success'))<div class="alert alert-success" role="status"><i class="fas fa-circle-check me-2"></i>{{ session('success') }}</div>@endif
            @if(session('errores_clave'))
                <div class="alert alert-warning" role="alert"><strong>Revisa estos puntos:</strong><ul class="mb-0">@foreach(session('errores_clave') as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('cambio.clave.procesar') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="clave_actual">{{ $modoVoluntario ? 'Contraseña actual' : 'Contraseña temporal' }} <span>{{ $modoVoluntario ? 'La que utilizas para iniciar sesión' : 'La que recibiste al crear tu cuenta' }}</span></label>
                    <div class="password-wrap"><input type="password" id="clave_actual" name="clave_actual" required autocomplete="current-password" placeholder="{{ $modoVoluntario ? 'Ingresa tu contraseña actual' : 'Ingresa tu contraseña temporal' }}"><button class="toggle-password" type="button" data-toggle-password="clave_actual" aria-label="Mostrar contraseña actual"><i class="fas fa-eye"></i></button></div>
                </div>
                <div class="fields-grid">
                    <div class="field">
                        <label for="nueva_clave">Nueva contraseña <span>Mínimo 8 caracteres</span></label>
                        <div class="password-wrap"><input type="password" id="nueva_clave" name="nueva_clave" required minlength="8" autocomplete="new-password" placeholder="Crea una contraseña segura"><button class="toggle-password" type="button" data-toggle-password="nueva_clave" aria-label="Mostrar nueva contraseña"><i class="fas fa-eye"></i></button></div>
                    </div>
                    <div class="field">
                        <label for="nueva_clave_confirmation">Confirmación <span>Escríbela nuevamente</span></label>
                        <div class="password-wrap"><input type="password" id="nueva_clave_confirmation" name="nueva_clave_confirmation" required autocomplete="new-password" placeholder="Repite tu nueva contraseña"><button class="toggle-password" type="button" data-toggle-password="nueva_clave_confirmation" aria-label="Mostrar confirmación"><i class="fas fa-eye"></i></button></div>
                    </div>
                </div>

                <div class="strength" aria-live="polite">
                    <div class="strength-head"><span>Seguridad de la contraseña</span><b id="strengthLabel">Comienza a escribir</b></div>
                    <div class="strength-track"><span id="strengthBar" class="strength-bar"></span></div>
                    <div class="requirements">
                        <div class="requisito incumplido" id="req-longitud"><i class="fas fa-times"></i><span>Mínimo 8 caracteres</span></div>
                        <div class="requisito incumplido" id="req-mayuscula"><i class="fas fa-times"></i><span>Al menos una mayúscula</span></div>
                        <div class="requisito incumplido" id="req-numero"><i class="fas fa-times"></i><span>Al menos un número</span></div>
                        <div class="requisito incumplido" id="req-coinciden"><i class="fas fa-times"></i><span>Ambas contraseñas coinciden</span></div>
                    </div>
                </div>

                <button type="submit" class="submit-button" id="btn-submit" disabled><i class="fas fa-lock me-2"></i>{{ $modoVoluntario ? 'Actualizar contraseña' : 'Guardar contraseña y entrar' }}</button>
                @if($modoVoluntario)<a class="secondary-link" href="{{ route('inicio.profesor') }}"><i class="fas fa-arrow-left"></i>Volver al panel del Profesor</a>@endif
                <p class="privacy-copy"><i class="fas fa-key"></i>CodePlay nunca mostrará ni compartirá tu contraseña.</p>
            </form>
        </div>
    </section>
</main>

<script>
    const nuevaClave=document.getElementById('nueva_clave');
    const confirmarClave=document.getElementById('nueva_clave_confirmation');
    const btnSubmit=document.getElementById('btn-submit');
    const strengthBar=document.getElementById('strengthBar');
    const strengthLabel=document.getElementById('strengthLabel');

    const actualizarRequisito=(id,cumplido)=>{const elemento=document.getElementById(id);elemento.className='requisito '+(cumplido?'cumplido':'incumplido');elemento.querySelector('i').className='fas '+(cumplido?'fa-check':'fa-times');};
    function validarRequisitos(){
        const clave=nuevaClave.value,confirmacion=confirmarClave.value;
        const reglas=[clave.length>=8,/[A-Z]/.test(clave),/[0-9]/.test(clave),clave===confirmacion&&clave.length>0];
        ['req-longitud','req-mayuscula','req-numero','req-coinciden'].forEach((id,indice)=>actualizarRequisito(id,reglas[indice]));
        const cantidad=reglas.slice(0,3).filter(Boolean).length;
        strengthBar.style.width=(cantidad/3*100)+'%';
        strengthBar.style.background=cantidad===3?'var(--success)':cantidad===2?'var(--gold)':'var(--danger)';
        strengthLabel.textContent=clave.length===0?'Comienza a escribir':cantidad===3?'Lista para usar':cantidad===2?'Casi lista':'Aún es débil';
        btnSubmit.disabled=!reglas.every(Boolean);
    }
    document.querySelectorAll('[data-toggle-password]').forEach(boton=>boton.addEventListener('click',()=>{const input=document.getElementById(boton.dataset.togglePassword),mostrar=input.type==='password';input.type=mostrar?'text':'password';boton.querySelector('i').className='fas '+(mostrar?'fa-eye-slash':'fa-eye');boton.setAttribute('aria-label',(mostrar?'Ocultar ':'Mostrar ')+input.getAttribute('placeholder').toLowerCase());input.focus();}));
    nuevaClave.addEventListener('input',validarRequisitos);confirmarClave.addEventListener('input',validarRequisitos);validarRequisitos();
</script>
</body>
</html>
