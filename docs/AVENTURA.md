# Aventura: preparación del personaje

## Alcance actual

`GET /alumno/aventura` muestra el campamento. `POST /alumno/aventura/equipamiento` guarda la selección. Ambas rutas requieren sesión; la escritura utiliza CSRF y límite de solicitudes. Están disponibles los 50 escenarios, la escena final, el trofeo permanente y la interfaz de combate animada de las etapas 9 y 10.

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

La XP para subir es `100 + 50 * (nivel actual - 1)`; el excedente pasa al nivel siguiente. Cada atributo recibe independientemente `random_int(0, 2)` por subida. Se persiste el resultado y el último aumento. La XP no se obtiene al comprar, recargar ni elegir un camino.

Para integrar el futuro motor, invocar desde lógica confiable del servidor:

- `AventuraService::iniciar(usuarioId, expedicionId)`: pasa de preparación a activa e impide reequiparse.
- `AventuraService::otorgarExperiencia(usuarioId, evento, cantidad)`: llamar solo después de validar un evento real. Usar una clave única estable, por ejemplo `expedicion:42:encuentro:3`. La misma clave no entrega XP dos veces. No usar una clave nueva en cada reintento.
- `AventuraService::registrarDerrota(usuarioId, expedicionId)`: solo para una expedición activa cuya derrota haya calculado el servidor. Vacía el equipo y reintegra exactamente la inversión. Repetir el evento no duplica la devolución. El personaje no se modifica. La próxima visita al campamento abre una preparación vacía.

No hay rutas que acepten del navegador XP, victorias o derrotas. No añadirlas sin validar el estado del combate en servidor. Los consumos de pociones durante futuros combates deben reducir sus cantidades, pero conservar `inversion` para devolver también el coste de las consumidas al perder. Los efectos de hechizos y pociones están descritos en el catálogo; su ejecución corresponde al futuro motor de combate.

## Fase 2: atlas y decisiones persistentes

- `GET /alumno/aventura/mapa` (`alumno.aventura.mapa`) muestra el atlas; `?region=1..5` solo cambia el territorio consultado, nunca el escenario real.
- `POST /alumno/aventura/iniciar` recibe expedicion_id/version y activa el equipo confirmado mediante el servicio anterior. Repetir el inicio no cura ni reinicia la partida. El cliente presenta una animación tras el clic, con una versión sin movimiento cuando el sistema la solicita.
- `POST /alumno/aventura/camino` recibe expedicion_id/version/camino (0, 1 o 2). AventuraExploracion bloquea el usuario, valida propiedad, estado, vida y versión antes de sortear y persistir el encuentro. Ambos POST requieren sesión, CSRF y throttle.
- La migración `2026_09_09_090000_add_aventura_exploracion.php` añade un campo JSON nullable a la expedición existente. No crea otro sistema de partidas ni modifica recursos. El campamento permanece disponible antes de aplicar la migración; el mapa informa de mantenimiento.
- `AventuraMundo` define las cinco regiones y es la única fuente de probabilidades: 1–3 tesoro, 4–30 narrativa, 31–50 gremio, 51–100 enemigo. Los escenarios 10/20/30/40 fuerzan jefe y el 50 dragón, independientemente de la tirada. Las pistas de los tres caminos no revelan el sorteo.
- `exploracion[escenario]` conserva camino, tipo, estado pendiente y fecha. No se vuelve a sortear al recargar, consultar otras regiones ni repetir solicitudes. No se envía un resultado futuro antes de elegir.
- Esta fase deliberadamente no resuelve encuentros ni permite avanzar saltándolos: no existe endpoint de victoria, entrega de oro o de marcar resuelto. Los motores de fases posteriores resolverán el encuentro y avanzarán el escenario bajo el mismo bloqueo y control de versión, conservando el historial. No presentar la fase 2 como un recorrido ya jugable de 50 combates.
- El atlas usa Blade/SVG local original (`aventura/partials/territorio`), CSS y JavaScript independientes del campamento. Muestra diez etapas por región, el lugar actual, recursos, mochila, efectos y mejoras. El tema consultado no altera la partida. No incluye sonidos ni librerías gráficas nuevas.
- PHPUnit comprueba los 100 valores posibles del sorteo, todos los jefes, las 50 asignaciones de región, autenticación, propiedad de partidas, doble clic, versiones antiguas y ausencia de premios o saltos manipulados. La prueba de navegador existente también cubre mapa móvil, cinco territorios, entrada, persistencia y campamento activo.

## Fase 3: combate del bosque

La migración `2026_09_09_120000_add_aventura_bosque_combate.php` amplía las tablas existentes `enemigos` (clave/rasgos) y `combates` (expedición, escenario, resumen). Inserta cinco enemigos y Silvar sin reemplazar filas anteriores. La batalla en curso se guarda en `exploracion[escenario].combate`; `Combate` guarda únicamente resultados terminados, con una restricción única por expedición/escenario. No se inventa un empate para representar una batalla en curso.

