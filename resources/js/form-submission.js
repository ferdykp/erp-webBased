// Guard ordinary navigation forms. AJAX and Alpine confirmation flows keep their own lifecycle.
export function initializeFormSubmissions() {
    const pending = new Set();
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented
            || form.method.toLowerCase() === 'get' || form.getAttribute('method') === 'dialog'
            || form.target === '_blank') return;
        if (pending.has(form)) { event.preventDefault(); return; }
        // Run after other submit handlers, so cancelled/async submissions never stay locked.
        queueMicrotask(() => {
            if (event.defaultPrevented) return;
            pending.add(form);
            form.setAttribute('aria-busy', 'true');
            form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(button => {
                button.setAttribute('aria-disabled', 'true');
                button.dataset.navigationPending = 'true';
            });
        });
    });
    window.addEventListener('pageshow', () => {
        pending.forEach(form => {
            form.removeAttribute('aria-busy');
            form.querySelectorAll('[data-navigation-pending]').forEach(button => {
                button.removeAttribute('aria-disabled');
                delete button.dataset.navigationPending;
            });
        });
        pending.clear();
    });
}
