function openQCModal(booking, targetStatus) {
    const modal = document.getElementById("qcModal");
    const form = document.getElementById("qcForm");
    const icon = document.getElementById("qcIcon");
    const submitBtn = document.getElementById("qcSubmitBtn");

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
    // Injeksi Data Customer & Summary (Data tetap sama seperti kode Anda)
    document.getElementById("qcCustName").innerText = booking.customer
        ? booking.customer.name
        : "Guest";
    document.getElementById("qcCustAvatar").innerText = booking.customer
        ? booking.customer.name.substring(0, 1).toUpperCase()
        : "?";
    document.getElementById("qcCode").innerText =
        booking.booking_code || `#BOK-${booking.id}`;
    document.getElementById("qcPic").innerText = booking.pic_warehouse || "-";
    document.getElementById("qcArrival").innerText = booking?.arrival_time
        ? `${formatDate(booking.arrival_time)} WIB`
        : "-";
    document.getElementById("qcPalletCount").innerText = booking.pallets
        ? booking.pallets.length
        : 0;

    document.getElementById("qcStatusInput").value = targetStatus;
    document.getElementById("qcTargetStatus").innerText =
        `READY TO ${targetStatus.toUpperCase()}`;
    form.action = `/admin/bookings/${booking.id}/status`;

    const tableBody = document.getElementById("qc_detail_batch_table_body");
    tableBody.innerHTML = "";
    let totalQty = 0;

    if (booking.batches && booking.batches.length > 0) {
        booking.batches.forEach((batch) => {
            totalQty += parseFloat(batch.quantity);
            const row = `
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-4"><span class="text-[10px] font-black text-slate-800">#BTCH-${batch.batch_number}</span></td>
                    <td class="px-6 py-4"><span class="text-xs font-bold text-slate-600">${batch.porter_name || "-"}</span></td>
                    <td class="px-6 py-4"><p class="text-xs font-black text-slate-700">${booking.products[0]?.product_name || "Product"}</p></td>
                    <td class="px-6 py-4 text-center"><span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black">${booking.products[0]?.dmin || 0} - ${booking.products[0]?.dmax || 0} kGy</span></td>
                    <td class="px-8 py-4 text-right"><span class="font-black text-slate-800">${batch.quantity}</span> <span class="text-[9px] font-bold text-slate-400 uppercase">${batch.unit}</span></td>
                </tr>`;
            tableBody.insertAdjacentHTML("beforeend", row);
        });
    }

    document.getElementById("qcTotalQty").innerText = totalQty;
    document.getElementById("qc_batch_total_sum").innerText = totalQty;
    document.getElementById("qc_batch_count_badge").innerText =
        `${booking.batches ? booking.batches.length : 0} Batches`;

    if (targetStatus === "processing") {
        icon.className =
            "flex items-center justify-center w-16 h-16 mx-auto mb-4 text-purple-600 shadow-sm rounded-3xl bg-purple-50 shadow-purple-100";
        submitBtn.className =
            "flex-[2] py-5 text-xs font-black text-white uppercase transition-all shadow-xl rounded-[2rem] bg-purple-600 shadow-purple-100 hover:bg-purple-700 active:scale-95";
        submitBtn.innerText = "Authorize & Start Irradiation";
    } else {
        icon.className =
            "flex items-center justify-center w-16 h-16 mx-auto mb-4 shadow-sm rounded-3xl bg-emerald-50 text-emerald-600 shadow-emerald-100";
        submitBtn.className =
            "flex-[2] py-5 text-xs font-black text-white uppercase transition-all shadow-xl rounded-[2rem] bg-emerald-600 shadow-emerald-100 hover:bg-emerald-700 active:scale-95";
        submitBtn.innerText = "Confirm Quality & Release";
    }

    modal.classList.replace("hidden", "flex");
}

/** * TAMBAHKAN FUNGSI INI AGAR TOMBOL CANCEL BEKERJA
 */
function closeQCModal() {
    const modal = document.getElementById("qcModal");
    const form = document.getElementById("qcForm");

    modal.classList.replace("flex", "hidden");
    form.reset(); // Membersihkan checkbox yang sudah dicentang
}

// Tambahan: Menutup modal jika area luar modal diklik
window.addEventListener("click", function (e) {
    const modal = document.getElementById("qcModal");
    if (e.target === modal) {
        closeQCModal();
    }
});
