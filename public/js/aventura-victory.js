(() => {
    const scene = document.getElementById('victoryCinematic');
    if (!scene) return;
    const video = scene.querySelector('video');
    const status = scene.querySelector('[data-victory-status]');
    const retry = scene.querySelector('[data-victory-retry]');
    const epilogue = document.querySelector('.epilogue');
    const key = `codeplay:victory-cinematic:${scene.dataset.run}`;
    let running = false, complete = false, watchdog, lastTime = 0;
    const reveal = () => {
        complete = true;
        clearTimeout(watchdog);
        scene.hidden = true;
        epilogue.hidden = false;
        epilogue.inert = false;
        document.dispatchEvent(new CustomEvent('codeplay:epilogue-revealed'));
        document.getElementById('encuentro-title')?.focus();
    };
    try { if (sessionStorage.getItem(key) === 'complete') { reveal(); return; } } catch (_) {}
    const fail = () => {
        if (complete) return;
        running = false;
        clearTimeout(watchdog);
        video.pause();
        status.textContent = 'Tu victoria está guardada. Pulsa para reproducir la batalla final.';
        retry.hidden = false;
        retry.focus();
    };
    const arm = () => { clearTimeout(watchdog); watchdog = setTimeout(fail, 30000); };
    const play = () => {
        if (running || complete) return;
        running = true;
        retry.hidden = true;
        status.textContent = 'Preparando el último combate…';
        lastTime = 0;
        arm();
        try {
            if (video.error) video.load();
            video.currentTime = 0;
            video.muted = false;
            video.volume = 1;
            video.playbackRate = 1;
            video.play().catch(fail);
        } catch (_) { fail(); }
    };
    video.addEventListener('ended', () => {
        if (!running || complete) return;
        if (!video.ended || !Number.isFinite(video.duration) || video.currentTime < video.duration - .25) { fail(); return; }
        try { sessionStorage.setItem(key, 'complete'); } catch (_) {}
        reveal();
    });
    video.addEventListener('error', fail);
    video.addEventListener('playing', () => { status.textContent = ''; });
    video.addEventListener('waiting', () => { status.textContent = 'Cargando la batalla final…'; });
    video.addEventListener('timeupdate', () => { if (running && video.currentTime > lastTime) { lastTime = video.currentTime; arm(); } });
    retry.addEventListener('click', play);
    play();
})();
