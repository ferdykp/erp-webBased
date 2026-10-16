const initialized = new WeakSet();

// One listener per table; newer searches always supersede older responses.
export function initializeAsyncTables(root = document) {
    root.querySelectorAll('[data-async-table]').forEach(container => {
        const input = document.getElementById(container.dataset.searchInput);
        if (!input || initialized.has(container)) return;
        initialized.add(container);
        const error = document.createElement('p');
        error.className = 'hidden p-3 text-sm text-red-700';
        error.setAttribute('role', 'alert');
        container.before(error);
        let controller;
        let timer;
        let revision = 0;

        async function load(url = container.dataset.asyncTable) {
            const current = ++revision;
            controller?.abort();
            controller = new AbortController();
            const target = new URL(url, window.location.href);
            target.searchParams.set('search', input.value);
            container.style.opacity = '0.5';
            container.setAttribute('aria-busy', 'true');
            error.classList.add('hidden');
            try {
                const response = await fetch(target, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!response.ok || response.redirected) throw new Error('Request failed');
                const html = await response.text();
                if (current !== revision) return;
                // Alpine 3 automatically initializes inserted elements via its observer.
                container.innerHTML = html;
            } catch (failure) {
                if (current === revision && failure.name !== 'AbortError') {
                    error.textContent = 'Unable to load data. Try again or reload the page.';
                    error.classList.remove('hidden');
                }
            } finally {
                if (current === revision) {
                    container.style.opacity = '1';
                    container.setAttribute('aria-busy', 'false');
                }
            }
        }
        input.addEventListener('input', () => {
            clearTimeout(timer);
            ++revision;
            controller?.abort();
            timer = setTimeout(() => load(), 300);
        });
        container.addEventListener('click', event => {
            const link = event.target.closest('nav a, .pagination a');
            if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            clearTimeout(timer);
            load(link.href);
        });
    });
}
