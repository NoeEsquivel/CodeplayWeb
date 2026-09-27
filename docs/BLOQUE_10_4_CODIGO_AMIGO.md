# Bloque 10.4 — Código Amigo y biblioteca

## Consulta previa

El alumno introduce un código y, antes de guardar el banco, ve únicamente:

- Nombre del autor.
- Nombre del banco.
- Materia.
- Descripción.
- Cantidad de preguntas.

No se envían correo, identificadores de cuenta, respuestas correctas ni información administrativa. El código solo funciona entre alumnos del mismo colegio que compartan una clase activa.

## Biblioteca

Al confirmar, el banco se relaciona con la biblioteca del alumno como contenido compartido. La llave compuesta de banco y usuario impide duplicados en la base de datos, y el servicio también devuelve un mensaje claro cuando el banco ya existe.

Desde la biblioteca se puede:

- Practicar el banco publicado.
- Crear una copia editable e independiente.
- Retirar el banco de la biblioteca.

Retirar un banco elimina únicamente la relación de biblioteca. No elimina ni modifica el original.

## Propiedad y copias

Un banco compartido es de solo lectura. El receptor obtiene 404 al intentar abrir las rutas de edición del original.

“Crear mi copia” produce:

- Un banco nuevo bajo el usuario receptor.
- Estado borrador.
- Copia de materia, descripción, preguntas, tiempos y explicaciones.
- Ningún código compartible heredado.

Los cambios posteriores en la copia no modifican el original.

## Desactivación

El creador dispone de “Dejar de compartir”. Esta acción conserva el código histórico, pero lo deja inactivo:

- Nuevos alumnos ya no pueden consultarlo ni añadirlo.
- No se eliminan las bibliotecas existentes.
- Los alumnos que ya lo guardaron pueden practicarlo mientras continúe publicado.
- El creador puede reactivar posteriormente el mismo código.

Editar un banco publicado también lo devuelve a borrador y desactiva su código hasta una nueva publicación.

## Verificación

Las pruebas transaccionales cubren:

- Ficha previa sin correo ni respuestas.
- Incorporación única y rechazo de duplicados.
- Retiro sin afectar el original.
- Práctica del banco compartido.
- Bloqueo de edición del original.
- Creación y edición independiente de una copia.
- Desactivación del código para nuevos alumnos.
- Conservación de bibliotecas existentes.

