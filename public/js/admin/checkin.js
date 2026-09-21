(() => {
    'use strict';

    let currentStep = 1;
    let maxQty = 0;
    let placementRowCounter = 0;

    const byId = (id) => document.getElementById(id);
    const toNumber = (value) => {
        const normalized = String(value ?? '')
            .replace(/\s/g, '')
            .replace(/\./g, '')
            .replace(',', '.');
        const parsed = Number(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatNumber = (value, maxDecimal = 3) =>
        Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: maxDecimal,
        });

    function getInventoryData() {
        return Array.from(document.querySelectorAll('#palletInventoryData div')).map((el) => ({
            line: String(el.dataset.line ?? ''),
            petak: String(el.dataset.petak ?? ''),
            status: String(el.dataset.status ?? 'empty'),
        }));
    }

    window.currentInventory = getInventoryData();

    function generatePorterOptions() {
        const options = ['<option value="">Choose Porter</option>'];
        document.querySelectorAll('#porterDataSource div').forEach((el) => {
            const name = el.dataset.name || '';
            if (!name) return;
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            options.push(option.outerHTML);
        });
        return options.join('');
    }

    function calculateVolumeFromDimension() {
        const dimension = byId('check_dimension')?.textContent || '';
        const parts = dimension.toLowerCase().replace(/\s|cm/g, '').split('x');
        if (parts.length !== 3) return;

        const [length, width, height] = parts.map((part) => Number(part) || 0);
        const input = document.querySelector('[name="vol_per_pcs"]');
        if (input && length > 0 && width > 0 && height > 0) {
            input.value = length * width * height;
        }
        calculateTotals();
    }

    function calculateTotals() {
        const qty = maxQty;
        const volPer = toNumber(document.querySelector('[name="vol_per_pcs"]')?.value);
        const netPer = toNumber(document.querySelector('[name="net_weight_pcs"]')?.value);
        const grossPer = toNumber(document.querySelector('[name="gross_weight_pcs"]')?.value);

        const volTotal = document.querySelector('[name="vol_total"]');
        const netTotal = document.querySelector('[name="total_net_weight"]');
        const grossTotal = document.querySelector('[name="total_gross_weight"]');

        if (volTotal) volTotal.value = formatNumber(qty * volPer);
        if (netTotal) netTotal.value = formatNumber(qty * netPer);
        if (grossTotal) grossTotal.value = formatNumber(qty * grossPer);
    }

    function resetModalInputs() {
        const form = byId('checkInForm');
        if (!form) return;

        form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"])').forEach((input) => {
            if (!input.readOnly) input.value = '';
        });
        form.querySelectorAll('input[type="checkbox"]').forEach((input) => (input.checked = false));
        form.querySelectorAll('select').forEach((select) => (select.selectedIndex = 0));
        byId('placementContainer')?.replaceChildren();
        byId('pallet_summary')?.classList.add('hidden');
    }

    window.openWarehouseModal = function openWarehouseModal(code) {
        const dataSource = document.querySelector(`#bookingDataSource [data-code="${CSS.escape(String(code))}"]`);
        if (!dataSource) {
            alert('Kode booking tidak valid atau tidak tersedia pada halaman ini.');
            return;
        }

        resetModalInputs();
        maxQty = Number(dataSource.dataset.qty) || 0;

        byId('modal_booking_id').value = dataSource.dataset.id || '';
        byId('modal_booking_code').value = code;
        byId('display_booking_code').textContent = code;
        byId('check_product_name').textContent = dataSource.dataset.name || '-';
        byId('check_product_type').textContent = dataSource.dataset.type || '-';
        byId('check_qty').textContent = String(maxQty);
        byId('check_unit').textContent = dataSource.dataset.unit || '';
        byId('check_temp').textContent = dataSource.dataset.temp || '-';
        byId('check_dmin').textContent = formatNumber(Number(dataSource.dataset.dmin) || 0);
        byId('check_dmax').textContent = dataSource.dataset.dmax ? formatNumber(Number(dataSource.dataset.dmax)) : '-';
        byId('check_dimension').textContent = dataSource.dataset.dimension || '-';

        byId('ci_net_weight_pcs').value = dataSource.dataset.netPcs || 0;
        byId('ci_total_net_weight').value = dataSource.dataset.netTotal || 0;
        byId('ci_gross_weight_pcs').value = dataSource.dataset.grossPcs || 0;
        byId('ci_total_gross_weight').value = dataSource.dataset.grossTotal || 0;

        const porterContainer = byId('porterContainer');
        if (porterContainer) {
            porterContainer.innerHTML = `
                <select name="porter_name" required
                    class="w-full px-5 py-3.5 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                    ${generatePorterOptions()}
                </select>`;
        }

        currentStep = 1;
        updateStepUI();
        syncHiddenInputs();
        calculateVolumeFromDimension();

        const modal = byId('warehouseModal');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closeWarehouseModal = function closeWarehouseModal(force = false) {
        if (!force && !confirm('Batalkan proses check-in? Data yang diisi akan hilang.')) return;
        const modal = byId('warehouseModal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    function validateCurrentStep() {
        if (currentStep === 1) {
            const confirmed = byId('step1')?.querySelector('input[type="checkbox"]')?.checked;
            if (!confirmed) {
                alert('Centang konfirmasi bahwa data fisik sudah diverifikasi.');
                return false;
            }
        }

        if (currentStep === 2) {
            const pic = document.querySelector('[name="pic_warehouse"]')?.value?.trim();
            const porter = document.querySelector('[name="porter_name"]')?.value?.trim();
            const palletCount = Number(byId('pallet_count')?.value) || 0;
            const perPallet = Number(byId('per_pallet')?.value) || 0;

            if (!pic) {
                alert('Please choose PIC Warehouse.');
                return false;
            }
            if (!porter) {
                alert('Please choose Porter Team.');
                return false;
            }
            if (palletCount <= 0 || perPallet <= 0) {
                alert('Isi Qty per Pallet dan Number of Pallets terlebih dahulu.');
                return false;
            }

            const expected = Math.ceil(maxQty / perPallet);
            if (palletCount !== expected) {
                alert(`Jumlah pallet tidak sesuai. Dengan ${maxQty} item dan ${perPallet} item/pallet dibutuhkan ${expected} pallet.`);
                return false;
            }
        }

        return true;
    }

    function updateStepUI() {
        document.querySelectorAll('.step-content').forEach((el, index) => {
            el.classList.toggle('hidden', index + 1 !== currentStep);
        });

        document.querySelectorAll('.step-item').forEach((el, index) => {
            const circle = el.querySelector('.step-circle');
            if (!circle) return;
            const step = index + 1;
            if (step < currentStep) {
                circle.className = 'flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-xs font-bold text-white rounded-full step-circle bg-emerald-500';
                circle.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else if (step === currentStep) {
                circle.className = 'flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-xs font-bold text-white bg-blue-600 rounded-full shadow-lg step-circle shadow-blue-100';
                circle.textContent = String(step);
            } else {
                circle.className = 'flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-xs font-bold rounded-full step-circle bg-slate-100 text-slate-400';
                circle.textContent = String(step);
            }
        });

        byId('prevBtn')?.classList.toggle('hidden', currentStep === 1);
        byId('nextBtn')?.classList.toggle('hidden', currentStep === 3);
        byId('finalSubmitBtn')?.classList.toggle('hidden', currentStep !== 3);
    }

    window.changeStep = function changeStep(delta) {
        if (delta > 0 && !validateCurrentStep()) return;
        currentStep = Math.min(3, Math.max(1, currentStep + delta));
        if (currentStep === 3) preparePlacementFields();
        updateStepUI();
    };

    function generateLineOptions() {
        const lines = [...new Set(window.currentInventory.map((item) => item.line).filter(Boolean))];
        return lines.map((line) => `<option value="${line}">Line ${line}</option>`).join('');
    }

    function preparePlacementFields() {
        const container = byId('placementContainer');
        if (!container) return;
        container.innerHTML = '';
        placementRowCounter = 0;
        byId('total_pallets_needed_display').textContent = byId('pallet_count').value || '0';
        byId('allocated_pallets_display').textContent = '0';
        addPlacementRowGroup();
    }

    window.addPlacementRowGroup = function addPlacementRowGroup() {
        const container = byId('placementContainer');
        if (!container) return;
        const index = placementRowCounter++;
        const isFirst = container.children.length === 0;
        const row = document.createElement('div');
        row.id = `placement_row_${index}`;
        row.className = 'grid items-end grid-cols-1 gap-3 p-4 border placement-group-row sm:grid-cols-2 lg:grid-cols-4 bg-slate-50 border-slate-100 rounded-2xl';
        row.innerHTML = `
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Line</label>
                <select name="lines[]" id="group_line_${index}" required
                    class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Choose Line</option>${generateLineOptions()}
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Petak (Section)</label>
                <select name="petaks[]" id="group_petak_${index}" required
                    class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Choose Section</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Number of Pallets</label>
                <input type="number" name="pallet_qty[]" min="1" required placeholder="Example: 2"
                    class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-center min-h-11">
                ${isFirst
                    ? '<span class="text-[10px] text-slate-400 font-bold italic px-2">Main Location</span>'
                    : `<button type="button" class="px-3 py-2 text-xs font-bold text-red-600 rounded-xl hover:bg-red-50" data-remove-placement="${index}"><i class="mr-1 fa-solid fa-trash-can"></i>Remove</button>`}
            </div>`;

        container.appendChild(row);
        row.querySelector(`#group_line_${index}`)?.addEventListener('change', () => updateGroupPetakOptions(index));
        row.querySelector('[name="pallet_qty[]"]')?.addEventListener('input', updateAllocatedPallets);
        row.querySelector('[data-remove-placement]')?.addEventListener('click', () => {
            row.remove();
            updateAllocatedPallets();
        });
    };

    function updateGroupPetakOptions(index) {
        const line = byId(`group_line_${index}`)?.value || '';
        const select = byId(`group_petak_${index}`);
        if (!select) return;
        select.innerHTML = '<option value="">Choose Section</option>';

        const selectedLocations = new Set(
            Array.from(document.querySelectorAll('.placement-group-row')).map((row) => {
                const l = row.querySelector('[name="lines[]"]')?.value;
                const p = row.querySelector('[name="petaks[]"]')?.value;
                return l && p ? `${l}:${p}` : '';
            }).filter(Boolean),
        );

        window.currentInventory
            .filter((item) => item.line === line && item.status !== 'filled')
            .forEach((item) => {
                const key = `${item.line}:${item.petak}`;
                if (selectedLocations.has(key)) return;
                select.insertAdjacentHTML('beforeend', `<option value="${item.petak}">Petak ${item.petak}</option>`);
            });
    }

    function updateAllocatedPallets() {
        const allocated = Array.from(document.querySelectorAll('[name="pallet_qty[]"]'))
            .reduce((sum, input) => sum + (Number(input.value) || 0), 0);
        const total = Number(byId('pallet_count')?.value) || 0;
        const display = byId('allocated_pallets_display');
        if (!display) return;
        display.textContent = String(allocated);
        display.className = allocated === total ? 'font-black text-emerald-600' : allocated > total ? 'font-black text-red-600' : 'font-black text-blue-600';
    }

    function validatePlacement() {
        const rows = Array.from(document.querySelectorAll('.placement-group-row'));
        const totalNeeded = Number(byId('pallet_count')?.value) || 0;
        let allocated = 0;
        const locations = new Set();

        if (!rows.length) return false;

        for (const row of rows) {
            const line = row.querySelector('[name="lines[]"]')?.value;
            const petak = row.querySelector('[name="petaks[]"]')?.value;
            const qty = Number(row.querySelector('[name="pallet_qty[]"]')?.value) || 0;
            if (!line || !petak || qty <= 0) {
                alert('Lengkapi semua Line, Petak, dan Number of Pallets.');
                return false;
            }
            const key = `${line}:${petak}`;
            if (locations.has(key)) {
                alert(`Lokasi Line ${line} Petak ${petak} tidak boleh dipilih dua kali.`);
                return false;
            }
            locations.add(key);
            allocated += qty;
        }

        if (allocated !== totalNeeded) {
            alert(`Jumlah pallet yang dialokasikan (${allocated}) harus sama dengan kebutuhan (${totalNeeded}).`);
            return false;
        }

        return true;
    }

    function syncHiddenInputs() {
        if (byId('hidden_total_qty')) byId('hidden_total_qty').value = String(maxQty);
        if (byId('hidden_per_pallet')) byId('hidden_per_pallet').value = byId('per_pallet')?.value || '';

        const form = byId('checkInForm');
        if (!form) return;
        form.querySelectorAll('input[name="product_names[]"]').forEach((el) => el.remove());
        const productName = byId('check_product_name')?.textContent || 'Product';
        document.querySelectorAll('.placement-group-row').forEach(() => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_names[]';
            input.value = productName;
            form.appendChild(input);
        });
    }

    function updatePalletSummary() {
        const perPallet = Number(byId('per_pallet')?.value) || 0;
        if (perPallet <= 0 || maxQty <= 0) {
            byId('pallet_summary')?.classList.add('hidden');
            return;
        }

        const palletCount = Math.ceil(maxQty / perPallet);
        const remainder = maxQty % perPallet;
        byId('pallet_count').value = palletCount;
        byId('pallet_remainder').value = remainder;
        byId('pallet_summary')?.classList.remove('hidden');
        byId('sum_qty').textContent = String(maxQty);
        byId('sum_pallet').textContent = String(palletCount);
        byId('sum_per_pallet').textContent = String(perPallet);
        byId('sum_remainder').textContent = String(remainder);
        syncHiddenInputs();
    }

    window.handleManualInput = function handleManualInput() {
        const input = byId('manual_booking_input');
        if (input?.value.trim()) {
            window.openWarehouseModal(input.value.trim());
            input.value = '';
        }
    };

    function init() {
        document.querySelector('[name="vol_per_pcs"]')?.addEventListener('input', calculateTotals);
        document.querySelector('[name="net_weight_pcs"]')?.addEventListener('input', calculateTotals);
        document.querySelector('[name="gross_weight_pcs"]')?.addEventListener('input', calculateTotals);
        byId('per_pallet')?.addEventListener('input', updatePalletSummary);
        byId('pallet_count')?.setAttribute('readonly', 'readonly');

        const form = byId('checkInForm');
        form?.addEventListener('submit', (event) => {
            if (currentStep !== 3 || !validatePlacement()) {
                event.preventDefault();
                return;
            }
            syncHiddenInputs();
            const bookingId = byId('modal_booking_id')?.value;
            if (!bookingId) {
                event.preventDefault();
                alert('Booking ID tidak ditemukan.');
                return;
            }
            form.action = `/admin/bookings/${bookingId}/placement`;
        });

        if (typeof Html5QrcodeScanner !== 'undefined' && byId('reader')) {
            const scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 });
            scanner.render((code) => window.openWarehouseModal(code));
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
