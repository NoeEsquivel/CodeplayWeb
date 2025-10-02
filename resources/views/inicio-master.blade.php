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
                                    Administrar Usuarios Administradores
                                </button>
                                
                                <button class="btn btn-outline-info btn-lg" 
                                        onclick="mostrarGestion('alumnos')">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Administrar Usuarios Alumnos
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

// Variables globales para almacenar temporalmente los IDs de usuario
let usuarioIdActual = null;

function mostrarGestion(tipo) {
    // Ocultar botones principales
    document.querySelector('.row.mt-4').style.display = 'none';
    
    // Mostrar sección de gestión
    const gestionDiv = document.getElementById('gestion-usuarios');
    gestionDiv.style.display = 'block';
    
    // Cargar contenido según el tipo
    if (tipo === 'administradores') {
        gestionDiv.innerHTML = `
            <div class="game-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-warning mb-0">
                        <i class="fas fa-user-shield me-2"></i>Usuarios Administradores
                    </h3>
                    <div>
                        <button class="btn btn-success me-2" onclick="mostrarFormularioCrear('Administrador')">
                            <i class="fas fa-plus me-1"></i> Nuevo Administrador
                        </button>
                        <button class="btn btn-secondary" onclick="volverAlMenu()">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                    </div>
                </div>
                <div id="lista-administradores">
                    Cargando administradores...
                </div>
                <div id="formulario-crear" style="display: none;"></div>
            </div>
        `;
        cargarUsuarios('Administrador');
    } else {
        gestionDiv.innerHTML = `
            <div class="game-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-info mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Usuarios Alumnos
                    </h3>
                    <div>
                        <button class="btn btn-success me-2" onclick="mostrarFormularioCrear('Alumno')">
                            <i class="fas fa-plus me-1"></i> Nuevo Alumno
                        </button>
                        <button class="btn btn-secondary" onclick="volverAlMenu()">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                    </div>
                </div>
                <div id="lista-alumnos">
                    Cargando alumnos...
                </div>
                <div id="formulario-crear" style="display: none;"></div>
            </div>
        `;
        cargarUsuarios('Alumno');
    }
}

function volverAlMenu() {
    document.querySelector('.row.mt-4').style.display = 'block';
    document.getElementById('gestion-usuarios').style.display = 'none';
}

