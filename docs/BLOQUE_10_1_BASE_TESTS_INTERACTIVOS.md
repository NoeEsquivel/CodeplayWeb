# Bloque 10.1 — Base de datos y reglas generales

## Alcance implementado

La base común permite construir las cinco experiencias de Tests y Evaluaciones sin cambiar el funcionamiento de los tests actuales.

- Bancos oficiales de CodePlay, bancos personales de alumnos y bancos creados por profesores.
- Preguntas de opción única, selección múltiple, verdadero/falso y respuesta directa.
- Biblioteca personal y códigos de ocho caracteres para compartir entre compañeros de una misma clase.
- Sesiones de práctica, amigos y clase; modalidades práctica, trivia y examen.
- Estados seguros: configuración, espera, activa, pausada, finalizada y cerrada.
- Máximo de 30 participantes.
- Historial de preguntas emitidas, respuestas, tiempo, orden, acierto y puntos otorgados.
- Resultados persistentes de examen por alumno, clase, sesión y colegio.
- Datos de reconexión almacenados solamente como hash.
- Palabras clave de sala almacenadas solamente como hash.

## Aislamiento

Los bancos personales pertenecen a un colegio y a su creador. Un código de alumno solo puede importarlo otro alumno activo del mismo colegio que comparta al menos una clase activa. Las sesiones de clase solo pueden crearlas profesores asignados a esa clase. Una sesión nunca puede usar un banco privado de otro colegio ni un banco que no esté en la biblioteca del anfitrión.

Los bancos oficiales son globales y de solo lectura. El usuario Master y el Administrador de Colegio no crean bancos ni sesiones de estudio con estas reglas.

## Puntuación

Los puntos obtenidos en tests viven en `puntos_test` dentro de participantes y resultados. No escriben en `usuarios.puntos_totales` ni en `usuarios.puntos_usados`.

En trivia, las respuestas correctas reciben un valor proporcional al orden de llegada:

`redondear(100 × (participantes - posición + 1) / participantes)`

Con dos participantes se obtienen 100 y 50 puntos. Una respuesta incorrecta siempre obtiene 0.

## Compatibilidad

La migración copia los tests y preguntas existentes a bancos oficiales, conservando intactas las tablas `tests` y `preguntas_test`. Así, Documentación, Aventura y el sistema de tests actual continúan usando su código existente mientras los bloques siguientes consumen la estructura nueva.

El importador `LegacyTestBankImporter` es idempotente y sirve para sincronizar una instalación recién cargada con seeders sin duplicar bancos ni preguntas.

## Verificación

- Migración aplicada a la base local.
- 17 tests originales convertidos en 17 bancos oficiales.
- 180 preguntas originales convertidas en 180 preguntas estructuradas.
- Pruebas unitarias para tipos de pregunta, puntuación y transiciones.
- Pruebas de integración preparadas para creación, códigos, límites e aislamiento. Estas se ejecutan cuando el entorno dispone de PDO SQLite.

