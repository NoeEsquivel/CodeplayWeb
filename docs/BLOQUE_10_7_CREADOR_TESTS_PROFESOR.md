# Bloque 10.7: creador de tests del Profesor

## Funciones incorporadas

- Acceso **Tests y evaluaciones** desde el panel del Profesor.
- Creación, edición, publicación y eliminación de bancos propios.
- Preguntas de opción única, selección múltiple, verdadero/falso y respuesta escrita.
- Uso de bancos oficiales completos y copia selectiva de preguntas oficiales dentro de bancos propios, sin modificar el contenido original del sistema.
- Asignación de bancos a una o varias clases impartidas por el Profesor.
- Fechas opcionales de inicio y cierre tanto para la asignación como para la sesión.
- Creación de sesiones de clase en modalidad `trivia` o `examen`.
- Nombre de actividad, duración opcional del examen y puntaje configurable para cada pregunta.
- Selección obligatoria de clase y de bancos previamente asignados a esa clase.
- Monitor de código, estado, participantes, respuestas correctas y Puntos de Test.
- Controles de inicio, pausa, reanudación y cierre seguro de la sesión.

## Aislamiento

Toda escritura valida en el servidor que el Profesor pertenece al colegio y que imparte la clase seleccionada. Los bancos docentes solo pueden ser modificados por su creador. Los bancos del sistema son de solo lectura. Una sesión solo puede ser administrada por el Profesor que la creó, y el motor rechaza a cualquier alumno que no esté matriculado en la clase de la sesión.

La tabla `test_banco_clase` mantiene las asignaciones y su disponibilidad. Las fechas de sesión se guardan directamente en `test_sesiones` y también son verificadas al entrar o iniciar.

## Verificación

`TeacherTestCreatorTest` cubre creación y propiedad de bancos, rechazo de bancos ajenos, asignación a clases propias, rechazo de clases de otro colegio, creación programada de examen, acceso del alumno matriculado, rechazo del alumno ajeno y aislamiento del monitor de otra sesión.
