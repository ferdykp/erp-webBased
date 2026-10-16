<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Irradiation Process Report</title>
    <style>
        @page {
            margin: 30px 36px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #172033;
            line-height: 1.45
        }

        h1 {
            font-size: 21px;
            margin: 3px 0 5px
        }

        h2 {
            font-size: 12px;
            border-bottom: 1px solid #ced5de;
            padding-bottom: 6px;
            margin: 22px 0 8px;
            color: #193d6a
        }

        .muted {
            color: #64748b
        }

        .tag {
            font-weight: bold;
            letter-spacing: 1px;
            color: #3265a1
        }

        .head {
            border-bottom: 3px solid #183e71;
            padding-bottom: 16px
        }

        .grid {
            width: 100%;
            border-collapse: collapse
        }

        .grid th,
        .grid td {
            border: 1px solid #d8dfe7;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top
        }

        .grid th {
            background: #f2f5fa;
            font-weight: bold
        }

        .noborder td {
            border: 0;
            padding: 4px 6px
        }

        .notice {
            background: #f2f5fa;
            padding: 12px;
            margin: 16px 0;
            font-size: 9px
        }

        .footer {
            font-size: 9px;
            margin-top: 24px;
            color: #64748b
        }

        .right {
            text-align: right
        }
    </style>
</head>

<body>
    <div class="head">
        <div class="tag">BEAMCUSTOM / PROCESS RECORD</div>
        <h1>Irradiation Process Report</h1>
        <div class="muted">Order {{ $booking->booking_code }} · Generated {{ now()->format('d M Y, H:i') }}
            (Asia/Jakarta)</div>
    </div>
    <div class="notice">This document summarizes recorded order, production and dosimetry data. It is not a certificate
        of sterility, dosimetric compliance, or independent quality release. Review source records and facility-approved
        SOP before releasing goods.</div>
    <h2>1. Customer & Order Information</h2>
    <table class="grid noborder">
        <tr>
            <td width="24%"><b>Company</b></td>
            <td>{{ $booking->customer?->company_name ?: 'Not recorded' }}</td>
            <td width="22%"><b>Order status</b></td>
            <td>{{ ucfirst($booking->status) }}</td>
        </tr>
        <tr>
            <td><b>Customer contact</b></td>
            <td>{{ $booking->customer?->contacts->firstWhere('is_primary', true)?->name ?? ($booking->customer?->contacts->first()?->name ?? 'Not recorded') }}
            </td>
            <td><b>Goods received</b></td>
            <td>{{ $booking->arrival_time?->format('d M Y H:i') ?? 'Not recorded' }}</td>
        </tr>
        {{-- <tr>
            <td><b>Warehouse PIC</b></td>
            <td>{{ $booking->pic_warehouse ?: 'Not recorded' }}</td>
            <td><b>Porter</b></td>
            <td>{{ $booking->porter_name ?: 'Not recorded' }}</td>
        </tr> --}}
    </table>
    <h2>2. Products & Requested Irradiation</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Product type</th>
                <th>Minimum dose (kGy)</th>
                <th>Maximum dose (kGy)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($booking->products as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ number_format((float) $product->quantity) }} {{ $product->unit }}</td>
                    <td>{{ $product->product_type ?: '—' }}</td>
                    <td>{{ $product->dmin ?? '—' }}</td>
                    <td>{{ $product->dmax ?? '—' }}</td>
            </tr>@empty<tr>
                    <td colspan="5">No product records</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <h2>3. Production Batch Records</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Batch</th>
                <th>Quantity</th>
                <th>Machine</th>
                <th>Target dose (kGy)</th>
                <th>Beam speed</th>
                <th>Status</th>
                <th>Finished</th>
            </tr>
        </thead>
        <tbody>
            @forelse($booking->batches as $batch)
                <tr>
                    <td>{{ $batch->batch_number }}</td>
                    <td>{{ $batch->quantity }} {{ $batch->unit }}</td>
                    <td>{{ $batch->productionLine?->name ?: '—' }}</td>
                    <td>{{ $batch->target_dose ?? '—' }}</td>
                    <td>{{ $batch->beam_speed ?? '—' }}</td>
                    <td>{{ ucfirst($batch->status) }}</td>
                    <td>{{ $batch->finished_at?->format('d M Y H:i') ?? '—' }}</td>
            </tr>@empty<tr>
                    <td colspan="7">No batch records</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <h2>4. Dosimetry Measurements</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Dosimeter ID</th>
                <th>Tablet</th>
                <th>Absorbance</th>
                <th>Measured dose (kGy)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($measurements as $item)
                <tr>
                    <td>{{ $item->dosimeter_number ?: '—' }}</td>
                    <td>{{ $item->tablet_number }}</td>
                    <td>{{ $item->absorbance ?? '—' }}</td>
                    <td>{{ $item->dose_kgy ?? '—' }}</td>
            </tr>@empty<tr>
                    <td colspan="4">No dosimetry measurements have been recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <table class="grid noborder" style="margin-top:8px">
        <tr>
            <td><b>Minimum measured dose</b>:
                {{ $doseStats['min'] === null ? 'Not available' : number_format($doseStats['min'], 3) . ' kGy' }}</td>
            <td><b>Maximum measured dose</b>:
                {{ $doseStats['max'] === null ? 'Not available' : number_format($doseStats['max'], 3) . ' kGy' }}</td>
            <td><b>Average measured dose</b>:
                {{ $doseStats['average'] === null ? 'Not available' : number_format($doseStats['average'], 3) . ' kGy' }}
            </td>
        </tr>
    </table>

    <div class="footer">Automatically generated from BeamCustom at {{ now()->format('Y-m-d H:i:s') }}. Missing data is
        shown as not recorded; no values were inferred.</div>


    @if (request()->routeIs('admin.orders.report.print'))
        <script>
            window.addEventListener('load', function() {
                window.focus();

                requestAnimationFrame(function() {
                    window.print();
                });
            });
        </script>
    @endif

</body>

</html>
