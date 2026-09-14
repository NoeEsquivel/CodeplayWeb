(() => {
    'use strict';
    const audio = document.getElementById('forestMusic');
    const controls = document.getElementById('forestMusicControls');
    if (!audio || !controls) return;
    const slider = controls.querySelector('input');
    const toggle = controls.querySelector('button');
    const output = controls.querySelector('output');
    const read = (store, key) => { try { return store.getItem(key); } catch { return null; } };
    const write = (store, key, value) => { try { store.setItem(key, String(value)); } catch {} };
    // Each visit to the forest starts with music enabled. Volume choices remain
    // available during the visit, but never make a later forest screen silent.
    audio.muted = false;
    audio.volume = 0.35;
    let lastVolume = audio.volume;
    let enteringCombat = false;
    let pending = false;
    let restored = audio.readyState >= HTMLMediaElement.HAVE_METADATA;
    const eligible = () => document.getElementById('world')?.dataset.forestMusic === 'true';
    const cinematic = () => [...document.querySelectorAll('#worldEntrance, #bossIntroduction')].some(el => !el.hidden);
    const allowed = () => eligible() && !enteringCombat && !cinematic();
    const update = () => {
        slider.value = Math.round(audio.volume * 100);
        output.value = `${slider.value}%`;
        toggle.textContent = audio.paused && audio.volume > 0 ? '▶ Música' : audio.volume === 0 ? '♪ Activar' : '♪ Silenciar';
        toggle.setAttribute('aria-label', audio.paused && audio.volume > 0 ? 'Reproducir música del bosque' : audio.volume === 0 ? 'Activar música del bosque' : 'Silenciar música del bosque');
    };
    const sync = () => {
        controls.hidden = !eligible() || enteringCombat || cinematic();
        if (!restored) {
            update();
            return;
        }
        if (!allowed()) {
            audio.pause();
            if (!eligible() || enteringCombat) {
                audio.currentTime = 0;
                write(sessionStorage, 'codeplay-forest-position', 0);
            }
            update();
            return;
        }
        if (audio.paused && audio.volume > 0 && !pending) {
            pending = true;
            audio.play().then(() => { if (!allowed()) audio.pause(); }).catch(() => {
                // A fresh browser session may require the first user gesture.
            }).finally(() => { pending = false; update(); });
        }
        update();
    };
    audio.addEventListener('loadedmetadata', () => {
        const position = Number(read(sessionStorage, 'codeplay-forest-position'));
        if (eligible() && Number.isFinite(position) && position > 0 && position < audio.duration) audio.currentTime = position;
        restored = true;
        sync();
    });
    audio.addEventListener('playing', update);
    audio.addEventListener('pause', update);
    slider.addEventListener('input', () => {
        audio.volume = Number(slider.value) / 100;
        if (audio.volume > 0) lastVolume = audio.volume;
        sync();
    });
    toggle.addEventListener('click', () => {
        audio.volume = audio.volume === 0 ? lastVolume : audio.paused ? audio.volume : 0;
        sync();
    });
    // Capture before the combat handler or the autoplay-unlock listener runs.
    document.addEventListener('click', event => {
        if (event.target.closest('[data-combat-action="abrir"]')) {
            enteringCombat = true;
            sync();
        }
    }, true);
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    document.addEventListener('codeplay:turn-rendered', () => { enteringCombat = false; sync(); });
    document.addEventListener('codeplay:action-error', () => { enteringCombat = false; sync(); });
    for (const el of document.querySelectorAll('#worldEntrance, #bossIntroduction')) {
        new MutationObserver(sync).observe(el, { attributes: true, attributeFilter: ['hidden'] });
    }
    window.addEventListener('pagehide', () => {
        write(sessionStorage, 'codeplay-forest-position', allowed() ? audio.currentTime : 0);
        audio.pause();
    });
    window.addEventListener('pageshow', () => { enteringCombat = false; sync(); });
    sync();
})();