function cargarUsuarios(rol) {
    const listaId = rol === 'Administrador' ? 'lista-administradores' : 'lista-alumnos';
    
    fetch(`/api/usuarios-por-rol/${rol}`)
        .then(response => response.json())
        .then(usuarios => {
            const listaDiv = document.getElementById(listaId);
            
            if (usuarios.length === 0) {
                listaDiv.innerHTML = '<p class="text-center text-muted">No hay usuarios de este tipo.</p>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Puntos Totales</th>
                                <th>Fecha Creación</th>
                                <th>Estado Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            usuarios.forEach(usuario => {
                // Añadir el estado de la contraseña
                const requiereCambio = usuario.cambio_clave_requerido ? 
                    '<span class="badge bg-warning">Requiere cambio</span>' : 
                    '<span class="badge bg-success">Actualizada</span>';
                
                html += `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.nombre}</td>
                        <td>${usuario.puntos_totales}</td>
                        <td>${new Date(usuario.created_at).toLocaleDateString()}</td>
                        <td>${requiereCambio}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" onclick="resetearPassword(${usuario.id})">
                                <i class="fas fa-key"></i> Resetear Clave
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${usuario.id})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            listaDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById(listaId).innerHTML = '<p class="text-danger">Error al cargar los usuarios.</p>';
        });
}

function mostrarFormularioCrear(rol) {
    const formularioDiv = document.getElementById('formulario-crear');
    const listaId = rol === 'Administrador' ? 'lista-administradores' : 'lista-alumnos';
    
    // Ocultar la lista de usuarios
    document.getElementById(listaId).style.display = 'none';
    
    formularioDiv.style.display = 'block';
    formularioDiv.innerHTML = `
        <div class="game-card p-4 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-success mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Crear Nuevo ${rol}
                </h4>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ocultarFormularioYMostrarLista('${rol}')">
                    <i class="fas fa-arrow-left me-1"></i> Volver a la lista
                </button>
            </div>
            <form id="formCrearUsuario" onsubmit="crearUsuario(event, '${rol}')">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nombre" required 
                                   placeholder="Ingrese el nombre de usuario" autofocus>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Contraseña Temporal</label>
                            <input type="text" class="form-control" value="Ined1234" readonly 
                                   style="background-color: #e9ecef;">
                            <small class="text-warning">Esta será la contraseña temporal del usuario</small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        El usuario recibirá la contraseña temporal "Ined1234" y deberá cambiarla en su primer acceso.
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Crear Usuario
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="ocultarFormularioYMostrarLista('${rol}')">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Enfocar automáticamente el campo de nombre
    setTimeout(() => {
        const nombreInput = document.querySelector('input[name="nombre"]');
        if (nombreInput) nombreInput.focus();
    }, 100);
}

function ocultarFormularioYMostrarLista(rol) {
    const formularioDiv = document.getElementById('formulario-crear');
    const listaId = rol === 'Administrador' ? 'lista-administradores' : 'lista-alumnos';
    
    // Ocultar formulario
    formularioDiv.style.display = 'none';
    
    // Mostrar lista de usuarios
    document.getElementById(listaId).style.display = 'block';
}

// Si quieres mantener la función original para otros usos, puedes dejarla así:
function ocultarFormulario() {
    document.getElementById('formulario-crear').style.display = 'none';
}

function ocultarFormulario() {
    document.getElementById('formulario-crear').style.display = 'none';
}

function crearUsuario(event, rol) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const datos = {
        nombre: formData.get('nombre'),
        rol: rol,
        clave: 'Ined1234' // Contraseña temporal fija
    };
    
    // Mostrar loading
    const btn = event.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creando...';
    btn.disabled = true;
    
    fetch('/api/usuarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        // Restaurar botón
        btn.innerHTML = originalText;
        btn.disabled = false;
        
            if (data.success) {
                mostrarNotificacion('success', 
                    '✅ Usuario Creado', 
                    `El usuario "${datos.nombre}" ha sido creado exitosamente como ${rol}.`,
                    false
                );
                ocultarFormularioYMostrarLista(rol);
                cargarUsuarios(rol); // Recargar la lista
        } else {
            // Manejar errores específicos
            let mensajeError = 'Ha ocurrido un error al crear el usuario.';
            
            if (data.message && data.message.includes('already been taken')) {
                mensajeError = 'Este nombre de usuario ya existe. Prueba con otro o añade números.';
            } else if (data.message && data.message.includes('The nombre has already been taken')) {
                mensajeError = 'Este nombre de usuario ya existe. Prueba con otro o añade números.';
            } else if (data.message) {
                // Si hay otro mensaje de error, mostrarlo tal cual
                mensajeError = data.message;
            }
            
            mostrarNotificacion('error',
                '❌ Error al Crear',
                mensajeError,
                false
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        mostrarNotificacion('error',
            '❌ Error de Conexión',
            'No se pudo conectar con el servidor. Intenta nuevamente.',
            false
        );
    });
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
                    // Recargar la lista actual para actualizar el estado
                    const titulo = document.querySelector('#gestion-usuarios h3');
                    if (titulo.textContent.includes('Administradores')) {
                        cargarUsuarios('Administrador');
                    } else {
                        cargarUsuarios('Alumno');
                    }
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
                    // Recargar la lista actual
                    const titulo = document.querySelector('#gestion-usuarios h3');
                    if (titulo.textContent.includes('Administradores')) {
                        cargarUsuarios('Administrador');
                    } else {
                        cargarUsuarios('Alumno');
                    }
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