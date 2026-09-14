<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña Obligatorio - CodePlay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
        }
        
        .security-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            border: 3px solid #ffc107;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .requisito {
            font-size: 0.9rem;
        }
        
        .requisito.cumplido {
            color: #198754;
        }
        
        .requisito.incumplido {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="security-card p-4">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
                        <h2 class="text-dark">Cambio de Contraseña Requerido</h2>
                        <p class="text-muted">Por seguridad, debes cambiar tu contraseña temporal</p>
                    </div>

                    <!-- Alertas -->
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('cambio.clave.procesar') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="clave_actual" class="form-label">Contraseña Temporal Actual</label>
                            <input type="password" class="form-control" id="clave_actual" name="clave_actual" 
                                   required placeholder="Ingresa tu contraseña temporal actual">
                        </div>

                        <div class="mb-3">
                            <label for="nueva_clave" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="nueva_clave" name="nueva_clave" 
                                   required minlength="8" placeholder="Ingresa tu nueva contraseña">
                            <div class="form-text">La contraseña debe cumplir con los siguientes requisitos:</div>
                        </div>

                        <div class="mb-3">
                            <label for="nueva_clave_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="nueva_clave_confirmation" 
                                   name="nueva_clave_confirmation" required placeholder="Confirma tu nueva contraseña">
                        </div>

                        <!-- Mostrar errores de validación -->
                        @if(session('errores_clave'))
                            <div class="alert alert-warning">
                                <strong>Requisitos incumplidos:</strong>
                                <ul class="mb-0">
                                    @foreach(session('errores_clave') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Indicadores de requisitos -->
                        <div class="mb-4">
                            <small class="text-muted d-block mb-2">Verificación en tiempo real:</small>
                            <div class="requisito" id="req-longitud">
                                <i class="fas fa-times text-danger"></i> Mínimo 8 caracteres
                            </div>
                            <div class="requisito" id="req-mayuscula">
                                <i class="fas fa-times text-danger"></i> Al menos una mayúscula
                            </div>
                            <div class="requisito" id="req-numero">
                                <i class="fas fa-times text-danger"></i> Al menos un número
                            </div>
                            <div class="requisito" id="req-coinciden">
                                <i class="fas fa-times text-danger"></i> Las contraseñas coinciden
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-2" id="btn-submit" disabled>
                            <i class="fas fa-save me-2"></i> Cambiar Contraseña y Continuar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const nuevaClave = document.getElementById('nueva_clave');
        const confirmarClave = document.getElementById('nueva_clave_confirmation');
        const btnSubmit = document.getElementById('btn-submit');

        function validarRequisitos() {
            const clave = nuevaClave.value;
            const confirmacion = confirmarClave.value;
            
            let todosCumplidos = true;

            // Validar longitud
            const reqLongitud = document.getElementById('req-longitud');
            if (clave.length >= 8) {
                reqLongitud.innerHTML = '<i class="fas fa-check text-success"></i> Mínimo 8 caracteres';
                reqLongitud.className = 'requisito cumplido';
            } else {
                reqLongitud.innerHTML = '<i class="fas fa-times text-danger"></i> Mínimo 8 caracteres';
                reqLongitud.className = 'requisito incumplido';
                todosCumplidos = false;
            }

            // Validar mayúscula
            const reqMayuscula = document.getElementById('req-mayuscula');
            if (/[A-Z]/.test(clave)) {
                reqMayuscula.innerHTML = '<i class="fas fa-check text-success"></i> Al menos una mayúscula';
                reqMayuscula.className = 'requisito cumplido';
            } else {
                reqMayuscula.innerHTML = '<i class="fas fa-times text-danger"></i> Al menos una mayúscula';
                reqMayuscula.className = 'requisito incumplido';
                todosCumplidos = false;
            }

            // Validar número
            const reqNumero = document.getElementById('req-numero');
            if (/[0-9]/.test(clave)) {
                reqNumero.innerHTML = '<i class="fas fa-check text-success"></i> Al menos un número';
                reqNumero.className = 'requisito cumplido';
            } else {
                reqNumero.innerHTML = '<i class="fas fa-times text-danger"></i> Al menos un número';
                reqNumero.className = 'requisito incumplido';
                todosCumplidos = false;
            }

            // Validar coincidencia
            const reqCoinciden = document.getElementById('req-coinciden');
            if (clave === confirmacion && clave.length > 0) {
                reqCoinciden.innerHTML = '<i class="fas fa-check text-success"></i> Las contraseñas coinciden';
                reqCoinciden.className = 'requisito cumplido';
            } else {
                reqCoinciden.innerHTML = '<i class="fas fa-times text-danger"></i> Las contraseñas coinciden';
                reqCoinciden.className = 'requisito incumplido';
                todosCumplidos = false;
            }

            // Habilitar/deshabilitar botón
            btnSubmit.disabled = !todosCumplidos;
        }

        nuevaClave.addEventListener('input', validarRequisitos);
        confirmarClave.addEventListener('input', validarRequisitos);

        // Validar al cargar la página
        document.addEventListener('DOMContentLoaded', validarRequisitos);
    </script>
</body>
</html>