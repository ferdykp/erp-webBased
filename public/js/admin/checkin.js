function syncStep1ToStep4() {
    const qtyEl = document.getElementById("check_qty");
    const dminEl = document.getElementById("check_dmin");
    const nettEl = document.querySelector('[name="net_weight_pcs"]');

    const qty = parseFloat(qtyEl?.innerText.replace(/[^0-9.]/g, "")) || 0;
    const dmin = parseFloat(dminEl?.innerText.replace(/[^0-9.]/g, "")) || 0;
    const nett = parseFloat(nettEl?.value) || 0;

    // simpan ke hidden
    if (document.getElementById("final_qty"))
        document.getElementById("final_qty").value = qty;

    if (document.getElementById("final_dmin"))
        document.getElementById("final_dmin").value = dmin;

    // update UI
    document.getElementById("calc_qty").innerText = qty;
    document.getElementById("calc_dmin").innerText = formatInteger(dmin);
    document.getElementById("calc_dmax").innerText = formatInteger(dmax);
    document.getElementById("calc_nett").innerText = nett;

    return { qty, nett, dmin };
}

function formatNumber(num, maxDecimal = 3) {
    if (isNaN(num) || num === null) return "0";

    return Number(num).toLocaleString("id-ID", {
        minimumFractionDigits: 0, // tidak paksa ada .000
        maximumFractionDigits: maxDecimal, // tapi batasi max desimal
    });
}

function formatInteger(num) {
    if (isNaN(num) || num === null) return "0";
    return Math.round(num).toLocaleString("id-ID");
}

