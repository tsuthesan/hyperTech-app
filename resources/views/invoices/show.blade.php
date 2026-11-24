@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-6">

        {{-- Success message --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error messages --}}
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Invoice heading --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">
                Invoice {{ $invoice->invoice_no }}
            </h1>

            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Download PDF
            </a>
        </div>

        {{-- Invoice details --}}
        <div class="bg-white rounded shadow p-6">

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="font-bold mb-2">Customer Details</h3>
                    <p>{{ $invoice->customer->name ?? '-' }}</p>
                    <p>{{ $invoice->customer->phone ?? '-' }}</p>
                    <p>{{ $invoice->customer->address ?? '-' }}</p>
                </div>

                <div class="text-right">
                    <p><strong>Invoice:</strong> {{ $invoice->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $invoice->invoice_date }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
                </div>
            </div>

            {{-- Products --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Product</th>
                        <th class="text-right p-2">Qty</th>
                        <th class="text-right p-2">Unit Price</th>
                        <th class="text-right p-2">Total</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($invoice->items as $item)
                        <tr class="border-b">
                            <td class="p-2">
                                {{ $item->product->name ?? 'Product unavailable' }}
                            </td>
                            <td class="p-2 text-right">{{ $item->quantity }}</td>
                            <td class="p-2 text-right">
                                Rs. {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="p-2 text-right">
                                Rs. {{ number_format($item->total_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="ml-auto mt-5 max-w-sm space-y-2">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>Rs. {{ number_format($invoice->sub_total, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Discount</span>
                    <span>Rs. {{ number_format($invoice->discount, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Tax</span>
                    <span>Rs. {{ number_format($invoice->tax, 2) }}</span>
                </div>

                <div class="flex justify-between font-bold border-t pt-2">
                    <span>Grand Total</span>
                    <span>Rs. {{ number_format($invoice->grand_total, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Amount Paid</span>
                    <span>Rs. {{ number_format($invoice->amount_paid, 2) }}</span>
                </div>

                <div class="flex justify-between font-bold text-red-600">
                    <span>Balance</span>
                    <span>Rs. {{ number_format($invoice->balance_amount, 2) }}</span>
                </div>
            </div>
        </div>
        @can('payments.view')
        {{-- Payment History --}}
        <div class="bg-white rounded shadow p-6 mt-6">
            <h2 class="text-xl font-bold mb-4">Payment History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Date</th>
                        <th class="text-left p-2">Method</th>
                        <th class="text-left p-2">Reference</th>
                        <th class="text-left p-2">Received By</th>
                        <th class="text-right p-2">Amount</th>
                        <th class="text-right p-2">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr class="border-b">
                            <td class="p-2">
                                {{ $payment->paid_at
                                    ? $payment->paid_at->format('d/m/Y H:i')
                                    : ($payment->payment_date?->format('d/m/Y') ?? '-') }}
                            </td>

                            <td class="p-2">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </td>

                            <td class="p-2">
                                {{ $payment->reference ?? '-' }}
                            </td>

                            <td class="p-2">
                                {{ $payment->receiver->name ?? '-' }}
                            </td>

                            <td class="p-2 text-right">
                                Rs. {{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="p-2 text-right">
                                <a href="{{ route('payments.receipt', $payment) }}"
                                   style="background:#2563eb;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;">
                                    View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">
                                No payment history found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endcan
        {{-- Record Payment --}}
        <div class="bg-white rounded shadow p-6 mt-6">

            <h2 class="text-xl font-bold mb-4">Record Payment</h2>
            @can('payments.record')
            @if((float) $invoice->balance_amount > 0)

                <p class="text-sm text-gray-600 mb-4">
                    Outstanding Balance:
                    <strong class="text-red-600">
                        Rs. {{ number_format($invoice->balance_amount, 2) }}
                    </strong>
                </p>

                <form method="POST"
                      action="{{ route('invoices.payments.store', $invoice) }}">

                    @csrf

                    <div class="grid md:grid-cols-2 gap-4">

                        <div>
                            <label class="block mb-1 font-medium">
                                Payment Amount (Rs.)
                            </label>

                            <input type="number"
                                   name="amount"
                                   min="0.01"
                                   max="{{ $invoice->balance_amount }}"
                                   step="0.01"
                                   value="{{ old('amount') }}"
                                   required
                                   class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Payment Method
                            </label>

                            <select name="payment_method"
                                    required
                                    class="w-full border rounded p-2">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Reference
                            </label>

                            <input type="text"
                                   name="reference"
                                   value="{{ old('reference') }}"
                                   class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Payment Date
                            </label>

                            <input type="date"
                                   name="paid_at"
                                   value="{{ old('paid_at', now()->toDateString()) }}"
                                   required
                                   class="w-full border rounded p-2">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">
                                Notes
                            </label>

                            <textarea name="notes"
                                      rows="3"
                                      class="w-full border rounded p-2">{{ old('notes') }}</textarea>
                        </div>

                    </div>

                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded mt-5 font-semibold">
                        Save Payment
                    </button>

                </form>

            @else

                <div class="bg-green-100 text-green-700 p-4 rounded">
                    This invoice has been fully paid. No outstanding balance.
                </div>

            @endif
            @else
                <div class="bg-yellow-100 text-yellow-700 p-4 rounded">
                    You do not have permission to record payments for this invoice.
                </div>
                @endcan

        </div>


    </div>
@endsection
