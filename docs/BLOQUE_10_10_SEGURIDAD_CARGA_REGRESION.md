# Bloque 10.10: seguridad, carga y regresión

## Cobertura terminada

Se añadió una batería de integración que trabaja contra la misma base de datos del proyecto bajo transacciones reversibles. Cubre dos colegios, varias clases y los cuatro roles: Alumno, Profesor, Administrador de Colegio y Master.

Las sesiones escolares validan simultáneamente colegio y membresía de clase. Un alumno del mismo colegio pero de otra clase no puede entrar; tampoco puede hacerlo un alumno de otro colegio. Los roles Administrador de Colegio y Master no pueden usar las acciones en vivo reservadas a alumnos y profesores.

## Carga y concurrencia

La prueba de carga crea una sesión con 30 alumnos y envía una ráfaga de respuestas sobre la misma pregunta. Comprueba que:

- Se registran exactamente 30 respuestas.
- El orden asignado por el servidor es único y consecutivo del 1 al 30.
- La puntuación de cada posición coincide con la fórmula oficial.
- No se generan duplicados.
- El resultado se publica al responder todos.

La operación usa bloqueo transaccional de la sesión y restricciones únicas en base de datos para serializar correctamente respuestas que llegan juntas.

## Manipulación y reconexión

El cliente solo puede enviar su respuesta. Aunque intente adjuntar puntos, tiempo, posición o resultado correcto, el servidor los recalcula y guarda sus propios valores. También se verifica el rechazo de respuestas duplicadas y respuestas enviadas después del cierre.

Cada reconexión rota el token. El token anterior deja de funcionar inmediatamente y ninguna sesión cerrada admite reconexión.

## Caducidad y limpieza

El comando `tests:limpiar-sesiones`:

- Cierra salas abandonadas después de 24 horas sin actividad.
- Respeta participantes que siguen activos.
- Cierra salas en espera cuyo período de disponibilidad terminó.
- Conserva respuestas, calificaciones e historial.
- Después de 365 días archiva la sesión y revoca código, palabra clave y tokens de reconexión.
- Nunca elimina físicamente el historial académico.

La tarea se ejecuta cada hora con protección para impedir dos ejecuciones simultáneas.

Ejemplo manual:

```console
php artisan tests:limpiar-sesiones --inactividad=24 --retencion=365
```

## Índices

Se añadieron índices para:

- Estado y última actualización de sesiones.
- Estado y disponibilidad de sesiones.
- Colegio, clase, tipo y estado.
- Estado y última actividad de participantes.
- Resultado y orden de respuestas por pregunta.

## Regresión funcional

La prueba abre como Alumno las vistas de Documentación, Tutoriales, Desafíos, Aventura y Mi Progreso. También verifica que registrar la lectura de un documento afecta únicamente al alumno autenticado.

Las pruebas anteriores de bancos personales, Código Amigo, Test con amigos, CodePlay en clase, calificaciones e historial permanecen en la regresión general.
