<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $batch->batch_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Pengaturan Ukuran Kertas (A4) */
        @page {
            size: A4;
            margin: 0;
            /* Menghilangkan margin bawaan browser (watermark URL/Header/Footer) */
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .cert-card {
                box-shadow: none !important;
                border: 12px double #e2e8f0 !important;
                margin: 0 !important;
                height: 100vh;
                /* Memastikan sertifikat mengisi penuh satu halaman */
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }

        /* Styling tambahan untuk memastikan konten presisi di tengah */
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>

<body class="p-0 font-serif bg-slate-50 md:p-10">

    <div class="cert-card max-w-4xl mx-auto bg-white border-[12px] border-double border-slate-200 p-16 relative">

        {{-- Watermark "PASSED" --}}
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
            <h1 class="text-[12rem] font-black -rotate-45">PASSED</h1>
        </div>

        {{-- Header --}}
        <div class="pb-8 mb-10 text-center border-b-2 border-slate-800">
            <h1 class="text-4xl font-black tracking-widest uppercase text-slate-900">Certificate of Irradiation</h1>
            <p class="mt-2 italic text-slate-500">Standard Operating Procedure Compliance: ISO 11137</p>
        </div>

        {{-- Body Content --}}
        <div class="grid grid-cols-2 gap-10 mb-12">
            <div>
                <h4 class="mb-2 text-xs font-black uppercase text-slate-400">Customer Information</h4>
                <p class="text-lg font-bold text-slate-800">{{ $batch->booking->customer->company_name ?? 'N/A' }}</p>
                <p class="text-sm text-slate-600">Booking Code: {{ $batch->booking->booking_code }}</p>
            </div>
            <div class="text-right">
                <h4 class="mb-2 text-xs font-black uppercase text-slate-400">Certificate Number</h4>
                <p class="text-lg font-bold text-slate-800">COI/{{ date('Ymd') }}/{{ $batch->id }}</p>
                <p class="text-sm text-slate-600">Issued Date: {{ now()->format('d M Y') }}</p>
            </div>
        </div>

        <table class="w-full mb-12 border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="p-3 text-xs text-left uppercase border border-slate-300">Description</th>
                    <th class="p-3 text-xs text-center uppercase border border-slate-300">Specification</th>
                    <th class="p-3 text-xs text-center uppercase border border-slate-300">Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-4 font-bold border border-slate-300">Product Name</td>
                    <td class="p-4 text-center border border-slate-300 text-slate-400">-</td>
                    <td class="p-4 font-bold text-center border border-slate-300">
                        {{ $batch->booking->products->first()->product_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="p-4 font-bold border border-slate-300">Batch Number</td>
                    <td class="p-4 text-center border border-slate-300 text-slate-400">-</td>
                    <td class="p-4 font-bold text-center border border-slate-300">Batch #{{ $batch->batch_number }}</td>
                </tr>
                <tr>
                    <td class="p-4 font-bold border border-slate-300">Irradiation Dose (kGy)</td>
                    <td class="p-4 italic text-center border border-slate-300 text-slate-500">Target:
                        {{ (int) $batch->target_dose }}</td>
                    <td class="p-4 font-black text-center border border-slate-300 text-emerald-700">Actual:
                        {{ $batch->qa->actual_dose ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="p-4 font-bold border border-slate-300">Visual Inspection</td>
                    <td class="p-4 italic text-center border border-slate-300 text-slate-500">Pass Criteria</td>
                    <td class="p-4 font-bold text-center uppercase border border-slate-300 text-emerald-600">
                        {{ $batch->qa->visual_check ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="p-6 mb-16 border bg-slate-50 rounded-xl border-slate-100">
            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Statement of Conformity</p>
            <p class="text-sm italic leading-relaxed text-slate-600">
                "This is to certify that the products identified above have been processed by E-Beam Irradiation
                on {{ $batch->qa->inspected_at ? $batch->qa->inspected_at->format('d M Y') : now()->format('d M Y') }}
                at Production Line {{ $batch->productionLine->name ?? '-' }}.
                All parameters monitored were within the validated range."
            </p>
        </div>

        {{-- Signature --}}
        <div class="flex items-end justify-between">
            <div class="text-center">
                <div class="w-32 h-1 mx-auto mb-2 bg-slate-200"></div>
                <p class="text-[10px] font-black uppercase text-slate-400">Quality Assurance Dept.</p>
            </div>
            <div class="text-center">
                <p class="mb-12 text-sm font-black text-slate-800">Authorized Signatory</p>
                <div class="w-48 h-px mx-auto mb-2 bg-slate-800"></div>
                <p class="text-xs font-bold text-slate-600">Production Manager</p>
            </div>
        </div>
    </div>

    {{-- Script Auto Print & Auto Close --}}
    <script>
        window.onload = function() {
            window.print();

            // Opsional: Menutup tab otomatis setelah dialog print selesai
            // Namun beberapa browser memblokir fungsi window.close()
            window.onafterprint = function() {
                window.close();
            };
        }
    </script>

</body>

</html>
