
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Invoice {{ $invoice->invoice_no }}</title>

    <style>
        @page {
            margin: 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company {
            font-size: 23px;
            font-weight: bold;
            color: #1e40af;
        }

        .muted {
            color: #777;
        }

        .invoice-title {
            font-size: 25px;
            font-weight: bold;
            text-align: right;
            color: #1e40af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 5px 0;
            vertical-align: top;
        }

        .items {
            margin-top: 25px;
        }

        .items th {
            background: #1e40af;
            color: white;
            padding: 10px 8px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid #ddd;
            padding: 10px 8px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .totals {
            width: 48%;
            margin-left: auto;
            margin-top: 20px;
        }

        .totals td {
            padding: 7px;
            border-bottom: 1px solid #eee;
        }

        .grand-total td {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            border-top: 2px solid #1e40af;
        }

        .payment-summary {
            margin-top: 25px;
            padding: 12px;
            background: #f3f4f6;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            color: #777;
            font-size: 10px;
        }
    </style>
</head>

<body>

<!-- Company Header -->
<table class="header">
    <tr>
        <td width="60%">
            <div class="company">HYPER TECH SOLUTIONS</div>
            <div class="muted">Inventory & POS Management</div>
            <div class="muted">Sri Lanka</div>
        </td>

        <td width="40%" class="right">
            <div class="invoice-title">INVOICE</div>
            <strong>{{ $invoice->invoice_no }}</strong><br>
            Date:
            {{ $invoice->invoice_date?->format('d M Y') }}
        </td>
    </tr>
</table>

<!-- Customer Information -->
<table class="details">
    <tr>
        <td width="50%">
            <strong>Bill To:</strong><br>

            {{ $invoice->customer?->name ?? 'Customer unavailable' }}<br>

            @if($invoice->customer?->phone)
                Phone: {{ $invoice->customer->phone }}<br>
            @endif

            @if($invoice->customer?->email)
                Email: {{ $invoice->customer->email }}
            @endif
        </td>

        <td width="50%" class="right">
            <strong>Payment Status:</strong><br>
            {{ ucfirst($invoice->status) }}<br>

            <strong>Payment Method:</strong><br>
            {{ ucfirst(str_replace('_', ' ', $invoice->payment_method ?? 'N/A')) }}
        </td>
    </tr>
</table>

<!-- Products -->
<table class="items">

    <thead>
    <tr>
        <th width="8%">#</th>
        <th width="42%">Product</th>
        <th width="15%" class="center">Qty</th>
        <th width="17%" class="right">Unit Price</th>
        <th width="18%" class="right">Total</th>
    </tr>
    </thead>

    <tbody>

    @foreach($invoice->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>

            <td>
                <strong>
                    {{ $item->product?->name ?? 'Product unavailable' }}
                </strong><br>

                <span class="muted">
                            {{ $item->product?->product_code ?? '' }}
                        </span>
            </td>

            <td class="center">
                {{ $item->quantity }}
            </td>

            <td class="right">
                {{ number_format($item->unit_price, 2) }}
            </td>

            <td class="right">
                {{ number_format($item->total_price, 2) }}
            </td>
        </tr>
    @endforeach

    </tbody>

</table>

<!-- Invoice Totals -->
<table class="totals">

    <tr>
        <td>Subtotal</td>
        <td class="right">
            Rs. {{ number_format($invoice->sub_total, 2) }}
        </td>
    </tr>

    <tr>
        <td>Discount</td>
        <td class="right">
            Rs. {{ number_format($invoice->discount, 2) }}
        </td>
    </tr>

    <tr>
        <td>Tax</td>
        <td class="right">
            Rs. {{ number_format($invoice->tax ?? 0, 2) }}
        </td>
    </tr>

    <tr class="grand-total">
        <td>Grand Total</td>
        <td class="right">
            Rs. {{ number_format($invoice->grand_total, 2) }}
        </td>
    </tr>

</table>

<!-- Payment Summary -->
<div class="payment-summary">

    <table>
        <tr>
            <td>
                <strong>Amount Paid</strong>
            </td>

            <td class="right">
                Rs. {{ number_format($invoice->amount_paid, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Outstanding Balance</strong>
            </td>

            <td class="right">
                <strong>
                    Rs. {{ number_format($invoice->balance_amount, 2) }}
                </strong>
            </td>
        </tr>
    </table>

</div>

<!-- Payment History -->
@if($invoice->payments->count())
    <h3>Payment History</h3>

    <table class="items">
        <thead>
        <tr>
            <th>Date</th>
            <th>Method</th>
            <th>Reference</th>
            <th class="right">Amount</th>
        </tr>
        </thead>

        <tbody>
        @foreach($invoice->payments as $payment)
            <tr>
                <td>
                    {{ $payment->payment_date
                        ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y')
                        : $payment->paid_at?->format('d M Y') }}
                </td>

                <td>
                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                </td>

                <td>{{ $payment->reference ?? '-' }}</td>

                <td class="right">
                    Rs. {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div class="footer">
    <strong>Thank you for your business!</strong><br>
    Generated by Hyper Tech Solutions Inventory & POS System.
</div>

</body>
</html>
