# Bloque 10.8: CodePlay en clase

## Descubrimiento seguro

El apartado **CodePlay en clase** lista únicamente sesiones disponibles de las clases activas del alumno. Cada tarjeta muestra actividad, clase, Profesor, modalidad y estado. Las consultas filtran por `colegio_id` y por las membresías reales de `clase_alumno`; abrir directamente una URL ajena también es rechazado por el servidor.

## Trivia

- Pregunta y reloj sincronizados por el servidor.
- Una respuesta por alumno y pregunta.
- Puntuación por exactitud y orden de llegada.
- Al cerrar cada pregunta se muestra la solución, los puntos sumados, el total del alumno y la clasificación nueva.
- Reconexión mediante token rotativo sin duplicar la participación.

## Examen

- Cada alumno avanza de forma independiente.
- Las respuestas se guardan en la base de datos antes de avanzar.
- El alumno puede reconectarse y continuar con la primera pregunta pendiente.
- No existe clasificación entre alumnos.
- La calificación y las respuestas correctas permanecen ocultas mientras el examen está en progreso.
- El Profesor puede dejarlo sin duración general o fijar un máximo de 1 a 1440 minutos.
- El cierre de disponibilidad también actúa como fecha límite.
- La entrega puede ser manual, automática al vencer el límite o forzada al cerrar la sesión.
- Tras la entrega se muestra la calificación, aciertos y puntos obtenidos.

## Puntaje configurado por el Profesor

Al crear la sesión, el Profesor asigna entre 1 y 1000 puntos a cada pregunta seleccionada. Ese valor se copia a `test_sesion_preguntas.puntos_maximos`, de modo que posteriores cambios en el banco no alteren un examen ya abierto. La calificación se calcula como `puntos_obtenidos / puntos_posibles × 100`.

## Persistencia

- `test_banco_preguntas.puntos`: valor predeterminado de la pregunta.
- `test_sesion_preguntas.puntos_maximos`: valor congelado para esa sesión.
- `test_sesion_participantes.examen_iniciado_at`: comienzo individual.
- `test_sesion_participantes.examen_limite_at`: límite individual efectivo.
- `test_resultados_sesion`: avance y entrega final del alumno.

## Pruebas

`ClassroomCodePlayTest` comprueba aislamiento entre colegio y clase, datos visibles de actividad, puntaje por pregunta, calificación oculta, guardado individual, entrega manual, ingreso a examen activo, separación de respuestas y entrega automática por vencimiento.
