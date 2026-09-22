<!DOCTYPE html>
<html lang="en" class="bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Receipt - {{ $booking->booking_code }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white font-sans text-slate-700 print:m-0 print:p-0">
    <main class="mx-auto max-w-5xl p-6 print:max-w-none print:p-8 sm:p-10">
        <header class="mb-6 flex items-end justify-between gap-6 border-b-2 border-blue-600 pb-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">Warehouse Receipt</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-blue-600">GOODS RECEIPT INVOICE</h1>
            </div>
            <p class="text-sm font-bold text-slate-800">#{{ $booking->booking_code }}</p>
        </header>

        <section class="mb-6 grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 print:bg-white">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Customer Info</p>
                <p class="mt-2 font-semibold text-slate-800">{{ $booking->customer->contacts->first()->name }}</p>
                <p class="mt-1 text-xs text-slate-500">Booking Date: {{ $booking->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-left print:bg-white sm:text-right">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Warehouse Acceptance</p>
                <p class="mt-2 text-xs text-slate-500">Arrival: {{ \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y H:i') }}</p>
                <p class="mt-1 text-xs text-slate-500">PIC Warehouse: {{ $booking->pic_warehouse ?? '-' }}</p>
                <span class="mt-2 inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700">ACCEPTED</span>
            </div>
        </section>

        <section>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full border-collapse text-left text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border-b border-slate-200 px-4 py-3 font-extrabold uppercase tracking-wider text-slate-500">Product Name</th>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Type</th>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Dimensions</th>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Quantity</th>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Dose Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->products as $product)
                            <tr>
                                <td class="border-b border-slate-100 px-4 py-3 font-semibold text-slate-800">{{ $product->product_name }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-center">{{ $product->product_type }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-center">{{ $product->dimension_pack }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-center">{{ \App\Support\NumberFormatter::integer($product->quantity) }} {{ $product->unit }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-center">{{ \App\Support\NumberFormatter::smart($product->dmin, 4) }} - {{ \App\Support\NumberFormatter::smart($product->dmax, 4) }} kGy</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8">
            <h2 class="mb-3 text-sm font-bold text-slate-800">Reception Details (Batches)</h2>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full border-collapse text-left text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Batch #</th>
                            <th class="border-b border-slate-200 px-4 py-3 font-extrabold uppercase tracking-wider text-slate-500">Porter</th>
                            <th class="border-b border-slate-200 px-4 py-3 text-center font-extrabold uppercase tracking-wider text-slate-500">Received Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->batches as $batch)
                            <tr>
                                <td class="border-b border-slate-100 px-4 py-3 text-center font-semibold">{{ $batch->batch_number }}</td>
                                <td class="border-b border-slate-100 px-4 py-3">{{ $batch->porter_name }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-center">{{ \App\Support\NumberFormatter::integer($batch->quantity) }} {{ $batch->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="mt-12 border-t border-slate-200 pt-4 text-center text-[11px] leading-5 text-slate-500">
            <p>This is an automated receipt based on warehouse check-in system.</p>
            <p>Processing will start shortly according to the queue. Thank you for your business.</p>
        </footer>
    </main>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