`AventuraCombate` valida y ejecuta `POST /alumno/aventura/combate`. Acepta expedicion_id, version y una acción: abrir, atacar, defender, hechizo, pocion, responder o continuar. Objetivo es el índice de un enemigo vivo; objeto es una clave del catálogo comprado. El servidor obtiene usuario, precios, cantidades, daño, pregunta y premios de sus propios datos. La ruta exige auth.custom, CSRF y throttle 60/minuto. Toda la ronda, inventario, registro y recompensa se confirman juntos, bloqueando primero al usuario. Una solicitud antigua o duplicada recibe 409.

Acciones y reglas:

- Ataque básico: `max(1, 8 + 2 * daño total - defensa enemiga)`.
- Hechizos: ejecutan los diez efectos y costes descritos en AventuraCatalogo. La defensa enemiga reduce el daño mágico en la mitad de su valor; fuego causa 50% adicional al hongo. Curaciones respetan la vida máxima. Barrera se consume en el siguiente golpe, sin acumular varias barreras.
- Defender: reduce a la mitad el daño directo de toda la ronda enemiga y recupera hasta 4 de maná. No evita el veneno.
- Pociones: consumen una unidad del inventario existente y un turno. No cambian la inversión original. Si no aportarían recuperación, se rechazan sin consumirlas.
- Tras actuar responden todos los enemigos vivos. Desde el escenario 5 hay 25% de grupos de dos enemigos (probabilidad interna del encuentro, no altera el reparto de eventos). Los fragmentos de slime no generan recompensas adicionales.
- Lobo: embestida cada tercer turno. Slime: división única bajo media vida. Duende: roba hasta 8 de oro una vez y se recupera al ganar. Avispa: veneno de tres turnos, 2 de daño por turno, cada tercera ronda. Hongo: defensa física alta y vulnerabilidad al fuego.
- Silvar aparece exclusivamente en el escenario 10 con 210 vida, 19 ataque y 5 defensa. Bajo media vida multiplica su ataque normal por 1.4. Cada tercer turno pregunta desde PreguntaTest, filtrando tests.modulo html1–html4. Acertar interrumpe; fallar activa daño doble (2.6 en fase dos), mitigable por defensa/barrera. Se conserva la pregunta al recargar. El navegador no recibe respuesta_correcta. No registra un test de documentación ni consume su intento diario. Si no hay preguntas, hace ataque normal; si se elimina la pregunta ya seleccionada, deja continuar sin penalización.
- Victoria: entrega una sola vez la XP mediante el registro de eventos existente y suma oro a la expedición, sin aumentar puntos educativos. `enemigos.recompensa_puntos` se interpreta como oro para estos enemigos de aventura. Elimina veneno/barrera del combate. Continuar solo es válido tras victoria y avanza un escenario, sin poder saltar el jefe. Tras el 10 se abre el mapa de Ruinas, cuyo combate llegará en su fase.
- Derrota: registra el resultado y llama a registrarDerrota, conserva XP/nivel, vacía los objetos de la expedición y devuelve exactamente su inversión. `GET /alumno/aventura/derrota/{expedicion}` verifica propiedad y estado, muestra el resultado y permite volver al campamento; visitarla no devuelve puntos otra vez.

No se cambian los porcentajes 3/27/20/50. Un encuentro de tesoro/narrativa/gremio sigue pendiente; esta fase no añade un botón para saltarlo. Por tanto, el recorrido continuo de los primeros diez escenarios depende también de las fases 4 y 5. No se han implementado el resto de regiones, el dragón, trofeo o mejoras aleatorias.

Pruebas: AventuraCombateTest utiliza SQLite en memoria y preguntas ficticias aisladas, nunca añade preguntas al banco real. Cubre reintentos, acceso entre usuarios, turnos, todos los hechizos, pociones y transacciones fallidas, varios enemigos, rasgos, preguntas, victoria, derrota, XP y devolución. La prueba de navegador usa únicamente su archivo temporal permitido e incluye batalla, recarga, jefe, vista móvil y regreso al campamento tras perder.

## Fase 4: encuentros, preguntas y tesoros

No necesita migraciones ni un segundo banco de preguntas. `AventuraRelatos::todos()` define doce relatos extensibles. `AventuraEventos` resuelve sus decisiones y tesoros sobre `exploracion[escenario].narrativa` de la expedición existente. `AventuraExploracion` elige y guarda el relato junto al camino, evitando repetirlo hasta agotar el catálogo. Los encuentros guardados antes de esta fase muestran «Acercarme», que los prepara una sola vez. Leer el mapa no sortea, cobra ni entrega premios.

`POST /alumno/aventura/evento` exige sesión, CSRF y límite de 60 solicitudes/minuto. Acepta expedición, versión y una acción de la lista cerrada: descubrir, preguntar, ayudar, ignorar, abrir, responder, equipar o continuar; opcionalmente respuesta a–d o hechizo a reemplazar. No acepta precios, preguntas elegidas, premios, resultados ni usuario del cliente. Se bloquea la fila del usuario, se valida propiedad y versión y se confirma toda la operación en una transacción. La repetición recibe 409 y no duplica premio, objeto ni experiencia.

