<!DOCTYPE html>
<html lang="en" class="bg-slate-50 print:bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $batch->batch_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 p-0 font-serif text-slate-800 print:bg-white md:p-10 print:md:p-0">
    <main class="relative mx-auto flex min-h-[calc(100vh-5rem)] max-w-4xl flex-col justify-center border-[12px] border-double border-slate-200 bg-white p-8 shadow-xl print:min-h-screen print:max-w-none print:border-[12px] print:p-12 print:shadow-none sm:p-12 lg:p-16">
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[0.03]">
            <h1 class="-rotate-45 text-[8rem] font-black sm:text-[12rem]">PASSED</h1>
        </div>

        <div class="relative pb-8 text-center border-b-2 border-slate-800">
            <h1 class="text-3xl font-black uppercase tracking-widest text-slate-900 sm:text-4xl">Certificate of Irradiation</h1>
            <p class="mt-2 italic text-slate-500">Standard Operating Procedure Compliance: ISO 11137</p>
        </div>

        <div class="relative mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 sm:gap-10">
            <div>
                <h4 class="mb-2 text-xs font-black uppercase text-slate-400">Customer Information</h4>
                <p class="text-lg font-bold text-slate-800">{{ $batch->booking->customer->company_name ?? 'N/A' }}</p>
                <p class="text-sm text-slate-600">Booking Code: {{ $batch->booking->booking_code }}</p>
            </div>
            <div class="sm:text-right">
                <h4 class="mb-2 text-xs font-black uppercase text-slate-400">Certificate Number</h4>
                <p class="text-lg font-bold text-slate-800">COI/{{ date('Ymd') }}/{{ $batch->id }}</p>
                <p class="text-sm text-slate-600">Issued Date: {{ now()->format('d M Y') }}</p>
            </div>
        </div>

        <div class="relative mt-10 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="border border-slate-300 p-3 text-left text-xs uppercase">Description</th>
                        <th class="border border-slate-300 p-3 text-center text-xs uppercase">Specification</th>
                        <th class="border border-slate-300 p-3 text-center text-xs uppercase">Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-slate-300 p-4 font-bold">Product Name</td>
                        <td class="border border-slate-300 p-4 text-center text-slate-400">-</td>
                        <td class="border border-slate-300 p-4 text-center font-bold">{{ $batch->booking->products->first()->product_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-4 font-bold">Batch Number</td>
                        <td class="border border-slate-300 p-4 text-center text-slate-400">-</td>
                        <td class="border border-slate-300 p-4 text-center font-bold">Batch #{{ $batch->batch_number }}</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-4 font-bold">Irradiation Dose (kGy)</td>
                        <td class="border border-slate-300 p-4 text-center italic text-slate-500">Target: {{ $batch->target_dose !== null ? \App\Support\NumberFormatter::smart($batch->target_dose, 4) : '-' }}</td>
                        <td class="border border-slate-300 p-4 text-center font-black text-emerald-700">Actual: {{ $batch->qa?->actual_dose !== null ? \App\Support\NumberFormatter::smart($batch->qa->actual_dose, 4) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-4 font-bold">Visual Inspection</td>
                        <td class="border border-slate-300 p-4 text-center italic text-slate-500">Pass Criteria</td>
                        <td class="border border-slate-300 p-4 text-center font-bold uppercase text-emerald-600">{{ $batch->qa->visual_check ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="relative mt-10 rounded-xl border border-slate-100 bg-slate-50 p-6">
            <p class="mb-2 text-[10px] font-black uppercase text-slate-400">Statement of Conformity</p>
            <p class="text-sm italic leading-relaxed text-slate-600">"This is to certify that the products identified above have been processed by E-Beam Irradiation on {{ $batch->qa->inspected_at ? $batch->qa->inspected_at->format('d M Y') : now()->format('d M Y') }} at Production Line {{ $batch->productionLine->name ?? '-' }}. All parameters monitored were within the validated range."</p>
        </div>

        <div class="relative mt-14 flex items-end justify-between gap-8">
            <div class="text-center">
                <div class="mx-auto mb-2 h-1 w-32 bg-slate-200"></div>
                <p class="text-[10px] font-black uppercase text-slate-400">Quality Assurance Dept.</p>
            </div>
            <div class="text-center">
                <p class="mb-12 text-sm font-black text-slate-800">Authorized Signatory</p>
                <div class="mx-auto mb-2 h-px w-48 bg-slate-800"></div>
                <p class="text-xs font-bold text-slate-600">Production Manager</p>
            </div>
        </div>
    </main>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() { window.close(); };
        }
    </script>
</body>
</html>
