/* Run with NODE_PATH pointing to installed Playwright and PHP_BINARY set.
   Uses a local fixture server; no real account, payment or reward is changed. */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const project = path.resolve(__dirname, '../..');
const php = process.env.PHP_BINARY || 'php';
const helper = path.join(__dirname, 'arcade20-support.php');
const solution = JSON.parse(fs.readFileSync(path.join(project, 'tests/Fixtures/mini-arcade-solution.json'), 'utf8'));
let html, base, awards = 0;
const screenshots = process.env.ARCADE_SCREENSHOTS;
const phpChecks = data => JSON.parse(execFileSync(php, [helper, 'checks'], { input: data, encoding: 'utf8', cwd: project }));
const server = http.createServer(async (req, res) => {
    const url = new URL(req.url, base);
    if (req.method === 'POST') {
        let body = ''; for await (const chunk of req) body += chunk;
        const data = JSON.parse(body);
        const result = phpChecks(data.codigo);
        if (url.pathname === '/desafios/procesar-recompensa') {
            assert(Object.values(result.checks).every(Boolean), 'Cannot award incomplete code');
            assert.equal(data.nivel, 20);
            awards++;
            res.setHeader('Content-Type', 'application/json');
            return res.end(JSON.stringify({ success: true, puntos_otorgados: 1000 }));
        }
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify(result));
    }
    if (url.pathname === '/alumno/desafios/nivel20') { res.setHeader('Content-Type', 'text/html;charset=utf-8'); return res.end(html); }
    const file = path.resolve(project, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(project, 'public') + path.sep) || !fs.existsSync(file) || !fs.statSync(file).isFile()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', { '.js': 'application/javascript', '.css': 'text/css', '.jpg': 'image/jpeg', '.html': 'text/html' }[path.extname(file)] || 'application/octet-stream');
    res.end(fs.readFileSync(file));
});

