# Bloque 10.9: calificaciones, historial e impresión

## Resultado implementado

El panel del Profesor dispone de **Calificaciones e historial**, con acceso desde el panel principal y desde Tests y evaluaciones.

El reporte permite filtrar por alumno, clase, sesión, banco, modalidad y período. Los resultados pueden agruparse por alumno, clase, sesión, banco, Profesor o colegio. Cada agrupación muestra intentos, promedio, respuestas correctas e incorrectas y Puntos de Test.

## Historial permanente

Al entregar un examen o finalizar una trivia se guarda una copia académica de cada respuesta con:

- Pregunta y tipo de pregunta.
- Opciones existentes al responder.
- Respuesta correcta.
- Respuesta escrita o seleccionada por el alumno.
- Resultado correcto o incorrecto.
- Puntos logrados y puntos máximos.
- Tiempo y fecha de respuesta.
- Alumno, clase, banco, Profesor, colegio, sesión e intento.

Esta copia permanece aunque una trivia se repita. Cada repetición incrementa el número de intento y comienza con el marcador activo en cero, sin borrar los intentos anteriores.

En los exámenes, la calificación usa los puntos configurados por el Profesor para cada pregunta. En las trivias, la calificación representa el porcentaje de respuestas correctas; los puntos por rapidez se conservan por separado como Puntos de Test.

## Impresión y exportación

- **Reporte de clase:** requiere seleccionar una clase y respeta todos los demás filtros activos.
- **Examen individual:** muestra calificación, respuestas, soluciones correctas, fecha, tiempo y puntuación por pregunta.
- **CSV:** exporta exactamente los resultados visibles bajo los filtros elegidos.

Las hojas de impresión tienen estilos específicos para papel y evitan incluir menús o botones.

## Archivo y eliminación

El Profesor puede archivar y restaurar sus sesiones. También puede retirar del panel una sesión finalizada o cerrada. La retirada es lógica: oculta la sesión de los reportes ordinarios y conserva el historial académico para no destruir evidencia de calificaciones.

## Aislamiento

Todas las consultas parten de las clases vinculadas al Profesor autenticado, su colegio y las sesiones que él creó. Un identificador de otro Profesor, clase o colegio devuelve un resultado vacío o acceso no encontrado. La misma regla se aplica al detalle imprimible, la impresión de clase, el CSV y las acciones de archivo.

## Verificación automática

Las pruebas cubren:

- Cálculo ponderado de un examen.
- Conservación de la respuesta escrita.
- Detalle individual imprimible.
- Filtro e impresión por clase.
- Exportación CSV.
- Bloqueo de resultados de otro colegio.
- Archivo y restauración.
- Retirada lógica con historial conservado.
- Repetición sin pérdida del intento anterior.
