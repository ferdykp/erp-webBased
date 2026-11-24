import "./bootstrap";
import Alpine from "alpinejs";
import receivingAssistant from "./receiving-assistant";

import batchAssistant from "./batch-assistant";

import customerWizard from "./customer-wizard";

window.Alpine = Alpine;
Alpine.data("customerWizard", customerWizard);
Alpine.data("receivingAssistant", receivingAssistant);
Alpine.data("batchAssistant", batchAssistant);

// Give Alpine-controlled popovers, menus, and dropdowns a shared motion curve
// when a component does not define its own transition.
document.querySelectorAll('[x-show]:not([data-no-transition])').forEach((element) => {
    const hasTransition = Array.from(element.attributes).some(({ name }) =>
        name === 'x-transition' || name.startsWith('x-transition.') || name.startsWith('x-transition:')
    );
    if (!hasTransition) element.setAttribute('x-transition.opacity.scale.95.duration.180ms', '');
});
Alpine.start();

import { initializeAsyncTables } from './async-table';
initializeAsyncTables();


import { initializeDeleteConfirmation } from './delete-confirmation';
initializeDeleteConfirmation();

import { initializeModalLifecycle } from './modal-lifecycle';
initializeModalLifecycle();

import { initializeFormSubmissions } from './form-submission';
initializeFormSubmissions();

const navigationPendingKey = 'beamCustom.navigationPending';
const navigationRoot = document.documentElement;

const beginPageNavigation = () => {
    try {
        window.sessionStorage.setItem(navigationPendingKey, 'true');
    } catch (_) {}
    navigationRoot.classList.remove('navigation-entering');
    navigationRoot.classList.add('navigation-leaving');
};

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (!(event.target instanceof Element)) return;

    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || link.target && link.target !== '_self'
        || link.closest('[data-no-page-transition]')) return;

    let destination;
    try {
        destination = new URL(link.href, window.location.href);
    } catch (_) {
        return;
    }

    if (destination.origin !== window.location.origin
        || destination.pathname === window.location.pathname && destination.search === window.location.search) return;

    beginPageNavigation();
}, { passive: true });

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    queueMicrotask(() => {
        if (!event.defaultPrevented && form.target !== '_blank' && !form.closest('[data-no-page-transition]')) {
            beginPageNavigation();
        }
    });
});

let navigationFinishTimer;
const finishPageNavigation = () => {
    if (!navigationRoot.classList.contains('navigation-entering')
        && !navigationRoot.classList.contains('navigation-leaving')) return;

    window.clearTimeout(navigationFinishTimer);
    navigationFinishTimer = window.setTimeout(() => {
        const shouldAnimateContent = navigationRoot.classList.contains('navigation-entering');
        navigationRoot.classList.remove('navigation-entering', 'navigation-leaving');
        try {
            window.sessionStorage.removeItem(navigationPendingKey);
        } catch (_) {}

        if (shouldAnimateContent) {
            document.querySelectorAll('[data-page-content]').forEach((content) => {
                content.classList.add('page-content-entering');
                window.setTimeout(() => content.classList.remove('page-content-entering'), 260);
            });
        }
    }, 180);
};

if (navigationRoot.classList.contains('navigation-entering')) {
    document.addEventListener('DOMContentLoaded', finishPageNavigation, { once: true });
}
window.addEventListener('pageshow', finishPageNavigation);

// These layouts scroll inside custom containers, so browser history
// restoration does not reliably reset the content area on a full page visit.
if ('scrollRestoration' in window.history) {
    window.history.scrollRestoration = 'manual';
}

const sidebarScrollKey = `beamCustom.sidebarScroll:${window.location.origin}:${window.location.pathname.split('/')[1] || 'root'}`;
const sidebarNavs = document.querySelectorAll('[data-app-sidebar] nav');

sidebarNavs.forEach((nav) => {
    nav.addEventListener('scroll', () => {
        window.sessionStorage.setItem(sidebarScrollKey, String(nav.scrollTop));
    }, { passive: true });
});

const resetContentScrollPosition = () => {
    window.scrollTo(0, 0);
    document.querySelectorAll('[data-app-scroll]').forEach((element) => {
        element.scrollTop = 0;
    });
};

const restoreSidebarScrollPosition = () => {
    const savedScrollTop = Number(window.sessionStorage.getItem(sidebarScrollKey) || 0);
    sidebarNavs.forEach((nav) => {
        nav.scrollTop = savedScrollTop;
    });
};

window.addEventListener('pageshow', () => {
    resetContentScrollPosition();
    requestAnimationFrame(restoreSidebarScrollPosition);
});
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        resetContentScrollPosition();
        restoreSidebarScrollPosition();
    }, { once: true });
} else {
    resetContentScrollPosition();
    restoreSidebarScrollPosition();
}
