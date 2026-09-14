(() => {
    'use strict';
    let root = document.getElementById('world');
    if (!root) return;
    let busy = false;
    const overlay = document.getElementById('worldEntrance');
    const entranceVideo = overlay?.querySelector('.world-entrance-video');
    let errorBox = document.getElementById('worldError');
    function beginWorldEntrance(options = {}) {
        const video = options.video || entranceVideo;
        const retryLabel = options.retryLabel || 'Abrir los caminos';
        if (!video) return Promise.reject(new Error('No se encontró el video de entrada. Recarga la página para intentarlo de nuevo.'));

        // Reduced motion disables decorative CSS animations, not this requested movie.
        // Only a completed playback can unlock the map; errors must never count as success.
        return new Promise((resolve, reject) => {
            let finished = false;
            let watchdog;
            let lastTime = 0;
            const status = options.status || document.getElementById('entranceStatus');
            const cleanup = () => {
                clearTimeout(watchdog);
                video.removeEventListener('ended', onEnded);
                video.removeEventListener('error', onError);
                video.removeEventListener('timeupdate', onProgress);
                video.removeEventListener('playing', onPlaying);
                video.removeEventListener('waiting', onWaiting);
            };
            const fail = error => {
                if (finished) return;
                finished = true;
                cleanup();
                video.pause();
                reject(error);
            };
            const armWatchdog = () => {
                clearTimeout(watchdog);
                watchdog = setTimeout(() => fail(new Error(`El video no pudo seguir reproduciéndose. Pulsa «${retryLabel}» para volver a intentarlo.`)), 30000);
            };
            const onEnded = () => {
                if (finished) return;
                if (!video.ended || !Number.isFinite(video.duration) || video.currentTime < video.duration - 0.25) {
                    fail(new Error(`El video se interrumpió antes de terminar. Pulsa «${retryLabel}» para volver a intentarlo.`));
                    return;
                }
                finished = true;
                cleanup();
                resolve();
            };
            const onError = () => fail(new Error(`No se pudo reproducir el video. Pulsa «${retryLabel}» para volver a intentarlo.`));
            const onProgress = () => {
                if (video.currentTime > lastTime) {
                    lastTime = video.currentTime;
                    armWatchdog();
                }
            };
            const onPlaying = () => { status.textContent = options.playingText ?? 'Atravesando el portal…'; };
            const onWaiting = () => { status.textContent = options.loadingText || 'Preparando el portal…'; };
            video.addEventListener('ended', onEnded);
            video.addEventListener('error', onError);
            video.addEventListener('timeupdate', onProgress);
            video.addEventListener('playing', onPlaying);
            video.addEventListener('waiting', onWaiting);
            armWatchdog();
            try {
                video.muted = false;
                video.volume = 1;
                video.playbackRate = 1;
                // Reload only after a media error, so the first click uses the preloaded file.
                if (video.error) video.load();
                video.currentTime = 0;
                video.play().catch(error => {
                    console.warn('CodePlay: no se pudo iniciar la cinemática', error);
                    fail(new Error(`El navegador no pudo iniciar el video. Pulsa «${retryLabel}» para volver a intentarlo.`));
                });
            } catch (error) {
                fail(error);
            }
        });
    }
    // The boss movie lives outside #world. Route transitions replace only
    // #world, so keep this companion layer in sync with the rendered turn.
    function syncBossIntroduction(nextDocument) {
        const nextScene = nextDocument.getElementById('bossIntroduction');
        const currentScene = document.getElementById('bossIntroduction');
        if (!nextScene) {
            currentScene?.remove();
            return;
        }
        const replacement = document.importNode(nextScene, true);
        if (currentScene) currentScene.replaceWith(replacement);
        else document.body.append(replacement);
    }
    async function ensureBossIntroduction() {
        const currentScene = document.getElementById('bossIntroduction');
        if (currentScene) return currentScene;
        const page = await fetch(location.href, { headers: { Accept: 'text/html' }, signal: AbortSignal.timeout(20000) });
        if (!page.ok || page.redirected) throw new Error('No se pudo preparar la presentación del guardián. Inténtalo de nuevo.');
        const nextDocument = new DOMParser().parseFromString(await page.text(), 'text/html');
        const source = nextDocument.getElementById('bossIntroduction');
        if (!source) throw new Error('La presentación del guardián no está disponible en este encuentro.');
        const scene = document.importNode(source, true);
        document.body.append(scene);
        return scene;
    }
    async function playBossIntroduction() {
        const scene = await ensureBossIntroduction();
        const bossName = scene.dataset.bossName;
        const wasInert = root.inert;
        scene.hidden = false;
        root.inert = true;
        try {
            await beginWorldEntrance({
                video: scene.querySelector('video'),
                status: scene.querySelector('[data-cinematic-status]'),
                playingText: '',
                loadingText: `Preparando el encuentro con ${bossName}…`,
                retryLabel: `Ver presentación de ${bossName}`
            });
        } finally {
            scene.hidden = true;
            root.inert = wasInert;
        }
    }
    async function action(start, camino, combat = null, eventAction = false) {
        if (busy) return;
        busy = true;
        errorBox.hidden = true;
        const buttons = root.querySelectorAll('button');
        const disabledBefore = [...buttons].map(button => button.disabled);
        buttons.forEach(button => { button.disabled = true; });
        let saved = false;
        try {
            if (start) {
                overlay.hidden = false; root.inert = true;
                document.getElementById('entranceStatus').textContent = 'Preparando el portal…';
                await beginWorldEntrance();
                document.getElementById('entranceStatus').textContent = 'El bosque te espera.';
            }
            if (combat?.accion === 'abrir' && root.querySelector('[data-boss-intro]')) {
                await playBossIntroduction();
            }
            const response = await fetch(eventAction === 'gremio' ? root.dataset.guild : eventAction ? root.dataset.event : combat ? root.dataset.combat : start ? root.dataset.start : root.dataset.path, {
                method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ expedicion_id: Number(root.dataset.id), version: Number(root.dataset.version), ...(combat || (start ? {} : { camino })) }),
                signal: AbortSignal.timeout(20000)
            });
            if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) throw new Error('Tu sesión terminó. Vuelve a iniciar sesión para continuar.');
            const data = await response.json();
            if (!response.ok) {
                if (response.status === 409) throw new Error('Tu partida ya cambió o el encuentro está guardado. Recarga el mapa para continuar.');
                if ([401,419].includes(response.status)) throw new Error('Tu sesión terminó. Vuelve a iniciar sesión.');
                if (response.status === 429) throw new Error('Espera un momento antes de volver a intentarlo.');
                throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo guardar la decisión.');
            }
            saved = true;
            const destination = new URL(data.url, location.origin);
            if (destination.origin !== location.origin) throw new Error('No se pudo abrir el mapa.');
            // The saved final blow is presented by the movie before the epilogue.
            if (data.cinematica_final) { location.assign(destination.href); return; }
            if(combat && !eventAction && data.secuencia && root.querySelector('[data-theatre]')) {
                let nextRoot=null, nextTheme=document.body.dataset.theme, nextDocument=null;
                if(destination.pathname===location.pathname) {
                    const page=await fetch(destination.href,{headers:{Accept:'text/html'},signal:AbortSignal.timeout(20000)});
                    if(!page.ok || page.redirected) throw new Error('El turno se guardó. Recarga para ver el estado actual.');
                    nextDocument=new DOMParser().parseFromString(await page.text(),'text/html');
                    nextRoot=nextDocument.getElementById('world');nextTheme=nextDocument.body.dataset.theme;
                    if(!nextRoot) throw new Error('El turno se guardó. Recarga para continuar.');
                }
                await window.CodePlayArena.play(root,data.secuencia,nextRoot);
                if(nextRoot) {
                    syncBossIntroduction(nextDocument);
                    root.replaceWith(nextRoot);root=nextRoot;errorBox=root.querySelector('#worldError');busy=false;
                    document.body.dataset.theme=nextTheme;
                    history.replaceState(null,'',destination.href);bind();
                    const focus=root.querySelector('.rune-question h3, .battle-victory, .battle-narration');
                    if(focus){focus.tabIndex=-1;focus.focus({preventScroll:true});}
                    document.dispatchEvent(new CustomEvent('codeplay:turn-rendered'));
                    return;
                }
            }
            // Story, guild and route transitions remain on this document. That
            // preserves the forest soundtrack until an actual combat begins.
            if ((!combat || eventAction) && !start && destination.pathname === location.pathname) {
                const page = await fetch(destination.href,{headers:{Accept:'text/html'},signal:AbortSignal.timeout(20000)});
                if(!page.ok || page.redirected) throw new Error('La decisión se guardó. Recarga para ver el estado actual.');
                const nextDocument = new DOMParser().parseFromString(await page.text(),'text/html');
                const nextRoot = nextDocument.getElementById('world');
                if(!nextRoot) throw new Error('La decisión se guardó. Recarga para continuar.');
                syncBossIntroduction(nextDocument);
                root.replaceWith(nextRoot);root=nextRoot;errorBox=root.querySelector('#worldError');busy=false;
                document.body.dataset.theme=nextDocument.body.dataset.theme;
                history.replaceState(null,'',destination.href);bind();
                document.dispatchEvent(new CustomEvent('codeplay:turn-rendered'));
                return;
            }
            // A fragment-only navigation would not reload the rendered turn.
            if (destination.pathname === location.pathname && destination.search === location.search) {
                history.replaceState(null, '', destination.href); location.reload();
            } else location.assign(destination.href);
        } catch (error) {
            overlay.hidden = true; root.inert = false;
            document.dispatchEvent(new CustomEvent('codeplay:action-error'));
            errorBox.textContent = error.name === 'TimeoutError' ? 'La conexión tardó demasiado. Recarga para comprobar si se guardó la acción antes de intentarlo de nuevo.' : error.message;
            errorBox.hidden = false;
            errorBox.scrollIntoView({ block: 'nearest' });
            if(!saved) buttons.forEach((button, index) => { button.disabled = disabledBefore[index]; });
            else {errorBox.textContent='El turno ya se guardó. Recarga la página para continuar sin repetir la acción.';}
            busy = false;
        }
    }
    function bind() {
    window.CodePlayArena?.init(root);
    root.querySelector('[data-preview-boss]')?.addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        errorBox.hidden = true;
        try { await playBossIntroduction(); }
        catch (error) {
            errorBox.textContent = error.message;
            errorBox.hidden = false;
        } finally { busy = false; }
    });
    if (root.matches('.event-stage, .combat-stage')) {
        const heading = document.getElementById('story-title');
        if (heading) { heading.tabIndex = -1; heading.focus({ preventScroll: true }); }
    }
    document.getElementById('beginJourney')?.addEventListener('click', () => action(true));
    root.querySelectorAll('[data-guild-action]').forEach(button => button.addEventListener('click', () => {
        action(false, null, {accion:button.dataset.guildAction,
            ...(button.dataset.quest ? {encargo:button.dataset.quest} : {}),
            ...(button.dataset.source ? {origen:Number(button.dataset.source)} : {}),
            ...(button.dataset.shopItem ? {oferta:button.dataset.shopItem} : {}),
            ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {})}, 'gremio');
    }));
    root.querySelectorAll('[data-event-action]').forEach(button => button.addEventListener('click', () => {
        const replacement = document.getElementById('replaceSpell')?.value;
        action(false, null, { accion: button.dataset.eventAction,
            ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {}),
            ...(button.dataset.spell ? {hechizo:button.dataset.spell} : {}),
            ...(replacement ? {reemplazar:replacement} : {}) }, true);
    }));
    root.querySelectorAll('[data-path-choice]').forEach(button => button.addEventListener('click', () => action(false, Number(button.dataset.pathChoice))));
    root.querySelectorAll('[data-combat-action]').forEach(button => button.addEventListener('click', () => {
        if(button.dataset.answer) root.querySelector('.rune-question-backdrop')?.remove();
        const target = root.querySelector('input[name="enemy-target"]:checked');
        action(false, null, { accion: button.dataset.combatAction, ...(target ? {objetivo:Number(target.value)} : {}),
            ...(button.dataset.item ? {objeto:button.dataset.item} : {}), ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {}) });
    }));
    const timer = root.querySelector('[data-rune-timer]');
    const questionModal = root.querySelector('.rune-question-backdrop');
    if (timer && questionModal) {
        let remaining = 15;
        const tick = () => {
            if (!document.body.contains(questionModal)) return false;
            timer.textContent = String(remaining);
            if (remaining <= 5) timer.parentElement.classList.add('is-warning');
            if (remaining <= 0) {
                questionModal.querySelectorAll('[data-combat-action]').forEach(button => { button.disabled = true; });
                questionModal.remove();
                action(false, null, { accion: 'responder', tiempo_agotado: true });
                return false;
            }
            remaining -= 1;
            return true;
        };
        tick();
        const timerId = setInterval(() => { if (!tick()) clearInterval(timerId); }, 1000);
    }
    }
    bind();
})();
