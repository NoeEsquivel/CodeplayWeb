# Bloque 10.6 · Test con amigos

## Acceso del alumno

La cuarta opción de **Tests y Evaluaciones** ya abre el área `Test con amigos`. Desde ahí el alumno puede crear una mesa o entrar mediante la palabra clave o el código de una sala.

## Ser anfitrión

El anfitrión puede:

- Combinar hasta 20 bancos publicados.
- Elegir bancos oficiales, propios y bancos compartidos de su biblioteca.
- Crear una palabra clave de acceso.
- Configurar entre 5 y 120 segundos por pregunta.
- Definir la cantidad de puntos necesaria para ganar.
- Activar o desactivar el orden aleatorio.
- Abrir una mesa para un máximo de 30 participantes.
- Iniciar, pausar, reanudar y terminar la partida.
- Repetir la partida conservando a los participantes.
- Volver a configurar bancos, palabra, tiempo, meta y orden sin expulsar a los alumnos de la mesa.

Los bancos en borrador o inactivos no pueden utilizarse en una sesión en vivo.

## Unirse a un grupo

El alumno puede:

- Escribir la palabra clave o el código de sala.
- Ver la mesa de espera y a sus compañeros conectados.
- Reanudar automáticamente la comunicación después de una interrupción de red.
- Recuperar su participación mediante un token local de reconexión.
- Responder la misma pregunta y con el mismo límite de tiempo que los demás.
- Ver el resultado individual, los puntos sumados y la clasificación después de cada pregunta.

## Salón de estudio

La mesa está dibujada con HTML y CSS. Incluye:

- Pizarra para preguntas y resultados.
- Pupitres adaptables con los nombres de los participantes.
- Indicador de conexión.
- Temporizador.
- Clasificación en vivo.
- Controles exclusivos para el anfitrión.
- Trofeo y ganador al alcanzar la meta.

El diseño se adapta a escritorio, tableta y teléfono sin depender de una imagen fija.

## Seguridad

- Solo el anfitrión puede iniciar, pausar, reanudar, terminar, repetir o reconfigurar.
- La selección de bancos se vuelve a validar en el servidor.
- Una sesión solo admite alumnos de su mismo colegio.
- Las respuestas correctas permanecen ocultas hasta la fase de resultado.
- Los puntos de la trivia son independientes de los puntos CodePlay.
