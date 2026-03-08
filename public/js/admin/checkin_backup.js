function formatNumber(num, maxDecimal = 3) {
    return parseFloat(num.toFixed(maxDecimal));
}
function calculateVolumeFromDimension() {
    const dimension = document.getElementById("check_dimension").innerText;

    if (!dimension || dimension === "-") return;

    const parts = dimension.split("x");

    if (parts.length !== 3) return;

    const length = parseFloat(parts[0]) || 0;
    const width = parseFloat(parts[1]) || 0;
    const height = parseFloat(parts[2]) || 0;

    const volumeCm = length * width * height;
    const volumeM = volumeCm / 1000000;

    document.querySelector("[name='vol_per_pcs']").value = formatNumber(
        volumeM,
        6,
    );

    calculateTotals();
}

function calculateTotals() {
    const qty = parseFloat(document.getElementById("check_qty").innerText) || 0;

    const volPer =
        parseFloat(document.querySelector("[name='vol_per_pcs']").value) || 0;
    const netPer =
        parseFloat(document.querySelector("[name='net_weight_pcs']").value) ||
        0;
    const grossPer =
        parseFloat(document.querySelector("[name='gross_weight_pcs']").value) ||
        0;

    document.querySelector("[name='vol_total']").value = formatNumber(
        qty * volPer,
        3,
    );

    document.querySelector("[name='total_net_weight']").value = formatNumber(
        qty * netPer,
        3,
    );

    document.querySelector("[name='total_gross_weight']").value = formatNumber(
        qty * grossPer,
        3,
    );
}

document
    .querySelector("[name='vol_per_pcs']")
    .addEventListener("input", calculateTotals);
document
    .querySelector("[name='net_weight_pcs']")
    .addEventListener("input", calculateTotals);
document
    .querySelector("[name='gross_weight_pcs']")
    .addEventListener("input", calculateTotals);

let currentStep = 1;
let maxQty = 0;

// 1. DATA INITIALIZATION
function getInventoryData() {
    const rawInventory = document.querySelectorAll("#palletInventoryData div");
    return Array.from(rawInventory).map((el) => ({
        line: el.dataset.line,
        petak: el.dataset.petak,
        pallet: el.dataset.pallet,
    }));
}
window.currentInventory = getInventoryData();

// public/js/admin/checkin.js

function openWarehouseModal(code) {
    const dataSource = document.querySelector(
        `#bookingDataSource [data-code="${code}"]`,
    );
    if (!dataSource)
        return alert("🚨 Kode Booking tidak valid atau sudah diproses!");

    maxQty = parseFloat(dataSource.getAttribute("data-qty")) || 0;

    // 1. Populate Data Dasar
    document.getElementById("check_product_name").innerText =
        dataSource.getAttribute("data-name");
    document.getElementById("check_product_type").innerText =
        dataSource.getAttribute("data-type");
    document.getElementById("check_qty").innerText = maxQty;
    document.getElementById("check_unit").innerText =
        dataSource.getAttribute("data-unit");

    // 2. Populate Data Teknis Lengkap
    document.getElementById("check_temp").innerText =
        dataSource.getAttribute("data-temp");
    document.getElementById("check_dmin").innerText =
        dataSource.getAttribute("data-dmin");
    document.getElementById("check_dmax").innerText =
        dataSource.getAttribute("data-dmax");
    document.getElementById("check_dimension").innerText =
        dataSource.getAttribute("data-dimension");
    document.getElementById("check_weight").innerText =
        dataSource.getAttribute("data-weight");

    // 3. Header & Global Info
    document.getElementById("total_qty_display").innerText = maxQty;
    document.getElementById("display_booking_code").innerText = code;
    document.getElementById("modal_booking_code").value = code;

    // Reset Flow Modal
    currentStep = 1;
    document.getElementById("batchContainer").innerHTML = "";
    addBatchField();
    updateStepUI();

    document
        .getElementById("warehouseModal")
        .classList.replace("hidden", "flex");

    setTimeout(() => {
        calculateVolumeFromDimension();
    }, 200);
}

// 2. NAVIGATION & VALIDATION
function changeStep(n) {
    if (n === 1 && !validateCurrentStep()) return;
    currentStep += n;
    updateStepUI();
    if (currentStep === 3) preparePlacementFields();
}

