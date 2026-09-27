# Bloque 10.5 · Motor sincronizado para sesiones en vivo

## Comportamiento implementado

- Cada sala recibe un código único y puede exigir una palabra clave guardada de forma cifrada. El alumno puede localizarla mediante el código o escribiendo directamente una palabra clave que no sea ambigua dentro de su colegio.
- El anfitrión abre la sala, espera participantes e inicia la partida.
- La admisión queda bloqueada al iniciar y nunca supera el límite configurado ni 30 personas.
- El servidor abre una sola pregunta para toda la sala y fija su hora exacta de cierre.
- Las respuestas se registran una vez, con orden correlativo protegido por transacción e índice único.
- Las respuestas tardías, dobles o procedentes de usuarios ajenos a la sala son rechazadas.
- Solo las respuestas correctas reciben puntos:

  `redondear(100 × (participantes - posición + 1) / participantes)`

- Cuando todos responden o vence el tiempo, la pregunta pasa a `resultado`.
- Durante `resultado` cada jugador ve si acertó, sus puntos de la ronda, sus puntos acumulados, la respuesta correcta, la explicación y la clasificación completa.
- Tras el intervalo de resultados, el servidor abre la siguiente pregunta.
- Cuando existe una meta y se agotan las preguntas, se crea otra ronda con las mismas preguntas en orden aleatorio.
- La partida termina al alcanzar la meta o al agotar las preguntas cuando no existe meta.
- Un participante puede reconectarse con un token de un solo uso; al reconectar recibe uno nuevo.
- Solamente el anfitrión puede cerrar la sesión.
- Las sesiones y participantes siempre permanecen dentro del mismo colegio. Las sesiones de clase también validan la matrícula del alumno.

## Sincronización

La interfaz consulta periódicamente el estado de la sala. Esa consulta usa la hora del servidor para cerrar preguntas, publicar resultados y abrir la siguiente ronda. Los relojes del navegador son únicamente visuales y nunca deciden si una respuesta llegó a tiempo.

## API preparada para los siguientes bloques

- `POST /api/tests/en-vivo/salas`
- `POST /api/tests/en-vivo/salas/unirse`
- `POST /api/tests/en-vivo/salas/{sesion}/abrir`
- `POST /api/tests/en-vivo/salas/{sesion}/reconectar`
- `POST /api/tests/en-vivo/salas/{sesion}/iniciar`
- `GET /api/tests/en-vivo/salas/{sesion}/estado`
- `POST /api/tests/en-vivo/salas/{sesion}/responder`
- `POST /api/tests/en-vivo/salas/{sesion}/cerrar`

Estas rutas admiten alumnos y profesores autenticados. El servicio valida por separado quién puede crear, entrar, iniciar, responder o cerrar.

## Persistencia y seguridad

- El orden de respuesta tiene una restricción única por pregunta.
- El token de reconexión se guarda únicamente como hash SHA-256.
- La palabra clave se guarda con el sistema de hash de Laravel.
- Las respuestas correctas permanecen ocultas durante la pregunta y se publican solo en la fase de resultado.
- Los puntos de la sesión se guardan en `puntos_test`; no modifican los puntos CodePlay.
