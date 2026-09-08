// Real server-side service + disposable SQLite; does not modify any real account.
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const crypto = require('node:crypto');
const http = require('node:http');
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const project = path.resolve(__dirname, '../..');
const database = path.join(os.tmpdir(), 'codeplay-aventura-test-' + crypto.randomBytes(8).toString('hex') + '.sqlite');
const php = process.env.PHP_BINARY || 'php';
const helper = path.join(__dirname, 'aventura-support.php');
let base;
const run = (mode, input) => execFileSync(php, ['-d', 'extension=pdo_sqlite', helper, mode, database, base || 'http://localhost'], { cwd: project, encoding: 'utf8', input, maxBuffer: 2e6 });
const server = http.createServer(async (req, res) => {
    try {
        const url = new URL(req.url, base);
        if (req.method === 'POST') {
            let body = ''; for await (const chunk of req) body += chunk;
            const result = JSON.parse(run('save', body));
            res.statusCode = result.status || 200; res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify(result)); return;
        }
        if (url.pathname === '/alumno/aventura') { res.setHeader('Content-Type', 'text/html;charset=utf-8'); res.end(run('render')); return; }
        const file = path.resolve(project, 'public', '.' + decodeURIComponent(url.pathname));
        if (!file.startsWith(path.join(project, 'public') + path.sep) || !fs.existsSync(file) || !fs.statSync(file).isFile()) { res.statusCode = 404; res.end(); return; }
        res.setHeader('Content-Type', { '.css': 'text/css', '.js': 'application/javascript', '.svg': 'image/svg+xml' }[path.extname(file)] || 'application/octet-stream');
        res.end(fs.readFileSync(file));
    } catch (error) { res.statusCode = 500; res.end(String(error)); }
});
(async () => {
    run('init');
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({ headless: true, executablePath: process.env.BROWSER_BINARY || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' });
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 1050 } });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', route => new URL(route.request().url()).origin === base || process.env.ADVENTURE_ONLINE === '1' ? route.continue() : route.abort());
        await page.goto(base + '/alumno/aventura');
        assert.equal(await page.locator('.item-card').count(), 12);
        assert.equal(await page.locator('#maxHealth').textContent(), '100');
        assert.equal(await page.locator('#maxMana').textContent(), '50');
        assert(await page.locator('[data-item="compilador-origen"] button').isDisabled());
        await page.locator('[data-item="espada-viajero"] button').click();
        await page.click('#cat-armadura');
        assert(await page.locator('[data-item="coraza-caja"] button').isEnabled());
        await page.locator('[data-item="coraza-caja"] button').click();
        assert.equal(await page.locator('#maxHealth').textContent(), '110');
        await page.click('#cat-hechizo');
        await page.locator('[data-item="brasa"] button').click();
        await page.locator('[data-item="alivio"] button').click();
        await page.locator('[data-item="escarcha"] button').click();
        assert((await page.locator('#shopNotice').textContent()).includes('dos hechizos'));
        await page.click('#cat-pocion');
        await page.getByRole('button', { name: 'Añadir Poción de vida', exact: true }).click();
        await page.getByRole('button', { name: 'Añadir Poción de vida', exact: true }).click();
        assert.equal(await page.locator('#draftCost').textContent(), '195');
        await page.click('#reviewButton');
        await page.click('#cancelPurchase');
        assert.equal(await page.locator('#walletReserved').textContent(), '0');
        await page.click('#reviewButton');
        await page.click('#confirmPurchase');
        await page.locator('#saveMessage.success').waitFor();
        assert.equal(await page.locator('#walletReserved').textContent(), '195');
        assert.equal(await page.locator('#walletAvailable').textContent(), '1205');
        await page.reload();
        assert.equal(await page.locator('#walletReserved').textContent(), '195');
        assert.equal(await page.locator('#maxHealth').textContent(), '110');
        console.log('PASS preview, exact challenge unlock, two-spell limit, multiple potions, cancel, server purchase and reload');
        await page.click('#cat-armadura');
        const reports = process.env.ADVENTURE_SCREENSHOTS;
        if (reports) { fs.mkdirSync(reports, { recursive: true }); await page.screenshot({ path: path.join(reports, 'camp-desktop.png'), fullPage: true }); }
        await page.click('#clearDraft');
        await page.click('#reviewButton');
        await page.click('#confirmPurchase');
        await page.waitForFunction(() => document.getElementById('walletReserved').textContent === '0');
        assert.equal(await page.locator('#walletAvailable').textContent(), '1400');
        assert.equal(await page.locator('#maxHealth').textContent(), '100');
        await page.click('#adventureButton');
        assert(await page.locator('#adventureDialog').isVisible());
        await page.keyboard.press('Escape');
        console.log('PASS full equipment refund and no premature adventure start');
        await page.setViewportSize({ width: 390, height: 844 });
        await page.click('#cat-pocion');
        await page.selectOption('#rarityFilter', 'legendario');
        assert(await page.locator('.empty-results').isVisible());
        await page.selectOption('#rarityFilter', '');
        assert.equal(await page.locator('.item-card').count(), 4);
        await page.click('#cat-arma');
        await page.check('#unlockedFilter');
        assert.equal(await page.locator('.item-card').count(), 10);
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
        if (reports) await page.screenshot({ path: path.join(reports, 'camp-mobile.png'), fullPage: true });
        assert.deepEqual(errors, []);
        console.log('PASS filters, mobile layout and no JavaScript errors');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; }).finally(() => { server.close(); if (fs.existsSync(database)) fs.unlinkSync(database); });
