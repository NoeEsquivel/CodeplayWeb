@extends('layouts.app')

@section('title', 'Inicio Administrador - CodePlay')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="game-card p-4 text-center text-white">
                <h1 class="mb-4">👨‍💼 Panel de Administración</h1>
                <h3 class="text-warning">{{ session('usuario_nombre') }}</h3>
                <p class="lead">Gestión de usuarios alumnos del sistema</p>
                
                <div class="row mt-4">
                    <!-- Solo mostramos Gestión de Alumnos -->
                    <div class="col-md-8 offset-md-2 mb-3">
                        <div class="game-card p-4">
                            <i class="fas fa-user-graduate fa-3x text-info mb-3"></i>
                            <h4>Gestión de Alumnos</h4>
                            <p class="mb-3">Administra los usuarios alumnos del sistema</p>
                            
                            <div class="d-grid">
                                <button class="btn btn-outline-info btn-lg" 
                                        onclick="mostrarGestionAlumnos()">
                                    <i class="fas fa-users me-2"></i>
                                    Administrar Usuarios Alumnos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección para mostrar la gestión de alumnos -->
                <div id="gestion-alumnos" style="display: none;">
                    <!-- Aquí se cargará el contenido dinámicamente -->
                </div>
                
                <div class="mt-4">
                    <span class="badge bg-warning fs-6 p-2">
                        <i class="fas fa-user-shield me-1"></i> ADMINISTRADOR
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

function mostrarGestionAlumnos() {
    // Ocultar botones principales
    document.querySelector('.row.mt-4').style.display = 'none';
    
    // Mostrar sección de gestión
    const gestionDiv = document.getElementById('gestion-alumnos');
    gestionDiv.style.display = 'block';
    
    // Cargar contenido de alumnos
    gestionDiv.innerHTML = `
        <div class="game-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-info mb-0">
                    <i class="fas fa-user-graduate me-2"></i>Gestión de Alumnos
                </h3>
                <div>
                    <button class="btn btn-success me-2" onclick="mostrarFormularioCrearAlumno()">
                        <i class="fas fa-plus me-1"></i> Nuevo Alumno
                    </button>
                    <button class="btn btn-secondary" onclick="volverAlMenuAdmin()">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </button>
                </div>
            </div>
            <div id="lista-alumnos">
                Cargando alumnos...
            </div>
            <div id="formulario-crear-alumno" style="display: none;"></div>
        </div>
    `;
    cargarAlumnos();
}

function volverAlMenuAdmin() {
    document.querySelector('.row.mt-4').style.display = 'block';
    document.getElementById('gestion-alumnos').style.display = 'none';
}

function cargarAlumnos() {
    fetch('/api/usuarios-por-rol/Alumno')
        .then(response => response.json())
        .then(alumnos => {
            const listaDiv = document.getElementById('lista-alumnos');
            
            if (alumnos.length === 0) {
                listaDiv.innerHTML = '<p class="text-center text-muted">No hay alumnos registrados.</p>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de Usuario</th>
                                <th>Puntos Totales</th>
                                <th>Fecha Creación</th>
                                <th>Estado Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            alumnos.forEach(alumno => {
                const requiereCambio = alumno.cambio_clave_requerido ? 
                    '<span class="badge bg-warning">Requiere cambio</span>' : 
                    '<span class="badge bg-success">Actualizada</span>';
                
                html += `
                    <tr>
                        <td>${alumno.id}</td>
                        <td>${alumno.nombre}</td>
                        <td>${alumno.puntos_totales}</td>
                        <td>${new Date(alumno.created_at).toLocaleDateString()}</td>
                        <td>${requiereCambio}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" onclick="resetearPasswordAlumno(${alumno.id})">
                                <i class="fas fa-key"></i> Resetear Clave
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarAlumno(${alumno.id})">
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
            document.getElementById('lista-alumnos').innerHTML = '<p class="text-danger">Error al cargar los alumnos.</p>';
        });
}

function mostrarFormularioCrearAlumno() {
    const formularioDiv = document.getElementById('formulario-crear-alumno');
    
    // Ocultar la lista de alumnos
    document.getElementById('lista-alumnos').style.display = 'none';
    
    formularioDiv.style.display = 'block';
    formularioDiv.innerHTML = `
        <div class="game-card p-4 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-success mb-0">
                    <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Alumno
                </h4>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ocultarFormularioYMostrarListaAlumnos()">
                    <i class="fas fa-arrow-left me-1"></i> Volver a la lista
                </button>
            </div>
            <form id="formCrearAlumno" onsubmit="crearAlumno(event)">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nombre" required 
                                   placeholder="Ingrese el nombre de usuario para el alumno" autofocus>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Contraseña Temporal</label>
                            <input type="text" class="form-control" value="Ined1234" readonly 
                                   style="background-color: #e9ecef;">
                            <small class="text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                El alumno deberá cambiar esta contraseña en su primer acceso
                            </small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Información:</strong> El nuevo alumno tendrá la contraseña temporal "Ined1234" 
                        y se le solicitará cambiarla en su primer ingreso al sistema.
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Alumno
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="ocultarFormularioYMostrarListaAlumnos()">
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

function ocultarFormularioYMostrarListaAlumnos() {
    const formularioDiv = document.getElementById('formulario-crear-alumno');
    
    // Ocultar formulario
    formularioDiv.style.display = 'none';
    
    // Mostrar lista de alumnos
    document.getElementById('lista-alumnos').style.display = 'block';
}

function ocultarFormularioAlumno() {
    document.getElementById('formulario-crear-alumno').style.display = 'none';
}

function crearAlumno(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const datos = {
        nombre: formData.get('nombre'),
        rol: 'Alumno', // Siempre será Alumno
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
                '✅ Alumno Creado', 
                `El alumno "${datos.nombre}" ha sido registrado exitosamente.`,
                false
            );
            ocultarFormularioYMostrarListaAlumnos();
            cargarAlumnos(); // Recargar la lista
        } else {
            // Manejar errores específicos
            let mensajeError = 'Ha ocurrido un error al crear el alumno.';
            
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
function resetearPasswordAlumno(alumnoId) {
    usuarioIdActual = alumnoId;
    
    mostrarConfirmacion(
        'warning',
        '🔑 Resetear Contraseña',
        '¿Estás seguro de que deseas resetear la contraseña de este alumno a "Ined1234"?<br><br><small>El alumno deberá cambiar su contraseña en el próximo acceso.</small>',
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
                        'La contraseña del alumno ha sido restablecida exitosamente a "Ined1234".',
                        true
                    );
                    cargarAlumnos(); // Recargar la lista para actualizar estado
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

// Función modificada para eliminar alumno
function eliminarAlumno(alumnoId) {
    usuarioIdActual = alumnoId;
    
    mostrarConfirmacion(
        'danger',
        '🗑️ Eliminar Alumno',
        '¿Estás seguro de que deseas eliminar este alumno permanentemente?<br><br><small>Esta acción no se puede deshacer y se perderán todos los datos del alumno.</small>',
        function() {
            // Código para eliminar alumno
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
                        '🗑️ Alumno Eliminado',
                        'El alumno ha sido eliminado exitosamente del sistema.',
                        true
                    );
                    cargarAlumnos(); // Recargar la lista
                } else {
                    mostrarNotificacion('error',
                        '❌ Error al Eliminar',
                        data.message || 'Ha ocurrido un error al eliminar el alumno.',
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