(async () => {
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
    html = execFileSync(php, [helper, 'render', base], { encoding: 'utf8', cwd: project, maxBuffer: 2e6 });
    const browser = await chromium.launch({ headless: true, executablePath: process.env.BROWSER_BINARY || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' });
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        // Keep the fixture entirely local. The textarea fallback is part of the app.
        await page.route('**/*', route => new URL(route.request().url()).origin === base || process.env.ARCADE_ONLINE === '1' ? route.continue() : route.abort());
        await page.goto(base + '/alumno/desafios/nivel20');
        await page.locator('#previewFrame').contentFrame().locator('h1').waitFor();
        if (process.env.ARCADE_ONLINE === '1') assert.equal(await page.locator('.CodeMirror').count(), 3, 'CodeMirror must load in online mode');
        assert(await page.locator('#submitButton').isDisabled());
        await page.click('#checkButton');
        await page.waitForFunction(() => document.getElementById('progressCount').textContent === '1 / 9');
        assert(await page.locator('#submitButton').isDisabled());
        assert.equal(awards, 0);
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        console.log('PASS starter rejected: 1/9 and no award');

        const edit = async (view, source) => {
            await page.click(`[data-editor="${view}"]`);
            await page.evaluate(({ view, source }) => {
                const textarea = document.getElementById(`editor-${view}`);
                const wrapper = textarea.parentElement.querySelector('.CodeMirror');
                if (wrapper?.CodeMirror) wrapper.CodeMirror.setValue(source);
                else { textarea.value = source; textarea.dispatchEvent(new Event('input', { bubbles: true })); }
            }, { view, source });
        };
        for (const [view, source] of Object.entries(solution)) await edit(view, source);
        await page.click('#checkButton');
        await page.waitForFunction(() => document.getElementById('progressCount').textContent === '9 / 9');
        assert(await page.locator('#submitButton').isEnabled());
        console.log('PASS completed functions: 9/9 both server and sandbox');

        await page.click('#previewHome');
        let game = page.locator('#previewFrame').contentFrame();
        await game.locator('[data-vista="culebrita"]').click();
        game = page.locator('#previewFrame').contentFrame();
        await game.locator('#iniciar').click();
        await game.locator('#puntos').filter({ hasText: /^10$/ }).waitFor({ timeout: 5000 });
        await game.locator('#mensaje').filter({ hasText: 'chocaste con la pared' }).waitFor({ timeout: 6000 });
        await game.locator('#iniciar').click();
        assert.equal(await game.locator('#puntos').textContent(), '0');
        await game.locator('[data-vista="menu"]').click();
        console.log('PASS snake: movement, food +10, wall collision, restart');

        game = page.locator('#previewFrame').contentFrame();
        await game.locator('[data-vista="memoria"]').click();
        game = page.locator('#previewFrame').contentFrame();
        await game.locator('.carta').first().waitFor();
        const cards = await game.locator('.carta').evaluateAll(cards => cards.map(card => ({ id: card.dataset.indice, value: card.dataset.valor })));
        assert.equal(cards.length, 12);
        const wrong = cards.find(card => card.value !== cards[0].value);
        await game.locator(`[data-indice="${cards[0].id}"]`).click();
        await game.locator(`[data-indice="${cards[0].id}"]`).click();
        assert.equal(await game.locator('.abierta').count(), 1);
        await game.locator(`[data-indice="${wrong.id}"]`).click();
        await game.locator('.carta.abierta').first().waitFor({ state: 'hidden' });
        assert.equal(await game.locator('#puntos').textContent(), '0');
        const grouped = Object.groupBy(cards, card => card.value);
        for (const pair of Object.values(grouped)) for (const card of pair) await game.locator(`[data-indice="${card.id}"]`).click();
        assert.equal(await game.locator('#puntos').textContent(), '120');
        assert.equal(await game.locator('.encontrada').count(), 12);
        assert((await game.locator('#mensaje').textContent()).includes('todas las parejas'));
        await game.locator('#reiniciar').click();
        assert.equal(await game.locator('#puntos').textContent(), '0');
        assert.equal(await game.locator('.encontrada').count(), 0);
        console.log('PASS memory: 12 cards, same-card guard, mismatch, six pairs +120, restart');

        // Editing invalidates previous validation; stale results cannot enable delivery.
        await edit('memoria', solution.memoria.replace('return puntos + 20;', 'return puntos + 10;'));
        assert(await page.locator('#submitButton').isDisabled());
        await page.click('#checkButton');
        await page.waitForFunction(() => document.getElementById('progressCount').textContent === '8 / 9');
        assert.equal(await page.locator('[data-check="memoryScore"]').getAttribute('data-ok'), 'false');
        console.log('PASS wrong score rejected and delivery disabled after edits');

        await edit('memoria', solution.memoria);
        await page.click('#checkButton');
        await page.waitForFunction(() => document.getElementById('progressCount').textContent === '9 / 9');
        // A valid check alone must not celebrate; a failed reward must not either.
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        await page.route('**/desafios/procesar-recompensa', route => route.fulfill({ status: 500, contentType: 'application/json', body: JSON.stringify({ success: false, message: 'Entrega no registrada' }) }));
        await page.click('#submitButton');
        await page.locator('#feedback').filter({ hasText: 'Entrega no registrada' }).waitFor();
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        assert(await page.locator('#finalAchievement').isHidden());
        await page.unroute('**/desafios/procesar-recompensa');
        await page.click('#submitButton');
        await page.locator('#feedback').filter({ hasText: '1000 puntos de CodePlay' }).waitFor();
        assert.equal(awards, 1); // mocked reward endpoint; no real account/DB.
        assert(await page.locator('#submitButton').isDisabled());
        assert.equal(await page.locator('#previewFrame').getAttribute('sandbox'), 'allow-scripts');
        assert.equal(errors.length, 0, errors.join('\n'));
        await page.locator('#finalCelebration').waitFor({ state: 'visible' });
        assert.equal(await page.locator('#celebrationReward').textContent(), '+ 1000 puntos de CodePlay');
        assert(await page.locator('#finalAchievement').isVisible());
        assert(await page.locator('#closeCelebration').evaluate(el => document.activeElement === el));
        if (screenshots) {
            fs.mkdirSync(screenshots, { recursive: true });
            await page.screenshot({ path: path.join(screenshots, 'celebration-desktop.png') });
        }
        await page.setViewportSize({ width: 390, height: 844 });
        assert(await page.locator('#finalCelebration').evaluate(el => el.scrollWidth <= el.clientWidth));
        await page.locator('#closeCelebration').scrollIntoViewIfNeeded();
        if (screenshots) await page.screenshot({ path: path.join(screenshots, 'celebration-mobile.png') });
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        assert.equal(await page.locator('#celebrationConfetti i').count(), 0);
        assert(await page.locator('#checkButton').evaluate(el => document.activeElement === el));
        await page.setViewportSize({ width: 1440, height: 1000 });
        console.log('PASS celebration only after successful delivery; mobile, Escape and focus restoration');

        // The optional export must remain playable without a server or network.
        const downloadPromise = page.waitForEvent('download');
        await page.click('#downloadProject');
        const download = await downloadPromise;
        const exportDirectory = screenshots || require('node:os').tmpdir();
        fs.mkdirSync(exportDirectory, { recursive: true });
        const exportedFile = path.join(exportDirectory, 'arcade20-export.html');
        await download.saveAs(exportedFile);
        const offline = await browser.newPage();
        const { pathToFileURL } = require('node:url');
        await offline.goto(pathToFileURL(exportedFile).href);
        let exportedGame = offline.locator('iframe').contentFrame();
        await exportedGame.locator('[data-vista="memoria"]').click();
        exportedGame = offline.locator('iframe').contentFrame();
        await exportedGame.locator('.carta').first().waitFor();
        assert.equal(await exportedGame.locator('.carta').count(), 12);
        await offline.close();
        console.log('PASS downloadable arcade works offline');

        await page.reload();
        await page.click('[data-editor="memoria"]');
        assert(await page.locator('#submitButton').isDisabled(), 'Reload requires fresh validation');
        await page.click('#runButton');
        await page.locator('#previewFrame').contentFrame().locator('.carta').first().waitFor();
        console.log('PASS draft survives reload; old validation is not reused');
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.click('#checkButton');
        await page.waitForFunction(() => document.getElementById('progressCount').textContent === '9 / 9');
        await page.click('#submitButton');
        await page.locator('#finalCelebration').waitFor({ state: 'visible' });
        assert.equal(await page.locator('#celebrationConfetti i').count(), 0);
        assert.equal(await page.locator('#finalCelebration').evaluate(el => getComputedStyle(el).animationName), 'none');
        await page.click('#closeCelebration');
        assert.equal(await page.locator('#finalCelebration').evaluate(el => el.open), false);
        console.log('PASS reduced motion and close button; reload does not replay celebration');

        if (screenshots) {
            fs.mkdirSync(screenshots, { recursive: true });
            await page.locator('#previewHost').scrollIntoViewIfNeeded();
            await page.locator('#previewFrame').contentFrame().locator('.carta').first().waitFor();
            await page.screenshot({ path: path.join(screenshots, 'arcade20-desktop.png'), fullPage: true });
        }
        await page.setViewportSize({ width: 390, height: 844 });
        await page.click('#previewHome');
        game = page.locator('#previewFrame').contentFrame();
        await game.locator('[data-vista="memoria"]').click();
        game = page.locator('#previewFrame').contentFrame();
        await game.locator('.carta').first().waitFor();
        const widths = await page.evaluate(() => ({ document: document.documentElement.scrollWidth, viewport: innerWidth }));
        assert(widths.document <= widths.viewport, JSON.stringify(widths));
        assert.equal(await game.locator('#tableroMemoria').evaluate(el => getComputedStyle(el).gridTemplateColumns.split(' ').length), 4);
        if (screenshots) await page.screenshot({ path: path.join(screenshots, 'arcade20-mobile.png'), fullPage: true });
        console.log('PASS responsive 390px, four memory columns, no horizontal overflow');
        console.log('PASS complete interface and mocked delivery; no real rewards changed');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; }).finally(() => server.close());
