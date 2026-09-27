(() => {
    'use strict';

    const root = document.getElementById('codeplay-lux');
    if (!root) return;

    const launcher = document.getElementById('lux-launcher');
    const panel = document.getElementById('lux-panel');
    const closeButton = document.getElementById('lux-close');
    const portrait = document.getElementById('lux-portrait');
    const heading = document.getElementById('lux-heading');
    const message = document.getElementById('lux-message');
    const actions = document.getElementById('lux-actions');
    const tip = document.getElementById('lux-tip');
    const tipText = document.getElementById('lux-tip-text');
    const route = root.dataset.route || '';
    const isRanger = route.startsWith('alumno.aventura')
        || route.startsWith('alumno.desafios')
        || route.startsWith('alumno.tests.amigos')
        || route.startsWith('alumno.tests.clase.trivia');
    if (isRanger) launcher.querySelector('img').src = root.dataset.rangerPelota;
    const sprites = isRanger
        ? [root.dataset.rangerSaludo, root.dataset.rangerGuia, root.dataset.rangerPiensa]
        : [root.dataset.orbitSaludo, root.dataset.orbitExplica, root.dataset.orbitSaludo];

    let lastLuxInteraction = Date.now();
    let lastTip = Date.now();
    let tipTimer;
    let autoCloseTimer;
    let introStarted = false;
    let activeSteps = [];
    let stepIndex = 0;
    let tipIndex = 0;

    const tips = [
        'Un programa es una serie de instrucciones que una computadora sigue paso a paso.',
        'HTML organiza el contenido; CSS le da estilo y JavaScript le añade interacción.',
        'Un error en el código es una pista: observar cuándo aparece ayuda a encontrarlo.',
        'En los videojuegos, una regla sencilla puede crear decisiones muy interesantes.',
        'Practicar un poco cada día suele ser más útil que estudiar todo de una vez.',
        'Explicar una idea con tus propias palabras ayuda a descubrir lo que ya comprendes.',
        'Las variables permiten guardar datos para usarlos más adelante.',
        'Probar varias soluciones es parte normal de aprender a programar.',
        'En CSS, una clase puede aplicar el mismo estilo a varios elementos.',
        'Los juegos también tienen algoritmos: reglas para decidir qué ocurre después.',
        'Una pausa breve puede ayudarte a volver a un problema con nuevas ideas.',
        'Los botones son más fáciles de entender cuando su texto describe la acción.',
        'Dividir un reto grande en tareas pequeñas hace más fácil empezar.',
        'Una condición permite que el programa elija entre distintos caminos.',
        'Leer, probar y corregir es una gran combinación para aprender.',
        'Guardar tus avances te permite comparar cuánto has aprendido con el tiempo.',
        'En programación, preguntar «¿qué pasa si…?» es una forma de experimentar.',
        'Los tutoriales te muestran un camino; cambiar el ejemplo te ayuda a hacerlo tuyo.',
        'Un buen juego enseña sus reglas mientras te deja practicar.',
        'Equivocarse en una pregunta te muestra qué tema conviene repasar después.'
    ];

    const intro = [
        ['¡Bienvenido a CodePlay!', 'Soy Lux. Te acompañaré mientras aprendes programación a tu ritmo. Puedes llamarme con la pequeña esfera de la esquina cuando necesites orientación.'],
        ['Documentación', 'Aquí encontrarás explicaciones de HTML, CSS, JavaScript y otros temas. Lee cada apartado y prueba sus tests cuando estés listo.'],
        ['Tutoriales Interactivos', 'Estos tutoriales te guían paso a paso para construir cosas y poner en práctica lo aprendido.'],
        ['Desafíos y Aprender Jugando', 'En los desafíos pondrás a prueba tus conocimientos. En la aventura, tus decisiones y respuestas te ayudarán a avanzar.'],
        ['Tests y Evaluaciones', 'Puedes practicar con preguntas de CodePlay, crear tus propios bancos, estudiar con amigos y participar en actividades de clase.'],
        ['Mi Progreso', 'Aquí verás tus avances y sabrás cuándo un test de documentación vuelve a estar disponible para ganar puntos.'],
        ['¡Empecemos!', 'Explora con calma. Yo descansaré como una esfera en la esquina inferior derecha; haz clic en mí cuando quieras que te explique una pantalla o te lleve a algún módulo.']
    ];

    function hideTip() {
        clearTimeout(tipTimer);
        tip.hidden = true;
    }

    function touchLux() {
        clearTimeout(autoCloseTimer);
        lastLuxInteraction = Date.now();
        lastTip = Date.now();
        hideTip();
    }

    function button(label, onClick, primary = false) {
        const node = document.createElement('button');
        node.type = 'button';
        node.textContent = label;
        if (primary) node.className = 'lux-primary';
        node.addEventListener('click', () => {
            touchLux();
            onClick();
        });
        actions.appendChild(node);
    }

    function show(title, body, pose, choices) {
        heading.textContent = title;
        message.textContent = body;
        portrait.src = sprites[pose] || sprites[0];
        portrait.alt = `Lux ${isRanger ? 'Ranger' : 'Orbit'}, tu guía de CodePlay`;
        actions.replaceChildren();
        choices();
    }

    function open() {
        touchLux();
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        launcher.setAttribute('aria-expanded', 'true');
        showMenu();
        closeButton.focus({preventScroll: true});
    }

    function close() {
        touchLux();
        root.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        launcher.setAttribute('aria-expanded', 'false');
        launcher.focus({preventScroll: true});
    }

    function showMenu() {
        show('¿Cómo puedo ayudarte?', 'Puedo explicarte esta pantalla o ayudarte a elegir tu siguiente actividad. ¿Qué te gustaría hacer?', 0, () => {
            button('Explícame esta pantalla', startGuide, true);
            button('¿Qué puedo hacer?', showDestinations);
            button('Descansa un poco', close);
        });
    }

    function currentPageTitle() {
        const title = document.querySelector('main h1, .container h1, h1');
        return title?.textContent?.trim().replace(/\s+/g, ' ').slice(0, 65) || 'esta pantalla';
    }

    function guideForRoute() {
        const page = currentPageTitle();
        if (route === 'inicio.alumno') return [
            ['Tu menú principal', 'Cada tarjeta abre uno de los cinco módulos. El indicador de puntos muestra lo que puedes usar en la aventura. El botón con tu nombre abre tu opción de cerrar sesión.'],
            ['Elige tu camino', 'Documentación y Tutoriales sirven para aprender; Desafíos para practicar jugando; Tests y Evaluaciones para responder preguntas; Mi Progreso para revisar tus logros.']
        ];
        if (route === 'alumno.documentacion') return [
            ['Documentación', 'Los botones de cada tema abren una lección. Puedes comenzar por Fundamentos HTML y seguir con CSS, JavaScript y otros contenidos.'],
            ['Lee y comprueba', 'Dentro de cada apartado encontrarás el contenido y el acceso a su test. Al regresar aquí puedes escoger otra lección.']
        ];
        if (route.startsWith('alumno.documentacion.show')) return [
            [page, 'Lee el ejemplo de esta lección y prueba los controles interactivos que aparezcan en ella. Puedes volver al índice para elegir otro tema.'],
            ['Ponlo a prueba', 'Cuando encuentres el botón del test, úsalo para responder preguntas sobre lo que acabas de estudiar. Si todavía no está disponible, Mi Progreso te muestra cuándo podrás repetirlo.']
        ];
        if (route.startsWith('test.')) return [
            ['Test de documentación', 'Selecciona tu respuesta en cada pregunta y usa el botón de evaluación para revisar el resultado. Tómate un momento para leer todas las opciones.'],
            ['Después del resultado', 'Puedes volver a la lección o consultar Mi Progreso para ver tus avances y la próxima oportunidad de obtener puntos.']
        ];
        if (route === 'alumno.tutoriales') return [
            ['Tutoriales Interactivos', 'Cada botón de tutorial abre una guía paso a paso. Puedes comenzar con una página web y continuar con estilos, botones y otros proyectos.'],
            ['Aprende haciendo', 'Sigue las instrucciones, prueba el ejemplo y usa los controles de avance de cada tutorial para continuar.']
        ];
        if (route.startsWith('tutorial.')) return [
            [page, 'Sigue los pasos de esta guía y prueba los ejemplos. Los botones de la lección te permiten avanzar, comprobar o completar la actividad según el tutorial.'],
            ['Ve a tu ritmo', 'Si necesitas repasar, vuelve al paso anterior o al listado de tutoriales. No hay prisa.']
        ];
        if (route === 'alumno.desafios' || route === 'alumno.desafios.programacion') return [
            ['Desafíos y aventura', 'Elige Desafíos de Programación para resolver niveles o Aventura para explorar el juego. Cada tarjeta indica a qué actividad entrarás.'],
            ['Sigue avanzando', 'En los desafíos responde con atención y revisa el resultado antes de continuar al siguiente nivel.']
        ];
        if (route.startsWith('alumno.desafios.nivel')) return [
            [page, 'Lee la consigna, selecciona o escribe tu respuesta y utiliza el botón de comprobar. La pantalla te mostrará si fue correcta y qué ocurrió después.'],
            ['Siguiente paso', 'Después de revisar el resultado, usa el botón de continuar o volver para seguir con el desafío.']
        ];
        if (route === 'alumno.aventura') return [
            ['Campamento de aventura', 'Equiparme abre la armería. Las categorías Armas, Armaduras, Hechizos y Pociones cambian los objetos disponibles. Revisa y confirma tu selección antes de partir.'],
            ['Iniciar aventura', 'Cuando estés listo, pulsa Iniciar aventura. Puedes consultar tus atributos, usar Sugerir equipo inicial o volver a cambiar lo elegido.']
        ];
        if (route === 'alumno.aventura.mapa') return [
            ['Mapa de aventura', 'Aquí ves tu posición y los escenarios. Abrir los caminos muestra las opciones disponibles; elige un camino para avanzar al siguiente escenario.'],
            ['Más controles del mapa', 'Las regiones permiten consultar otros territorios sin saltar niveles. Equipo y mochila muestra lo que llevas; también puedes volver al Campamento.']
        ];
        if (route.startsWith('alumno.aventura')) return [
            [page, 'La aventura presenta decisiones, combates y preguntas. Lee la narración y usa las acciones visibles para continuar cuando estés listo.'],
            ['Durante el combate', 'Atacar, Hechizos, Mochila y Defender tienen efectos distintos. Revisa tu vida y maná antes de elegir.']
        ];
        if (route === 'alumno.tests') return [
            ['Tests y Evaluaciones', 'Las cinco tarjetas abren Test CodePlay, tus bancos personales, Código Amigo, Test con amigos y CodePlay en clase.'],
            ['Elige una modalidad', 'Practica a solas, comparte preguntas con compañeros o entra en una actividad creada por tu profesor.']
        ];
        if (route.startsWith('alumno.tests.codeplay')) return [
            ['Test CodePlay', 'Elige un tema o el banco completo para practicar. Cada intento usa preguntas del sistema y al final verás tus aciertos y errores.'],
            ['Solo práctica', 'Estos tests no suman puntos CodePlay. Puedes repetirlos para reforzar lo aprendido.']
        ];
        if (route.startsWith('alumno.bancos')) return [
            ['Tus bancos de preguntas', 'Puedes crear, editar, duplicar y eliminar tus bancos. Añade preguntas y explicaciones antes de publicarlos o compartir su código.'],
            ['Practicar y compartir', 'Prueba tu banco para estudiar. Al compartirlo, un compañero podrá practicarlo sin modificar tu original.']
        ];
        if (route.startsWith('alumno.codigo-amigo')) return [
            ['Código Amigo', 'Escribe el código que te compartieron y consulta el nombre, autor y número de preguntas antes de añadir el banco.'],
            ['Tu biblioteca', 'Después podrás practicar ese banco, copiarlo a uno propio o retirarlo de tu biblioteca.']
        ];
        if (route.startsWith('alumno.tests.amigos')) return [
            ['Test con amigos', 'Ser anfitrión te permite elegir bancos, tiempo y meta de puntos. Unirme a un grupo te deja entrar con la palabra clave de otra mesa.'],
            ['En la mesa', 'Espera a los demás participantes. Cuando el anfitrión inicie, responde cada pregunta y revisa la clasificación al terminar la ronda.']
        ];
        if (route.startsWith('alumno.tests.clase')) return [
            ['CodePlay en clase', 'Aquí aparecen solo las actividades de tus clases. Elige la clase y la sesión para entrar a una trivia o a un examen.'],
            ['Trivia o examen', 'En trivia ves puntos y clasificación tras cada pregunta. En examen avanzas a tu ritmo, tus respuestas se guardan y la calificación aparece al terminar.']
        ];
        if (route === 'alumno.progreso') return [
            ['Mi Progreso', 'Los paneles resumen tu recorrido por Documentación, Tutoriales, Desafíos y Aventura.'],
            ['Tests disponibles', 'En la lista de documentación, cada test muestra si ya puede darte puntos otra vez. Cuando esté listo, usa Realizar test y ganar puntos.']
        ];
        return [[page, 'Lee el contenido y usa los botones de esta pantalla para avanzar, volver o comprobar tus respuestas. Si dudas, puedo llevarte a cualquiera de los módulos principales.']];
    }

    function renderStep() {
        const [title, body] = activeSteps[stepIndex];
        show(title, body, stepIndex % 2 ? 1 : 0, () => {
            if (stepIndex < activeSteps.length - 1) {
                button(`Siguiente · ${stepIndex + 1}/${activeSteps.length}`, () => { stepIndex++; renderStep(); }, true);
            } else {
                button('Entendido, gracias Lux', close, true);
            }
            if (activeSteps !== intro) button('¿Qué más puedo hacer?', showMenu);
            button('Descansa un poco', close);
        });
        if (stepIndex === activeSteps.length - 1) {
            autoCloseTimer = setTimeout(() => {
                if (root.classList.contains('is-open')) close();
            }, activeSteps === intro ? 20000 : 15000);
        }
    }

    function startGuide() { activeSteps = guideForRoute(); stepIndex = 0; renderStep(); }

    function showDestinations() {
        show('¿Qué te apetece hacer?', 'Escoge un destino y te llevaré allí. Puedes volver a llamarme en cualquier pantalla.', 2, () => {
            [
                ['Quiero estudiar · Documentación', 'docs'],
                ['Quiero ver tutoriales', 'tutoriales'],
                ['Quiero jugar · Desafíos', 'desafios'],
                ['Quiero explorar la aventura', 'aventura'],
                ['Quiero practicar tests', 'tests'],
                ['Quiero ver mi progreso', 'progreso']
            ].forEach(([label, key]) => button(label, () => { window.location.assign(root.dataset[key]); }));
            button('Volver a las opciones', showMenu);
            button('Descansa un poco', close);
        });
    }

    async function claimWelcome() {
        try {
            const response = await fetch(root.dataset.welcomeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'X-CSRF-TOKEN': root.dataset.csrf, 'Accept': 'application/json'}
            });
            if (!response.ok) return false;
            return (await response.json()).show === true;
        } catch (_) {
            // Si falla la conexión, la bienvenida seguirá pendiente para el siguiente ingreso.
            return false;
        }
    }

    launcher.addEventListener('click', () => {
        if (root.classList.contains('is-open')) close(); else open();
    });
    closeButton.addEventListener('click', close);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) close();
    });

    if (root.dataset.welcomePending === '1') {
        setTimeout(async () => {
            if (introStarted) return;
            introStarted = true;
            if (!await claimWelcome()) return;
            touchLux();
            root.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            launcher.setAttribute('aria-expanded', 'true');
            activeSteps = intro;
            stepIndex = 0;
            renderStep();
        }, 700);
    }

    setInterval(() => {
        const now = Date.now();
        if (document.hidden || root.classList.contains('is-open') || !tip.hidden) return;
        if (now - lastLuxInteraction < 300000 || now - lastTip < 300000) return;
        tipText.textContent = tips[tipIndex++ % tips.length];
        tip.hidden = false;
        lastTip = now;
        tipTimer = setTimeout(hideTip, 9500);
    }, 15000);
})();