function calculateVolumeFromDimension() {
    const dimension = document.getElementById("check_dimension").innerText;

    if (!dimension || dimension === "-") return;

    // const parts = dimension.split("x");

    const parts = dimension.toLowerCase().replace(/\s/g, "").split("x");

    if (parts.length !== 3) return;

    const length = parseFloat(parts[0]) || 0;
    const width = parseFloat(parts[1]) || 0;
    const height = parseFloat(parts[2]) || 0;

    const volumeCm = length * width * height;
    // const volumeM = volumeCm / 1;

    document.querySelector("[name='vol_per_pcs']").value = formatNumber(
        volumeCm,
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

const volInput = document.querySelector("[name='vol_per_pcs']");
if (volInput) {
    volInput.addEventListener("input", calculateTotals);
}

const netInput = document.querySelector("[name='net_weight_pcs']");
if (netInput) {
    netInput.addEventListener("input", calculateTotals);
}

const grossInput = document.querySelector("[name='gross_weight_pcs']");
if (grossInput) {
    grossInput.addEventListener("input", calculateTotals);
}
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

function openWarehouseModal(code) {
    const dataSource = document.querySelector(
        `#bookingDataSource [data-code="${code}"]`,
    );
    document.getElementById("modal_booking_id").value =
        dataSource.getAttribute("data-id");
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
    document.getElementById("check_dmin").innerText = formatInteger(
        dataSource.getAttribute("data-dmin"),
    );

    document.getElementById("check_dmax").innerText = formatInteger(
        dataSource.getAttribute("data-dmax"),
    );
    document.getElementById("check_dimension").innerText =
        dataSource.getAttribute("data-dimension");
    // document.getElementById("check_weight").innerText =
    //     dataSource.getAttribute("data-weight");

    // 3. Header & Global Info
    // document.getElementById("total_qty_display").innerText = maxQty;
    document.getElementById("display_booking_code").innerText = code;
    document.getElementById("modal_booking_code").value = code;

    document.getElementById("porterContainer").innerHTML = `
<select name="porters[]"
class="w-full px-6 py-4 font-bold border-none bg-slate-50 rounded-2xl">
${generatePorterOptions()}
</select>
`;
    // Reset Flow Modal
    currentStep = 1;
    updateStepUI();

    document
        .getElementById("warehouseModal")
        .classList.replace("hidden", "flex");

    // setTimeout(() => {
    //     calculateVolumeFromDimension();
    // }, 200);
    // ✅ AUTO FILL DARI DATASET (JS BARU STYLE)
    document.getElementById("ci_net_weight_pcs").value = formatInteger(
        dataSource.dataset.netPcs,
    );

    document.getElementById("ci_total_net_weight").value =
        dataSource.dataset.netTotal || 0;

    document.getElementById("ci_gross_weight_pcs").value = formatInteger(
        dataSource.dataset.grossPcs,
    );

    document.getElementById("ci_total_gross_weight").value =
        dataSource.dataset.grossTotal || 0;

    // tetap hitung volume dari dimensi
    setTimeout(() => {
        calculateVolumeFromDimension();
        calculateTotals();
    }, 100);
}

function generatePorterOptions() {
    const porterData = document.querySelectorAll("#porterDataSource div");

    let options = '<option value="">Choose Porter</option>';

    porterData.forEach((p) => {
        options += `<option value="${p.dataset.name}">
            ${p.dataset.name}
        </option>`;
    });

    return options;
}
// Fungsi untuk menambah baris grup lokasi baru secara dinamis
function addPlacementRowGroup() {
    const container = document.getElementById("placementContainer");

    // Gunakan nilai counter saat ini sebagai indeks unik
    const index = placementRowCounter;

    const div = document.createElement("div");
    div.className =
        "grid items-end grid-cols-1 gap-4 p-4 duration-200 border placement-group-row sm:grid-cols-4 bg-slate-50 border-slate-100 rounded-2xl animate-in fade-in zoom-in";
    div.setAttribute("id", `placement_row_${index}`);

    // Tentukan apakah ini baris pertama di dalam container saat ini
    const isFirstRow = container.children.length === 0;

    div.innerHTML = `
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Line</label>
            <select name="lines[]" onchange="updateGroupPetakOptions(${index})" id="group_line_${index}" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Choose Line</option>
                ${generateLineOptions()}
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Petak (Section)</label>
            <select name="petaks[]" id="group_petak_${index}" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Choose Section</option>
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Jumlah Palet</label>
            <input type="number" name="pallet_qty[]" min="1" oninput="updateAllocatedPallets()" placeholder="Example: 15" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="flex items-center h-full pb-1">
            ${
                !isFirstRow
                    ? `
                <button type="button" onclick="document.getElementById('placement_row_${index}').remove(); updateAllocatedPallets();" class="text-xs font-bold text-red-500 hover:text-red-700 px-2 py-1">
                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                </button>
            `
                    : '<span class="text-[10px] text-slate-400 font-bold italic px-2">Lokasi Utama</span>'
            }
        </div>
    `;
    container.appendChild(div);

    // Naikkan nilai counter agar baris berikutnya mendapatkan ID yang berbeda
    placementRowCounter++;
}
function changeStep(n) {
    if (n === 1 && !validateCurrentStep()) return;

    currentStep += n;
    updateStepUI();

    // ✅ Saat masuk step 3: generate placement fields
    if (currentStep === 3) {
        preparePlacementFields();

        // Set form action di sini
        const form = document.getElementById("checkInForm");
        const bId = document.getElementById("modal_booking_id").value;
        form.action = `/admin/bookings/${bId}/placement`;
    }
}

function validateCurrentStep() {
    if (currentStep === 1) {
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
            alert("Mohon isi nama PIC Warehouse");
            return false;
        }

        const porters = document.querySelectorAll('[name="porters[]"]');
        if (porters.length === 0) {
            alert("Tambahkan minimal 1 porter");
            return false;
        }

        let porterFilled = true;
        porters.forEach((p) => {
            if (!p.value) porterFilled = false;
        });
        if (!porterFilled) {
            alert("Semua porter harus dipilih!");
            return false;
        }

        // ✅ Validasi pallet count & per pallet sudah diisi
        const palletCount =
            parseInt(document.getElementById("pallet_count").value) || 0;
        const perPallet =
            parseInt(document.getElementById("per_pallet").value) || 0;
        if (palletCount <= 0 || perPallet <= 0) {
            alert("Mohon isi Jumlah Palet dan Qty per Palet!");
            return false;
        }
    }

    // ✅ Validasi step 3: semua line & petak harus dipilih sebelum submit
    // (ini tidak dipanggil via changeStep, tapi bisa ditambah di finalSubmit)

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
    // ✅ nextBtn hanya muncul di step 1 dan 2
    document
        .getElementById("nextBtn")
        .classList.toggle("hidden", currentStep === 3);
    // ✅ submitBtn hanya muncul di step 3
    document
        .getElementById("finalSubmitBtn")
        .classList.toggle("hidden", currentStep !== 3);
}
function submitCheckin() {
    // Validasi semua line & petak sudah dipilih
    const lines = document.querySelectorAll('[name="lines[]"]');
    const petaks = document.querySelectorAll('[name="petaks[]"]');

    let valid = true;
    lines.forEach((l) => {
        if (!l.value) valid = false;
    });
    petaks.forEach((p) => {
        if (!p.value) valid = false;
    });

    if (!valid) {
        alert("Please Choose Liane and Section for All Pallets!");
        return;
    }

    // Sync hidden inputs sebelum submit
    syncHiddenInputs();

    document.getElementById("checkInForm").submit();
}

function addPorterField() {
    const container = document.getElementById("porterContainer");

    const porterData = document.querySelectorAll("#porterDataSource div");

    let options = '<option value="">Choose Porter</option>';

    porterData.forEach((p) => {
        options += `<option value="${p.dataset.name}">
            ${p.dataset.name}
        </option>`;
    });

    const select = document.createElement("select");

    select.name = "porters[]";

    select.className =
        "w-full px-6 py-4 font-bold border-none bg-slate-50 rounded-2xl";

    select.innerHTML = options;

    container.appendChild(select);
}

// 3. BATCH MANAGEMENT
function addBatchField() {
    const container = document.getElementById("batchContainer");
    const porterData = document.querySelectorAll("#porterDataSource div");

    let porterOptions = '<option value="">Choose Porter</option>';
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

function syncHiddenInputs() {
    const totalQty = document.getElementById("check_qty").innerText;
    const perPallet = document.getElementById("per_pallet").value;

    document.getElementById("hidden_total_qty").value = totalQty;
    document.getElementById("hidden_per_pallet").value = perPallet;
}

// Panggil fungsi ini setiap kali ada perubahan pada input jumlah
document
    .getElementById("per_pallet")
    .addEventListener("input", syncHiddenInputs);

// 4. PLACEMENT LOGIC
// function preparePlacementFields() {
//     const container = document.getElementById("placementContainer");
//     container.innerHTML = "";

//     // AMBIL NAMA PRODUK DARI ELEMEN UI YANG SUDAH ADA
//     const namaProduk =
//         document.getElementById("check_product_name").innerText || "Produk";

//     const palletCount =
//         parseInt(document.getElementById("pallet_count").value) || 0;
//     const perPallet =
//         parseInt(document.getElementById("per_pallet").value) || 0;
//     const remainder =
//         parseInt(document.getElementById("pallet_remainder").value) || 0;

//     for (let i = 0; i < palletCount; i++) {
//         let qty =
//             i === palletCount - 1 && remainder > 0 ? remainder : perPallet;

//         const div = document.createElement("div");
//         div.className =
//             "grid grid-cols-3 gap-4 p-4 border bg-slate-50 border-slate-100 rounded-2xl";

//         div.innerHTML = `
//             <div>
//                 <p class="text-[10px] font-black text-slate-400 uppercase">Pallet ${i + 1}</p>
//                 <p class="text-sm font-bold text-slate-800">${qty} Box</p>
//                 <input type="hidden" name="pallet_qty[]" value="${qty}">
//             </div>

//             <input type="hidden" name="product_names[]" value="${namaProduk}">

//             <select name="lines[]" onchange="updatePetakOptions(${i})" id="line_${i}" class="px-4 py-2 text-xs font-bold bg-white rounded-lg" required>
//                 <option value="">Choose Line</option>
//                 ${generateLineOptions()}
//             </select>
//             <select name="petaks[]" id="petak_${i}" class="px-4 py-3 text-xs font-bold bg-white rounded-lg" required>
//                 <option value="">Choose Section</option>
//             </select>
//         `;
//         container.appendChild(div);
//     }
// }

// 4. PLACEMENT LOGIC

// Sediakan satu variabel counter global agar ID elemen selalu unik
let placementRowCounter = 0;

// Fungsi yang dipanggil otomatis saat masuk ke Step 3
function preparePlacementFields() {
    const container = document.getElementById("placementContainer");
    container.innerHTML = ""; // Reset container

    // Reset counter setiap kali masuk step 3 dari awal
    placementRowCounter = 0;

    const palletCount =
        parseInt(document.getElementById("pallet_count").value) || 0;

    // Update info display status alokasi di atas
    document.getElementById("total_pallets_needed_display").innerText =
        palletCount;
    document.getElementById("allocated_pallets_display").innerText = 0;

    // Secara default, buatkan 1 baris kosong pertama
    addPlacementRowGroup();
}

// Fungsi untuk menambah baris grup lokasi baru secara dinamis
function addPlacementRowGroup() {
    const container = document.getElementById("placementContainer");

    // Gunakan nilai counter saat ini sebagai indeks unik
    const index = placementRowCounter;

    const div = document.createElement("div");
    div.className =
        "grid items-end grid-cols-1 gap-4 p-4 duration-200 border placement-group-row sm:grid-cols-4 bg-slate-50 border-slate-100 rounded-2xl animate-in fade-in zoom-in";
    div.setAttribute("id", `placement_row_${index}`);

    // Tentukan apakah ini baris pertama di dalam container saat ini
    const isFirstRow = container.children.length === 0;

    div.innerHTML = `
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Line</label>
            <select name="lines[]" onchange="updateGroupPetakOptions(${index})" id="group_line_${index}" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Choose Line</option>
                ${generateLineOptions()}
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Petak (Section)</label>
            <select name="petaks[]" id="group_petak_${index}" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Choose Section</option>
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Number of Palets</label>
            <input type="number" name="pallet_qty[]" min="1" oninput="updateAllocatedPallets()" placeholder="Example: 15" class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="flex items-center h-full pb-1">
            ${
                !isFirstRow
                    ? `
                <button type="button" onclick="document.getElementById('placement_row_${index}').remove(); updateAllocatedPallets();" class="text-xs font-bold text-red-500 hover:text-red-700 px-2 py-1">
                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                </button>
            `
                    : '<span class="text-[10px] text-slate-400 font-bold italic px-2">Main Location</span>'
            }
        </div>
    `;
    container.appendChild(div);

    // Naikkan nilai counter agar baris berikutnya mendapatkan ID yang berbeda
    placementRowCounter++;
}

// Pembaruan select petak berdasarkan line terpilih pada baris spesifik
function updateGroupPetakOptions(idx) {
    const line = document.getElementById(`group_line_${idx}`).value;
    const petakSelect = document.getElementById(`group_petak_${idx}`);

    petakSelect.innerHTML = '<option value="">Choose Section</option>';
    if (!line) return;

    const availablePetaks = window.currentInventory.filter(
        (i) => i.line == line,
    );
    const uniquePetaks = [...new Set(availablePetaks.map((i) => i.petak))];

    uniquePetaks.forEach((p) => {
        const pallet = availablePetaks.find((i) => i.petak == p);
        const isFilled = pallet?.status === "filled";

        petakSelect.innerHTML += `
        <option value="${p}">
            Petak ${p} ${isFilled ? "(Terisi)" : ""}
        </option>`;
    });
}

// Hitung total nilai input palet yang sudah dimasukkan oleh user
function updateAllocatedPallets() {
    const qtyInputs = document.querySelectorAll('input[name="pallet_qty[]"]');
    let totalAllocated = 0;

    qtyInputs.forEach((input) => {
        totalAllocated += parseInt(input.value) || 0;
    });

    const displayEl = document.getElementById("allocated_pallets_display");
    if (displayEl) {
        displayEl.innerText = totalAllocated;

        const totalNeeded =
            parseInt(document.getElementById("pallet_count").value) || 0;
        if (totalAllocated === totalNeeded) {
            displayEl.className = "font-black text-emerald-600";
        } else if (totalAllocated > totalNeeded) {
            displayEl.className = "font-black text-red-600";
        } else {
            displayEl.className = "font-black text-blue-600";
        }
    }
}

// Tambahkan pengecekan ini di bagian akhir fungsi penanganan submit form Anda
// Validasi Step 3 dan generate input hidden name produk secara dinamis
function validateStep3BeforeSubmit() {
    const lines = document.querySelectorAll('select[name="lines[]"]');
    const petaks = document.querySelectorAll('select[name="petaks[]"]');
    const qtys = document.querySelectorAll('input[name="pallet_qty[]"]');
    const totalNeeded =
        parseInt(document.getElementById("pallet_count").value) || 0;
    const form = document.getElementById("checkInForm");

    let totalAllocated = 0;
    let allFilled = true;

    // 1. Validasi Kelengkapan Data Input
    for (let i = 0; i < lines.length; i++) {
        if (!lines[i].value || !petaks[i].value || !qtys[i].value) {
            allFilled = false;
        }
        totalAllocated += parseInt(qtys[i].value) || 0;
    }

    if (!allFilled) {
        alert(
            "Please complete all Line, Plot, and Number of Pallets options on the form!",
        );
        return false;
    }

    if (totalAllocated !== totalNeeded) {
        alert(
            `Number of pallets allocated (${totalAllocated}) does not match the total number of pallets required (${totalNeeded})!`,
        );
        return false;
    }

    // 2. AMBIL NAMA PRODUK DARI UI HEADER MODAL ATAU STEP 1
    const namaProduk =
        document.getElementById("check_product_name")?.innerText || "Produk";

    // 3. HAPUS INPUT PRODUCT_NAMES SEBELUMNYA AGAR TIDAK DUPLIKAT
    const oldProductInputs = form.querySelectorAll(
        'input[name="product_names[]"]',
    );
    oldProductInputs.forEach((el) => el.remove());

    // 4. GENERATE INPUT HIDDEN BARU SEBANYAK JUMLAH BARIS LOCATION
    // Ini menjamin Laravel menerima array dengan panjang yang sama persis
    for (let i = 0; i < lines.length; i++) {
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "product_names[]";
        hiddenInput.value = namaProduk;
        form.appendChild(hiddenInput);
    }

    return true;
}

// Intercept form submit untuk validasi
document.getElementById("checkInForm").addEventListener("submit", function (e) {
    if (currentStep === 3) {
        if (!validateStep3BeforeSubmit()) {
            e.preventDefault(); // Batalkan submit jika validasi gagal atau kuantitas salah
            return false;
        }
    }
});
// Hubungkan ke tombol finalSubmitBtn
document.getElementById("checkInForm").addEventListener("submit", function (e) {
    if (currentStep === 3) {
        if (!validateStep3BeforeSubmit()) {
            e.preventDefault(); // Batalkan submit jika salah kuantitas
            return false;
        }
        syncHiddenInputs();
    }
});

function generateLineOptions() {
    const uniqueLines = [
        ...new Set(window.currentInventory.map((i) => i.line)),
    ];
    return uniqueLines
        .map((l) => `<option value="${l}">Line ${l}</option>`)
        .join("");
}

function updatePetakOptions(idx) {
    const line = document.getElementById(`line_${idx}`).value;
    const petakSelect = document.getElementById(`petak_${idx}`);

    petakSelect.innerHTML = '<option value="">Choose Section</option>';

    if (!line) return;

    // Ambil SEMUA petak yang ada di line tersebut, TANPA filter status
    // const availablePetaks = window.currentInventory.filter(
    //     (i) => i.line == line,
    // );
    const availablePetaks = window.currentInventory.filter(
        (i) => i.line == line,
    );

    // Gunakan Set untuk menghindari duplikat jika ada data ganda di DOM
    // const uniquePetaks = [...new Set(availablePetaks.map((i) => i.petak))];

    // uniquePetaks.forEach((p) => {
    //     petakSelect.innerHTML += `<option value="${p}">Petak ${p}</option>`;
    // });
    const uniquePetaks = [...new Set(availablePetaks.map((i) => i.petak))];

    uniquePetaks.forEach((p) => {
        const pallet = availablePetaks.find((i) => i.petak == p);
        const isFilled = pallet?.status === "filled";

        petakSelect.innerHTML += `
        <option value="${p}">
            Petak ${p} ${isFilled ? "(Terisi)" : ""}
        </option>`;
    });
}

// function calculatePalletFromPerPallet() {
//     const qty = maxQty;

//     const perPallet =
//         parseFloat(document.getElementById("per_pallet").value) || 0;

//     if (perPallet <= 0) return;

//     const pallet = Math.ceil(qty / perPallet);
//     const remainder = qty % perPallet;

//     document.getElementById("pallet_count").value = pallet;
//     document.getElementById("pallet_remainder").value = remainder;

//     updatePalletSummary(pallet, perPallet, remainder);
// }
function calculatePalletFromPerPallet() {
    const qty = maxQty;

    const perPallet =
        parseFloat(document.getElementById("per_pallet").value) || 0;

    if (perPallet <= 0) return;

    // 1. Hitung palet utama yang terisi penuh
    let pallet = Math.floor(qty / perPallet);
    const remainder = qty % perPallet;

    // 2. Jika ada sisa box, maka butuh +1 palet tambahan untuk menampungnya
    if (remainder > 0) {
        pallet = pallet + 1;
    }

    // Tuliskan hasil kalkulasi final ke input form
    document.getElementById("pallet_count").value = pallet;
    document.getElementById("pallet_remainder").value = remainder;

    // Perbarui ringkasan visual hijau di bawahnya
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

const perPalletInput = document.getElementById("per_pallet");
if (perPalletInput) {
    perPalletInput.addEventListener("input", calculatePalletFromPerPallet);
}

const palletCountInput = document.getElementById("pallet_count");
if (palletCountInput) {
    palletCountInput.addEventListener("input", calculatePalletFromCount);
}

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
    document.getElementById("sum_pallet").innerText = pallet; // Ini akan otomatis tercetak 39 sekarang!

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

if (typeof Html5QrcodeScanner !== "undefined") {
    let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
        fps: 10,
        qrbox: 250,
    });

    html5QrcodeScanner.render(onScanSuccess);
}

function setFormAction() {
    const form = document.getElementById("checkInForm");
    // Pastikan ID ini terisi saat modal dibuka
    const bookingId = document.getElementById("modal_booking_id").value;

    if (!bookingId) {
        alert("ID Booking tidak ditemukan!");
        return false;
    }

    form.action = `/admin/bookings/${bookingId}/placement`;
}
