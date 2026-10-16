export function initializeDeleteConfirmation() {
    const approved = new WeakSet();
    const dialog = document.createElement('dialog');
    dialog.className = 'w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-2xl backdrop:bg-slate-950/50';
    dialog.innerHTML = `<form method="dialog"><h2 data-confirm-title class="text-xl font-semibold">Confirm Deletion</h2><p data-confirm-message class="mt-3 text-sm text-slate-600">Delete this record? Records with operational history will remain protected.</p><p data-delete-target class="mt-2 break-words text-sm font-medium"></p><div class="mt-6 flex justify-end gap-3"><button value="cancel" class="rounded-xl border px-4 py-2 text-sm">Cancel</button><button value="delete" data-confirm-button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white">Delete Record</button></div></form>`;
    document.body.append(dialog);
    let pending;
    dialog.addEventListener('close', () => {
        const form = pending;
        pending = null;
        if (dialog.returnValue !== 'delete' || !form?.isConnected) return;
        approved.add(form);
        let confirmation = form.querySelector('[name="confirmed"]');
        if (!confirmation) {
            confirmation = document.createElement('input');
            confirmation.type = 'hidden'; confirmation.name = 'confirmed'; form.append(confirmation);
        }
        confirmation.value = '1';
        form.requestSubmit();
    });
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const isDelete = form.querySelector('[name="_method"]')?.value.toUpperCase() === 'DELETE';
        if (!isDelete && !form.dataset.confirmAction) return;
        // Override old inline confirmation handlers, including historical views.
        form.removeAttribute('onsubmit');
        if (form.getAttribute('aria-busy') === 'true') {
            event.preventDefault(); event.stopImmediatePropagation(); return;
        }
        if (approved.has(form)) { approved.delete(form); return; }
        if (form.dataset.deleteConfirmed === 'true') {
            if (!form.querySelector('[name="confirmed"]')) {
                const input = document.createElement('input'); input.type = 'hidden'; input.name = 'confirmed'; input.value = '1'; form.append(input);
            }
            return;
        }
        event.preventDefault(); event.stopImmediatePropagation();
        pending = form;
        dialog.querySelector('[data-confirm-title]').textContent = isDelete ? 'Confirm Deletion' : 'Confirm Action';
        dialog.querySelector('[data-confirm-message]').textContent = isDelete ? 'Delete this record? Records with operational history remain protected.' : form.dataset.confirmAction;
        dialog.querySelector('[data-confirm-button]').textContent = isDelete ? 'Delete Record' : 'Confirm';
        dialog.querySelector('[data-delete-target]').textContent = form.dataset.recordName || '';
        dialog.returnValue = 'cancel';
        dialog.showModal();
    }, true);
}
