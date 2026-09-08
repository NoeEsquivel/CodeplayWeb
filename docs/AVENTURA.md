# Aventura: preparación del personaje

## Alcance actual

`GET /alumno/aventura` muestra el campamento. `POST /alumno/aventura/equipamiento` guarda la selección. Ambas rutas requieren sesión; la escritura utiliza CSRF y límite de solicitudes. La historia, los encuentros y el combate aún no se inician desde la interfaz.

- `AventuraController` valida la forma de la solicitud y actualiza el saldo de sesión.
- `AventuraCatalogo` define 38 objetos, sus precios y bonos, y comprueba las categorías, los límites y los desafíos requeridos.
- `AventuraService` mantiene el personaje, presupuesto y expedición en transacciones.
- `AventuraPersonaje` y `AventuraExpedicion` representan progreso permanente y equipo temporal.
- `resources/views/aventura/Index.blade.php`, `public/css/aventura.css` y `public/js/aventura.js` presentan y previsualizan la selección. La imagen del personaje es un SVG local.

## Economía

Se conserva `usuarios.puntos_totales` como total ganado. El equipo incrementa `puntos_usados`; cada expedición conserva su propia `inversion`. El presupuesto de reequipamiento es `puntos_totales - puntos_usados + inversion`. Guardar cobra o devuelve únicamente la diferencia con la compra anterior. No cambia otros gastos ni puntos obtenidos posteriormente en tutoriales o desafíos.

Se puede llevar una arma, una armadura, dos hechizos diferentes y hasta 20 pociones. Las compras son del equipo que se lleva en esa expedición, no una colección permanente. En preparación, vaciar y confirmar devuelve toda esa inversión. Los precios, desbloqueos y bonos provienen del servidor, nunca de los valores enviados por JavaScript.

Las escrituras bloquean primero la fila del usuario; una versión de expedición evita sobrescribir cambios de otra pestaña o cobrar dos veces un reintento de la misma solicitud.

## Reliquias

Se comprueba `desafio_recompensas.completado_primera_vez` del usuario y del desafío exacto. Desbloquear el acceso al nivel no basta. El desbloqueo permite comprar la reliquia en cada expedición, no la equipa gratis:

| Desafío | Objeto | Rareza |
| --- | --- | --- |
| 5 | Coraza del modelo de caja | Raro |
| 10 | Filo de la validación | Raro |
| 15 | Manto de la memoria | Super raro |
| 20 | Compilador del origen | Legendario, reliquia única |

## Experiencia y siguiente etapa

Nivel inicial 1, todos los atributos en 1, 100 de vida y 50 de maná. La vida máxima se calcula como `100 + 5 * (vitalidad total - 1)` y el maná como `50 + 2 * (inteligencia total - 1)`. La vitalidad e inteligencia totales incluyen el equipo.

La XP para subir es `100 + 50 * (nivel actual - 1)`; el excedente pasa al nivel siguiente. Cada atributo recibe independientemente `random_int(0, 2)` por subida. Se persiste el resultado y el último aumento. La XP no se obtiene al comprar ni al recargar.

Para integrar el futuro motor, invocar desde lógica confiable del servidor:

- `AventuraService::iniciar(usuarioId, expedicionId)`: pasa de preparación a activa e impide reequiparse.
- `AventuraService::otorgarExperiencia(usuarioId, evento, cantidad)`: llamar solo después de validar un evento real. Usar una clave única estable, por ejemplo `expedicion:42:encuentro:3`. La misma clave no entrega XP dos veces. No usar una clave nueva en cada reintento.
- `AventuraService::registrarDerrota(usuarioId, expedicionId)`: solo para una expedición activa cuya derrota haya calculado el servidor. Vacía el equipo y reintegra exactamente la inversión. Repetir el evento no duplica la devolución. El personaje no se modifica. La próxima visita al campamento abre una preparación vacía.

No hay rutas que acepten del navegador XP, victorias o derrotas. No añadirlas sin validar el estado del combate en servidor. Los consumos de pociones durante futuros combates deben reducir sus cantidades, pero conservar `inversion` para devolver también el coste de las consumidas al perder. Los efectos de hechizos y pociones están descritos en el catálogo; su ejecución corresponde al futuro motor de combate.

## Verificación

`tests/Feature/AventuraEquipamientoTest.php` usa SQLite en memoria con tres migraciones explícitas; no ejecuta las migraciones generales sobre la base de datos real. Si PHP no carga SQLite, habilitarlo para el proceso de pruebas con `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit`.

`tests/Browser/aventura.cjs` usa Playwright y el servicio PHP real con una base SQLite descartable en el directorio temporal. Configurar `NODE_PATH`, `PHP_BINARY` y opcionalmente `ADVENTURE_SCREENSHOTS`. Cubre selección, cantidades, persistencia, devolución y diseño móvil. El auxiliar solo admite archivos temporales con nombre `codeplay-aventura-test-*.sqlite`; no toca cuentas reales.
