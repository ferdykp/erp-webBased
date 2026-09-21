<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking Ticket - {{ $booking->booking_code }}</title>
    @php
        $manifestPath = public_path('build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $tailwindAsset = $manifest['resources/css/app.css']['file'] ?? null;
        $tailwindCss = $tailwindAsset && file_exists(public_path('build/'.$tailwindAsset))
            ? file_get_contents(public_path('build/'.$tailwindAsset))
            : '';
    @endphp
    <style>{!! $tailwindCss !!}</style>
</head>
<body class="m-0 bg-white p-0 font-sans text-slate-700">
    <main class="p-8">
        <header class="mb-8 border-b-2 border-slate-100 pb-5">
            <table class="w-full border-collapse">
                <tr>
                    <td class="align-top">
                        <div class="text-2xl font-extrabold tracking-tight text-blue-600">BEAM<span class="text-slate-500">APP</span></div>
                        <div class="mt-1 text-[10px] text-slate-500">E-Beam Sterilization Ticket</div>
                    </td>
                    <td class="align-top text-right">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Booking Confirmation</div>
                        <div class="mt-1 text-xl font-extrabold text-slate-800">#{{ $booking->booking_code }}</div>
                    </td>
                </tr>
            </table>
        </header>

        <table class="mb-10 w-full border-collapse">
            <tr>
                <td class="w-[30%] align-top text-center">
                    @php
                        $qrcode = QrCode::size(140)->margin(0)->generate($booking->booking_code);
                    @endphp
                    <img src="data:image/svg+xml;base64,{{ base64_encode($qrcode) }}" width="140" height="140" class="mx-auto">
                    <div class="mt-2 text-[9px] text-slate-400">Scan for verification</div>
                </td>
                <td class="w-[70%] align-top pl-10">
                    <table class="w-full border-collapse">
                        <tr>
                            <td class="pb-4 align-top">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Customer Name</div>
                                <div class="mt-1 text-sm font-bold text-slate-900">{{ auth('customer')->user()->username ?? auth('customer')->user()->name ?? '-' }}</div>
                            </td>
                            <td class="pb-4 align-top">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</div>
                                <div class="mt-1 inline-block rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase text-slate-600">{{ $booking->status }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 align-top" colspan="2">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Booking Created</div>
                                <div class="mt-1 text-sm font-bold text-slate-900">{{ $booking->created_at->format('l, d F Y · H:i') }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Product Details</div>
        <table class="w-full border-collapse">
            <thead class="bg-slate-50">
                <tr>
                    <th class="w-10 border-b border-slate-200 px-3 py-3 text-left text-[11px] font-bold uppercase text-slate-500">No</th>
                    <th class="border-b border-slate-200 px-3 py-3 text-left text-[11px] font-bold uppercase text-slate-500">Product Name</th>
                    <th class="border-b border-slate-200 px-3 py-3 text-right text-[11px] font-bold uppercase text-slate-500">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->products as $index => $product)
                    <tr>
                        <td class="border-b border-slate-100 px-3 py-3 text-xs text-slate-600">{{ $index + 1 }}</td>
                        <td class="border-b border-slate-100 px-3 py-3">
                            <div class="text-xs font-bold text-slate-800">{{ $product->product_name }}</div>
                            <div class="mt-0.5 text-[10px] text-slate-500">Sterilization Service</div>
                        </td>
                        <td class="border-b border-slate-100 px-3 py-3 text-right text-xs font-bold text-slate-700">{{ $product->quantity }} {{ $product->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="mt-12 rounded-xl bg-slate-50 p-4">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Important Note</div>
            <ul class="mt-2 list-disc space-y-1 pl-4 text-[10px] leading-5 text-slate-600">
                <li>Harap membawa tiket ini dalam bentuk digital atau cetak saat kedatangan.</li>
                <li>Pastikan barang sudah dikemas sesuai standar operasional.</li>
                <li>Koordinasikan waktu kedatangan dengan pihak operasional sebelum pengiriman barang.</li>
            </ul>
        </section>

        <footer class="mt-12 border-t border-slate-100 pt-4 text-center text-[10px] leading-5 text-slate-400">
            Generated by BeamApp System · {{ date('d M Y H:i:s') }}<br>
            E-Beam Sterilization Operations
        </footer>
    </main>
</body>
</html>
