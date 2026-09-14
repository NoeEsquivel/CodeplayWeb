// Run with: node tests/Browser/aventura-entrance.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const sourcePath = process.argv[2] || path.resolve(__dirname, '../../public/js/aventura-mapa.js');
const source = fs.readFileSync(sourcePath, 'utf8');
const begin = source.slice(source.indexOf('    function beginWorldEntrance('), source.indexOf('    async function playBossIntroduction('));

function fixture({ rejectPlay = false, reducedMotion = false } = {}) {
    const video = new EventTarget();
    Object.assign(video, { currentTime: 0, duration: 9.709, ended: false, error: null, paused: true, calls: 0,
        play() { this.calls++; this.paused = false; return rejectPlay ? Promise.reject(new Error('NotAllowedError')) : Promise.resolve(); },
        pause() { this.paused = true; }, load() { this.error = null; }
    });
    const timers = new Map(); let id = 0;
    const start = new Function('entranceVideo', 'document', 'matchMedia', 'setTimeout', 'clearTimeout', 'console', begin + '\nreturn beginWorldEntrance;')(
        video, {getElementById: () => ({textContent: ''})}, () => ({matches: reducedMotion}),
        fn => {timers.set(++id, fn);return id;}, key => timers.delete(key), {warn() {}}
    );
    return {video, timers, start};
}

for (const reducedMotion of [false, true]) {
    test(`video must actually finish (reduced motion: ${reducedMotion})`, async () => {
        const {video, timers, start} = fixture({reducedMotion});
        let completed = false;
        const playback = start().then(() => {completed = true;});
        assert.equal(video.calls, 1);
        assert.equal(video.muted, false);
        assert.equal(video.volume, 1);
        assert.equal(video.paused, false);
        for (const time of [0.5, 4, 9]) {
            video.currentTime = time;video.dispatchEvent(new Event('timeupdate'));
            await Promise.resolve();assert.equal(completed, false);
        }
        video.currentTime = video.duration;video.ended = true;video.dispatchEvent(new Event('ended'));
        await playback;assert.equal(completed, true);assert.equal(timers.size, 0);
    });
}
test('blocked playback rejects instead of unlocking the map', async () => {
    const {start, video, timers} = fixture({rejectPlay: true});
    await assert.rejects(start(), /no pudo iniciar/);
    assert.equal(video.paused, true);assert.equal(timers.size, 0);
});
test('media failure rejects and a new click can replay', async () => {
    const {start, video} = fixture();
    const first = start();const rejected = assert.rejects(first, /No se pudo reproducir/);
    video.error = {code: 3};video.dispatchEvent(new Event('error'));await rejected;
    const retry = start();assert.equal(video.calls, 2);assert.equal(video.error, null);
    video.currentTime = video.duration;video.ended = true;video.dispatchEvent(new Event('ended'));await retry;
});
test('a stalled video times out as an error, never a completed movie', async () => {
    const {start, timers} = fixture();
    const rejected = assert.rejects(start(), /no pudo seguir/);
    [...timers.values()][0]();await rejected;assert.equal(timers.size, 0);
});
test('premature ended event cannot unlock the map', async () => {
    const {start, video} = fixture();const rejected = assert.rejects(start(), /interrumpió/);
    video.currentTime = 1;video.ended = true;video.dispatchEvent(new Event('ended'));await rejected;
});

test('boss cinematic plays the selected remote video without starting the adventure movie', async () => {
    const {video: adventure, start} = fixture();
    const {video: boss} = fixture();
    const status = {textContent: ''};
    const playback = start({video: boss, status, playingText: '', loadingText: 'Cargando a Silvar'});
    assert.equal(boss.calls, 1);assert.equal(adventure.calls, 0);
    boss.dispatchEvent(new Event('waiting'));assert.equal(status.textContent, 'Cargando a Silvar');
    boss.dispatchEvent(new Event('playing'));assert.equal(status.textContent, '');
    boss.currentTime = boss.duration;boss.ended = true;boss.dispatchEvent(new Event('ended'));await playback;
});