(() => {
    'use strict';
    const audio = document.getElementById('ruinsMusic');
    const controls = document.getElementById('ruinsMusicControls');
    if (!audio || !controls) return;
    const slider = controls.querySelector('input');
    const toggle = controls.querySelector('button');
    const output = controls.querySelector('output');
    const read = (store, key) => { try { return store.getItem(key); } catch { return null; } };
    const write = (store, key, value) => { try { store.setItem(key, String(value)); } catch {} };
    audio.muted = false;
    audio.volume = 0.35;
    let lastVolume = audio.volume;
    let enteringCombat = false;
    let pending = false;
    let restored = audio.readyState >= HTMLMediaElement.HAVE_METADATA;
    const eligible = () => document.getElementById('world')?.dataset.ruinsMusic === 'true';
    const cinematic = () => [...document.querySelectorAll('#worldEntrance, #bossIntroduction')].some(el => !el.hidden);
    const allowed = () => eligible() && !enteringCombat && !cinematic();
    const update = () => {
        slider.value = Math.round(audio.volume * 100);
        output.value = `${slider.value}%`;
        toggle.textContent = audio.paused && audio.volume > 0 ? '▶ Música' : audio.volume === 0 ? '♪ Activar' : '♪ Silenciar';
        toggle.setAttribute('aria-label', audio.paused && audio.volume > 0 ? 'Reproducir música de Ruinas Antiguas' : audio.volume === 0 ? 'Activar música de Ruinas Antiguas' : 'Silenciar música de Ruinas Antiguas');
    };
    const sync = () => {
        controls.hidden = !eligible() || enteringCombat || cinematic();
        if (!restored) { update(); return; }
        if (!allowed()) {
            audio.pause();
            if (!eligible() || enteringCombat) {
                audio.currentTime = 0;
                write(sessionStorage, 'codeplay-ruins-position', 0);
            }
            update();
            return;
        }
        if (audio.paused && audio.volume > 0 && !pending) {
            pending = true;
            audio.play().then(() => { if (!allowed()) audio.pause(); }).catch(() => {
                // A fresh browser session may require the first user gesture.
            }).finally(() => { pending = false; update(); });
        }
        update();
    };
    audio.addEventListener('loadedmetadata', () => {
        const position = Number(read(sessionStorage, 'codeplay-ruins-position'));
        if (eligible() && Number.isFinite(position) && position > 0 && position < audio.duration) audio.currentTime = position;
        restored = true;
        sync();
    });
    audio.addEventListener('playing', update);
    audio.addEventListener('pause', update);
    slider.addEventListener('input', () => {
        audio.volume = Number(slider.value) / 100;
        if (audio.volume > 0) lastVolume = audio.volume;
        sync();
    });
    toggle.addEventListener('click', () => {
        audio.volume = audio.volume === 0 ? lastVolume : audio.paused ? audio.volume : 0;
        sync();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('[data-combat-action="abrir"]')) {
            enteringCombat = true;
            sync();
        }
    }, true);
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    document.addEventListener('codeplay:turn-rendered', () => { enteringCombat = false; sync(); });
    document.addEventListener('codeplay:action-error', () => { enteringCombat = false; sync(); });
    for (const el of document.querySelectorAll('#worldEntrance, #bossIntroduction')) {
        new MutationObserver(sync).observe(el, { attributes: true, attributeFilter: ['hidden'] });
    }
    window.addEventListener('pagehide', () => {
        write(sessionStorage, 'codeplay-ruins-position', allowed() ? audio.currentTime : 0);
        audio.pause();
    });
    window.addEventListener('pageshow', () => { enteringCombat = false; sync(); });
    sync();
})();

(() => {
    'use strict';
    const audio = document.getElementById('forestBattleMusic');
    if (!audio) return;
    let pending = false;
    const active = () => document.getElementById('world')?.dataset.forestBattleMusic === 'true';
    const sync = () => {
        if (!active()) {
            audio.pause();
            audio.currentTime = 0;
            return;
        }
        if (audio.paused && !pending) {
            pending = true;
            audio.volume = 0.38;
            audio.play().catch(() => {
                // If autoplay is blocked, the next player interaction resumes it.
            }).finally(() => { pending = false; });
        }
    };
    audio.addEventListener('loadedmetadata', sync, { once: true });
    document.addEventListener('codeplay:turn-rendered', sync);
    document.addEventListener('codeplay:combat-sound', event => {
        audio.muted = event.detail?.enabled === false;
        if (!audio.muted) sync();
    });
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    window.addEventListener('pagehide', () => audio.pause());
    window.addEventListener('pageshow', sync);
    sync();
})();