function validateCurrentStep() {
    if (currentStep === 1) {
        // const pic = document
        //     .querySelector('[name="pic_warehouse"]')
        //     .value.trim();
        const check = document.querySelector(
            '#step1 input[type="checkbox"]',
        ).checked;
        if (!check) {
            alert("Mohon isi nama PIC dan centang konfirmasi data!");
            return false;
        }
    }
    if (currentStep === 2) {
        const pic = document
            .querySelector('[name="pic_warehouse"]')
            .value.trim();
        if (!pic) {
            alert("Mohon isi nama PIC ");
            return false;
        }

        const inputs = document.querySelectorAll(".batch-input");
        let total = 0;
        let allFilled = true;

        inputs.forEach((i) => {
            const val = parseFloat(i.value) || 0;
            total += val;
            if (val <= 0) allFilled = false;
        });

        if (inputs.length === 0 || !allFilled) {
            alert("Semua Qty Batch harus diisi dengan angka positif!");
            return false;
        }
        // Use epsilon for float comparison
        if (Math.abs(total - maxQty) > 0.001) {
            alert(
                `Total batch (${total}) belum sesuai dengan qty booking (${maxQty})!`,
            );
            return false;
        }

        // Validate Porter selections
        const porters = document.querySelectorAll('[name="batch_porters[]"]');
        let porterFilled = true;
        porters.forEach((p) => {
            if (!p.value) porterFilled = false;
        });
        if (!porterFilled) {
            alert("Pilih porter untuk setiap batch!");
            return false;
        }
    }
    return true;
}

function updateStepUI() {
    document.querySelectorAll(".step-content").forEach((el, idx) => {
        el.classList.toggle("hidden", idx + 1 !== currentStep);
    });

    document.querySelectorAll(".step-item").forEach((el, idx) => {
        const circle = el.querySelector(".step-circle");
        const stepNum = idx + 1;
        if (stepNum < currentStep) {
            circle.className =
                "flex items-center justify-center w-10 h-10 font-bold text-white rounded-full step-circle bg-emerald-500";
            circle.innerHTML = '<i class="fa-solid fa-check"></i>';
        } else if (stepNum === currentStep) {
            circle.className =
                "flex items-center justify-center w-10 h-10 font-bold text-white bg-blue-600 rounded-full shadow-lg step-circle shadow-blue-100";
            circle.innerText = stepNum;
        } else {
            circle.className =
                "flex items-center justify-center w-10 h-10 font-bold rounded-full step-circle bg-slate-100 text-slate-400";
            circle.innerText = stepNum;
        }
    });

    document
        .getElementById("prevBtn")
        .classList.toggle("hidden", currentStep === 1);
    document
        .getElementById("nextBtn")
        .classList.toggle("hidden", currentStep === 3);
    document
        .getElementById("finalSubmitBtn")
        .classList.toggle("hidden", currentStep !== 3);
}

// 3. BATCH MANAGEMENT
function addBatchField() {
    const container = document.getElementById("batchContainer");
    const porterData = document.querySelectorAll("#porterDataSource div");

    let porterOptions = '<option value="">Pilih Porter</option>';
    porterData.forEach((p) => {
        porterOptions += `<option value="${p.dataset.name}">${p.dataset.name}</option>`;
    });

    const div = document.createElement("div");
    div.className =
        "batch-row p-6 bg-slate-50 border border-slate-100 rounded-[2rem] grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-4 animate-in fade-in zoom-in duration-300";
    div.innerHTML = `
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Qty Batch</label>
                <input type="number" name="batch_quantities[]" oninput="updateBatchTotal()" step="any" required 
                    class="w-full px-6 py-3 font-bold bg-white border-none batch-input rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Porter Penanggung Jawab</label>
                <select name="batch_porters[]" required 
                    class="w-full px-6 py-3 font-bold bg-white border-none rounded-xl focus:ring-2 focus:ring-blue-500">
                    ${porterOptions}
                </select>
            </div>
            <button type="button" onclick="this.parentElement.remove(); updateBatchTotal();" 
                class="pb-4 text-xs font-bold text-red-500 hover:text-red-700">
                <i class="fa-solid fa-trash-can"></i> Hapus
            </button>
        `;
    container.appendChild(div);
    updateBatchTotal();
}

function updateBatchTotal() {
    const inputs = document.querySelectorAll(".batch-input");
    let total = 0;
    inputs.forEach((input) => (total += parseFloat(input.value) || 0));

    // Hitung estimasi palet yang dibutuhkan
    let totalPalletsNeeded = 0;
    inputs.forEach((input) => {
        totalPalletsNeeded += Math.ceil((parseFloat(input.value) || 0) / 10);
    });

    document.getElementById("current_total_display").innerText =
        total.toLocaleString();

    // Tambahkan info palet di UI jika Anda punya elemennya
    const infoPalet = document.getElementById("pallet_needed_info");
    if (infoPalet)
        infoPalet.innerText = `Estimasi palet dibutuhkan: ${totalPalletsNeeded}`;
}