Flujo narrativo: decisión → pregunta → resultado → siguiente escenario. Consultar contexto no revela la respuesta. Seguir de largo desde la decisión resuelve sin premio ni penalización. Una pregunta ya aceptada debe resolverse. Se guarda su ID y se reutiliza al recargar. `AventuraPreguntas` consulta `PreguntaTest` y `tests.modulo`: HTML en escenarios 1–10, CSS en 11–20, JavaScript en 21–30 y conceptos del mundo de programación en 31–50; si falta ese grupo, busca los anteriores. Esta progresión usa temas, porque el banco no tiene una dificultad numérica fiable. No modifica intentos ni resultados de los tests de documentación. Si no hay preguntas o se elimina una pendiente, se permite continuar sin penalización ni recompensa. Nunca se envía `respuesta_correcta` antes de responder.

Los fallos muestran la respuesta correcta escapada como texto y una consecuencia: nada, pérdida de hasta 5% de vida máxima, hasta 10 de oro de la partida o aparición de un enemigo del bosque. Si la vida llega a cero se reutiliza la derrota y devolución existentes. En regiones cuyo motor aún no existe, la consecuencia de enemigo se sustituye por la marcha del personaje. Las apariciones conservan el relato y su explicación antes de entrar al combate.

El reparto principal sigue siendo exactamente 3% tesoro / 27% narrativa / 20% gremio / 50% enemigo. Dentro de un cofre del bosque: 8% Slime Mimic, 27% sello con pregunta, 65% apertura directa. El Mimic usa el modelo de especie slime existente y el mismo combate, división, recompensas y registro; añade 15 de oro a su botín. En regiones posteriores no se activa el Mimic hasta ampliar el combate: esas tiradas pasan a sello. Estas probabilidades internas no alteran la categoría de evento sorteada al elegir camino.

Las recompensas se calculan en el servidor, con seis alternativas equiprobables: oro, XP permanente, poción, equipo, mejora de partida o inspiración. El oro no se convierte en puntos educativos. La experiencia usa el registro idempotente `expedicion:<id>:evento:<escenario>`. El equipo se sortea entre las rarezas existentes: normal 60%, raro 25%, super raro 10%, ultra raro 4%, legendario 1%, excluyendo reliquias cuyo desafío no esté completado. Las pociones se eligen entre las cuatro existentes.

`AventuraInventario::recibir` añade el botín a la tabla `inventario`, sin cambiar la inversión reembolsable. Respeta otras expediciones y objetos previos sin expedición. Se mantiene el límite de 20 pociones y una copia de cada arma/armadura/hechizo; si no cabe o ya existe, entrega 15 de oro en su lugar. Las pociones quedan listas para usar; el equipo se guarda sin equipar y puede equiparse en la pantalla del resultado. Si hay dos hechizos, se elige cuál reemplazar. El objeto anterior permanece en la mochila. Cambiar equipo ajusta los máximos y limita recursos, sin curar ni restaurar maná gratis. No habilita compras durante la expedición ni altera el precio de devolución al perder o reiniciar.

`AventuraMejoras` centraliza siete mejoras acumulables hasta su límite: daño +10% (máximo 30%), vida máxima +10% (30%), robo de vida +5% (15%), probabilidad de crítico +5% (15%, daño ×1.5), regeneración tras victoria +5% (15%), XP +10% (30%) y resistencia al daño directo +5% (20%). Alcanzado un límite, un nuevo premio de ese tipo da 15 de oro. Robo de vida calcula sobre el daño realmente quitado al enemigo, sin aprovechar daño sobrante. La vida máxima se comparte con el campamento y motor de combate; su aumento recupera solo la diferencia de máximos. Inspiración aumenta el daño 20% durante el siguiente combate y desaparece al terminarlo; no se acumula consigo misma. Todas estas ventajas desaparecen al cerrar la partida, conservando únicamente progreso permanente.

La vista `aventura/partials/evento` mantiene las escenas centradas y oscurecidas de la etapa anterior, con símbolos SVG locales, opciones, contexto, pregunta y recompensa. No usa modales para los eventos. Volver al mapa después del resultado avanza una sola etapa; los gremios continúan pendientes de fase 5. Esta fase no implementa todavía un recorrido continuo de 50 niveles.

`AventuraEventosTest` comprueba preguntas persistentes, todos los relatos, encuentros antiguos, resultados y consecuencias, cofres, Mimic, inventario y límites, equipamiento, mejoras funcionales, versiones y aislamiento de cuentas. La prueba de navegador también recorre un encuentro antiguo, contexto, pregunta, recarga, recompensa, equipo legendario y vuelta al mapa en escritorio y móvil. Todo se ejecuta en SQLite temporal sin tocar partidas reales.

## Fase 5: Gremio de Aventureros

