# Bloque 10.2 — Menú Tests y Evaluaciones y Test CodePlay

## Funciones disponibles

La opción **Tests y Evaluaciones** del menú principal ya abre un centro con las cinco áreas previstas:

1. Test CodePlay.
2. Estudia con ayuda de CodePlay.
3. Tengo un Código Amigo.
4. Test con amigos.
5. CodePlay en clase.

En este bloque queda activa la primera. Las otras cuatro aparecen identificadas con el bloque que las habilitará para que la estructura del módulo permanezca clara.

## Test CodePlay

El alumno puede practicar con:

- Fundamentos HTML: bancos oficiales `html1` a `html4`.
- Diseño visual con CSS: bancos oficiales `css1` a `css4`.
- JavaScript básico: bancos oficiales `js1` a `js4`.
- Mundos de programación: bancos oficiales `mundo1` a `mundo5`.
- Test CodePlay completo: mezcla todos los grupos anteriores.

Los proyectos integrados no forman parte de la selección.

Cada intento:

- Selecciona diez preguntas activas al azar.
- Presenta una pregunta por pantalla.
- Registra la respuesta, el tiempo empleado y si fue correcta.
- Impide respuestas duplicadas mediante la relación única de sesión, pregunta y participante.
- Muestra al final aciertos, errores, nota sobre 100 y revisión de respuestas.
- Guarda un resultado de práctica en el historial nuevo.
- Mantiene `puntos_test` en cero y no cambia `usuarios.puntos_totales` ni `usuarios.puntos_usados`.

## Seguridad

La práctica pertenece al alumno autenticado y a su colegio. Otro alumno recibe una respuesta 404 al intentar acceder. Las respuestas correctas no se incluyen en la página de la pregunta; se consultan en el servidor al responder. Los identificadores enviados por el navegador no deciden qué pregunta corregir: el servidor toma la siguiente pregunta pendiente de la sesión.

Las entradas admiten como máximo diez valores de 500 caracteres y las acciones de inicio y respuesta tienen límites de solicitudes.

## Verificación

Las pruebas transaccionales comprueban:

- Presencia de las cinco opciones del menú.
- Presencia de las cinco categorías de Test CodePlay.
- Creación de exactamente diez preguntas.
- Recorrido completo con diez respuestas.
- Resultado final correcto.
- Conservación exacta de los puntos CodePlay.
- Rechazo de acceso entre alumnos.

