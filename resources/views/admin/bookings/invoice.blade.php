<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice Receipt - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.5;
        }

        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin: 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .product-table th {
            background: #f3f4f6;
            padding: 10px;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        .product-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <h1 class="title">GOODS RECEIPT INVOICE</h1>
                </td>
                <td align="right"><strong>#{{ $booking->booking_code }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Customer Info:</strong><br>
                {{ $booking->customer->name }}<br>
                Booking Date: {{ $booking->created_at->format('d/m/Y') }}
            </td>
            <td width="50%" align="right">
                <strong>Warehouse Acceptance:</strong><br>
                Arrival: {{ \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y H:i') }}<br>
                PIC Warehouse: {{ $booking->pic_warehouse ?? '-' }}<br>
                Status: <span class="status-badge">ACCEPTED</span>
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Type</th>
                <th>Dimensions</th>
                <th>Quantity</th>
                <th>Dose Target</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($booking->products as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td align="center">{{ $product->product_type }}</td>
                    <td align="center">{{ $product->dimension_pack }}</td>
                    <td align="center">{{ $product->quantity }} {{ $product->unit }}</td>
                    <td align="center">{{ $product->dmin }} - {{ $product->dmax }} kGy</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h4 style="margin-bottom: 5px; font-size: 14px;">Reception Details (Batches)</h4>
        <table class="product-table">
            <thead>
                <tr>
                    <th>Batch #</th>
                    <th>Porter</th>
                    <th>Received Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->batches as $batch)
                    <tr>
                        <td align="center">{{ $batch->batch_number }}</td>
                        <td>{{ $batch->porter_name }}</td>
                        <td align="center">{{ $batch->quantity }} {{ $batch->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is an automated receipt based on warehouse check-in system.</p>
        <p>Processing will start shortly according to the queue. Thank you for your business.</p>
    </div>
</body>

</html>