`AventuraGremio` define y ejecuta los encargos, reutilizando `exploracion[escenario].gremio` de la expedición existente. No añade tablas, usuarios, inventarios ni bancos de preguntas. Cada visita ofrece cuatro encargos diferentes, sorteados y guardados una vez al elegir camino. Una visita guardada antes de esta fase permite «Consultar el tablón» para generarlo una vez. Consultar la vista no modifica ofertas ni premios. La probabilidad principal del gremio sigue siendo 20%, centralizada en AventuraMundo.

Tipos disponibles:

| Encargo | Objetivo | Premio base |
| --- | --- | --- |
| Patrulla del sendero | Derrotar 3 enemigos desde la aceptación | 40 XP + 20 oro |
| Huellas entre los árboles | Derrotar 1 lobo desde la aceptación | 25 XP + 15 oro + Poción de vida |
| La inscripción del gremio | Resolver una pregunta del banco actual, un intento | 20 XP + 10 oro |
| Aprender para ayudar | Acertar 2 preguntas después de aceptar | 40 XP + 20 oro + mejora de XP de la fase 4 |
| Un botiquín para el camino | Entregar 1 Poción de vida existente | 20 XP + 15 oro |
| Reconocer el terreno | Avanzar 2 escenarios desde la aceptación | 25 XP + 15 oro + Inspiración |
| Silenciar las raíces | Vencer a Silvar después de aceptar | 80 XP + 40 oro + Coraza de hierro |
| Suministros perdidos | Encontrar una poción en evento o tesoro posterior | 25 XP + 15 oro + Poción de maná |

El servidor guarda una línea base al aceptar y calcula el avance desde el historial real, sin aceptar contadores del navegador. Enemigos cuentan únicamente en batallas ganadas, excluyendo fragmentos con XP cero. Preguntas incluyen aciertos narrativos, de encargos y del jefe (AventuraCombate guarda `aciertos`). Hallazgos cuentan premios efectivamente recibidos en eventos/tesoros, nunca compras o recompensas del gremio. La entrega consulta la cantidad actual en `inventario`; solo cobra si puede consumir una unidad en esa misma transacción. La inversión original reembolsable no cambia.

Se admiten hasta tres encargos activos por expedición. Los listos para cobrar ocupan espacio hasta cobrarlos. Abandonar requiere confirmación visual, libera el espacio y no permite aceptar otra vez ese encargo del mismo tablón. También liberan espacio los encargos fallidos, cancelados o cobrados. Una pregunta fallada muestra la respuesta correcta como texto y no quita recursos; una pregunta eliminada cancela sin premio ni penalización. La pregunta seleccionada persiste al recargar y su respuesta correcta no se envía antes de responder.

`POST /alumno/aventura/gremio` requiere sesión, CSRF y throttle 60/minuto. Valida expedición, versión y acción cerrada (abrir, aceptar, salir, responder, cobrar, abandonar, equipar); origen y clave identifican un encargo ya aceptado de esa misma partida. Bloquea primero al usuario, comprueba propiedad/estado/versión y aplica la operación en una transacción. Durante un combate no terminado rechaza gestiones del diario. Un doble clic o versión antigua recibe 409. La XP usa la clave única `expedicion:<id>:gremio:<escenario_origen>:<clave>`, y el estado cobrado impide también duplicar oro, objetos y mejoras. No acepta cantidades, premios, precios ni objetivos desde el cliente.

El oro es de la partida; la XP usa los bonos existentes y permanece. Los objetos entran mediante `AventuraInventario::recibir`, con sus límites y bloqueos; si están repetidos o no caben, se conceden 15 de oro. La armadura de recompensa puede equiparse desde el diario utilizando la misma lógica de botín, sin curar gratis ni cobrar puntos. Las mejoras reutilizan AventuraMejoras e Inspiración sin nuevos efectos paralelos.

`aventura/partials/gremio` muestra el tablón en la escena centrada. `aventura/partials/diario` presenta progreso, preguntas, cobro e historial tanto allí como entre encuentros y después de una victoria. Marcharse conserva los encargos; se puede cobrar desde el mapa sin encontrar otro gremio. Salir resuelve la visita y avanza solo un escenario. Reiniciar o perder abre otra expedición sin encargos, conservando únicamente el progreso permanente y la devolución existentes.

No se ofrecen misiones de lobo/Silvar desde el escenario 10, ni de avanzar dos escenarios después del 48. La fase no incorpora enemigos de otras regiones; el primer territorio ya tiene resueltos los cuatro tipos de encuentro y su jefe. Las fases posteriores ampliarán el recorrido de combate, el dragón y su final.

Pruebas: AventuraGremioTest cubre los ocho tipos, persistencia de ofertas, compatibilidad con visitas antiguas, progreso posterior a aceptar, entrega atómica, doble cobro, preguntas, límites, abandono, aislamiento de usuarios, protección durante combate y reinicio. La prueba de navegador recorre aceptación, pregunta, cobro, salida y entrega desde el diario fuera del gremio, con capturas en escritorio y móvil. Utiliza exclusivamente SQLite temporal.

## Fase 6: bestiario regional y guardianes

