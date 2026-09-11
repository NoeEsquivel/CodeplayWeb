(() => {
    'use strict';
    let root = document.getElementById('world');
    if (!root) return;
    let busy = false;
    const overlay = document.getElementById('worldEntrance');
    let errorBox = document.getElementById('worldError');
    async function action(start, camino, combat = null, eventAction = false) {
        if (busy) return;
        busy = true;
        errorBox.hidden = true;
        const buttons = root.querySelectorAll('button');
        const disabledBefore = [...buttons].map(button => button.disabled);
        buttons.forEach(button => { button.disabled = true; });
        if (start) { overlay.hidden = false; root.inert = true; }
        const began = performance.now();
        let saved = false;
        try {
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
            if (start) {
                document.getElementById('entranceStatus').textContent = 'Tu equipo está listo. La aventura te espera.';
                const duration = matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 2100;
                await new Promise(resolve => setTimeout(resolve, Math.max(0, duration - (performance.now() - began))));
            }
            const destination = new URL(data.url, location.origin);
            if (destination.origin !== location.origin) throw new Error('No se pudo abrir el mapa.');
            if(combat && !eventAction && data.secuencia && root.querySelector('[data-theatre]')) {
                let nextRoot=null, nextTheme=document.body.dataset.theme;
                if(destination.pathname===location.pathname) {
                    const page=await fetch(destination.href,{headers:{Accept:'text/html'},signal:AbortSignal.timeout(20000)});
                    if(!page.ok || page.redirected) throw new Error('El turno se guardó. Recarga para ver el estado actual.');
                    const nextDocument=new DOMParser().parseFromString(await page.text(),'text/html');
                    nextRoot=nextDocument.getElementById('world');nextTheme=nextDocument.body.dataset.theme;
                    if(!nextRoot) throw new Error('El turno se guardó. Recarga para continuar.');
                }
                await window.CodePlayArena.play(root,data.secuencia,nextRoot);
                if(nextRoot) {
                    root.replaceWith(nextRoot);root=nextRoot;errorBox=root.querySelector('#worldError');busy=false;
                    document.body.dataset.theme=nextTheme;
                    history.replaceState(null,'',destination.href);bind();
                    const focus=root.querySelector('.rune-question h3, .battle-victory, .battle-narration');
                    if(focus){focus.tabIndex=-1;focus.focus({preventScroll:true});}
                    document.dispatchEvent(new CustomEvent('codeplay:turn-rendered'));
                    return;
                }
            }
            // A fragment-only navigation would not reload the rendered turn.
            if (destination.pathname === location.pathname && destination.search === location.search) {
                history.replaceState(null, '', destination.href); location.reload();
            } else location.assign(destination.href);
        } catch (error) {
            overlay.hidden = true; root.inert = false;
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
    if (root.matches('.event-stage, .combat-stage')) {
        const heading = document.getElementById('story-title');
        if (heading) { heading.tabIndex = -1; heading.focus({ preventScroll: true }); }
    }
    document.getElementById('beginJourney')?.addEventListener('click', () => action(true));
    root.querySelectorAll('[data-guild-action]').forEach(button => button.addEventListener('click', () => {
        action(false, null, {accion:button.dataset.guildAction,
            ...(button.dataset.quest ? {encargo:button.dataset.quest} : {}),
            ...(button.dataset.source ? {origen:Number(button.dataset.source)} : {}),
            ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {})}, 'gremio');
    }));
    root.querySelectorAll('[data-event-action]').forEach(button => button.addEventListener('click', () => {
        const replacement = document.getElementById('replaceSpell')?.value;
        action(false, null, { accion: button.dataset.eventAction,
            ...(button.dataset.answer ? {respuesta:button.dataset.answer} : {}),
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
