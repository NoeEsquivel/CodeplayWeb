(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory();
    else root.ArcadeChecks = factory();
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    function check(id, message, callback) {
        let ok = false;
        try { ok = callback() === true; } catch (_) { ok = false; }
        return { id, ok, message };
    }

    function hasScore(doc) {
        const score = doc.getElementById('puntos');
        return Boolean(score && /^\d+$/.test(score.textContent.trim()));
    }

    function run(view, win) {
        const doc = win.document;
        if (view === 'menu') {
            return [
                check('menuTitle', 'Pon un nombre propio de al menos 4 caracteres a tu arcade y conserva los dos botones de juego.', function () {
                    const title = doc.querySelector('h1');
                    const text = title ? title.textContent.trim() : '';
                    return text.length >= 4 && text.toLocaleLowerCase() !== 'mi mini arcade'
                        && Boolean(doc.querySelector('[data-vista="culebrita"]'))
                        && Boolean(doc.querySelector('[data-vista="memoria"]'));
                }),
                check('menuDescription', 'Explica cómo jugar en el párrafo #descripcion usando al menos 35 caracteres.', function () {
                    const description = doc.getElementById('descripcion');
                    const text = description ? description.textContent.trim() : '';
                    return text.length >= 35 && !text.toLocaleLowerCase().includes('escribe aquí cómo jugar');
                })
            ];
        }
        if (view === 'culebrita') {
            return [
                check('snakeMove', 'siguienteCabeza debe sumar x e y sin modificar los objetos recibidos. Conserva el canvas y el botón de iniciar.', function () {
                    if (typeof win.siguienteCabeza !== 'function' || !doc.querySelector('canvas#tableroSerpiente')
                        || !doc.getElementById('iniciar') || !doc.querySelector('[data-vista="menu"]')) return false;
                    return [
                        [{x: 4, y: 3}, {x: 1, y: 0}, {x: 5, y: 3}],
                        [{x: 4, y: 3}, {x: -1, y: 0}, {x: 3, y: 3}],
                        [{x: 6, y: 5}, {x: 0, y: 1}, {x: 6, y: 6}],
                        [{x: 6, y: 5}, {x: 0, y: -1}, {x: 6, y: 4}],
                        [{x: 0, y: 0}, {x: -1, y: 0}, {x: -1, y: 0}]
                    ].every(function (test) {
                        const head = {...test[0]};
                        const direction = {...test[1]};
                        const next = win.siguienteCabeza(head, direction);
                        return Boolean(next && next !== head && next !== direction
                            && next.x === test[2].x && next.y === test[2].y
                            && head.x === test[0].x && head.y === test[0].y
                            && direction.x === test[1].x && direction.y === test[1].y);
                    });
                }),
                check('snakeWall', 'chocaConPared debe detectar las cuatro paredes y permitir todas las casillas interiores.', function () {
                    if (typeof win.chocaConPared !== 'function') return false;
                    return [
                        [{x: -1, y: 4}, 12, true], [{x: 12, y: 4}, 12, true],
                        [{x: 4, y: -1}, 12, true], [{x: 4, y: 12}, 12, true],
                        [{x: 0, y: 0}, 12, false], [{x: 11, y: 11}, 12, false],
                        [{x: 5, y: 5}, 12, false], [{x: 7, y: 1}, 7, true],
                        [{x: 6, y: 6}, 7, false]
                    ].every(function (test) {
                        const head = {...test[0]};
                        return win.chocaConPared(head, test[1]) === test[2]
                            && head.x === test[0].x && head.y === test[0].y;
                    });
                }),
                check('snakeScore', 'puntosPorComida debe sumar 10 a los puntos actuales. Conserva el contador #puntos.', function () {
                    return typeof win.puntosPorComida === 'function' && hasScore(doc)
                        && [0, 10, 40, 110].every(value => win.puntosPorComida(value) === value + 10);
                })
            ];
        }
        if (view === 'memoria') {
            return [
                check('memoryMatch', 'sonPareja compara los dos símbolos. Conserva las 12 cartas, el regreso al menú y el reinicio.', function () {
                    const board = doc.getElementById('tableroMemoria');
                    if (typeof win.sonPareja !== 'function' || !board || board.querySelectorAll('.carta').length !== 12
                        || !doc.getElementById('reiniciar') || !doc.querySelector('[data-vista="menu"]')) return false;
                    return [
                        ['🍒', '🍒', true], ['🚀', '🚀', true],
                        ['🍒', '🚀', false], ['⭐', '🎮', false],
                        ['A', 'A', true], ['A', 'a', false]
                    ].every(test => win.sonPareja(test[0], test[1]) === test[2]);
                }),
                check('memoryScore', 'puntosPorPareja debe sumar 20 a los puntos actuales. Conserva el contador #puntos.', function () {
                    return typeof win.puntosPorPareja === 'function' && hasScore(doc)
                        && [0, 20, 60, 100].every(value => win.puntosPorPareja(value) === value + 20);
                }),
                check('memoryWin', 'partidaTerminada devuelve true al encontrar las 6 parejas y false antes.', function () {
                    return typeof win.partidaTerminada === 'function'
                        && [0, 1, 3, 5, 6].every(value => win.partidaTerminada(value) === (value === 6));
                })
            ];
        }
        return [];
    }

    return { run };
}));