La migración `2026_09_09_180000_add_aventura_regiones.php` añade 20 enemigos normales y tres jefes a `enemigos`, reutilizando sus columnas y sin cambiar personajes, partidas, inventarios o saldos. Son 25 enemigos normales y cuatro guardianes en total. La inserción es transaccional y el rollback rechaza eliminar enemigos asociados a combates históricos o guardados. Aplicar solo esta migración sobre las fases anteriores, después de conservar una copia local de seguridad.

`AventuraCombate::REGIONES` y `JEFES` centralizan qué registros participan en cada escenario. La selección utiliza la región actual de la partida, no el territorio que el usuario consulte en el mapa. Cada región tiene cinco enemigos. Los escenarios 10/20/30/40 generan un único jefe obligatorio; el 50 no permite iniciar combate todavía. Los datos del enemigo se guardan al abrir la batalla, conservando las batallas anteriores. Estadísticas normales escalan dentro de su región: +3 vida y +2 XP por escenario desde el primero de la región; +1 ataque cada dos escenarios. Los jefes tienen valores fijos. La probabilidad de grupos sigue siendo 25% desde el escenario 5.

| Región | Enemigos |
| --- | --- |
| Ruinas | Momia del archivo, Vampiro de las ruinas, Espectro de tinta, Centinela de piedra, Nigromante del archivo |
| Pantano | Sanguijuela gigante, Bruja del pantano, Slime de cieno, Saqueador del fango, Basilisco de musgo |
| Montañas | Yeti de la ventisca, Hechicero de escarcha, Gólem de hielo, Lince de cristal, Alma de la nieve |
| Tierras del Dragón | Salamandra de ceniza, Caballero de obsidiana, Cultista del volcán, Devorador de brasas, Acechador de lava |

Mecánicas nuevas: vampiros recuperan 35% del daño efectivamente causado, sin curarse por daño absorbido; magos drenan maná en turnos pares y la defensa lo evita; resistencia mágica reduce 40% y resistencia física 35% después de la defensa; invocadores generan una sola vez un eco en su tercer turno, con máximo de tres entidades para invocar, sin premio ni progreso de misiones y sin ataque inmediato. El robo de pociones resta una unidad real, conserva su fila y la devuelve una sola vez al ganar; al perder se aplica el cierre y reembolso existentes. El veneno regional causa de 2 a 6 de daño por turno según región. Se mantienen división, embestida, robo de oro y vulnerabilidad al fuego. Los Mimics y las consecuencias narrativas de combate ya funcionan en todas las regiones previas al 50.

Guardianes nuevos:

- **Aster (20):** 300 vida, 25 ataque, 8 defensa; 180 XP y 90 oro. Su sello reduce 25% del daño físico en turnos pares. Fallar Sello del olvido también drena hasta 6 maná.
- **Náyade (30):** 420 vida, 33 ataque, 10 defensa; 260 XP y 120 oro. Recupera 25% del daño real de sus golpes normales. Fallar Marea tóxica aplica veneno de 4 durante tres turnos.
- **Bóreas (40):** 560 vida, 41 ataque, 12 defensa; 360 XP y 160 oro. Es vulnerable al fuego (+50%) y sus ataques normales drenan maná si no te defiendes. Su especial es Tormenta de cristal.

Todos conservan la fase de furia bajo media vida (+40% al ataque normal) y el desafío educativo cada tercer turno. Acertar interrumpe el especial; fallar aplica ataque ×2, o ×2.6 bajo media vida, mitigable por armadura, defensa y barrera. Las preguntas usan AventuraPreguntas: HTML, CSS, JavaScript y conceptos según el escenario, con fallback al banco disponible. El mensaje y la vista muestran el nombre del jefe y su ataque real, sin textos fijos de Silvar. No se crean preguntas nuevas ni se exponen respuestas antes de contestar.

La interfaz mantiene la cámara de combate sin mapa, temas de color por región y siluetas SVG locales por mecánica y jefe en `aventura/partials/enemigo-regional`. Inspeccionar explica cómo contrarrestar cada enemigo. Continuar tras un jefe indica la siguiente región y avanza exactamente un escenario. No se modifica la distribución de eventos 3/27/20/50.

`AventuraRegionesTest` recorre todos los escenarios 11–49, comprueba cinco enemigos por región, jefes exclusivos, robo/restitución sin duplicación, invocaciones, drenaje, absorción, preguntas y mecánicas de fase dos. Una simulación con atributos base 10, arma/armadura legendarias y respuestas correctas vence a los cuatro jefes sin vida ilimitada, verificando una ruta viable; no pretende representar todos los estilos ni sustituir las futuras pruebas de equilibrio del Dragón. Playwright recorre los tres jefes nuevos y el combate de la quinta región en escritorio y móvil. Estas pruebas usan datos temporales aislados.

## Fase 7: Ignar, Dragón del Último Horizonte

La migración `2026_09_10_090000_add_aventura_dragon.php` agrega un solo registro al bestiario existente: 1000 vida, 65 ataque, 18 defensa, 600 XP y 300 oro de expedición. No añade tablas ni modifica puntos, personajes, equipo o partidas. El rollback rechaza eliminarlo si existe un combate histórico o una batalla guardada que lo referencia. Aplicar únicamente esta migración después de una copia de seguridad. Si falta el registro, abrir el encuentro devuelve mantenimiento sin alterar la partida.

