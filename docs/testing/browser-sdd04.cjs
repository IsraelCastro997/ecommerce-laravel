const { chromium } = require(process.env.SDD_PLAYWRIGHT_MODULE || 'playwright-core');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const page = await context.newPage();
    const base = process.env.SDD_BASE_URL || 'http://127.0.0.1:8010';
    const output = path.join(__dirname, 'screenshots', 'sdd04');
    fs.mkdirSync(output, { recursive: true });
    const errors = [];
    const checks = [];
    page.on('pageerror', error => errors.push(error.message));
    const visit = async url => {
        const response = await page.goto(base + url);
        assert.equal(response.status(), 200, url);
        await page.waitForFunction(() => Boolean(window.Alpine?.store('shop')));
        await page.locator('main').waitFor();
        checks.push(url);
    };
    try {
        await visit('/');
        assert.equal(await page.locator('.product-card').count(), 4);
        await page.screenshot({ path: path.join(output, 'home-desktop.png'), fullPage: true });
        await page.getByRole('searchbox', { name: 'Buscar', exact: true }).fill('Sierra');
        assert.equal(await page.locator('.product-card').count(), 1);
        await page.getByRole('button', { name: 'Agregar a bolsa' }).click();
        await page.getByRole('button', { name: 'Limpiar filtros' }).click();
        assert.equal(await page.locator('.product-card').count(), 4);
        await page.getByRole('combobox', { name: 'Moneda', exact: true }).selectOption('USD');
        await visit('/carrito');
        assert.equal(await page.locator('.cart-list li').count(), 1);
        assert.match(await page.locator('main').innerText(), /USD/);
        await page.getByRole('button', { name: 'Sumar Mochila Sierra 35 L' }).click();
        await page.reload();
        await page.waitForFunction(() => window.Alpine?.store('shop')?.count === 2);
        await page.screenshot({ path: path.join(output, 'cart-desktop.png'), fullPage: true });
        await visit('/productos/MO-035');
        await page.getByRole('heading', { name: 'Mochila Sierra 35 L', exact: true }).waitFor();
        await visit('/checkout');
        await page.getByRole('textbox', { name: 'Correo electrónico', exact: true }).fill('sample@example.invalid');
        await page.getByRole('button', { name: 'Solicitar compra al servidor' }).click();
        await page.getByText('El catálogo de ejemplo todavía no tiene IDs reales de la base de datos.', { exact: true }).waitFor();
        // Synthetic frontend DTO only. The transport stub must not persist an order.
        await page.evaluate(() => { window.Alpine.store('shop').products[0].product_id = 42; });
        const posted = page.waitForRequest(request => request.method() === 'POST' && new URL(request.url()).pathname === '/checkout');
        await page.getByRole('button', { name: 'Solicitar compra al servidor' }).click();
        const payload = (await posted).postDataJSON();
        assert.deepEqual(payload.items, [{ product_id: 42, quantity: 2 }]);
        assert.deepEqual(Object.keys(payload).sort(), ['currency','email','items']);
        await page.getByText('La ruta de checkout está conectada.', { exact: false }).waitFor();
        await visit('/pedido-confirmado/123');
        await page.getByRole('heading', { name: 'Sin confirmación del servidor' }).waitFor();
        await visit('/register');
        const email = `presentation-${Date.now()}@example.invalid`;
        await page.getByLabel('Nombre', { exact: true }).fill('Explorador de prueba');
        await page.getByLabel('Correo electrónico', { exact: true }).fill(email);
        await page.getByLabel('Contraseña', { exact: true }).fill('SyntheticOnly!2026');
        await page.getByLabel('Confirmar contraseña', { exact: true }).fill('SyntheticOnly!2026');
        await Promise.all([page.waitForURL('**/dashboard'), page.getByRole('button', { name: 'Crear cuenta', exact: true }).click()]);
        await page.getByText('Explorar administración de demostración', { exact: true }).click();
        await page.getByRole('heading', { name: 'Módulos de ejemplo', exact: true }).waitFor();
        await page.screenshot({ path: path.join(output, 'dashboard-desktop.png'), fullPage: true });
        await page.getByRole('button', { name: 'Nuevo ejemplo', exact: true }).click();
        await page.getByRole('dialog').waitFor();
        await page.getByRole('button', { name: 'Cancelar', exact: true }).click();
        await page.getByRole('combobox', { name: 'Módulo de ejemplo' }).selectOption('exchange');
        await page.getByRole('heading', { name: 'Banxico', exact: true }).waitFor();
        for (const url of ['/cuenta', '/cuenta/pedidos', '/cuenta/pedidos/RF-1042', '/cuenta/direcciones', '/cuenta/moneda', '/settings/profile', '/settings/password', '/settings/appearance', '/confirm-password']) await visit(url);
        await visit('/settings/appearance');
        await page.getByRole('button', { name: 'Oscuro', exact: true }).click();
        assert.equal(await page.locator('html').getAttribute('class'), 'dark');
        await page.getByRole('button', { name: 'Claro', exact: true }).click();
        for (const width of [320, 390, 768]) {
            await page.setViewportSize({ width, height: 900 });
            for (const url of ['/', '/catalogo', '/carrito', '/checkout', '/cuenta', '/login']) {
                // Login redirects authenticated visitors, so use the destination for this check.
                await visit(url === '/login' ? '/settings/profile' : url);
                assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), `Horizontal overflow: ${url} at ${width}`);
            }
            await visit('/');
            await page.screenshot({ path: path.join(output, `home-${width}.png`), fullPage: true });
        }
        await page.setViewportSize({ width: 1440, height: 1000 });
        await visit('/settings/profile');
        await page.getByRole('button', { name: 'Eliminar mi cuenta', exact: true }).click();
        await page.getByRole('dialog').getByLabel('Contraseña actual').fill('SyntheticOnly!2026');
        await Promise.all([page.waitForURL(base + '/'), page.getByRole('button', { name: 'Eliminar definitivamente', exact: true }).click()]);
        assert.deepEqual(errors, []);
        const result = { status: 'PASS', checks, browserErrors: errors, viewports: [1440, 768, 390, 320], screenshots: fs.readdirSync(output).filter(f => f.endsWith('.png')) };
        fs.writeFileSync(path.join(__dirname, 'sdd04-browser-result.json'), JSON.stringify(result, null, 2) + '\n');
        console.log(JSON.stringify(result));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exit(1); });