// 4. PLACEMENT LOGIC
function preparePlacementFields() {
    const container = document.getElementById("placementContainer");
    container.innerHTML = "";
    const batchInputs = document.querySelectorAll(".batch-input");
    const porterInputs = document.querySelectorAll('[name="batch_porters[]"]');

    batchInputs.forEach((input, idx) => {
        const qty = input.value;
        const porter = porterInputs[idx].value || "Unknown";
        const div = document.createElement("div");
        div.className =
            "flex flex-col items-center gap-6 p-6 mb-4 bg-white border-2 shadow-sm border-slate-50 rounded-3xl lg:flex-row";

        div.innerHTML = `
                <div class="flex-1">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-600 text-[8px] font-black rounded-md uppercase">Batch ${idx + 1}</span>
                    <h5 class="text-sm font-bold text-slate-800">${porter} (${qty} Unit)</h5>
                </div>
                <div class="grid grid-cols-3 gap-3 flex-[2]">
                    <select onchange="updatePetakOptions(${idx})" id="line_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Line</option>
                    </select>
                    <select onchange="updatePalletOptions(${idx})" id="petak_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Petak</option>
                    </select>
                    <select name="pallet_ids[]" id="pallet_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Palet</option>
                    </select>
                </div>
            `;
        container.appendChild(div);

        // Init Lines for this batch
        const lineSelect = document.getElementById(`line_${idx}`);
        const uniqueLines = [
            ...new Set(window.currentInventory.map((i) => i.line)),
        ];
        uniqueLines.forEach((l) => {
            lineSelect.innerHTML += `<option value="${l}">Line ${l}</option>`;
        });
    });
}

function calculatePalletFromPerPallet() {
    const qty = maxQty;

    const perPallet =
        parseFloat(document.getElementById("per_pallet").value) || 0;

    if (perPallet <= 0) return;

    const pallet = Math.ceil(qty / perPallet);
    const remainder = qty % perPallet;

    document.getElementById("pallet_count").value = pallet;
    document.getElementById("pallet_remainder").value = remainder;

    updatePalletSummary(pallet, perPallet, remainder);
}

function calculatePalletFromCount() {
    const qty = maxQty;

    const pallet =
        parseFloat(document.getElementById("pallet_count").value) || 0;

    if (pallet <= 0) return;

    const perPallet = Math.floor(qty / pallet);
    const remainder = qty % pallet;

    document.getElementById("per_pallet").value = perPallet;
    document.getElementById("pallet_remainder").value = remainder;

    updatePalletSummary(pallet, perPallet, remainder);
}

document
    .getElementById("per_pallet")
    .addEventListener("input", calculatePalletFromPerPallet);

document
    .getElementById("pallet_count")
    .addEventListener("input", calculatePalletFromCount);

function updatePalletSummary(pallet, perPallet, remainder) {
    const summary = document.getElementById("pallet_summary");

    if (!pallet || !perPallet) {
        summary.classList.add("hidden");
        return;
    }

    summary.classList.remove("hidden");

    document.getElementById("sum_qty").innerText = maxQty;
    document.getElementById("sum_pallet").innerText = pallet;
    document.getElementById("sum_per_pallet").innerText = perPallet;
    document.getElementById("sum_remainder").innerText = remainder;

    const dist = document.getElementById("pallet_distribution");
    dist.innerHTML = "";

    for (let i = 1; i <= pallet; i++) {
        let qty = perPallet;

        if (i === pallet && remainder > 0) {
            qty = remainder;
        }

        const row = document.createElement("div");

        row.innerText = `Pallet ${i} - ${qty} box`;

        dist.appendChild(row);
    }
}

function updatePetakOptions(idx) {
    const line = document.getElementById(`line_${idx}`).value;
    const petakSelect = document.getElementById(`petak_${idx}`);
    const palletSelect = document.getElementById(`pallet_${idx}`);

    petakSelect.innerHTML = '<option value="">Petak</option>';
    palletSelect.innerHTML = '<option value="">Palet</option>';

    if (!line) return;

    const filteredPetak = [
        ...new Set(
            window.currentInventory
                .filter((i) => i.line === line)
                .map((i) => i.petak),
        ),
    ];

    filteredPetak.forEach((p) => {
        petakSelect.innerHTML += `<option value="${p}">Petak ${p}</option>`;
    });
}

function updatePalletOptions(idx) {
    const line = document.getElementById(`line_${idx}`).value;
    const petak = document.getElementById(`petak_${idx}`).value;
    const palletSelect = document.getElementById(`pallet_${idx}`);

    palletSelect.innerHTML = '<option value="">Palet</option>';

    if (!petak) return;

    const filteredPallets = window.currentInventory.filter(
        (i) => i.line === line && i.petak === petak,
    );

    filteredPallets.forEach((p) => {
        palletSelect.innerHTML += `<option value="${p.pallet}">${p.pallet}</option>`;
    });
}

function closeWarehouseModal() {
    if (confirm("Batalkan proses check-in? Data yang diisi akan hilang.")) {
        document
            .getElementById("warehouseModal")
            .classList.replace("flex", "hidden");
    }
}

// 5. SCANNER SETUP
function onScanSuccess(code) {
    // Play success sound
    new Audio("https://www.soundjay.com/buttons/beep-07a.mp3")
        .play()
        .catch(() => {});
    openWarehouseModal(code);
}

function handleManualInput() {
    const input = document.getElementById("manual_booking_input");
    if (input.value.trim()) {
        openWarehouseModal(input.value.trim());
        input.value = "";
    }
}

let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250,
});
html5QrcodeScanner.render(onScanSuccess);