`AventuraMundo` ya reservaba el escenario 50 para el tipo `dragon`; `AventuraCombate` ahora lo acepta exclusivamente allí, con un solo jefe y sin modificar el reparto 3/27/20/50 de los escenarios normales. Se reutilizan turnos, preguntas, equipamiento, pociones, mejoras, derrota, devolución e historial. Entrar no restaura recursos. Las batallas ya abiertas conservan su instantánea de estadísticas.

`AventuraDragon` centraliza los umbrales, nombres y frecuencia de especiales, compartidos por el servidor y la presentación:

| Fase | Vida restante | Regla |
| --- | --- | --- |
| Escamas de obsidiana | Más de 2/3 | Reduce 30% el daño físico posterior a defensa; especial cada tercer turno. |
| Alas de la tormenta | Hasta 2/3, más de 1/3 | Golpe normal ×1.15; especial cada tercer turno. |
| Corazón del volcán | Hasta 1/3 | Golpe normal ×1.35; especial cada segundo turno. |

El fuego causa 50% menos daño en todas las fases. Los especiales son Aliento del olvido, Tormenta de runas y Último eclipse: un fallo causa ataque ×2/2.3/2.6 según la fase, menos armadura, y después se aplican resistencia, defensa y barrera existentes. Las fases se calculan tras el golpe del jugador: si cruza un umbral, puede cambiar la respuesta del Dragón ese mismo turno, como explica el aviso visual.

Acertar interrumpe el especial, recupera hasta 6 maná y deja `expuesto` al Dragón: el siguiente golpe gana 50% de daño y consume la apertura. Defenderse, curarse o beber no la consumen; varias respuestas no acumulan aperturas. Una pregunta eliminada disipa el ataque sin penalizar ni conceder apertura/maná. La selección usa el banco existente y guarda IDs utilizados para evitar repetirlos mientras haya alternativas disponibles; no publica la respuesta correcta antes del envío. Sin preguntas válidas se usa un golpe normal.

La cámara existente muestra una silueta SVG original, inspección, fase actual, aviso de intención y apertura disponible. No implementa todavía la interfaz animada prevista para la etapa 9 ni sonidos de la 10.

Ganar registra `Combate` con escenario 50 y la recompensa idempotente `expedicion:<id>:combate:50`. El evento queda resuelto en la expedición; recargar conserva la victoria y no vuelve a cobrar. No existe escenario 51: `continuar` lo rechaza y la vista ofrece volver al campamento. La expedición permanece activa con el resultado guardado hasta reiniciarla mediante el flujo existente. La escena de Noé Esquivel, el cierre de victoria y el trofeo permanente del menú se implementarán en la etapa 8; esta etapa conserva la evidencia de victoria para esa integración.

`AventuraDragonTest` comprueba una victoria con atributos base 20, arma/armadura legendarias, Supernova, pociones, daño +20%, resistencia +10% y respuestas correctas. Utiliza recursos máximos reales y consume pociones; no usa vida ilimitada ni altera la vida del Dragón para la prueba de equilibrio. También comprueba derrota con preparación básica, devolución única, fases, resistencia al fuego, preguntas persistentes, absorción, ausencia de preguntas, aislamiento de usuarios y rechazo del escenario 51. Es una ruta viable, no una garantía de victoria para todas las distribuciones aleatorias de atributos. Las pruebas de navegador incluyen el Dragón en escritorio/móvil y una instantánea controlada de victoria para verificar su presentación y recarga.

Verificación al terminar esta fase: 66 pruebas PHPUnit, 1470 aserciones, recorrido de navegador completo sin errores de JavaScript. Todas las acciones de juego de prueba usan SQLite aislado. La migración local se aplicó después del respaldo y se compararon íntegramente saldos, personajes, expediciones e inventario antes/después, sin cambios en esos datos.

## Fase 8: desenlace, trofeo y nueva aventura

`AventuraFinal::victoria(usuario, expedicion opcional)` consulta el historial existente de `Combate`: exige escenario 50, victoria, enemigo con clave dragon, vida del enemigo cero, vida del usuario positiva y expedición del mismo usuario. Ese registro persistente y único por expedición/escenario es la fuente del trofeo. No añade una tabla de logros ni una bandera duplicada. Las victorias de la fase 7 se reconocen inmediatamente, aunque la partida ya se haya reiniciado. Reiniciar, cerrar sesión o volver a jugar no elimina el historial.

El controlador redirige a `GET /alumno/aventura/final/{expedicion}` al vencer; visitar el mapa de una expedición ya ganada también lleva al final. La ruta comprueba propiedad y victoria, sin crear partidas ni cobrar premios. La escena tiene tres pasos: silueta misteriosa, presentación y mensaje de Noé Esquivel, trofeo. El jugador controla el ritmo y puede saltar al logro. Sin JavaScript todo el texto y los enlaces siguen disponibles; `prefers-reduced-motion` suprime animaciones. No usa sonido ni la futura cámara animada de combate.

