document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("detailModal");
    const card = document.getElementById("detailCard");

    // 1. Event Delegation (Menangani klik tombol)
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-detail");

        if (btn) {
            console.log("Tombol diklik!");
            try {
                // Parsing data dari atribut data-booking
                const booking = JSON.parse(btn.dataset.booking);
                console.log("Data Booking:", booking);
                openDetailModal(booking);
            } catch (error) {
                console.error("Gagal membaca data booking:", error);
            }
        }
    });

    // 2. Fungsi Utama Membuka Modal & Mengisi Data
    function openDetailModal(booking) {
        // --- A. Informasi Dasar ---
        document.getElementById("detail_booking_code").innerText =
            booking.booking_code || `#BOK-${booking.id}`;
        document.getElementById("detail_customer_name").innerText =
            booking.customer?.name || "Guest";
        document.getElementById("detail_customer_email").innerText =
            booking.customer?.email || "-";
        document.getElementById("detail_pic_warehouse").innerText =
            booking.pic_warehouse || "Not Assigned";

        // --- B. Status Badge ---
        const statusBadge = document.getElementById("detail_status_badge");
        const statusClasses = {
            pending: "bg-amber-100 text-amber-700",
            approved: "bg-emerald-100 text-emerald-700",
            processing: "bg-blue-100 text-blue-700",
            completed: "bg-slate-200 text-slate-700",
        };
        const currentStatus = booking.status || "pending";
        statusBadge.innerText = currentStatus.toUpperCase();
        statusBadge.className = `px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg ${statusClasses[currentStatus] || "bg-gray-100"}`;

        // --- C. Format Tanggal ---
        const formatDate = (dateStr) => {
            if (!dateStr) return "-";
            const d = new Date(dateStr);
            return (
                d.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                }) +
                " " +
                d.toLocaleTimeString("id-ID", {
                    hour: "2-digit",
                    minute: "2-digit",
                })
            );
        };

        document.getElementById("detail_booking_date").innerText = formatDate(
            booking.created_at,
        );
        document.getElementById("detail_arrival_time").innerText =
            booking.arrival_time
                ? formatDate(booking.arrival_time) + " WIB"
                : "Waiting...";

        // --- D. Render Pallets ---
        const palletContainer = document.getElementById("detail_pallets_list");
        const palletCountText = document.getElementById("pallet_count_text");
        palletContainer.innerHTML = "";

        if (booking.pallets && booking.pallets.length > 0) {
            palletCountText.innerText = `${booking.pallets.length} Pallets`;
            booking.pallets.forEach((p) => {
                palletContainer.innerHTML += `
                    <div class="flex items-center gap-2 px-3 py-2 border bg-slate-50 border-slate-100 rounded-xl">
                        <i class="text-[10px] text-blue-500 fa-solid fa-box-archive"></i>
                        <span class="text-[10px] font-black text-slate-700 uppercase">${p.pallet_number}</span>
                    </div>`;
            });
        } else {
            palletCountText.innerText = `0 Pallets`;
            palletContainer.innerHTML =
                '<p class="text-xs italic text-slate-300">No pallets assigned</p>';
        }

        // --- E. Render Production Distribution (Tabel Batch) ---
        const batchTableBody = document.getElementById(
            "detail_batch_table_body",
        );
        const batchSection = document.getElementById("batch_result_section");
        batchTableBody.innerHTML = "";

        const mainProduct =
            booking.products && booking.products.length > 0
                ? booking.products[0]
                : {};
        const pUnit = mainProduct.unit || "Unit";

        if (booking.batches && booking.batches.length > 0) {
            let totalQty = 0;
            booking.batches.forEach((batch, i) => {
                const qty = parseFloat(batch.quantity || 0);
                totalQty += qty;

                batchTableBody.innerHTML += `
                    <tr class="transition-colors hover:bg-emerald-50/30">
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-lg uppercase">BTCH-${String(i + 1).padStart(2, "0")}</span>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">${batch.porter_name || "Not Assigned"}</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">In Charge</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <p class="text-sm font-black text-slate-800">${mainProduct.product_name || "N/A"}</p>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg uppercase">${mainProduct.dmin || "0"} - ${mainProduct.dmax || "0"} <span class="text-[8px] opacity-70">kGy</span></span>
                        </td>
                        <td class="px-8 py-6 font-black text-right text-slate-800">
                            ${qty.toLocaleString()} <span class="text-[9px] text-slate-400 font-bold ml-1">${pUnit}</span>
                        </td>
                    </tr>`;
            });

            document.getElementById("batch_total_sum").innerText =
                `${totalQty.toLocaleString()} ${pUnit}`;
            document.getElementById("batch_count_badge").innerText =
                `${booking.batches.length} Batches`;
            if (batchSection) batchSection.classList.remove("hidden");
        } else {
            if (batchSection) batchSection.classList.add("hidden");
        }

        // --- F. Animasi Tampilkan Modal ---
        modal.classList.remove("opacity-0", "pointer-events-none");
        modal.classList.add("opacity-100", "pointer-events-auto");
        setTimeout(() => {
            card.classList.remove("scale-95", "opacity-0");
            card.classList.add("scale-100", "opacity-100");
        }, 50);
    }

    // 3. Fungsi Tutup Modal
    window.closeDetailModal = function () {
        card.classList.add("scale-95", "opacity-0");
        card.classList.remove("scale-100", "opacity-100");
        setTimeout(() => {
            modal.classList.add("opacity-0", "pointer-events-none");
            modal.classList.remove("opacity-100", "pointer-events-auto");
        }, 300);
    };
});

// 4. Auto-hide alert
setTimeout(() => {
    const alert = document.getElementById("status-alert");
    if (alert) alert.style.display = "none";
}, 5000);
