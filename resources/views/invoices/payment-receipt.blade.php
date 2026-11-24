<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Payment Receipt - {{ $payment->receipt_no }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 25px;
            color: #222;
            font-size: 14px;
        }

        .receipt {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .header p {
            margin: 5px 0;
            color: #555;
        }

        .receipt-title {
            text-align: center;
            margin: 25px 0;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .details {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .details td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .details td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .amount {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .amount h2 {
            color: #15803d;
            font-size: 28px;
            margin: 8px 0;
        }

        .footer {
            width: 100%;
            margin-top: 70px;
        }

        .signature {
            width: 45%;
            display: inline-block;
            text-align: center;
            border-top: 1px solid #222;
            padding-top: 8px;
        }

        .actions {
            max-width: 800px;
            margin: 20px auto;
            text-align: right;
        }

        .actions button,
        .actions a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin-left: 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                border: none;
            }

            .actions {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>

<body>

@if(!($pdfMode ?? false))
    <div class="actions">
        <button onclick="window.print()">Print Receipt</button>

        <a href="{{ route('payments.receipt.pdf', $payment) }}">
            Download PDF
        </a>

        <a href="{{ route('invoices.show', $payment->invoice_id) }}">
            Back to Invoice
        </a>
    </div>
@endif

<div class="receipt">

    <div class="header">
        <h1>HYPER TECH SOLUTIONS (PVT) LTD</h1>
        <p>Jaffna, Sri Lanka</p>
        <p>Payment Receipt</p>
    </div>

    <div class="receipt-title">
        PAYMENT RECEIPT
    </div>

    <table class="details">
        <tr>
            <td>Receipt Number</td>
            <td>{{ $payment->receipt_no ?? 'Not assigned' }}</td>
        </tr>

        <tr>
            <td>Payment Date</td>
            <td>
                {{ $payment->payment_date
                    ? $payment->payment_date->format('d/m/Y')
                    : '-' }}
            </td>
        </tr>

        <tr>
            <td>Invoice Number</td>
            <td>{{ $payment->invoice->invoice_no }}</td>
        </tr>

        <tr>
            <td>Customer Name</td>
            <td>{{ $payment->invoice->customer->name ?? '-' }}</td>
        </tr>

        <tr>
            <td>Customer Phone</td>
            <td>{{ $payment->invoice->customer->phone ?? '-' }}</td>
        </tr>

        <tr>
            <td>Payment Method</td>
            <td>
                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
            </td>
        </tr>

        <tr>
            <td>Reference</td>
            <td>{{ $payment->reference ?? '-' }}</td>
        </tr>

        <tr>
            <td>Received By</td>
            <td>{{ $payment->receiver->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="amount">
        <p>AMOUNT RECEIVED</p>
        <h2>Rs. {{ number_format($payment->amount, 2) }}</h2>
    </div>

    @if($payment->notes)
        <p><strong>Notes:</strong> {{ $payment->notes }}</p>
    @endif

    <div class="footer">
        <table style="width:100%">
            <tr>
                <td style="width:50%; text-align:center;">
                    _______________________<br>
                    Customer Signature
                </td>

                <td style="width:50%; text-align:center;">
                    _______________________<br>
                    Authorized Signature
                </td>
            </tr>
        </table>
    </div>

    <p style="text-align:center; margin-top:40px; color:#666;">
        Thank you for your payment!
    </p>

</div>

</body>
</html>
