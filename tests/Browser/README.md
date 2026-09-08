# Mini Arcade: pruebas del desafío 20

`mini-arcade.cjs` usa Playwright y un servidor temporal en `127.0.0.1`.
Renderiza la vista Blade real con datos de prueba y envía las comprobaciones
al servicio PHP `MiniArcadeValidator`. La entrega usa una respuesta simulada:
no inicia sesión con una cuenta real ni modifica puntos o tablas.

Configura `PHP_BINARY` con la ruta de PHP y `BROWSER_BINARY` con la ruta de
Chromium, Chrome o Edge. Playwright debe estar disponible para Node
(por ejemplo, mediante `NODE_PATH`). Ejecuta desde la raíz:

```text
node tests/Browser/mini-arcade.cjs
```

Por defecto bloquea recursos externos para comprobar el editor de texto de
respaldo. `ARCADE_ONLINE=1` permite cargar CodeMirror y comprueba los tres
editores completos. `ARCADE_SCREENSHOTS` indica una carpeta opcional para las
capturas y la descarga de prueba.

Casos incluidos: plantilla incompleta, solución correcta, movimiento y puntos
de culebrita, pared y reinicio, cartas distintas e iguales, seis parejas y
120 puntos, invalidación tras editar, entrega simulada, descarga jugable sin
servidor, recuperación del borrador y matriz de cuatro columnas a 390 px.

Las pruebas PHP se ejecutan con `php artisan test`. Cubren autenticación,
rechazo de peticiones y plantillas incompletas antes de acceder a la base de
datos, expresiones incorrectas y puntuación base de 1000.

## Alcance de la validación

Las seis tareas enseñan funciones con una sola expresión `return`. El servidor
interpreta únicamente ese pequeño lenguaje de sumas, comparaciones, objetos
`x/y` y operadores lógicos. No ejecuta JavaScript, PHP ni SQL del estudiante.
El navegador prueba además las funciones en documentos con sandbox, comprueba
elementos del juego y revisa varias entradas. No es una auditoría de cualquier
modificación posible al motor de los juegos; es una comprobación del ejercicio
guiado. Los casos de navegador verifican que los motores entregados funcionen.

El código se guarda como borrador en `sessionStorage` de la aplicación, separado
de las vistas del alumno. Cada vista se crea en un iframe nuevo sin
`allow-same-origin`, con una política de recursos restrictiva. Al cambiar de
pantalla se destruye la anterior y sus temporizadores. El puente solo admite
navegar a `menu`, `culebrita` o `memoria` y verifica emisor, token y origen.