El menú `/inicio-alumno` muestra una sola tarjeta «Conquistador de la Aventura» si hay una victoria verificable, con enlace para recordar el primer final. La consulta se realiza con el usuario de sesión, nunca con un usuario enviado por formulario. El logro no depende de caché, almacenamiento del navegador ni variables de sesión que puedan desaparecer al salir.

«Terminar aventura y volver al campamento» envía un POST con CSRF a `/alumno/aventura/completar`, bajo autenticación y límite de diez solicitudes/minuto. Reutiliza el cierre de `AventuraService`, con bloqueo del usuario, propiedad y versión. Antes de cerrar exige una victoria real del Dragón. El estado final es `victoria` (la columna existente admite texto); se devuelve la inversión original y se eliminan únicamente los objetos y recursos de esa expedición. Nivel, experiencia, historial y otros gastos permanecen. La próxima preparación se crea mediante el servicio existente. Un reintento de una expedición ya cerrada no devuelve puntos ni afecta compras de una nueva partida. Recordar un final antiguo solo presenta enlaces, sin formulario de cierre.

Esta fase no requiere migración ni cambia datos al desplegarse. Las pruebas añaden cierre, trofeo en menú, persistencia tras sesión nueva, repetición tras compras de otra expedición y acceso entre cuentas. Se mantienen las pruebas del Dragón y fases anteriores. Todas las pruebas de juego se ejecutan en SQLite aislado.

## Fase 9: escenario y secuencia visual del combate

`aventura/partials/arena.blade.php` presenta al explorador en primer plano y a los enemigos al fondo, con cinco escenarios originales de SVG/CSS (bosque, ruinas, pantano, hielo, volcán). Reutiliza las ilustraciones del bestiario mediante `enemigo-arte`; no utiliza imágenes de Pokémon. Seleccionar un enemigo en el escenario sincroniza el objetivo de las tarjetas de inspección. Atacar y defender están en el menú de cuatro comandos; Hechizos y Mochila despliegan el contenido existente. Las reglas, costes y cantidades no cambian.

En el escenario del bosque, la cámara es el único panel operativo: reúne escena, objetivo, descripción breve, vida, maná, narración, ritmo, ataques, hechizos, mochila y defensa. Los paneles técnicos antiguos se mantienen fuera del flujo visual únicamente para compatibilidad y se ocultan en esta vista. La narración permanece aproximadamente tres segundos por paso en ritmo normal; el ritmo rápido reduce la espera y «Ver resultado» permite saltarla. La opción `prefers-reduced-motion` conserva la accesibilidad.

`AventuraCombate::secuencia()` expone una secuencia transitoria por solicitud, construida dentro de la transacción: acción del jugador, acción de cada enemigo vivo, invocación, efectos al cerrar turno, pregunta o resultado final. Cada paso contiene tipo, actor, narración y una instantánea limitada de vida/maná y vida/nombre/clave/máximo de los enemigos. No envía el banco de preguntas, respuestas correctas pendientes, datos de otros usuarios ni estado privado de la partida. El historial persistente sigue siendo el existente; las instantáneas visuales no se almacenan en otra tabla ni generan recompensas.

El controlador de combate devuelve `secuencia` junto a la URL existente. `aventura-mapa.js` guarda primero el turno mediante POST, obtiene después el HTML actual del servidor y reproduce los pasos con `aventura-arena.js`. Al terminar reemplaza únicamente el contenido del mapa, conservando la página, y enlaza de nuevo sus controles con la versión actual. Al cambiar de región también cambia el tema. La derrota y el final del Dragón se abren después de la secuencia, en sus rutas existentes. Entrar al primer combate todavía puede cargar la página inicial del escenario.

Las barras y números muestran valores calculados por el servidor: el cliente no calcula impactos ni decide victorias. Se animan movimientos, golpes, curación, defensa, hechizos y caída. Nuevos enemigos se incorporan desde el HTML autorizado. El texto de los pasos se inserta con `textContent`. No se ejecuta código procedente de respuestas o narraciones.

Durante el envío y la reproducción se bloquean los comandos. Cada paso de la narración queda fijo hasta que el jugador pulsa «Continuar»; «Ver resultado» permite resolver la secuencia de una vez sin enviar un segundo turno. La cámara incluye un control de sonido que genera efectos breves con Web Audio tras una interacción del usuario, sin archivos externos ni permisos especiales. `prefers-reduced-motion` evita animaciones y acorta la transición. Si falla la carga visual después de guardar, los comandos quedan deshabilitados y se solicita recargar para recuperar el estado persistido. Propiedad, CSRF, límites de solicitudes, versión e idempotencia permanecen en el servidor. No necesita migraciones.

Las pruebas comprueban orden jugador/enemigo, instantáneas coherentes y ausencia de respuestas pendientes. El navegador prueba hechizos, consumo de pociones, bloqueo y salto de animaciones, continuidad sin recarga, objetivos múltiples, movimiento reducido, preguntas, derrotas, regiones y final. Las cuentas y bases usadas son temporales.

