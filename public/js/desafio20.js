(() => {
    'use strict';

    const root = document.getElementById('arcadeChallenge');
    if (!root) return;
    const views = ['menu', 'culebrita', 'memoria'];
    const names = { menu: 'Menú', culebrita: 'Culebrita', memoria: 'Memoria' };
    const labels = {
        menuTitle: 'Menú · nombre propio', menuDescription: 'Menú · instrucciones',
        snakeMove: 'Culebrita · movimiento', snakeWall: 'Culebrita · paredes', snakeScore: 'Culebrita · +10 puntos',
        memoryMatch: 'Memoria · comparar parejas', memoryScore: 'Memoria · +20 puntos', memoryWin: 'Memoria · victoria',
        structure: 'Tres vistas y sus conexiones'
    };
    const groups = { menu: ['menuTitle', 'menuDescription'], culebrita: ['snakeMove', 'snakeWall', 'snakeScore'], memoria: ['memoryMatch', 'memoryScore', 'memoryWin'] };
    const successes = {
        menuTitle: 'Tu sala ya tiene nombre propio.', menuDescription: 'El menú incluye tus instrucciones.',
        snakeMove: 'La cabeza se mueve correctamente en las cuatro direcciones.', snakeWall: 'Detecta las cuatro paredes y permite las posiciones interiores.',
        snakeScore: 'Cada comida añade 10 a la puntuación actual.', memoryMatch: 'Distingue símbolos iguales de símbolos diferentes.',
        memoryScore: 'Cada pareja añade 20 a la puntuación actual.', memoryWin: 'La victoria llega al encontrar las seis parejas.'
    };
    const editors = {};
    const state = { active: 'menu', revision: 0, checkedRevision: -1, busy: false, awarded: false, checks: {} };
    const feedback = document.getElementById('feedback');
    const submitButton = document.getElementById('submitButton');
    const host = document.getElementById('previewHost');
    const previewError = document.getElementById('previewError');
    const sessions = new Map();
    let previewSession = null;
    let checksSourcePromise;
    let draftTimer;

    function setFeedback(message, type = '') {
        feedback.textContent = message;
        feedback.className = `feedback ${type}`;
    }
    function code() { return Object.fromEntries(views.map(view => [view, editors[view].getValue()])); }
    function claimAllowed() { return root.dataset.canClaim === '1' && !state.awarded; }
    function renderChecks() {
        const grid = document.getElementById('checksGrid');
        grid.replaceChildren();
        let passed = 0;
        for (const [id, label] of Object.entries(labels)) {
            const result = state.checks[id];
            const item = document.createElement('div');
            item.className = `check-item ${result ? (result.ok ? 'ok' : 'missing') : ''}`;
            item.dataset.check = id;
            item.dataset.ok = String(result?.ok === true);
            const title = document.createElement('b');
            title.textContent = `${result?.ok ? '✓' : '○'} ${label}`;
            const explanation = document.createElement('span');
            explanation.textContent = result?.message || 'Por comprobar. Completa la tarea y prueba tu código.';
            item.append(title, explanation);
            grid.append(item);
            if (result?.ok) passed++;
        }
        document.getElementById('progressCount').textContent = `${passed} / ${Object.keys(labels).length}`;
        document.getElementById('progressBar').value = passed;
        submitButton.disabled = state.busy || !claimAllowed() || passed !== Object.keys(labels).length || state.checkedRevision !== state.revision;
    }
    function busy(value) {
        state.busy = value;
        document.getElementById('checkButton').disabled = value;
        document.getElementById('checkButton').textContent = value ? 'Comprobando…' : '✓ Comprobar proyecto';
        renderChecks();
    }
    function persistDraft() {
        try {
            sessionStorage.setItem(root.dataset.storageKey, JSON.stringify({ version: 1, documents: code() }));
            document.getElementById('saveStatus').textContent = 'Cambios guardados en esta pestaña.';
        } catch {
            document.getElementById('saveStatus').textContent = 'Usa «Descargar mi proyecto» para conservar tus cambios.';
        }
    }
    function edited() {
        state.revision++;
        state.checkedRevision = -1;
        state.checks = {};
        renderChecks();
        clearTimeout(draftTimer);
        draftTimer = setTimeout(persistDraft, 350);
    }
    let saved;
    try {
        const draft = JSON.parse(sessionStorage.getItem(root.dataset.storageKey) || 'null');
        if (draft?.version === 1 && views.every(view => typeof draft.documents?.[view] === 'string' && draft.documents[view].length <= 60000)) saved = draft.documents;
    } catch { /* El editor también funciona sin almacenamiento. */ }
    for (const view of views) {
        const textarea = document.getElementById(`editor-${view}`);
        if (saved) textarea.value = saved[view];
        if (typeof CodeMirror !== 'undefined') {
            const editor = CodeMirror.fromTextArea(textarea, { mode: 'htmlmixed', theme: 'dracula', lineNumbers: true, lineWrapping: true, indentUnit: 4, tabSize: 4 });
            editor.on('change', edited);
            editors[view] = editor;
        } else {
            textarea.addEventListener('input', edited);
            editors[view] = { getValue: () => textarea.value, refresh: () => {}, focus: () => textarea.focus(), textarea };
        }
    }
    function selectEditor(view) {
        state.active = view;
        for (const key of views) {
            document.getElementById(`panel-${key}`).hidden = key !== view;
            const tab = document.getElementById(`tab-${key}`);
            tab.setAttribute('aria-selected', String(key === view));
            tab.tabIndex = key === view ? 0 : -1;
        }
        editors[view].refresh();
    }
    document.querySelectorAll('[data-editor]').forEach(tab => {
        tab.addEventListener('click', () => selectEditor(tab.dataset.editor));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const index = views.indexOf(state.active);
            selectEditor(views[(index + (event.key === 'ArrowRight' ? 1 : 2)) % 3]);
            document.getElementById(`tab-${state.active}`).focus();
        });
    });

    // Este puente se ejecuta dentro de cada documento aislado. Solo comunica
    // navegación permitida y errores; nunca recibe cookies ni datos de sesión.
    function bridge(config) {
        const send = (kind, payload) => parent.postMessage({ channel: 'codeplay-arcade20', token: config.token, kind, ...payload }, config.origin);
        const go = view => { if (['menu', 'culebrita', 'memoria'].includes(view)) send('navigate', { view }); };
        Object.defineProperty(window, 'CodePlay', { value: Object.freeze({ irA: go }), writable: false });
        document.addEventListener('click', event => {
            const target = event.target instanceof Element ? event.target : null;
            const button = target?.closest('[data-vista]');
            if (button) { event.preventDefault(); go(button.getAttribute('data-vista')); }
            else if (target?.closest('a')) event.preventDefault();
        }, true);
        document.addEventListener('submit', event => event.preventDefault(), true);
        window.addEventListener('error', event => send('error', { message: String(event.message || 'Hay un error en esta vista.').slice(0, 350) }));
        window.addEventListener('unhandledrejection', () => send('error', { message: 'Una operación de esta vista no pudo completarse.' }));
        window.addEventListener('DOMContentLoaded', () => send('ready', {}));
    }
    function buildDocument(source, config, harness = '') {
        const doc = new DOMParser().parseFromString(source, 'text/html');
        doc.querySelectorAll('base, meta[http-equiv]').forEach(element => element.remove());
        const policy = doc.createElement('meta');
        policy.httpEquiv = 'Content-Security-Policy';
        policy.content = "default-src 'none'; script-src 'unsafe-inline'; style-src 'unsafe-inline'; img-src data:; connect-src 'none'; frame-src 'none'; object-src 'none'; base-uri 'none'; form-action 'none'";
        const boot = doc.createElement('script');
        boot.textContent = `(${bridge.toString()})(${JSON.stringify(config)});`;
        doc.head.prepend(policy, boot);
        // También hidden se respeta cuando una plantilla usa display:grid.
        const style = doc.createElement('style');
        style.textContent = '[hidden]{display:none!important}';
        doc.head.append(style);
        if (harness) {
            const test = doc.createElement('script');
            test.textContent = harness + `\nwindow.addEventListener('load',()=>{try{const results=window.ArcadeChecks.run(${JSON.stringify(config.view)},window);parent.postMessage({channel:'codeplay-arcade20',token:${JSON.stringify(config.token)},kind:'results',results},${JSON.stringify(config.origin)});}catch(error){parent.postMessage({channel:'codeplay-arcade20',token:${JSON.stringify(config.token)},kind:'error',message:String(error.message).slice(0,350)},${JSON.stringify(config.origin)});}});`;
            doc.body.append(test);
        }
        return '<!DOCTYPE html>\n' + doc.documentElement.outerHTML;
    }
    function dispose(session) {
        if (!session) return;
        sessions.delete(session.token);
        clearTimeout(session.timer);
        session.frame.remove();
    }
    function makeSession(view, source, testing, harness, resolve) {
        const token = crypto.randomUUID();
        const frame = document.createElement('iframe');
        frame.setAttribute('sandbox', 'allow-scripts');
        frame.referrerPolicy = 'no-referrer';
        frame.title = testing ? `Comprobación de ${names[view]}` : `Vista previa: ${names[view]}`;
        if (testing) { frame.className = 'validation-frame'; frame.setAttribute('aria-hidden', 'true'); frame.tabIndex = -1; }
        else frame.id = 'previewFrame';
        const session = { token, frame, view, testing, resolve };
        sessions.set(token, session);
        frame.srcdoc = buildDocument(source, { token, view, origin: location.origin }, harness);
        (testing ? document.body : host).append(frame);
        if (testing) session.timer = setTimeout(() => {
            resolve(groups[view].map(id => ({ id, ok: false, message: 'La vista no respondió. Revisa errores o un bucle que no termina.' })));
            dispose(session);
        }, 7000);
        return session;
    }
    function preview(view) {
        if (!views.includes(view)) return;
        dispose(previewSession);
        host.replaceChildren();
        previewError.hidden = true;
        document.getElementById('previewName').textContent = names[view];
        previewSession = makeSession(view, editors[view].getValue(), false, '', null);
    }
    window.addEventListener('message', event => {
        if (event.origin !== 'null' || !event.data || event.data.channel !== 'codeplay-arcade20') return;
        const session = sessions.get(event.data.token);
        if (!session || event.source !== session.frame.contentWindow) return;
        const data = event.data;
        if (data.kind === 'navigate' && !session.testing && views.includes(data.view)) preview(data.view);
        if (data.kind === 'error' && typeof data.message === 'string') {
            if (session.testing) {
                session.resolve(groups[session.view].map(id => ({ id, ok: false, message: data.message.slice(0, 350) })));
                dispose(session);
            } else { previewError.textContent = data.message.slice(0, 350); previewError.hidden = false; }
        }
        if (data.kind === 'results' && session.testing && Array.isArray(data.results)) {
            const results = groups[session.view].map(id => {
                const item = data.results.find(result => result && result.id === id);
                return { id, ok: item?.ok === true, message: typeof item?.message === 'string' ? item.message.slice(0, 350) : 'La comprobación no devolvió este objetivo.' };
            });
            session.resolve(results);
            dispose(session);
        }
    });
    async function post(url, payload) {
        const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(payload) });
        if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) throw new Error('No se pudo comprobar tu sesión. Guarda tu proyecto y vuelve a iniciar sesión.');
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            if (response.status === 401 || response.status === 419) throw new Error('Tu sesión terminó. Guarda tu proyecto y vuelve a iniciar sesión.');
            if (response.status === 429) throw new Error('Espera un momento antes de volver a comprobar.');
            if (data.error === 'recompensa_cooldown') throw new Error(`Ya recibiste esta recompensa. Podrás reclamar ${data.tiempo_restante}.`);
            throw new Error(data.message || data.error || 'No se pudo comprobar en el servidor. Inténtalo de nuevo.');
        }
        return data;
    }
    async function checkProject() {
        if (state.busy) return false;
        busy(true);
        const revision = state.revision;
        const documents = code();
        state.checkedRevision = -1;
        setFeedback('Probando las tres vistas y tus respuestas con diferentes valores…');
        try {
            const serialized = JSON.stringify(documents);
            if (serialized.length > 60000) throw new Error('Tu proyecto supera el límite de 60 000 caracteres. Reduce el código antes de entregarlo.');
            checksSourcePromise ||= fetch(root.dataset.checksUrl).then(response => { if (!response.ok) throw new Error('No se pudo cargar la comprobación.'); return response.text(); }).catch(error => { checksSourcePromise = null; throw error; });
            const harness = await checksSourcePromise;
            const [server, ...batches] = await Promise.all([
                post(root.dataset.validateUrl, { codigo: serialized }),
                ...views.map(view => new Promise(resolve => makeSession(view, documents[view], true, harness, resolve)))
            ]);
            if (revision !== state.revision) { setFeedback('Cambiaste el código durante la comprobación. Vuelve a comprobar para revisar esa versión.'); return false; }
            state.checks = {};
            for (const result of batches.flat()) {
                const serverOK = server.checks?.[result.id] === true;
                state.checks[result.id] = { ok: result.ok && serverOK, message: result.ok && serverOK ? successes[result.id] : result.ok && !serverOK ? 'Usa una única expresión return en esta función, como indica la guía, y conserva sus parámetros.' : result.message };
            }
            state.checks.structure = { ok: server.checks?.structure === true, message: server.checks?.structure ? 'Las tres pantallas conservan sus tableros, contadores y botones de navegación.' : 'Conserva las tres páginas completas, sus tableros, puntos y botones data-vista. Revisa la guía.' };
            state.checkedRevision = revision;
            const all = Object.values(state.checks).length === 9 && Object.values(state.checks).every(result => result.ok);
            setFeedback(all ? '¡Las tres vistas pasaron! Juega una partida para revisar el resultado y entrega tu proyecto.' : 'Todavía hay tareas pendientes. Lee los mensajes de abajo y abre una pista si la necesitas.', all ? 'success' : '');
            return all;
        } catch (error) { state.checkedRevision = -1; setFeedback(error.message, 'error'); return false; }
        finally { busy(false); }
    }
    document.getElementById('runButton').addEventListener('click', () => { persistDraft(); preview(state.active); });
    document.getElementById('previewHome').addEventListener('click', () => preview('menu'));
    document.getElementById('checkButton').addEventListener('click', checkProject);
    const celebration = document.getElementById('finalCelebration');
    const confetti = document.getElementById('celebrationConfetti');
    let celebrationTimer;
    function clearCelebration() {
        clearTimeout(celebrationTimer);
        confetti.replaceChildren();
        celebration.classList.remove('celebrating');
        document.body.classList.remove('celebration-open');
    }
    function celebrateCompletion(points) {
        const reward = `${points} puntos de CodePlay recibidos`;
        document.getElementById('achievementReward').textContent = reward;
        document.getElementById('celebrationReward').textContent = `+ ${points} puntos de CodePlay`;
        document.getElementById('finalAchievement').hidden = false;
        celebration.showModal();
        document.body.classList.add('celebration-open');
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            celebration.classList.add('celebrating');
            const colors = ['#63eed4', '#c9a2ff', '#ffd66b', '#ff8dbd', '#75baff'];
            for (let i = 0; i < 70; i++) {
                const piece = document.createElement('i');
                piece.style.cssText = `--x:${Math.random() * 100}%;--drift:${Math.random() * 180 - 90}px;--delay:${Math.random() * .8}s;--duration:${2.3 + Math.random() * 1.3}s;--spin:${Math.random() * 720 - 360}deg;background:${colors[i % colors.length]}`;
                confetti.append(piece);
            }
            celebrationTimer = setTimeout(() => {
                confetti.replaceChildren();
                celebration.classList.remove('celebrating');
            }, 4600);
        }
    }
    document.getElementById('closeCelebration').addEventListener('click', () => celebration.close());
    celebration.addEventListener('close', () => {
        clearCelebration();
        // The delivery button is now disabled; return focus to an available action.
        document.getElementById('checkButton').focus({ preventScroll: true });
    });
    window.addEventListener('pagehide', clearCelebration);
    submitButton.addEventListener('click', async () => {
        if (!claimAllowed() || state.busy || state.checkedRevision !== state.revision) return;
        if (!(await checkProject())) return;
        const snapshot = JSON.stringify(code());
        busy(true);
        try {
            const data = await post(root.dataset.rewardUrl, { nivel: 20, codigo: snapshot });
            state.awarded = true;
            setFeedback(`¡Proyecto final completado! Recibiste ${data.puntos_otorgados} puntos de CodePlay. Tu Mini Arcade está listo para compartir.`, 'success');
            celebrateCompletion(data.puntos_otorgados);
        } catch (error) { setFeedback(error.message, 'error'); }
        finally { busy(false); }
    });
    document.getElementById('nextTask').addEventListener('click', () => {
        const editor = editors[state.active];
        const text = editor.getValue();
        const from = editor.indexFromPos ? editor.indexFromPos(editor.getCursor()) + 1 : 0;
        let index = text.indexOf('TODO', from);
        if (index < 0) index = text.indexOf('TODO');
        if (index < 0) { setFeedback('No quedan marcas TODO en esta vista. Comprueba el proyecto para revisar su comportamiento.'); return; }
        editor.focus();
        if (editor.posFromIndex) { const position = editor.posFromIndex(index); editor.setCursor(position); editor.scrollIntoView(position, 120); }
        else { editor.textarea.setSelectionRange(index, index + 4); }
    });
    document.getElementById('downloadProject').addEventListener('click', () => {
        const documents = JSON.stringify(code()).replace(/</g, '\\u003c');
        const player = `<!DOCTYPE html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi Mini Arcade</title><style>html,body,iframe{margin:0;width:100%;height:100%;border:0;background:#080e1e}</style><body><script>const documents=${documents};${bridge.toString()};${buildDocument.toString()};let active,token;function openView(view){if(!Object.hasOwn(documents,view))return;if(active)active.remove();token=crypto.randomUUID();active=document.createElement('iframe');active.sandbox='allow-scripts';active.title='Mini Arcade';active.srcdoc=buildDocument(documents[view],{token,view,origin:location.protocol==='file:'||location.origin==='null'?'*':location.origin});document.body.append(active);}window.addEventListener('message',event=>{const d=event.data;if(event.origin==='null'&&event.source===active.contentWindow&&d?.channel==='codeplay-arcade20'&&d.token===token&&d.kind==='navigate')openView(d.view);});openView('menu');<\/script></body></html>`;
        const url = URL.createObjectURL(new Blob([player], { type: 'text/html;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = url; link.download = 'mi-mini-arcade.html'; link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    window.addEventListener('pagehide', () => { persistDraft(); for (const session of sessions.values()) dispose(session); });
    selectEditor('menu');
    renderChecks();
    preview('menu');
    if (saved) document.getElementById('saveStatus').textContent = 'Recuperamos tus cambios de esta pestaña.';
})();
