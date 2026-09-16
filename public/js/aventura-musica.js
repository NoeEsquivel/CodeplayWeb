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
    const muteKey = 'codeplay-adventure-map-music-muted';
    let userMuted = read(localStorage, muteKey) === 'true';
    audio.muted = false;
    audio.volume = userMuted ? 0 : 0.35;
    let lastVolume = userMuted ? 0.35 : audio.volume;
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
        userMuted = audio.volume === 0;
        write(localStorage, muteKey, userMuted);
        sync();
    });
    toggle.addEventListener('click', () => {
        if (audio.volume === 0) { audio.volume = lastVolume; userMuted = false; }
        else if (audio.paused) userMuted = false;
        else { audio.volume = 0; userMuted = true; }
        write(localStorage, muteKey, userMuted);
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
    const muteKey = 'codeplay-adventure-map-music-muted';
    let userMuted = read(localStorage, muteKey) === 'true';
    audio.muted = false;
    audio.volume = userMuted ? 0 : 0.35;
    let lastVolume = userMuted ? 0.35 : audio.volume;
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
        userMuted = audio.volume === 0;
        write(localStorage, muteKey, userMuted);
        sync();
    });
    toggle.addEventListener('click', () => {
        if (audio.volume === 0) { audio.volume = lastVolume; userMuted = false; }
        else if (audio.paused) userMuted = false;
        else { audio.volume = 0; userMuted = true; }
        write(localStorage, muteKey, userMuted);
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
    const audio = document.getElementById('swampMusic');
    const controls = document.getElementById('swampMusicControls');
    if (!audio || !controls) return;
    const slider = controls.querySelector('input');
    const toggle = controls.querySelector('button');
    const output = controls.querySelector('output');
    const read = (store, key) => { try { return store.getItem(key); } catch { return null; } };
    const write = (store, key, value) => { try { store.setItem(key, String(value)); } catch {} };
    const muteKey = 'codeplay-adventure-map-music-muted';
    let userMuted = read(localStorage, muteKey) === 'true';
    audio.muted = false;
    audio.volume = userMuted ? 0 : 0.35;
    let lastVolume = userMuted ? 0.35 : audio.volume;
    let enteringCombat = false;
    let pending = false;
    let restored = audio.readyState >= HTMLMediaElement.HAVE_METADATA;
    const eligible = () => document.getElementById('world')?.dataset.swampMusic === 'true';
    const cinematic = () => [...document.querySelectorAll('#worldEntrance, #bossIntroduction')].some(el => !el.hidden);
    const allowed = () => eligible() && !enteringCombat && !cinematic();
    const update = () => {
        slider.value = Math.round(audio.volume * 100);
        output.value = `${slider.value}%`;
        toggle.textContent = audio.paused && audio.volume > 0 ? '▶ Música' : audio.volume === 0 ? '♪ Activar' : '♪ Silenciar';
        toggle.setAttribute('aria-label', audio.paused && audio.volume > 0 ? 'Reproducir música del Pantano Maldito' : audio.volume === 0 ? 'Activar música del Pantano Maldito' : 'Silenciar música del Pantano Maldito');
    };
    const sync = () => {
        controls.hidden = !eligible() || enteringCombat || cinematic();
        if (!restored) { update(); return; }
        if (!allowed()) {
            audio.pause();
            if (!eligible() || enteringCombat) { audio.currentTime = 0; write(sessionStorage, 'codeplay-swamp-position', 0); }
            update(); return;
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
        const position = Number(read(sessionStorage, 'codeplay-swamp-position'));
        if (eligible() && Number.isFinite(position) && position > 0 && position < audio.duration) audio.currentTime = position;
        restored = true; sync();
    });
    audio.addEventListener('playing', update);
    audio.addEventListener('pause', update);
    slider.addEventListener('input', () => {
        audio.volume = Number(slider.value) / 100;
        if (audio.volume > 0) lastVolume = audio.volume;
        userMuted = audio.volume === 0; write(localStorage, muteKey, userMuted); sync();
    });
    toggle.addEventListener('click', () => {
        if (audio.volume === 0) { audio.volume = lastVolume; userMuted = false; }
        else if (audio.paused) userMuted = false;
        else { audio.volume = 0; userMuted = true; }
        write(localStorage, muteKey, userMuted); sync();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('[data-combat-action="abrir"]')) { enteringCombat = true; sync(); }
    }, true);
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    document.addEventListener('codeplay:turn-rendered', () => { enteringCombat = false; sync(); });
    document.addEventListener('codeplay:action-error', () => { enteringCombat = false; sync(); });
    for (const el of document.querySelectorAll('#worldEntrance, #bossIntroduction')) new MutationObserver(sync).observe(el, { attributes: true, attributeFilter: ['hidden'] });
    window.addEventListener('pagehide', () => { write(sessionStorage, 'codeplay-swamp-position', allowed() ? audio.currentTime : 0); audio.pause(); });
    window.addEventListener('pageshow', () => { enteringCombat = false; sync(); });
    sync();
})();