## Verificación

`tests/Feature/AventuraEquipamientoTest.php` usa SQLite en memoria con tres migraciones explícitas; no ejecuta las migraciones generales sobre la base de datos real. Si PHP no carga SQLite, habilitarlo para el proceso de pruebas con `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit`.

`tests/Browser/aventura.cjs` usa Playwright y el servicio PHP real con una base SQLite descartable en el directorio temporal. Configurar `NODE_PATH`, `PHP_BINARY` y opcionalmente `ADVENTURE_SCREENSHOTS`. Cubre selección, cantidades, persistencia, devolución y diseño móvil. El auxiliar solo admite archivos temporales con nombre `codeplay-aventura-test-*.sqlite`; no toca cuentas reales.

## Fase 1: integración del inventario y estado persistente

Aplicar únicamente `2026_09_08_140000_integrate_aventura_inventory.php` después de conservar una copia de la base y autorizar su despliegue. La migración registra los 38 objetos del catálogo en `items` mediante `clave_catalogo`, transfiere el equipo de expediciones abiertas a la tabla existente `inventario` y elimina la columna JSON anterior. No cambia saldos, inversión ni atributos. Antes de alterar el esquema comprueba que no haya varias expediciones abiertas por usuario y que el equipo sea válido.

La tabla `inventario` es la fuente única de cantidades después de migrar. `expedicion_id` identifica a qué partida pertenece cada objeto y `posicion` indica arma, armadura, hechizo o poción; una posición nula queda disponible para futuro botín sin equipar. La restricción original usuario/item se conserva. `AventuraInventario::guardar` solo se invoca desde preparación bajo la transacción y bloqueo del usuario; rechaza apropiarse de objetos ajenos a esa expedición. Los objetos preexistentes sin expedición no se eliminan.

`AventuraExpedicion::equipo` es ahora un atributo calculado, compatible con el campamento. No almacena otro inventario. El catálogo PHP sigue definiendo precios y reglas autorizadas; `items` aporta identidad persistente y metadatos. Los futuros cambios al catálogo deben actualizar también sus registros mediante una migración explícita. No editar precios desde solicitudes del navegador.

Mientras la migración está pendiente, el servicio detecta la columna antigua y conserva el comportamiento previo de lectura y compra. Es una transición de despliegue, no dos inventarios simultáneos. No iniciar el nuevo motor sobre el esquema antiguo.

La expedición incorpora `escenario` (inicial 1, distinto del nivel permanente), `vida_actual`, `mana_actual`, `oro`, `efectos`, `mejoras` e `iniciada_at`. En preparación se calculan los recursos completos según personaje y equipo. Una partida activa conserva sus recursos al recargar o repetir `iniciar`: no se cura ni reinicia. Los efectos y mejoras solo se almacenan en esta fase; sus fórmulas llegarán con el motor de combate. El oro no modifica puntos educativos.

Al perder se eliminan únicamente los objetos asociados a esa expedición, sus efectos, mejoras y oro. Se devuelve la inversión original, aunque se hayan consumido pociones. No se modifican nivel, experiencia ni otros gastos. Las operaciones de inicio y derrota continúan siendo internas: no hay nuevas rutas que permitan al cliente declarar una victoria, derrota o recompensa.

El rollback reconstruye el equipo de preparación y se detiene si hay una partida activa o botín sin equipar para evitar pérdidas silenciosas. En MySQL las operaciones de esquema no son transaccionales: ante una interrupción de migración se debe inspeccionar el estado y usar la copia previa, no repetir a ciegas. Las pruebas de integración utilizan seis migraciones explícitas y comprueban ida/vuelta, compras antiguas, saldos, aislamiento entre usuarios, consumos, derrota y reanudación. La fase 2 añadirá mapa e inicio desde la interfaz; no están implementados en fase 1.
# Reinicio y presentación de escenas

El menú de aventura permite confirmar un reinicio desde el escenario 1. `POST /alumno/aventura/reiniciar` recibe únicamente expedición y versión; usa el usuario autenticado de la sesión. `AventuraService::reiniciar` comparte el cierre transaccional con la derrota, pero registra el estado `reiniciada`. Devuelve una sola vez la inversión original, elimina únicamente el inventario vinculado y los recursos de esa partida, y conserva nivel, experiencia y otros gastos. Una petición repetida no vuelve a reembolsar; una versión desactualizada de una partida activa devuelve 409. El campamento crea la siguiente preparación vacía, sin iniciar automáticamente la aventura.

El atlas conserva mapa y caminos al elegir. Un encuentro pendiente ocupa una escena centrada con fondo oscurecido, sin un elemento dialog; el contenido detrás queda inerte y el enlace al campamento permanece accesible. Cuando existe un combate, no se renderizan el mapa ni la navegación de regiones: aparecen combate y estado del personaje. Al continuar tras vencer se recupera la vista de exploración. Los encuentros narrativos pendientes mantienen su estado hasta implementar su resolución en la fase correspondiente.
