(() => {
    const music = document.getElementById('finalEpilogueMusic');
    let musicStartPending = false;
    const stopMusic = () => {
        if (!music) return;
        music.pause();
        music.currentTime = 0;
    };
    const playMusic = () => {
        if (!music || !music.paused || musicStartPending) return;
        musicStartPending = true;
        music.volume = 0.46;
        music.play().catch(() => {
            // A later interaction can satisfy browsers that require it for audio playback.
            document.addEventListener('pointerdown', playMusic, { once: true });
        }).finally(() => { musicStartPending = false; });
    };
    document.addEventListener('codeplay:epilogue-revealed', playMusic);
    document.querySelectorAll('[data-stop-epilogue-music]').forEach(link => link.addEventListener('click', stopMusic));
    window.addEventListener('pagehide', stopMusic);
    const chapters = [...document.querySelectorAll('.chapter')];
    const buttons = [...document.querySelectorAll('[data-next]')];
    const show = (id, focus = true) => {
        chapters.forEach(chapter => { chapter.hidden = chapter.id !== id; });
        document.body.dataset.chapter = id;
        document.querySelector('.skip').hidden = id === 'logro';
        if (focus) document.querySelector(`#${id} h1, #${id} h2`).focus();
    };
    buttons.forEach(button => {
        button.hidden = false;
        button.addEventListener('click', () => show(button.dataset.next));
    });
    show('encuentro', false);
    if (!document.querySelector('.epilogue')?.hidden) playMusic();
    document.querySelector('form')?.addEventListener('submit', event => {
        stopMusic();
        const button = event.currentTarget.querySelector('button');
        button.disabled = true;
        button.textContent = 'Regresando al campamento…';
    });
})();
