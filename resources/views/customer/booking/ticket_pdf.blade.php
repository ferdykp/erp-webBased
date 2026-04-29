<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Ticket - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 30px;
        }

        .header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            /* Blue 600 */
        }

        .ticket-label {
            text-align: right;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 2px;
            color: #94a3b8;
        }

        .booking-id {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
        }

        .main-content {
            width: 100%;
            margin-bottom: 40px;
        }

        .qr-section {
            width: 30%;
            text-align: center;
            vertical-align: top;
        }

        .info-section {
            width: 70%;
            padding-left: 40px;
            vertical-align: top;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
        }

        .table-products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-products th {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
        }

        .table-products td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .footer {
            position: fixed;
            bottom: 30px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="container">
        {{-- HEADER --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td>
                        <div class="logo-text">BEAM<span style="color: #64748b;">APP</span></div>
                        <div style="font-size: 10px; color: #64748b;">E-Beam Sterilization Ticket</div>
                    </td>
                    <td>
                        <div class="ticket-label">Booking Confirmation</div>
                        <div class="booking-id">#{{ $booking->booking_code }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- MAIN SECTION: QR & PRIMARY INFO --}}
        <table class="main-content">
            <tr>
                <td class="qr-section">
                    {{-- Kita render sebagai SVG string dan di-encode ke Base64 --}}
                    @php
                        $qrcode = QrCode::size(140)->margin(0)->generate($booking->booking_code);
                    @endphp
                    <img src="data:image/svg+xml;base64,{{ base64_encode($qrcode) }}" width="140" height="140">
                    <div style="margin-top: 10px; font-size: 9px; color: #94a3b8;">Scan for verification</div>
                </td>
                <td class="info-section">
                    <table width="100%">
                        <tr>
                            <td class="info-item">
                                <div class="info-label">Customer Name</div>
                                <div class="info-value">{{ auth()->user()->username ?? '-' }}</div>
                            </td>
                            <td class="info-item">
                                <div class="info-label">Status</div>
                                <div class="status-badge">{{ $booking->status }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-item" colspan="2">
                                <div class="info-label">Schedule Slot</div>
                                @if ($booking->slot)
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($booking->slot->date)->format('l, d F Y') }}<br>
                                        <span style="color: #2563eb;">{{ $booking->slot->start_time }} -
                                            {{ $booking->slot->end_time }}</span>
                                    </div>
                                @else
                                    <div class="text-gray-400 info-value">-</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- PRODUCT LIST --}}
        <div class="info-label" style="margin-bottom: 10px;">Product Details</div>
        <table class="table-products">
            <thead>
                <tr>
                    <th width="10">No</th>
                    <th>Product Name</th>
                    <th style="text-align: right;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: bold;">{{ $product->product_name }}</div>
                            <div style="font-size: 10px; color: #64748b;">Sterilization Service</div>
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            {{ $product->quantity }} {{ $product->unit }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- INSTRUCTIONS (Optional but helpful) --}}
        <div style="margin-top: 50px; background: #f8fafc; padding: 15px; border-radius: 10px;">
            <div class="info-label">Important Note:</div>
            <ul style="font-size: 10px; color: #475569; margin-top: 5px; padding-left: 15px;">
                <li>Harap membawa tiket ini (digital/cetak) saat kedatangan.</li>
                <li>Pastikan barang sudah dikemas sesuai dengan standar operasional.</li>
                <li>Datang 15 menit sebelum slot waktu yang dijadwalkan.</li>
            </ul>
        </div>

        <div class="footer">
            Generated by BeamApp System • {{ date('d M Y H:i:s') }}<br>
            Jl. Sterilisasi No. 123, Jakarta, Indonesia
        </div>
    </div>

</body>

</html>
