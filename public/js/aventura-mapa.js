(() => {
    'use strict';
    let root = document.getElementById('world');
    if (!root) return;
    let busy = false;
    const overlay = document.getElementById('worldEntrance');
    const entranceVideo = overlay?.querySelector('.world-entrance-video');
    const supernovaCinematic = document.getElementById('supernovaCinematic');
    let errorBox = document.getElementById('worldError');
    const combatFocusKey = 'codeplay:focus-combat';
    const mapFocusKey = 'codeplay:focus-map';
    function prepareCombatView(destination) {
        try { sessionStorage.setItem(combatFocusKey, 'pending'); } catch (_) {}
    }
    function revealCombatView() {
        const scroll = () => {
            const anchor = root?.querySelector('.battle-status, [data-theatre]');
            if (!anchor) return;
            const top = Math.max(0, window.scrollY + anchor.getBoundingClientRect().top - 12);
            window.scrollTo({ top, behavior: 'auto' });
        };
        // The browser may restore the old scroll position after rendering a
        // new page. Repeat briefly so the combat header reliably wins.
        requestAnimationFrame(() => requestAnimationFrame(scroll));
        window.setTimeout(scroll, 90);
        window.setTimeout(scroll, 240);
    }
    function preserveViewport(top) {
        const restore = () => window.scrollTo({ top, behavior: 'auto' });
        requestAnimationFrame(() => requestAnimationFrame(restore));
        window.setTimeout(restore, 90);
        window.setTimeout(restore, 240);
        window.setTimeout(restore, 480);
        window.setTimeout(restore, 800);
    }
    function preserveScrollContainer(getContainer, top) {
        if (top === null || top === undefined) return;
        const restore = () => {
            const container = getContainer();
            if (container) container.scrollTop = top;
        };
        requestAnimationFrame(() => requestAnimationFrame(restore));
        window.setTimeout(restore, 90);
        window.setTimeout(restore, 240);
        window.setTimeout(restore, 480);
    }
    function prepareMapView() {
        try { sessionStorage.setItem(mapFocusKey, 'pending'); } catch (_) {}
    }
    function revealMapView() {
        const center = () => {
            const map = root?.querySelector('.forest-3d-map, .territory-map');
            if (!map) return;
            const bounds = map.getBoundingClientRect();
            const top = Math.max(0, window.scrollY + bounds.top + (bounds.height / 2) - (window.innerHeight / 2));
            window.scrollTo({ top, behavior: 'auto' });
        };
        requestAnimationFrame(() => requestAnimationFrame(center));
        window.setTimeout(center, 90);
        window.setTimeout(center, 240);
    }
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
    async function playSupernovaCinematic() {
        if (!supernovaCinematic) throw new Error('No se encontró la cinemática de Supernova. Recarga la página e inténtalo de nuevo.');
        const wasInert = root.inert;
        supernovaCinematic.hidden = false;
        root.inert = true;
        try {
            await beginWorldEntrance({
                video: supernovaCinematic.querySelector('video'),
                status: supernovaCinematic.querySelector('[data-cinematic-status]'),
                playingText: '',
                loadingText: 'Canalizando Supernova…',
                retryLabel: 'Lanzar Supernova'
            });
        } finally {
            supernovaCinematic.hidden = true;
            root.inert = wasInert;
        }
    }
    async function action(start, camino, combat = null, eventAction = false) {
        if (busy) return;
        busy = true;
        errorBox.hidden = true;
        const reopenGuildShop = eventAction === 'gremio' && combat?.accion === 'comprar';
        const guildShopError = reopenGuildShop ? root.querySelector('[data-guild-shop-error]') : null;
        if (guildShopError) {
            guildShopError.hidden = true;
            guildShopError.textContent = '';
        }
        const guildScrollY = eventAction === 'gremio' ? window.scrollY : null;
        const guildModalScrollTop = eventAction === 'gremio'
            ? root.querySelector('.event-stage .story')?.scrollTop
            : null;
        // Removing the focused guild button can make some browsers jump to the
        // document start while the refreshed board replaces the current one.
        if (eventAction === 'gremio') document.activeElement?.blur?.();
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
            if (combat?.accion === 'hechizo' && combat.objeto === 'supernova') {
                await playSupernovaCinematic();
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
            if (combat?.accion === 'continuar') prepareMapView();
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
                // The guild's timed question is a viewport modal. Keep an old
                // instance from surviving outside #world before it is replaced.
                document.querySelector('[data-guild-rune-question]')?.remove();
                root.replaceWith(nextRoot);root=nextRoot;errorBox=root.querySelector('#worldError');busy=false;
                document.body.dataset.theme=nextDocument.body.dataset.theme;
                if (guildScrollY !== null) window.scrollTo({ top: guildScrollY, behavior: 'auto' });
                history.replaceState(null,'',destination.href);bind();
                if (reopenGuildShop) root.querySelector('[data-guild-shop]')?.showModal();
                if (guildScrollY !== null) preserveViewport(guildScrollY);
                preserveScrollContainer(() => root?.querySelector('.event-stage .story'), guildModalScrollTop);
                document.dispatchEvent(new CustomEvent('codeplay:turn-rendered'));
                return;
            }
            // A fragment-only navigation would not reload the rendered turn.
            if (destination.pathname === location.pathname && destination.search === location.search) {
                if (combat?.accion === 'abrir') prepareCombatView(destination);
                history.replaceState(null, '', destination.href); location.reload();
            } else {
                if (combat?.accion === 'abrir') prepareCombatView(destination);
                location.assign(destination.href);
            }
        } catch (error) {
            overlay.hidden = true; root.inert = false;
            document.dispatchEvent(new CustomEvent('codeplay:action-error'));
            const message = error.name === 'TimeoutError'
                ? 'La conexión tardó demasiado. Recarga para comprobar si se guardó la acción antes de intentarlo de nuevo.'
                : error.message;
            const shopDialog = reopenGuildShop ? root.querySelector('[data-guild-shop]') : null;
            const shopError = reopenGuildShop ? root.querySelector('[data-guild-shop-error]') : null;

            if (shopDialog && shopError) {
                if (!shopDialog.open) shopDialog.showModal();
                shopError.textContent = saved
                    ? 'El turno ya se guardó. Recarga la página para continuar sin repetir la acción.'
                    : message;
                shopError.hidden = false;
                shopError.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                shopError.focus({ preventScroll: true });
            } else {
                document.querySelectorAll('dialog[open]').forEach(dialog => dialog.close());
                errorBox.textContent = saved
                    ? 'El turno ya se guardó. Recarga la página para continuar sin repetir la acción.'
                    : message;
                errorBox.hidden = false;
                errorBox.scrollIntoView({ block: 'nearest' });
            }
            if(!saved) buttons.forEach((button, index) => { button.disabled = disabledBefore[index]; });
            busy = false;
        }
    }
    function bind() {
    window.CodePlayArena?.init(root);
    const forestMap = root.querySelector('[data-forest-map]');
    const forestPathDialog = document.getElementById('forestPathDialog');
    root.querySelector('[data-open-forest-paths]')?.addEventListener('click', event => {
        if (busy || !forestPathDialog) return;
        const nextNode = event.currentTarget;
        forestMap?.style.setProperty('--focus-x', nextNode.style.getPropertyValue('--x'));
        forestMap?.style.setProperty('--focus-y', nextNode.style.getPropertyValue('--y'));
        forestMap?.classList.add('is-zoomed');
        window.setTimeout(() => forestPathDialog.showModal(), 1180);
    });
    forestPathDialog?.addEventListener('close', () => forestMap?.classList.remove('is-zoomed'));
    forestPathDialog?.querySelector('[data-close-forest-paths]')?.addEventListener('click', () => forestMap?.classList.remove('is-zoomed'));
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
    if (root.matches('.combat-stage')) {
        try {
            if (sessionStorage.getItem(combatFocusKey) === 'pending') {
                sessionStorage.removeItem(combatFocusKey);
                revealCombatView();
                window.addEventListener('pageshow', revealCombatView, { once: true });
            }
        } catch (_) {}
    }
    if (root.querySelector('.forest-3d-map, .territory-map')) {
        try {
            if (sessionStorage.getItem(mapFocusKey) === 'pending') {
                sessionStorage.removeItem(mapFocusKey);
                revealMapView();
                window.addEventListener('pageshow', revealMapView, { once: true });
            }
        } catch (_) {}
    }
    document.getElementById('beginJourney')?.addEventListener('click', () => action(true));
    const guildShop = root.querySelector('[data-guild-shop]');
    root.querySelector('[data-open-guild-shop]')?.addEventListener('click', () => guildShop?.showModal());
    guildShop?.querySelectorAll('[data-close-guild-shop]').forEach(button => button.addEventListener('click', () => guildShop.close()));
    const requirementDialog = root.querySelector('[data-guild-requirement-dialog]');
    let pendingGuildAction = null;
    const runGuildAction = (button) => {
        action(false, null, {accion:button.dataset.guildAction,
        ...(button.dataset.quest ? {encargo:button.dataset.quest} : {}),
        ...(button.dataset.source ? {origen:Number(button.dataset.source)} : {}),
        ...(button.dataset.shopItem ? {oferta:button.dataset.shopItem} : {}),
        ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {})}, 'gremio');
    };
    root.querySelectorAll('[data-guild-action]').forEach(button => button.addEventListener('click', () => {
        if (button.dataset.guildRequires) {
            pendingGuildAction = button;
            const resourceName = button.dataset.guildRequiresName || 'la poción requerida';
            const message = requirementDialog?.querySelector('[data-guild-requirement-text]');
            if (message) message.textContent = `No tienes ${resourceName} en tu mochila.`;
            requirementDialog?.showModal();
            return;
        }
        runGuildAction(button);
    }));
    requirementDialog?.querySelector('[data-close-guild-requirement]')?.addEventListener('click', () => {
        pendingGuildAction = null;
        requirementDialog.close();
    });
    requirementDialog?.querySelector('[data-confirm-guild-requirement]')?.addEventListener('click', () => {
        const pendingAction = pendingGuildAction;
        pendingGuildAction = null;
        requirementDialog.close();
        if (pendingAction) runGuildAction(pendingAction);
    });
    const guildTimer = root.querySelector('[data-guild-rune-timer]');
    const guildQuestion = root.querySelector('[data-guild-rune-question]');
    if (guildTimer && guildQuestion) {
        let remaining = 30;
        const tick = () => {
            if (!document.body.contains(guildQuestion)) return false;
            guildTimer.textContent = String(remaining);
            if (remaining <= 5) guildTimer.parentElement.classList.add('is-warning');
            if (remaining <= 0) {
                guildQuestion.querySelectorAll('[data-guild-action]').forEach(button => { button.disabled = true; });
                action(false, null, {accion:'responder', encargo:guildQuestion.dataset.guildQuestion, origen:Number(guildQuestion.dataset.guildSource), tiempo_agotado:true}, 'gremio');
                return false;
            }
            remaining -= 1;
            return true;
        };
        tick();
        const timerId = setInterval(() => { if (!tick()) clearInterval(timerId); }, 1000);
    }
    // .story is transformed while an event is open, which would otherwise make
    // a fixed question modal position itself inside its scrollable content.
    // Moving the already-bound prompt to <body> makes it center in the viewport.
    if (guildQuestion?.isConnected) document.body.append(guildQuestion);
    root.querySelectorAll('[data-event-action]').forEach(button => button.addEventListener('click', () => {
        const replacement = document.getElementById('replaceSpell')?.value;
        action(false, null, { accion: button.dataset.eventAction,
            ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {}),
            ...(button.dataset.spell ? {hechizo:button.dataset.spell} : {}),
            ...(replacement ? {reemplazar:replacement} : {}) }, true);
    }));
    root.querySelectorAll('[data-path-choice]').forEach(button => button.addEventListener('click', () => {
        forestPathDialog?.close();
        action(false, Number(button.dataset.pathChoice));
    }));
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
