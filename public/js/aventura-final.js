(() => {
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
    document.querySelector('form')?.addEventListener('submit', event => {
        const button = event.currentTarget.querySelector('button');
        button.disabled = true;
        button.textContent = 'Regresando al campamento…';
    });
})();
