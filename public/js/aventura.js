(() => {
    'use strict';
    const root = document.getElementById('campamento');
    if (!root) return;
    let state = JSON.parse(document.getElementById('adventureState').textContent);
    const clone = value => JSON.parse(JSON.stringify(value));
    let draft = clone(state.expedicion.equipo);
    let category = 'arma';
    let busy = false;
    const names = { dano: 'Daño', magia: 'Magia', armadura: 'Armadura', inteligencia: 'Inteligencia', vitalidad: 'Vitalidad' };
    const rarities = { normal: 'Normal', raro: 'Raro', super_raro: 'Super raro', ultra_raro: 'Ultra raro', legendario: 'Legendario' };
    const $ = id => document.getElementById(id);
    const catalogue = () => Object.fromEntries(state.catalogo.map(item => [item.id, item]));
    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    function icon(name) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'item-icon');
        svg.setAttribute('aria-hidden', 'true');
        const use = document.createElementNS(svg.namespaceURI, 'use');
        use.setAttribute('href', '#i-' + name);
        svg.append(use);
        return svg;
    }
    function entries(equipment = draft) {
        return [equipment.arma, equipment.armadura, ...equipment.hechizos].filter(Boolean).map(id => ({ id, cantidad: 1 })).concat(equipment.pociones);
    }
    const cost = (equipment = draft) => entries(equipment).reduce((sum, entry) => sum + catalogue()[entry.id].precio * entry.cantidad, 0);
    const changed = () => JSON.stringify(draft) !== JSON.stringify(state.expedicion.equipo);
    const editable = () => state.expedicion.estado === 'preparacion' && !busy;
    const potionCount = () => draft.pociones.reduce((sum, entry) => sum + entry.cantidad, 0);
    const spellLimit = () => state.personaje.limite_hechizos || 2;
    function notice(text) { $('shopNotice').textContent = text; $('shopNotice').hidden = !text; }
    function feedback(text, type = '') { $('saveMessage').textContent = text; $('saveMessage').className = 'save-message ' + type; }
    function selected(item) {
        if (item.tipo === 'hechizo') return draft.hechizos.includes(item.id);
        if (item.tipo === 'pocion') return draft.pociones.find(potion => potion.id === item.id)?.cantidad || 0;
        return draft[item.tipo] === item.id;
    }
    function candidate(item, delta = 1) {
        const next = clone(draft);
        if (item.tipo === 'hechizo') next.hechizos = selected(item) ? next.hechizos.filter(id => id !== item.id) : [...next.hechizos, item.id];
        else if (item.tipo === 'pocion') {
            const amount = Number(selected(item)) + delta;
            next.pociones = next.pociones.filter(potion => potion.id !== item.id);
            if (amount > 0) next.pociones.push({ id: item.id, cantidad: amount });
        } else next[item.tipo] = selected(item) ? null : item.id;
        return next;
    }
    function choose(item, delta = 1) {
        if (!editable() || !item.desbloqueado) return;
        const next = candidate(item, delta);
        if (next.hechizos.length > spellLimit()) { notice(`Puedes llevar hasta ${spellLimit()} hechizos con tu nivel actual. Retira uno de los seleccionados para cambiarlo.`); return; }
        if (next.pociones.reduce((sum, potion) => sum + potion.cantidad, 0) > 20) { notice('Tu mochila admite 20 pociones. Retira alguna para añadir otra.'); return; }
        if (cost(next) > state.puntos.presupuesto) { notice('Esta combinación supera tu presupuesto. Prueba otra selección.'); return; }
        draft = next;
        notice('');
        render();
    }
    function renderCharacter() {
        const bonus = Object.fromEntries(Object.keys(names).map(key => [key, 0]));
        const items = catalogue();
        for (const entry of entries()) for (const [key, value] of Object.entries(items[entry.id].bonus)) bonus[key] += value;
        const base = state.personaje.base;
        $('characterStats').replaceChildren();
        for (const [key, name] of Object.entries(names)) {
            const row = el('div', 'stat-row');
            const values = el('span', 'stat-values');
            values.append(el('span', '', base[key]), el('b', '', '+' + bonus[key]), el('strong', '', '= ' + (base[key] + bonus[key])));
            row.append(el('span', '', name), values);
            $('characterStats').append(row);
        }
        $('maxHealth').textContent = 100 + 5 * (base.vitalidad + bonus.vitalidad - 1);
        $('maxMana').textContent = 50 + 2 * (base.inteligencia + bonus.inteligencia - 1);
    }
    function renderLoadout() {
        const items = catalogue();
        $('loadout').replaceChildren();
        const slots = [
            ['ARMA', 'sword', draft.arma ? [items[draft.arma].nombre] : ['Sin arma']],
            ['ARMADURA', 'armor', draft.armadura ? [items[draft.armadura].nombre] : ['Sin armadura']],
            ['HECHIZOS · ' + draft.hechizos.length + '/' + spellLimit(), 'bolt', draft.hechizos.length ? draft.hechizos.map(id => items[id].nombre) : ['Sin hechizos']],
            ['POCIONES · ' + potionCount() + '/20', 'potion', draft.pociones.length ? draft.pociones.map(p => `${p.cantidad} × ${items[p.id].nombre}`) : ['Mochila vacía']]
        ];
        for (const [title, art, lines] of slots) {
            const slot = el('div', 'loadout-slot');
            slot.append(el('span', 'slot-label', title), icon(art));
            for (const line of lines) slot.append(el('p', '', line));
            $('loadout').append(slot);
        }
        $('savedBadge').textContent = changed() ? 'SIN CONFIRMAR' : 'GUARDADO';
        $('savedBadge').classList.toggle('dirty', changed());
    }
    function renderCatalogue() {
        const grid = $('catalogue');
        // Restore the card action after rebuilding so keyboard users can buy multiple potions.
        const focused = document.activeElement?.dataset.actionKey;
        grid.replaceChildren();
        grid.setAttribute('aria-labelledby', 'cat-' + category);
        const filtered = state.catalogo.filter(item => item.tipo === category && (!$('rarityFilter').value || item.rareza === $('rarityFilter').value) && (!$('unlockedFilter').checked || item.desbloqueado));
        $('itemCount').textContent = filtered.length + ' objetos';
        for (const item of filtered) {
            const chosen = selected(item);
            const card = el('article', 'item-card' + (chosen ? ' chosen' : ''));
            card.dataset.rarity = item.rareza;
            card.dataset.item = item.id;
            const top = el('div', 'item-top');
            top.append(el('span', '', rarities[item.rareza].toUpperCase()), el('span', '', item.desafio ? '✧ RELIQUIA' : chosen ? '✓ EN MOCHILA' : ''));
            const art = el('div', 'item-art'); art.append(icon(item.icono));
            card.append(top, art, el('h3', '', item.nombre), el('p', 'item-description', item.descripcion));
            const bonuses = el('div', 'item-bonus');
            for (const [key, value] of Object.entries(item.bonus)) bonuses.append(el('span', '', `+${value} ${names[key]}`));
            if (item.mana) bonuses.append(el('span', '', item.mana + ' maná / uso'));
            card.append(bonuses);
            if (item.desafio) {
                const lock = el('p', 'item-lock', item.desbloqueado ? `✓ Desafío ${item.desafio} completado. Compra disponible.` : `🔒 Completa el desafío ${item.desafio} para comprarlo.`);
                if (!item.desbloqueado) {
                    const link = el('a', '', 'Ver desafíos →');
                    link.href = '/alumno/desafios/programacion';
                    lock.append(link);
                }
                card.append(lock);
            }
            const bottom = el('div', 'item-bottom');
            const price = el('span', 'item-price', item.precio + ' '); price.append(el('small', '', 'PTOS'));
            bottom.append(price);
            if (item.tipo === 'pocion') {
                const controls = el('div', 'potion-controls');
                const minus = el('button', '', '−'); minus.type = 'button'; minus.setAttribute('aria-label', 'Quitar ' + item.nombre); minus.dataset.actionKey = item.id + '-minus'; minus.disabled = !editable() || !chosen;
                const plus = el('button', '', '+'); plus.type = 'button'; plus.setAttribute('aria-label', 'Añadir ' + item.nombre); plus.dataset.actionKey = item.id + '-plus'; plus.disabled = !editable() || potionCount() >= 20 || cost(candidate(item)) > state.puntos.presupuesto;
                minus.addEventListener('click', () => choose(item, -1)); plus.addEventListener('click', () => choose(item));
                const amount = el('output', '', chosen || 0); amount.setAttribute('aria-label', 'Cantidad de ' + item.nombre);
                controls.append(minus, amount, plus); bottom.append(controls);
            } else {
                const affordable = cost(candidate(item)) <= state.puntos.presupuesto;
                const button = el('button', 'choose-button', !item.desbloqueado ? 'Bloqueado' : chosen ? 'Quitar' : !affordable ? 'Sin puntos' : 'Elegir');
                button.type = 'button'; button.dataset.actionKey = item.id;
                button.setAttribute('aria-label', (chosen ? 'Quitar ' : 'Elegir ') + item.nombre);
                button.disabled = !editable() || !item.desbloqueado || (!chosen && !affordable);
                button.addEventListener('click', () => choose(item)); bottom.append(button);
            }
            card.append(bottom); grid.append(card);
        }
        if (!filtered.length) grid.append(el('p', 'empty-results', 'No hay objetos con estos filtros. Prueba otra rareza.'));
        if (focused) [...grid.querySelectorAll('button')].find(button => button.dataset.actionKey === focused && !button.disabled)?.focus({ preventScroll: true });
    }
    function render() {
        renderCharacter(); renderLoadout(); renderCatalogue();
        $('walletAvailable').textContent = state.puntos.disponibles.toLocaleString('es');
        $('walletReserved').textContent = state.puntos.invertidos.toLocaleString('es');
        $('draftCost').textContent = cost().toLocaleString('es');
        $('draftRemaining').textContent = (state.puntos.presupuesto - cost()).toLocaleString('es');
        $('draftRemaining').classList.toggle('negative', cost() > state.puntos.presupuesto);
        const difference = cost() - state.puntos.invertidos;
        $('priceChange').textContent = difference > 0 ? `Al confirmar se usarán ${difference} puntos adicionales.` : difference < 0 ? `Al confirmar recuperarás ${-difference} puntos.` : 'Sin cambios en los puntos comprometidos.';
        $('reviewButton').disabled = !editable() || !changed() || cost() > state.puntos.presupuesto;
        $('clearDraft').disabled = !editable() || !entries().length;
        $('starterKit').disabled = !editable();
    }
    function changeCategory(value) {
        category = value;
        for (const tab of document.querySelectorAll('[data-category]')) {
            tab.setAttribute('aria-selected', String(tab.dataset.category === category));
            tab.tabIndex = tab.dataset.category === category ? 0 : -1;
        }
        notice(''); renderCatalogue();
    }
    document.querySelectorAll('[data-category]').forEach(tab => {
        tab.addEventListener('click', () => changeCategory(tab.dataset.category));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const categories = ['arma', 'armadura', 'hechizo', 'pocion'];
            changeCategory(categories[(categories.indexOf(category) + (event.key === 'ArrowRight' ? 1 : 3)) % 4]);
            $('cat-' + category).focus();
        });
    });
    $('rarityFilter').addEventListener('change', renderCatalogue);
    $('unlockedFilter').addEventListener('change', renderCatalogue);
    $('clearDraft').addEventListener('click', () => { draft = { arma: null, armadura: null, hechizos: [], pociones: [] }; notice('Selección vacía. Confirma para devolver el equipo guardado y recuperar sus puntos.'); render(); });
    $('starterKit').addEventListener('click', () => {
        draft = { arma: null, armadura: null, hechizos: [], pociones: [] };
        for (const id of ['espada-viajero', 'cuero', 'brasa', 'pocion-vida', 'pocion-mana', 'pocion-vida']) {
            const next = candidate(catalogue()[id]);
            if (cost(next) <= state.puntos.presupuesto) draft = next;
        }
        render(); notice(entries().length ? 'Te sugerimos un equipo sencillo dentro de tu presupuesto. Puedes cambiarlo antes de confirmar.' : 'Aún no tienes puntos para este equipo. Sigue aprendiendo en CodePlay para ampliar tus opciones.');
    });
    $('adventureButton').addEventListener('click', () => {
        $('journeyNotice').textContent = changed() ? 'Tienes cambios sin confirmar. Cierra este aviso y confirma tu selección antes de partir; al salir del campamento esos cambios se descartarán.' : 'Llevarás el equipo que hayas confirmado. Consultar el mapa no inicia la partida.';
        $('adventureDialog').showModal();
    });
    $('reviewButton').addEventListener('click', () => {
        $('purchaseSummary').replaceChildren();
        for (const entry of entries()) {
            const item = catalogue()[entry.id]; const row = el('div', 'purchase-line');
            row.append(el('span', '', `${entry.cantidad} × ${item.nombre}`), el('strong', '', item.precio * entry.cantidad + ' ptos'));
            $('purchaseSummary').append(row);
        }
        if (!entries().length) $('purchaseSummary').append(el('p', '', 'Devolverás todo el equipo. Tu mochila quedará vacía.'));
        $('purchaseSummary').append(el('div', 'purchase-total', `Total del equipo: ${cost()} puntos`), el('p', '', $('priceChange').textContent));
        $('purchaseDialog').showModal();
    });
    $('cancelPurchase').addEventListener('click', () => $('purchaseDialog').close());
    $('confirmPurchase').addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        const snapshot = clone(draft);
        $('confirmPurchase').disabled = true;
        $('confirmPurchase').textContent = 'Guardando…';
        render();
        try {
            const response = await fetch(root.dataset.saveUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ expedicion_id: state.expedicion.id, version: state.expedicion.version, equipo: snapshot }) });
            if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) throw new Error('Tu sesión terminó. Vuelve a iniciar sesión antes de guardar.');
            const data = await response.json();
            if (!response.ok) {
                if (response.status === 409) throw new Error('El equipo cambió en otra pestaña. Recarga la página para ver el estado guardado antes de continuar.');
                if (response.status === 419 || response.status === 401) throw new Error('Tu sesión terminó. Vuelve a iniciar sesión antes de guardar.');
                if (response.status === 429) throw new Error('Espera un momento antes de volver a guardar.');
                throw new Error(Object.values(data.errors || {}).flat()[0] || 'No se pudo guardar el equipo. Intenta de nuevo.');
            }
            state = data.estado;
            draft = clone(state.expedicion.equipo);
            feedback('✓ Equipo confirmado. Tu mochila y tus puntos ya están guardados en tu cuenta.', 'success');
        } catch (error) {
            feedback(error.message, 'error');
        } finally {
            busy = false;
            $('confirmPurchase').disabled = false;
            $('confirmPurchase').textContent = 'Confirmar equipo';
            $('purchaseDialog').close(); render();
            $('saveMessage').scrollIntoView({ block: 'nearest' });
        }
    });
    $('restartJourney')?.addEventListener('click', async event => {
        if (busy) return;
        busy = true;
        const button = event.currentTarget;
        button.disabled = true;
        $('restartError').hidden = true;
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ expedicion_id: state.expedicion.id, version: state.expedicion.version })
            });
            if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) throw new Error('Tu sesión terminó. Vuelve a iniciar sesión.');
            const data = await response.json();
            if (!response.ok) throw new Error(response.status === 409 ? 'La aventura cambió en otra pestaña. Recarga antes de reiniciar.' : Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo reiniciar. Inténtalo de nuevo.');
            window.location.assign(data.url);
        } catch (error) {
            $('restartError').textContent = error.message;
            $('restartError').hidden = false;
            busy = false;
            button.disabled = false;
        }
    });
    window.addEventListener('beforeunload', event => { if (changed()) { event.preventDefault(); event.returnValue = ''; } });
    render();


    const renameButton = document.getElementById('renameCharacterButton');
    const renameForm = document.getElementById('renameCharacterForm');
    const renameCancel = document.getElementById('cancelRenameCharacter');
    if (renameButton && renameForm) {
        renameButton.addEventListener('click', () => {
            renameForm.hidden = false;
            renameButton.hidden = true;
            renameForm.querySelector('input')?.focus();
        });
        renameCancel?.addEventListener('click', () => {
            renameForm.hidden = true;
            renameButton.hidden = false;
        });
        renameForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = renameForm.querySelector('input[name="nombre"]');
            const message = document.getElementById('renameCharacterMessage');
            try {
                const response = await fetch(root.dataset.renameUrl, {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({nombre: input.value})
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'No fue posible cambiar el nombre.');
                document.getElementById('characterTitle').textContent = data.nombre;
                document.querySelector('.topbar .player-name').textContent = data.nombre;
                input.value = data.nombre;
                message.textContent = 'Nombre guardado.';
                setTimeout(() => { renameForm.hidden = true; renameButton.hidden = false; message.textContent = ''; }, 900);
            } catch (error) { message.textContent = error.message; }
        });
    }

})();
