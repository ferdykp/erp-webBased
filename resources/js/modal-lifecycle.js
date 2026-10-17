// Keep keyboard focus and scrolling inside the currently open custom modal.
export function modalIsOpen(modal) {
    if (!modal.isConnected || modal.hidden || modal.hasAttribute('x-cloak')) return false;
    const style = getComputedStyle(modal);
    return style.display !== 'none' && style.visibility !== 'hidden'
        && !modal.classList.contains('pointer-events-none')
        && (modal.tagName !== 'DIALOG' || modal.open);
}

export function initializeModalLifecycle() {
    let active = null;
    let labelCount = 0;
    const origins = new WeakMap();
    const focusable = modal => [...modal.querySelectorAll('button, a[href], input, select, textarea, [tabindex]')]
        .filter(el => !el.disabled && el.tabIndex >= 0 && el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden');
    function sync() {
        const open = [...document.querySelectorAll('[data-ui-modal], dialog[open]')].filter(modalIsOpen);
        const next = open.sort((a, b) => (parseInt(getComputedStyle(a).zIndex) || 0) - (parseInt(getComputedStyle(b).zIndex) || 0)).at(-1) || null;
        document.documentElement.classList.toggle('modal-open', !!next);
        if (next === active) return;
        const previous = active;
        active = next;
        if (next) {
            if (!next.hasAttribute('aria-label') && !next.hasAttribute('aria-labelledby')) {
                const heading = next.querySelector('h1, h2, h3, h4');
                if (heading) {
                    heading.id ||= `modal-title-${++labelCount}`;
                    next.setAttribute('aria-labelledby', heading.id);
                } else next.setAttribute('aria-label', 'Dialog');
            }
            if (!origins.has(next)) origins.set(next, document.activeElement);
            if (!next.contains(document.activeElement)) (focusable(next)[0] || next).focus({ preventScroll: true });
        } else if (previous) {
            const origin = origins.get(previous);
            origins.delete(previous);
            if (origin?.isConnected) origin.focus({ preventScroll: true });
        }
        // A closed modal may be opened again from a different trigger.
        if (previous && !open.includes(previous)) origins.delete(previous);
    }
    let queued = false;
    new MutationObserver(() => {
        if (queued) return;
        queued = true;
        queueMicrotask(() => { queued = false; sync(); });
    }).observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['class', 'style', 'hidden', 'open', 'x-cloak'] });
    document.addEventListener('keydown', event => {
        if (!active) return;
        if (event.key === 'Escape' && active.tagName !== 'DIALOG' && !event.defaultPrevented) {
            const dismiss = active.querySelector('[data-modal-dismiss]:not(:disabled)');
            if (dismiss) { event.preventDefault(); dismiss.click(); }
            return;
        }
        if (event.key !== 'Tab') return;
        const items = focusable(active);
        const first = items[0], last = items.at(-1);
        if (!first) { event.preventDefault(); active.focus(); return; }
        if (!active.contains(document.activeElement) || (event.shiftKey && document.activeElement === first)) {
            event.preventDefault(); (event.shiftKey ? last : first).focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault(); first.focus();
        }
    });
    document.addEventListener('click', event => {
        if (active && event.target === active && active.tagName !== 'DIALOG') {
            active.querySelector('[data-modal-dismiss]:not(:disabled)')?.click();
        }
    });
    document.addEventListener('focusin', event => {
        if (active && !active.contains(event.target)) (focusable(active)[0] || active).focus({ preventScroll: true });
    });
    sync();
}