(() => {
    'use strict';
    const audio = document.getElementById('iceMusic');
    const controls = document.getElementById('iceMusicControls');
    if (!audio || !controls) return;
    const slider = controls.querySelector('input');
    const toggle = controls.querySelector('button');
    const output = controls.querySelector('output');
    const read = (store, key) => { try { return store.getItem(key); } catch { return null; } };
    const write = (store, key, value) => { try { store.setItem(key, String(value)); } catch {} };
    const muteKey = 'codeplay-adventure-map-music-muted';
    let userMuted = read(localStorage, muteKey) === 'true';
    audio.muted = false;
    audio.volume = userMuted ? 0 : 0.35;
    let lastVolume = userMuted ? 0.35 : audio.volume;
    let enteringCombat = false;
    let pending = false;
    let restored = audio.readyState >= HTMLMediaElement.HAVE_METADATA;
    const eligible = () => document.getElementById('world')?.dataset.iceMusic === 'true';
    const cinematic = () => [...document.querySelectorAll('#worldEntrance, #bossIntroduction')].some(el => !el.hidden);
    const allowed = () => eligible() && !enteringCombat && !cinematic();
    const update = () => {
        slider.value = Math.round(audio.volume * 100);
        output.value = `${slider.value}%`;
        toggle.textContent = audio.paused && audio.volume > 0 ? '▶ Música' : audio.volume === 0 ? '♪ Activar' : '♪ Silenciar';
        toggle.setAttribute('aria-label', audio.paused && audio.volume > 0 ? 'Reproducir música del Montañas Heladas' : audio.volume === 0 ? 'Activar música del Montañas Heladas' : 'Silenciar música del Montañas Heladas');
    };
    const sync = () => {
        controls.hidden = !eligible() || enteringCombat || cinematic();
        if (!restored) { update(); return; }
        if (!allowed()) {
            audio.pause();
            if (!eligible() || enteringCombat) { audio.currentTime = 0; write(sessionStorage, 'codeplay-ice-position', 0); }
            update(); return;
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
        const position = Number(read(sessionStorage, 'codeplay-ice-position'));
        if (eligible() && Number.isFinite(position) && position > 0 && position < audio.duration) audio.currentTime = position;
        restored = true; sync();
    });
    audio.addEventListener('playing', update);
    audio.addEventListener('pause', update);
    slider.addEventListener('input', () => {
        audio.volume = Number(slider.value) / 100;
        if (audio.volume > 0) lastVolume = audio.volume;
        userMuted = audio.volume === 0; write(localStorage, muteKey, userMuted); sync();
    });
    toggle.addEventListener('click', () => {
        if (audio.volume === 0) { audio.volume = lastVolume; userMuted = false; }
        else if (audio.paused) userMuted = false;
        else { audio.volume = 0; userMuted = true; }
        write(localStorage, muteKey, userMuted); sync();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('[data-combat-action="abrir"]')) { enteringCombat = true; sync(); }
    }, true);
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    document.addEventListener('codeplay:turn-rendered', () => { enteringCombat = false; sync(); });
    document.addEventListener('codeplay:action-error', () => { enteringCombat = false; sync(); });
    for (const el of document.querySelectorAll('#worldEntrance, #bossIntroduction')) new MutationObserver(sync).observe(el, { attributes: true, attributeFilter: ['hidden'] });
    window.addEventListener('pagehide', () => { write(sessionStorage, 'codeplay-ice-position', allowed() ? audio.currentTime : 0); audio.pause(); });
    window.addEventListener('pageshow', () => { enteringCombat = false; sync(); });
    sync();
})();

