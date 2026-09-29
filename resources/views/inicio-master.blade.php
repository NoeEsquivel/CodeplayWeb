@extends('layouts.app')

@section('title', 'Inicio Master - CodePlay')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="game-card p-4 text-center text-white">
                <h1 class="mb-4">👑 Panel Master</h1>
                <h3 class="text-warning">{{ session('usuario_nombre') }}</h3>
                <p class="lead">Control total del sistema CodePlay</p>

                <div class="row mt-4">
                    <!-- Solo mostramos Gestión de Usuarios -->
                    <div class="col-md-6 offset-md-3 mb-3">
                        <div class="game-card p-4">
                            <i class="fas fa-users-cog fa-3x text-danger mb-3"></i>
                            <h4>Gestión de Usuarios</h4>
                            <p class="mb-3">Administra todos los usuarios del sistema</p>

                            <div class="d-grid gap-3">
                                <button class="btn btn-outline-warning btn-lg"
                                        onclick="mostrarGestion('administradores')">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Administrar Usuarios Profesores
                                </button>

                                <button class="btn btn-outline-success btn-lg"
                                        onclick="mostrarGestionColegios()">
                                    <i class="fas fa-school me-2"></i>
                                    Administrar Colegios
                                </button>

                                <button class="btn btn-outline-danger btn-lg"
                                        onclick="mostrarGestion('administradores-colegio')">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Administradores de Colegio
                                </button>

                                <button class="btn btn-outline-info btn-lg"
                                        onclick="mostrarGestion('alumnos')">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Administrar Usuarios Alumnos
                                </button>

                                <button class="btn btn-outline-primary btn-lg"
                                        onclick="mostrarJerarquia()">
                                    <i class="fas fa-sitemap me-2"></i>
                                    Consultar Jerarquía por Colegio
                                </button>

                                <button class="btn btn-outline-light btn-lg"
                                        onclick="mostrarAuditoria()">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Registro de Acciones
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección para mostrar la gestión específica -->
                <div id="gestion-usuarios" style="display: none;">
                    <!-- Aquí se cargará el contenido dinámicamente -->
                </div>

                <div class="mt-4">
                    <span class="badge bg-danger fs-6 p-2">
                        <i class="fas fa-crown me-1"></i> VISTA MASTER
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para las notificaciones modales */
    .notification-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(10px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .notification-modal.show {
        opacity: 1;
    }

    .notification-content {
        background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        padding: 40px;
        border-radius: 20px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        border: 3px solid;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        transform: scale(0.7);
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
        overflow: hidden;
    }

    .notification-modal.show .notification-content {
        transform: scale(1);
        opacity: 1;
    }

    .notification-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        animation: bounceIn 0.6s ease-out;
    }

    .notification-title {
        color: white;
        font-size: 1.5rem;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .notification-message {
        color: #ccc;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .notification-button {
        background: linear-gradient(45deg, #00ff9d, #00a1ff);
        color: #000;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Roboto', sans-serif;
    }

    .notification-button:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
    }

    .success-border { border-color: #00ff9d; }
    .warning-border { border-color: #ffd93d; }
    .error-border { border-color: #ff6b6b; }
    .info-border { border-color: #2575fc; }

    .success-icon { color: #00ff9d; }
    .warning-icon { color: #ffd93d; }
    .error-icon { color: #ff6b6b; }
    .info-icon { color: #2575fc; }

    /* Estilos para los modales de confirmación */
    .confirmation-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(10px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .confirmation-modal.show {
        opacity: 1;
    }

    .confirmation-content {
        background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        padding: 40px;
        border-radius: 20px;
        text-align: center;
        max-width: 450px;
        width: 90%;
        border: 3px solid;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        transform: scale(0.7);
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .confirmation-modal.show .confirmation-content {
        transform: scale(1);
        opacity: 1;
    }

    .confirmation-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        animation: bounceIn 0.6s ease-out;
    }

    .confirmation-title {
        color: white;
        font-size: 1.4rem;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .confirmation-message {
        color: #ccc;
        margin-bottom: 30px;
        line-height: 1.5;
        font-size: 1rem;
    }

    .confirmation-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .confirm-btn {
        background: linear-gradient(45deg, #00ff9d, #00a1ff);
        color: #000;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Roboto', sans-serif;
        min-width: 120px;
    }

    .confirm-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
    }

    .cancel-btn {
        background: rgba(108, 117, 125, 0.8);
        color: white;
        border: 2px solid #6c757d;
        padding: 12px 25px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Roboto', sans-serif;
        min-width: 120px;
    }

    .cancel-btn:hover {
        background: rgba(108, 117, 125, 1);
        transform: scale(1.05);
    }

    .warning-border { border-color: #ffd93d; }
    .danger-border { border-color: #ff6b6b; }

    .warning-icon { color: #ffd93d; }
    .danger-icon { color: #ff6b6b; }

    @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.1); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes checkmark {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .checkmark {
        animation: checkmark 0.6s ease-out;
    }
</style>

<script>
// Cambiar el fondo específicamente para la vista Master
document.addEventListener('DOMContentLoaded', function() {
    document.body.style.background = 'url("{{ asset("imagenes/Fondo_Master.jpg") }}") no-repeat center center fixed';
    document.body.style.backgroundSize = 'cover';

    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    overlay.style.zIndex = '-1';
    document.body.appendChild(overlay);

    document.querySelector('main').style.position = 'relative';
    document.querySelector('main').style.zIndex = '1';
});

// Función para mostrar notificaciones modales
function mostrarNotificacion(tipo, titulo, mensaje, autoCerrar = true) {
    const modal = document.createElement('div');
    modal.className = 'notification-modal';

    const config = {
        success: { icon: '✅', class: 'success', buttonText: '¡Perfecto!' },
        warning: { icon: '⚠️', class: 'warning', buttonText: 'Entendido' },
        error: { icon: '❌', class: 'error', buttonText: 'Reintentar' },
        info: { icon: 'ℹ️', class: 'info', buttonText: 'Continuar' }
    }[tipo] || { icon: '🔔', class: 'info', buttonText: 'OK' };

    modal.innerHTML = `
        <div class="notification-content ${config.class}-border">
            <div class="notification-icon ${config.class}-icon checkmark">
                ${config.icon}
            </div>
            <h3 class="notification-title">${titulo}</h3>
            <p class="notification-message">${mensaje}</p>
            <button class="notification-button" onclick="cerrarNotificacion(this)">
                ${config.buttonText}
            </button>
        </div>
    `;

    document.body.appendChild(modal);

    // Animación de entrada
    setTimeout(() => modal.classList.add('show'), 10);

    // Auto-cierre si está habilitado
    if (autoCerrar) {
        setTimeout(() => {
            if (modal.parentElement) {
                cerrarNotificacion(modal);
            }
        }, 3000);
    }

    return modal;
}

function cerrarNotificacion(element) {
    const modal = element.closest ? element.closest('.notification-modal') : element;
    modal.classList.remove('show');
    setTimeout(() => {
        if (modal.parentElement) {
            modal.remove();
        }
    }, 300);
}

// Función para mostrar modales de confirmación elegantes
function mostrarConfirmacion(tipo, titulo, mensaje, onConfirm, onCancel = null) {
    const modal = document.createElement('div');
    modal.className = 'confirmation-modal';

    const config = {
        warning: {
            icon: '⚠️',
            class: 'warning',
            confirmText: 'Sí, Resetear',
            cancelText: 'Cancelar'
        },
        danger: {
            icon: '🗑️',
            class: 'danger',
            confirmText: 'Sí, Eliminar',
            cancelText: 'Cancelar'
        }
    }[tipo] || {
        icon: '❓',
        class: 'warning',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar'
    };

modal.innerHTML = `
    <div class="confirmation-content ${config.class}-border">
        <div class="confirmation-icon ${config.class}-icon">
            ${config.icon}
        </div>
        <h3 class="confirmation-title">${titulo}</h3>
        <p class="confirmation-message">${mensaje}</p>
        <div class="confirmation-buttons">
            <button class="cancel-btn" onclick="cerrarConfirmacion(this, false)">
                ${config.cancelText}
            </button>
            <button class="confirm-btn" onclick="cerrarConfirmacion(this, true)">
                ${config.confirmText}
            </button>
        </div>
    </div>
`;

// Guardar referencias directamente en el elemento modal
modal._onConfirm = onConfirm;
modal._onCancel = onCancel;

    document.body.appendChild(modal);

    // Animación de entrada
    setTimeout(() => modal.classList.add('show'), 10);

    // Guardar referencias para usar en los callbacks
    modal._onConfirm = onConfirm;
    modal._onCancel = onCancel;

    return modal;
}

function cerrarConfirmacion(element, confirmado) {
    const modal = element.closest('.confirmation-modal');
    modal.classList.remove('show');

    setTimeout(() => {
        if (modal.parentElement) {
            modal.remove();
        }
        // Ejecutar callback después de la animación
        if (confirmado && modal._onConfirm) {
            modal._onConfirm();
        } else if (!confirmado && modal._onCancel) {
            modal._onCancel();
        }
    }, 300);
}

let usuarioIdActual = null;
let rolGestionActual = null;

const configuracionesRol = {
    administradores: { rol: 'Profesor', titulo: 'Usuarios Profesores', singular: 'Profesor', listaId: 'lista-profesores', icono: 'fa-chalkboard-teacher', color: 'warning' },
    'administradores-colegio': { rol: 'AdministradorColegio', titulo: 'Administradores de Colegio', singular: 'Administrador de Colegio', listaId: 'lista-administradores-colegio', icono: 'fa-user-shield', color: 'danger' },
    alumnos: { rol: 'Alumno', titulo: 'Usuarios Alumnos', singular: 'Alumno', listaId: 'lista-alumnos', icono: 'fa-user-graduate', color: 'info' }
};

function configuracionPorRol(rol) {
    return Object.values(configuracionesRol).find(configuracion => configuracion.rol === rol);
}

function escaparHtml(valor) {
    return String(valor ?? '').replace(/[&<>"]/g, caracter => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[caracter]);
}


function activarSugerenciaUsuario(formulario, endpoint){
    var completo=formulario.querySelector('[name="nombre_completo"]'), usuario=formulario.querySelector('[name="nombre"]'), ayuda=formulario.querySelector('[data-sugerencia-usuario]');
    if(!completo||!usuario)return;
    var editado=false,temporizador;
    usuario.addEventListener('input',function(evento){if(evento.isTrusted)editado=true;});
    completo.addEventListener('input',function(){
        clearTimeout(temporizador);var valor=completo.value.trim();if(valor.length<3){if(ayuda)ayuda.textContent='Escribe el nombre completo para recibir una recomendación.';return;}
        temporizador=setTimeout(async function(){
            try{var respuesta=await fetch(endpoint,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({nombre_completo:valor})});var data=await respuesta.json();if(!respuesta.ok)throw new Error(data.message||'No se pudo generar la recomendación.');if(!editado||!usuario.value.trim())usuario.value=data.suggested_username;if(ayuda)ayuda.textContent='Recomendación disponible: '+data.suggested_username+'. Puedes modificarla.';}catch(error){if(ayuda)ayuda.textContent=error.message;}
        },300);
    });
}

function mostrarGestion(tipo) {
    const configuracion = configuracionesRol[tipo];
    if (!configuracion) return;

    rolGestionActual = configuracion.rol;
    document.querySelector('.row.mt-4').style.display = 'none';
    const gestionDiv = document.getElementById('gestion-usuarios');
    gestionDiv.style.display = 'block';
    gestionDiv.innerHTML = `
        <div class="game-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h3 class="text-${configuracion.color} mb-0"><i class="fas ${configuracion.icono} me-2"></i>${configuracion.titulo}</h3>
                <div>
                    <button class="btn btn-success me-2" onclick="mostrarFormularioCrear('${configuracion.rol}')"><i class="fas fa-plus me-1"></i> Nuevo ${configuracion.singular}</button>
                    <button class="btn btn-secondary" onclick="volverAlMenu()"><i class="fas fa-arrow-left me-1"></i> Volver</button>
                </div>
            </div>
            <div id="${configuracion.listaId}">Cargando usuarios...</div>
            <div id="formulario-crear" style="display: none;"></div>
        </div>`;
    cargarUsuarios(configuracion.rol);
}

function volverAlMenu() {
    rolGestionActual = null;
    document.querySelector('.row.mt-4').style.display = 'block';
    document.getElementById('gestion-usuarios').style.display = 'none';
}

function cargarUsuarios(rol) {
    const configuracion = configuracionPorRol(rol);
    if (!configuracion) return;

    fetch(`/api/usuarios-por-rol/${rol}`)
        .then(response => response.json())
        .then(usuarios => {
            const listaDiv = document.getElementById(configuracion.listaId);
            if (!listaDiv) return;
            if (!Array.isArray(usuarios) || usuarios.length === 0) {
                listaDiv.innerHTML = '<p class="text-center text-muted">No hay usuarios de este tipo.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>ID</th><th>Nombre completo</th><th>Usuario</th><th>Correo</th><th>Colegio</th><th>Estado</th><th>Puntos</th><th>Contraseña</th><th>Acciones</th></tr></thead><tbody>';
            usuarios.forEach(usuario => {
                const requiereCambio = usuario.cambio_clave_requerido ? '<span class="badge bg-warning text-dark">Requiere cambio</span>' : '<span class="badge bg-success">Actualizada</span>';
                const activo = usuario.activo !== false;
                const estado = activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Desactivado</span>';
                html += `<tr><td>${usuario.id}</td><td>${escaparHtml(usuario.nombre_completo || usuario.nombre)}</td><td><code>${escaparHtml(usuario.nombre)}</code></td><td>${escaparHtml(usuario.email || '—')}</td><td>${usuario.colegio ? escaparHtml(usuario.colegio.nombre) : '<span class="text-danger">Sin colegio</span>'}</td><td>${estado}</td><td>${usuario.puntos_totales}</td><td>${requiereCambio}</td><td><button class="btn btn-sm btn-warning me-1 mb-1" onclick="resetearPassword(${usuario.id})"><i class="fas fa-key"></i> Resetear</button><button class="btn btn-sm btn-outline-info me-1 mb-1" onclick="mostrarTraslado(${usuario.id}, ${usuario.colegio_id || 'null'}, '${configuracion.rol}')"><i class="fas fa-exchange-alt"></i> Trasladar</button><button class="btn btn-sm ${activo ? 'btn-outline-danger' : 'btn-outline-success'} mb-1" onclick="cambiarEstadoUsuario(${usuario.id}, ${!activo})">${activo ? 'Desactivar' : 'Activar'}</button></td></tr>`;
            });
            listaDiv.innerHTML = html + '</tbody></table></div>';
        })
        .catch(error => {
            console.error('Error:', error);
            const listaDiv = document.getElementById(configuracion.listaId);
            if (listaDiv) listaDiv.innerHTML = '<p class="text-danger">Error al cargar los usuarios.</p>';
        });
}

function mostrarFormularioCrear(rol) {
    const configuracion = configuracionPorRol(rol);
    if (!configuracion) return;

    const formularioDiv = document.getElementById('formulario-crear');
    const listaDiv = document.getElementById(configuracion.listaId);
    if (listaDiv) listaDiv.style.display = 'none';
    const requiereCorreo = rol === 'Profesor' || rol === 'AdministradorColegio';

    formularioDiv.style.display = 'block';
    formularioDiv.innerHTML = `
        <div class="game-card p-4 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-success mb-0"><i class="fas fa-plus-circle me-2"></i>Crear ${configuracion.singular}</h4>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ocultarFormularioYMostrarLista('${rol}')"><i class="fas fa-arrow-left me-1"></i> Volver</button>
            </div>
            <form id="formCrearUsuario" onsubmit="crearUsuario(event, '${rol}')">
                <div class="row">
                    <div class="col-md-6"><div class="mb-3"><label class="form-label text-white">Nombre completo</label><input type="text" class="form-control" name="nombre_completo" maxlength="160" required placeholder="Ej. Elvin Noe Esquivel Cabrera" autofocus></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label text-white">Nombre de usuario</label><input type="text" class="form-control" name="nombre" maxlength="100" required placeholder="Ej. EEsquivel"><small class="text-info" data-sugerencia-usuario>Se recomendará uno a partir del nombre completo.</small></div></div>
                    ${requiereCorreo ? `<div class="col-md-6"><div class="mb-3"><label class="form-label text-white">Correo electrónico</label><input type="email" class="form-control" name="email" required placeholder="usuario@colegio.edu"><small class="text-info">Se utilizará para recuperar la cuenta.</small></div></div>` : ''}
                    <div class="col-md-6"><div class="mb-3"><label class="form-label text-white">Colegio</label><select class="form-select" name="colegio_id" required><option value="">Cargando colegios...</option></select></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label text-white">Contraseña Temporal</label><input type="text" class="form-control" value="Ined1234" readonly style="background-color: #e9ecef;"></div></div>
                </div>
                <div class="alert alert-info">El usuario deberá cambiar la contraseña temporal <strong>Ined1234</strong> en su primer acceso.</div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Crear Usuario</button><button type="button" class="btn btn-secondary" onclick="ocultarFormularioYMostrarLista('${rol}')">Cancelar</button></div>
            </form>
        </div>`;

    cargarOpcionesColegios(formularioDiv.querySelector('select[name="colegio_id"]'));
    activarSugerenciaUsuario(formularioDiv.querySelector('form'), '/api/usuarios/sugerir-nombre');
    formularioDiv.querySelector('input[name="nombre_completo"]')?.focus();
}

function cargarOpcionesColegios(select, seleccionado = null) {
    fetch('/api/colegios')
        .then(response => response.json())
        .then(colegios => {
            const activos = colegios.filter(colegio => colegio.activo);
            select.innerHTML = '<option value="">Selecciona un colegio</option>' + activos.map(colegio => `<option value="${colegio.id}" ${String(colegio.id) === String(seleccionado) ? 'selected' : ''}>${colegio.nombre}</option>`).join('');
        })
        .catch(() => { select.innerHTML = '<option value="">No se pudieron cargar los colegios</option>'; });
}

function ocultarFormularioYMostrarLista(rol) {
    const configuracion = configuracionPorRol(rol);
    document.getElementById('formulario-crear').style.display = 'none';
    const listaDiv = configuracion ? document.getElementById(configuracion.listaId) : null;
    if (listaDiv) listaDiv.style.display = 'block';
}

function mostrarTraslado(usuarioId, colegioActual, rol) {
    const configuracion = configuracionPorRol(rol);
    const formulario = document.getElementById('formulario-crear');
    const lista = configuracion ? document.getElementById(configuracion.listaId) : null;
    if (lista) lista.style.display = 'none';
    formulario.style.display = 'block';
    formulario.innerHTML = `
        <div class="game-card p-4 mt-3">
            <h4 class="text-info mb-3"><i class="fas fa-exchange-alt me-2"></i>Trasladar usuario</h4>
            <p class="text-light">El usuario dejará sus clases actuales y será agregado a la Clase General del nuevo colegio cuando corresponda.</p>
            <form onsubmit="trasladarUsuario(event, ${usuarioId}, '${rol}')">
                <div class="mb-3"><label class="form-label text-white">Colegio de destino</label><select class="form-select" name="colegio_id" required><option>Cargando colegios...</option></select></div>
                <button class="btn btn-info" type="submit">Confirmar traslado</button>
                <button class="btn btn-secondary" type="button" onclick="ocultarFormularioYMostrarLista('${rol}')">Cancelar</button>
            </form>
        </div>`;
    cargarOpcionesColegios(formulario.querySelector('select'), colegioActual);
}

function trasladarUsuario(event, usuarioId, rol) {
    event.preventDefault();
    const colegioId = new FormData(event.target).get('colegio_id');
    fetch(`/api/usuarios/${usuarioId}/colegio`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ colegio_id: colegioId })
    }).then(async response => ({ ok: response.ok, data: await response.json() }))
      .then(({ ok, data }) => {
          if (!ok || !data.success) throw new Error(data.message || 'No fue posible trasladar el usuario.');
          mostrarNotificacion('success', '✅ Usuario Trasladado', data.message, false);
          ocultarFormularioYMostrarLista(rol);
          cargarUsuarios(rol);
      }).catch(error => mostrarNotificacion('error', '❌ Traslado no realizado', error.message, false));
}

function cambiarEstadoUsuario(usuarioId, activo) {
    mostrarConfirmacion('warning', activo ? 'Activar Usuario' : 'Desactivar Usuario', `¿Deseas ${activo ? 'activar' : 'desactivar'} esta cuenta?`, function() {
        fetch(`/api/usuarios/${usuarioId}/estado`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ activo })
        }).then(async response => ({ ok: response.ok, data: await response.json() }))
          .then(({ ok, data }) => {
              if (!ok || !data.success) throw new Error(data.message || 'No se pudo actualizar el estado.');
              mostrarNotificacion('success', '✅ Estado Actualizado', data.message, false);
              if (rolGestionActual) cargarUsuarios(rolGestionActual);
          }).catch(error => mostrarNotificacion('error', '❌ Estado no actualizado', error.message, false));
    });
}

function crearUsuario(event, rol) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const datos = { nombre_completo: formData.get('nombre_completo'), nombre: formData.get('nombre'), email: formData.get('email') || null, colegio_id: formData.get('colegio_id'), rol, clave: 'Ined1234' };
    const btn = event.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creando...';
    btn.disabled = true;

    fetch('/api/usuarios', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(datos) })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            if (data.success) {
                mostrarNotificacion('success', '✅ Usuario Creado', `El usuario "${datos.nombre}" fue creado correctamente.`, false);
                ocultarFormularioYMostrarLista(rol);
                cargarUsuarios(rol);
                return;
            }
            if (data.code === 'USERNAME_TAKEN' && data.suggested_username) {
                const nombreInput = event.target.querySelector('input[name="nombre"]');
                if (nombreInput) { nombreInput.value = data.suggested_username; nombreInput.focus(); nombreInput.select(); }
            }
            mostrarNotificacion('error', '❌ Error al Crear', data.message || 'Ha ocurrido un error al crear el usuario.', false);
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = originalText;
            btn.disabled = false;
            mostrarNotificacion('error', '❌ Error de Conexión', 'No se pudo conectar con el servidor.', false);
        });
}
function mostrarGestionColegios() {
    rolGestionActual = null;
    document.querySelector('.row.mt-4').style.display = 'none';
    const gestionDiv = document.getElementById('gestion-usuarios');
    gestionDiv.style.display = 'block';
    gestionDiv.innerHTML = `
        <div class="game-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h3 class="text-success mb-0"><i class="fas fa-school me-2"></i>Colegios</h3>
                <div>
                    <button class="btn btn-success me-2" onclick="mostrarFormularioColegio()"><i class="fas fa-plus me-1"></i> Nuevo Colegio</button>
                    <button class="btn btn-secondary" onclick="volverAlMenu()"><i class="fas fa-arrow-left me-1"></i> Volver</button>
                </div>
            </div>
            <div id="lista-colegios">Cargando colegios...</div>
            <div id="formulario-colegio" style="display: none;"></div>
        </div>`;
    cargarColegios();
}

function cargarColegios() {
    fetch('/api/colegios')
        .then(response => response.json())
        .then(colegios => {
            const lista = document.getElementById('lista-colegios');
            if (!colegios.length) {
                lista.innerHTML = '<p class="text-center text-muted">No hay colegios registrados.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Colegio</th><th>Código</th><th>Estado</th><th>Administradores</th><th>Profesores</th><th>Alumnos</th><th>Clases</th><th>Acciones</th></tr></thead><tbody>';
            colegios.forEach(colegio => {
                const nombreCodificado = encodeURIComponent(colegio.nombre);
                const estado = colegio.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Desactivado</span>';
                const accionEstado = colegio.activo ? 'Desactivar' : 'Activar';
                const claseEstado = colegio.activo ? 'btn-outline-warning' : 'btn-outline-success';
                html += `<tr><td><strong>${escaparHtml(colegio.nombre)}</strong></td><td>${escaparHtml(colegio.codigo)}</td><td>${estado}</td><td>${colegio.administradores_count}</td><td>${colegio.profesores_count}</td><td>${colegio.alumnos_count}</td><td>${colegio.clases_count}</td><td><button class="btn btn-sm btn-outline-primary me-1 mb-1" onclick="mostrarJerarquia(${colegio.id})"><i class="fas fa-sitemap"></i> Ver</button><button class="btn btn-sm btn-outline-info me-1 mb-1" onclick="mostrarFormularioColegio(${colegio.id}, decodeURIComponent('${nombreCodificado}'))"><i class="fas fa-pen"></i> Editar</button><button class="btn btn-sm ${claseEstado} me-1 mb-1" onclick="cambiarEstadoColegio(${colegio.id}, ${!colegio.activo})">${accionEstado}</button><button class="btn btn-sm btn-outline-danger mb-1" onclick="eliminarColegio(${colegio.id})"><i class="fas fa-trash"></i></button></td></tr>`;
            });
            lista.innerHTML = html + '</tbody></table></div>';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('lista-colegios').innerHTML = '<p class="text-danger">No se pudieron cargar los colegios.</p>';
        });
}

function mostrarFormularioColegio(id = null, nombre = '') {
    const formulario = document.getElementById('formulario-colegio');
    document.getElementById('lista-colegios').style.display = 'none';
    formulario.style.display = 'block';
    formulario.innerHTML = `
        <div class="game-card p-4 mt-3">
            <h4 class="text-success mb-3">${id ? 'Editar Colegio' : 'Crear Colegio'}</h4>
            <form onsubmit="guardarColegio(event, ${id || 'null'})">
                <div class="mb-3"><label class="form-label text-white">Nombre del colegio</label><input class="form-control" name="nombre" maxlength="150" required value="${nombre.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}"></div>
                <div class="d-flex gap-2"><button class="btn btn-success" type="submit"><i class="fas fa-save me-1"></i> Guardar</button><button class="btn btn-secondary" type="button" onclick="cerrarFormularioColegio()">Cancelar</button></div>
            </form>
        </div>`;
    formulario.querySelector('input[name="nombre"]')?.focus();
}

function cerrarFormularioColegio() {
    document.getElementById('formulario-colegio').style.display = 'none';
    document.getElementById('lista-colegios').style.display = 'block';
}

function guardarColegio(event, id) {
    event.preventDefault();
    const nombre = new FormData(event.target).get('nombre');
    const boton = event.target.querySelector('button[type="submit"]');
    boton.disabled = true;

    fetch(id ? `/api/colegios/${id}` : '/api/colegios', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ nombre })
    })
    .then(response => response.json())
    .then(data => {
        boton.disabled = false;
        if (!data.success) {
            mostrarNotificacion('error', '❌ No se guardó', data.message || 'Revisa los datos ingresados.', false);
            return;
        }
        mostrarNotificacion('success', '✅ Colegio Guardado', data.message, false);
        cerrarFormularioColegio();
        cargarColegios();
    })
    .catch(() => {
        boton.disabled = false;
        mostrarNotificacion('error', '❌ Error de Conexión', 'No se pudo guardar el colegio.', false);
    });
}

function cambiarEstadoColegio(id, activo) {
    mostrarConfirmacion('warning', activo ? 'Activar Colegio' : 'Desactivar Colegio', `¿Deseas ${activo ? 'activar' : 'desactivar'} este colegio?`, function() {
        fetch(`/api/colegios/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ activo })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', '✅ Estado Actualizado', data.message, false);
                cargarColegios();
            } else {
                mostrarNotificacion('error', '❌ Error', data.message, false);
            }
        });
    });
}

function eliminarColegio(id) {
    mostrarConfirmacion('danger', 'Eliminar Colegio', 'Solo se puede eliminar un colegio sin usuarios. ¿Deseas continuar?', function() {
        fetch(`/api/colegios/${id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('warning', '🗑️ Colegio Eliminado', data.message, false);
                    cargarColegios();
                } else {
                    mostrarNotificacion('error', '❌ No se puede eliminar', data.message, false);
                }
            });
    });
}

function mostrarJerarquia(colegioSeleccionado = null) {
    rolGestionActual = null;
    document.querySelector('.row.mt-4').style.display = 'none';
    const gestion = document.getElementById('gestion-usuarios');
    gestion.style.display = 'block';
    gestion.innerHTML = `
        <div class="game-card p-4 mt-4 text-start">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h3 class="text-primary mb-0"><i class="fas fa-sitemap me-2"></i>Jerarquía por Colegio</h3>
                <button class="btn btn-secondary" onclick="volverAlMenu()"><i class="fas fa-arrow-left me-1"></i> Volver</button>
            </div>
            <div class="mb-4"><label class="form-label text-white">Colegio</label><select id="colegio-jerarquia" class="form-select"><option value="">Selecciona un colegio</option></select></div>
            <div id="resumen-jerarquia" class="text-light">Selecciona un colegio para consultar su estructura.</div>
        </div>`;
    const select = document.getElementById('colegio-jerarquia');
    fetch('/api/colegios').then(r => r.json()).then(colegios => {
        select.innerHTML = '<option value="">Selecciona un colegio</option>' + colegios.map(c => `<option value="${c.id}" ${String(c.id) === String(colegioSeleccionado) ? 'selected' : ''}>${escaparHtml(c.nombre)}${c.activo ? '' : ' (desactivado)'}</option>`).join('');
        select.addEventListener('change', () => cargarJerarquia(select.value));
        if (colegioSeleccionado) cargarJerarquia(colegioSeleccionado);
    });
}

function cargarJerarquia(colegioId) {
    const contenedor = document.getElementById('resumen-jerarquia');
    if (!colegioId) { contenedor.innerHTML = 'Selecciona un colegio para consultar su estructura.'; return; }
    contenedor.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cargando estructura...';
    fetch(`/api/colegios/${colegioId}/resumen`).then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No fue posible consultar el colegio.');
        return data;
    }).then(data => {
        const personas = lista => lista.length ? lista.map(u => `<li>${escaparHtml(u.nombre_completo || u.nombre)} <small class="text-muted">(${escaparHtml(u.nombre)}) ${escaparHtml(u.email || '')}</small> ${u.activo === false ? '<span class="badge bg-secondary">Desactivado</span>' : ''}</li>`).join('') : '<li class="text-muted">Sin registros</li>';
        const clases = data.clases.length ? data.clases.map(clase => `<div class="border border-secondary rounded p-3 mb-2"><strong>${escaparHtml(clase.nombre)}</strong> <span class="badge bg-info text-dark">${clase.profesores_count} profesores</span> <span class="badge bg-success">${clase.alumnos_count} alumnos</span><div class="small mt-2">Profesores: ${clase.profesores.length ? clase.profesores.map(p => escaparHtml(p.nombre_completo || p.nombre)).join(', ') : 'Sin asignar'}</div></div>`).join('') : '<p class="text-muted">Sin clases.</p>';
        contenedor.innerHTML = `<h4>${escaparHtml(data.colegio.nombre)} ${data.colegio.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Desactivado</span>'}</h4><div class="row mt-3"><div class="col-md-4"><h5>Administradores</h5><ul>${personas(data.administradores)}</ul></div><div class="col-md-4"><h5>Profesores</h5><ul>${personas(data.profesores)}</ul></div><div class="col-md-4"><h5>Alumnos</h5><ul>${personas(data.alumnos)}</ul></div></div><hr><h5>Clases</h5>${clases}`;
    }).catch(error => { contenedor.innerHTML = `<p class="text-danger">${escaparHtml(error.message)}</p>`; });
}

function mostrarAuditoria() {
    rolGestionActual = null;
    document.querySelector('.row.mt-4').style.display = 'none';
    const gestion = document.getElementById('gestion-usuarios');
    gestion.style.display = 'block';
    gestion.innerHTML = `
        <div class="game-card p-4 mt-4 text-start">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2"><h3 class="text-light mb-0"><i class="fas fa-clipboard-list me-2"></i>Registro de Acciones</h3><button class="btn btn-secondary" onclick="volverAlMenu()">Volver</button></div>
            <div class="row mb-3"><div class="col-md-6"><label class="form-label text-white">Filtrar por colegio</label><select id="auditoria-colegio" class="form-select"><option value="">Todos los colegios</option></select></div></div>
            <div id="lista-auditoria">Cargando acciones...</div>
        </div>`;
    const select = document.getElementById('auditoria-colegio');
    fetch('/api/colegios').then(r => r.json()).then(colegios => {
        select.innerHTML = '<option value="">Todos los colegios</option>' + colegios.map(c => `<option value="${c.id}">${escaparHtml(c.nombre)}</option>`).join('');
        select.addEventListener('change', cargarAuditoria);
    });
    cargarAuditoria();
}

function cargarAuditoria() {
    const select = document.getElementById('auditoria-colegio');
    const lista = document.getElementById('lista-auditoria');
    const filtro = select?.value ? `?colegio_id=${encodeURIComponent(select.value)}&limite=100` : '?limite=100';
    fetch('/api/auditoria' + filtro).then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No fue posible cargar el registro.');
        return data;
    }).then(data => {
        if (!data.data.length) { lista.innerHTML = '<p class="text-muted">No hay acciones registradas.</p>'; return; }
        const filas = data.data.map(item => `<tr><td>${new Date(item.created_at).toLocaleString()}</td><td>${escaparHtml(item.actor?.nombre || 'Usuario eliminado')}</td><td><span class="badge bg-dark border">${escaparHtml(item.accion)}</span></td><td>${escaparHtml(item.objetivo_usuario?.nombre || item.entidad_tipo || '—')}</td><td>${escaparHtml(item.colegio?.nombre || '—')}</td></tr>`).join('');
        lista.innerHTML = `<div class="table-responsive"><table class="table table-dark table-striped"><thead><tr><th>Fecha</th><th>Responsable</th><th>Acción</th><th>Objetivo</th><th>Colegio</th></tr></thead><tbody>${filas}</tbody></table></div>`;
    }).catch(error => { lista.innerHTML = `<p class="text-danger">${escaparHtml(error.message)}</p>`; });
}
// Función modificada para resetear contraseña
function resetearPassword(usuarioId) {
    usuarioIdActual = usuarioId;

    mostrarConfirmacion(
        'warning',
        '🔑 Resetear Contraseña',
        '¿Estás seguro de que deseas resetear la contraseña de este usuario a "Ined1234"?<br><br><small>El usuario deberá cambiar su contraseña en el próximo acceso.</small>',
        function() {
            // Código para resetear contraseña
            fetch(`/api/usuarios/${usuarioIdActual}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nueva_clave: 'Ined1234' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success',
                        '🔑 Contraseña Reseteada',
                        'La contraseña ha sido restablecida exitosamente a "Ined1234".',
                        true
                    );
                    if (rolGestionActual) cargarUsuarios(rolGestionActual);
                } else {
                    mostrarNotificacion('error',
                        '❌ Error al Resetear',
                        data.message || 'Ha ocurrido un error al resetear la contraseña.',
                        false
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('error',
                    '❌ Error de Conexión',
                    'No se pudo conectar con el servidor. Intenta nuevamente.',
                    false
                );
            });
        }
    );
}

// Función modificada para eliminar usuario
function eliminarUsuario(usuarioId) {
    usuarioIdActual = usuarioId;

    mostrarConfirmacion(
        'danger',
        '🗑️ Eliminar Usuario',
        '¿Estás seguro de que deseas eliminar este usuario permanentemente?<br><br><small>Esta acción no se puede deshacer y se perderán todos los datos del usuario.</small>',
        function() {
            // Código para eliminar usuario
            fetch(`/api/usuarios/${usuarioIdActual}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('warning',
                        '🗑️ Usuario Eliminado',
                        'El usuario ha sido eliminado exitosamente del sistema.',
                        true
                    );
                    if (rolGestionActual) cargarUsuarios(rolGestionActual);
                } else {
                    mostrarNotificacion('error',
                        '❌ Error al Eliminar',
                        data.message || 'Ha ocurrido un error al eliminar el usuario.',
                        false
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('error',
                    '❌ Error de Conexión',
                    'No se pudo conectar con el servidor. Intenta nuevamente.',
                    false
                );
            });
        }
    );
}
</script>
@endsection
