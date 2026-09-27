# Bloque 10.3 — Creador de bancos personales

## Biblioteca del alumno

“Estudia con ayuda de CodePlay” permite al alumno:

- Crear bancos con título, materia y descripción.
- Mantenerlos como borrador.
- Editar y duplicar bancos.
- Eliminarlos mediante borrado lógico.
- Probarlos antes de publicarlos.
- Publicarlos y generar un código de ocho caracteres.

Cada banco pertenece a un alumno y a su colegio. Todas las acciones vuelven a comprobar esa propiedad en el servidor.

## Tipos de preguntas

El editor admite:

- Opción única.
- Selección múltiple.
- Verdadero o falso.
- Respuesta escrita.

Las preguntas pueden incluir una explicación posterior, entre 5 y 300 segundos para responder y, en respuestas escritas, hasta veinte alternativas válidas. Las comparaciones escritas ignoran mayúsculas, espacios repetidos y acentos.

El alumno puede subir o bajar preguntas para establecer su orden. La práctica mezcla las preguntas al azar.

## Borradores y publicación

Un banco nuevo comienza en estado `borrador`. Cualquier cambio en información, preguntas u orden lo devuelve a borrador y desactiva su uso compartido. Esto evita que un código entregue una versión que aún no fue revisada.

Para publicar se exige al menos una pregunta. Solo un banco publicado puede generar o reactivar su código.

Al duplicar:

- Se copian título, materia, descripción y preguntas.
- La copia se guarda como borrador.
- No se copia el código compartible.

## Práctica previa

El creador puede probar un borrador o un banco publicado. La práctica:

- Presenta las preguntas aleatoriamente y una por una.
- Usa el tiempo definido en cada pregunta, con 20 segundos por defecto.
- Conserva el tiempo restante al recargar.
- Envía automáticamente una respuesta vacía al agotarse.
- Comprueba también el vencimiento en el servidor.
- Muestra aciertos, errores, respuestas correctas y explicaciones.
- No modifica los puntos CodePlay.

## Aislamiento

Un alumno diferente, incluso dentro del mismo colegio, recibe 404 si intenta consultar, editar o eliminar el banco. Las preguntas anidadas se comprueban contra el banco antes de modificarlas.

## Verificación

Las pruebas transaccionales cubren:

- Los cuatro tipos de pregunta.
- Respuestas alternativas y explicaciones.
- Reordenamiento.
- Borrador, publicación y código.
- Duplicado y eliminación.
- Aislamiento entre alumnos del mismo colegio.
- Temporizador visible y vencimiento confirmado por servidor.
- Conservación de puntos CodePlay.

