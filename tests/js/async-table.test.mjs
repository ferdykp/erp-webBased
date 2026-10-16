import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeAsyncTables } from '../../resources/js/async-table.js';

function fixture() {
    const classes = new Set();
    const error = { setAttribute() {}, classList: { add: x => classes.add(x), remove: x => classes.delete(x) } };
    const input = { value: 'sample', addEventListener() {} };
    const listeners = {};
    const attributes = {};
    const container = {
        dataset: { asyncTable: '/customers', searchInput: 'search' }, style: {}, innerHTML: 'original',
        before() {}, setAttribute: (key, value) => attributes[key] = value,
        addEventListener: (key, callback) => listeners[key] = callback,
    };
    globalThis.document = { getElementById: () => input, createElement: () => error };
    globalThis.window = { location: { href: 'https://example.test/customers' } };
    initializeAsyncTables({ querySelectorAll: () => [container] });
    const click = () => listeners.click({ target: { closest: () => ({ href: 'https://example.test/customers?page=2' }) }, preventDefault() {} });
    return { container, error, attributes, click };
}
const flush = () => new Promise(resolve => setImmediate(resolve));

test('failed table requests preserve rows, show feedback and clear loading', async () => {
    const { container, error, attributes, click } = fixture();
    globalThis.fetch = async () => ({ ok: false });
    click();
    await flush();
    assert.equal(container.innerHTML, 'original');
    assert.equal(container.style.opacity, '1');
    assert.equal(attributes['aria-busy'], 'false');
    assert.match(error.textContent, /Unable to load data/);
});

test('older responses cannot overwrite the latest search or pagination result', async () => {
    const { container, click } = fixture();
    const pending = [];
    globalThis.fetch = (url, options) => new Promise(resolve => pending.push({ url, options, resolve }));
    click(); click();
    assert.equal(pending[0].options.signal.aborted, true);
    assert.equal(pending[1].url.searchParams.get('search'), 'sample');
    pending[1].resolve({ ok: true, text: async () => 'new rows' });
    await flush();
    pending[0].resolve({ ok: true, text: async () => 'stale rows' });
    await flush();
    assert.equal(container.innerHTML, 'new rows');
});
