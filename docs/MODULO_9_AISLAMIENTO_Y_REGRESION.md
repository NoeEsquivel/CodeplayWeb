# Módulo 9: aislamiento y regresión completa

Fecha de verificación: 26 de septiembre de 2026.

## Resultado

- Los datos administrativos y académicos permanecen aislados por colegio.
- Los cuatro roles solo pueden usar las rutas que les corresponden.
- La recuperación de cuenta conserva una respuesta genérica y los enlaces expiran, son de un solo uso y cierran sesiones anteriores.
- El usuario Master exige correo verificado, segundo factor y códigos de recuperación de un solo uso.
- Profesores y Administradores de Colegio no pueden consultar, modificar, asignar, exportar ni restablecer cuentas de otro colegio.
- Las relaciones de clases rechazan roles incorrectos, duplicados y usuarios de otro colegio.
- Las estadísticas del Profesor y del Administrador de Colegio se calculan únicamente con alumnos autorizados.
- Documentación, Tutoriales, Desafíos y Aventura conservan sus rutas y comportamiento para el Alumno.

## Auditoría de consultas

Se revisaron los controladores de usuarios, colegios, clases, panel del Profesor, panel del Administrador de Colegio, estadísticas y módulos del Alumno.

Consultas globales autorizadas:

- Inicio de sesión por nombre de usuario globalmente único.
- Recuperación por correo globalmente único, con respuesta que no revela si la cuenta existe.
- Catálogos compartidos de documentación, tutoriales, desafíos, tests, objetos y enemigos.
- Administración global de colegios y usuarios desde rutas exclusivas del Master.

Consultas que requieren aislamiento y fueron verificadas:

- Usuarios por `colegio_id` y rol.
- Clases por `colegio_id` y membresía del Profesor.
- Alumnos visibles mediante `clase_alumno` y `clase_profesor`.
- Estadísticas mediante identificadores de alumnos previamente limitados al colegio o a las clases del Profesor.
- Progreso individual mediante el identificador del Alumno autenticado.

La prueba `MultiTenantIsolationRegressionTest` intenta cruces directos entre dos colegios para lectura, modificación, asignación, restablecimiento de contraseña, filtros y exportación CSV.

## Rendimiento e índices

Se añadieron índices compuestos para las consultas frecuentes:

- Usuarios por colegio, rol y estado.
- Clases por colegio y estado.
- Membresías de profesores y alumnos en sentido usuario-clase.
- Progreso de tutoriales y niveles por usuario y estado.
- Desafíos completados por usuario.
- Resultados de tests por usuario y fecha.

Los índices ya existentes de expediciones, documentación, auditoría, tokens de seguridad y restricciones únicas se conservaron.

## Respaldos

El comando `php artisan codeplay:backup` crea un respaldo local, comprueba que no esté vacío, genera SHA-256 y conserva por defecto los diez más recientes. La contraseña de la base no forma parte de los argumentos visibles del proceso.

Respaldos verificados durante este módulo:

- Antes de los índices: `CodePlayWeb_2026-09-26_07-34-40.sql`, 1,041,805 bytes, SHA-256 `541c157fca440f56a279ea2771d5cea30b1c3174e5d1e31dfe94e67d4405578a`.
- Después de los índices: `CodePlayWeb_2026-09-26_07-35-11.sql`, 1,042,252 bytes, SHA-256 `260895d05efaae1c1ef50570e37a00c525df6a4c232dbc0d4cfbdd376048d8b4`.

Los archivos se guardan en `storage/app/backups`, quedan fuera de Git y tienen un archivo `.sha256` asociado.

## Verificación automática

Resultado final: **124 pruebas y 2,456 verificaciones aprobadas**.

La suite incluye acceso por roles, recuperación, correo, segundo factor, colegios, clases, panel Master, panel del Administrador de Colegio, panel del Profesor, estadísticas, módulos del Alumno, tests, minijuegos y toda la Aventura.