(() => {
    'use strict';
    const audio = document.getElementById('dragonMusic');
    const controls = document.getElementById('dragonMusicControls');
    if (!audio || !controls) return;
    const slider = controls.querySelector('input');
    const toggle = controls.querySelector('button');
    const output = controls.querySelector('output');
    const read = (store, key) => { try { return store.getItem(key); } catch { return null; } };
    const write = (store, key, value) => { try { store.setItem(key, String(value)); } catch {} };
    const muteKey = 'codeplay-adventure-map-music-muted';
    let userMuted = read(localStorage, muteKey) === 'true';
    audio.muted = false;
    audio.volume = userMuted ? 0 : 0.35;
    let lastVolume = userMuted ? 0.35 : audio.volume;
    let enteringCombat = false;
    let pending = false;
    let restored = audio.readyState >= HTMLMediaElement.HAVE_METADATA;
    const eligible = () => document.getElementById('world')?.dataset.dragonMusic === 'true';
    const cinematic = () => [...document.querySelectorAll('#worldEntrance, #bossIntroduction')].some(el => !el.hidden);
    const allowed = () => eligible() && !enteringCombat && !cinematic();
    const update = () => {
        slider.value = Math.round(audio.volume * 100);
        output.value = `${slider.value}%`;
        toggle.textContent = audio.paused && audio.volume > 0 ? '▶ Música' : audio.volume === 0 ? '♪ Activar' : '♪ Silenciar';
        toggle.setAttribute('aria-label', audio.paused && audio.volume > 0 ? 'Reproducir música del Tierras del Dragón' : audio.volume === 0 ? 'Activar música del Tierras del Dragón' : 'Silenciar música del Tierras del Dragón');
    };
    const sync = () => {
        controls.hidden = !eligible() || enteringCombat || cinematic();
        if (!restored) { update(); return; }
        if (!allowed()) {
            audio.pause();
            if (!eligible() || enteringCombat) { audio.currentTime = 0; write(sessionStorage, 'codeplay-dragon-position', 0); }
            update(); return;
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
        const position = Number(read(sessionStorage, 'codeplay-dragon-position'));
        if (eligible() && Number.isFinite(position) && position > 0 && position < audio.duration) audio.currentTime = position;
        restored = true; sync();
    });
    audio.addEventListener('playing', update);
    audio.addEventListener('pause', update);
    slider.addEventListener('input', () => {
        audio.volume = Number(slider.value) / 100;
        if (audio.volume > 0) lastVolume = audio.volume;
        userMuted = audio.volume === 0; write(localStorage, muteKey, userMuted); sync();
    });
    toggle.addEventListener('click', () => {
        if (audio.volume === 0) { audio.volume = lastVolume; userMuted = false; }
        else if (audio.paused) userMuted = false;
        else { audio.volume = 0; userMuted = true; }
        write(localStorage, muteKey, userMuted); sync();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('[data-combat-action="abrir"]')) { enteringCombat = true; sync(); }
    }, true);
    document.addEventListener('click', sync);
    document.addEventListener('keydown', sync);
    document.addEventListener('codeplay:turn-rendered', () => { enteringCombat = false; sync(); });
    document.addEventListener('codeplay:action-error', () => { enteringCombat = false; sync(); });
    for (const el of document.querySelectorAll('#worldEntrance, #bossIntroduction')) new MutationObserver(sync).observe(el, { attributes: true, attributeFilter: ['hidden'] });
    window.addEventListener('pagehide', () => { write(sessionStorage, 'codeplay-dragon-position', allowed() ? audio.currentTime : 0); audio.pause(); });
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

(() => {
    'use strict';
    const audio = document.getElementById('ruinsBattleMusic');
    if (!audio) return;
    let pending = false;
    const active = () => document.getElementById('world')?.dataset.ruinsBattleMusic === 'true';
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
(() => {
    'use strict';
    const audio = document.getElementById('swampBattleMusic');
    if (!audio) return;
    let pending = false;
    const active = () => document.getElementById('world')?.dataset.swampBattleMusic === 'true';
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
(() => {
    'use strict';
    const audio = document.getElementById('iceBattleMusic');
    if (!audio) return;
    let pending = false;
    const active = () => document.getElementById('world')?.dataset.iceBattleMusic === 'true';
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
(() => {
    'use strict';
    const audio = document.getElementById('dragonBattleMusic');
    if (!audio) return;
    let pending = false;
    const active = () => document.getElementById('world')?.dataset.dragonBattleMusic === 'true';
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
