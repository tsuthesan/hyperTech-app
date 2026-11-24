
@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">

        @php
            $grandTotal = (float) $invoice->grand_total;
            $amountPaid = (float) $invoice->amount_paid;
            $balance = (float) $invoice->balance_amount;

            $statusStyles = [
                'paid' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'partial' => 'bg-amber-100 text-amber-700 ring-amber-200',
                'overdue' => 'bg-red-100 text-red-700 ring-red-200',
                'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
                'sent' => 'bg-blue-100 text-blue-700 ring-blue-200',
            ];

            $statusClass = $statusStyles[$invoice->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
        @endphp

        {{-- Header --}}
        <div class="mx-auto max-w-7xl">

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">

                <div>
                    <a href="{{ route('invoices.index') }}"
                       class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Invoices
                    </a>

                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Invoice Details
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Invoice {{ $invoice->invoice_no }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            {{-- Success / Error Messages --}}
            @if(session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 print:hidden">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 print:hidden">
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Invoice Summary --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Grand Total</p>
                        <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 2v8m0 0v2m0-2c-1.657 0-3-.895-3-2"/>
                        </svg>
                    </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold text-slate-900">
                        Rs. {{ number_format($grandTotal, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Invoice value</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Amount Paid</p>
                        <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold text-emerald-600">
                        Rs. {{ number_format($amountPaid, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Total payments received</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Outstanding</p>
                        <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M5.07 19h13.86a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16a2 2 0 001.73 3z"/>
                        </svg>
                    </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                        Rs. {{ number_format($balance, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Remaining payment</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Invoice Status</p>
                        <span class="rounded-lg bg-slate-100 p-2 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    </div>
                    <div class="mt-4">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold capitalize ring-1 {{ $statusClass }}">
                        {{ $invoice->status }}
                    </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Current invoice status</p>
                </div>

            </div>

            {{-- Invoice and Customer Information --}}
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">Invoice Information</h2>
                            <p class="text-xs text-slate-400">Invoice reference details</p>
                        </div>
                    </div>

                    <dl class="space-y-4 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Invoice Number</dt>
                            <dd class="font-semibold text-slate-900">{{ $invoice->invoice_no }}</dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Invoice Date</dt>
                            <dd class="font-medium text-slate-900">
                                {{ $invoice->invoice_date?->format('d M Y') }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Payment Method</dt>
                            <dd class="font-medium capitalize text-slate-900">
                                {{ str_replace('_', ' ', $invoice->payment_method ?? 'Not specified') }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Created By</dt>
                            <dd class="font-medium text-slate-900">
                                {{ $invoice->creator?->name ?? 'N/A' }}
                            </dd>
                        </div>

                        @if($invoice->quotation_id)
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Quotation ID</dt>
                                <dd class="font-medium text-slate-900">
                                    #{{ $invoice->quotation_id }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5V4H2v16h5m10 0v-5a3 3 0 00-3-3H10a3 3 0 00-3 3v5m10 0H7m5-10a3 3 0 100-6 3 3 0 000 6z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">Customer Information</h2>
                            <p class="text-xs text-slate-400">Billing customer details</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $invoice->customer?->name ?? 'Walk-in Customer' }}
                    </h3>

                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start gap-3 text-slate-600">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 011.96 0l.72 3.2a2 2 0 01-.57 1.95l-1.3 1.3a16 16 0 006.46 6.46l1.3-1.3a2 2 0 011.95-.57l3.2.72a2 2 0 010 1.96V19a2 2 0 01-2 2h-1C10.16 21 3 13.84 3 5z"/>
                            </svg>
                            <span>{{ $invoice->customer?->phone ?? 'No phone number' }}</span>
                        </div>

                        <div class="flex items-start gap-3 text-slate-600">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $invoice->customer?->email ?? 'No email address' }}</span>
                        </div>

                        <div class="flex items-start gap-3 text-slate-600">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $invoice->customer?->address ?? 'No address available' }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Products --}}
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="font-bold text-slate-900">Invoice Items</h2>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $invoice->items->count() }} product(s)
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Product</th>
                            <th class="px-6 py-4 text-center font-semibold">Quantity</th>
                            <th class="px-6 py-4 text-right font-semibold">Unit Price</th>
                            <th class="px-6 py-4 text-right font-semibold">Total</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                        @forelse($invoice->items as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $item->product?->name ?? 'Product unavailable' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $item->product?->product_code ?? '' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-center font-medium text-slate-700">
                                    {{ number_format((float) $item->quantity, 2) }}
                                </td>

                                <td class="px-6 py-4 text-right text-slate-600">
                                    Rs. {{ number_format((float) $item->unit_price, 2) }}
                                </td>

                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    Rs. {{ number_format((float) $item->total_price, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                    No invoice items found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-6">
                    <div class="ml-auto max-w-sm space-y-3 text-sm">

                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-slate-800">
                            Rs. {{ number_format((float) $invoice->sub_total, 2) }}
                        </span>
                        </div>

                        <div class="flex justify-between text-slate-500">
                            <span>Discount</span>
                            <span class="font-medium text-slate-800">
                            - Rs. {{ number_format((float) $invoice->discount, 2) }}
                        </span>
                        </div>

                        <div class="flex justify-between text-slate-500">
                            <span>Tax</span>
                            <span class="font-medium text-slate-800">
                            Rs. {{ number_format((float) ($invoice->tax ?? 0), 2) }}
                        </span>
                        </div>

                        <div class="flex justify-between border-t border-slate-200 pt-4 text-base">
                            <span class="font-bold text-slate-900">Grand Total</span>
                            <span class="font-bold text-indigo-600">
                            Rs. {{ number_format($grandTotal, 2) }}
                        </span>
                        </div>

                        <div class="flex justify-between text-emerald-600">
                            <span>Amount Paid</span>
                            <span class="font-semibold">
                            Rs. {{ number_format($amountPaid, 2) }}
                        </span>
                        </div>

                        <div class="flex justify-between text-base">
                            <span class="font-bold text-slate-900">Balance Due</span>
                            <span class="font-bold {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            Rs. {{ number_format($balance, 2) }}
                        </span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900">Payment History</h2>
                        <p class="mt-1 text-xs text-slate-400">All payments recorded for this invoice</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $invoice->payments->count() }} payment(s)
                </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Receipt No.</th>
                            <th class="px-6 py-4 text-left font-semibold">Date</th>
                            <th class="px-6 py-4 text-left font-semibold">Method</th>
                            <th class="px-6 py-4 text-left font-semibold">Received By</th>
                            <th class="px-6 py-4 text-right font-semibold">Amount</th>
                            <th class="px-6 py-4 text-right font-semibold">Receipt</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                        @forelse($invoice->payments as $payment)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $payment->receipt_no ?? 'REC-' . $payment->id }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $payment->payment_date
                                        ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y')
                                        : ($payment->paid_at?->format('d M Y') ?? 'N/A') }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold capitalize text-blue-700">
                                        {{ str_replace('_', ' ', $payment->payment_method) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $payment->receiver?->name ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                    Rs. {{ number_format((float) $payment->amount, 2) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('payments.receipt', $payment) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">
                                        View
                                    </a>

                                    <a href="{{ route('payments.receipt.pdf', $payment) }}"
                                       class="ml-1 inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-medium text-slate-700">No payments recorded</p>
                                    <p class="mt-1 text-xs text-slate-400">Payments will appear here once recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Record Payment --}}
            @if($balance > 0)
                <div class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm print:hidden">

                    <div class="border-b border-indigo-100 bg-indigo-50/60 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-indigo-100 p-2.5 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 2v8m0 0v2m0-2c-1.657 0-3-.895-3-2"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-bold text-slate-900">Record New Payment</h2>
                                <p class="text-xs text-slate-500">
                                    Outstanding balance: Rs. {{ number_format($balance, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('invoices.payments.store', $invoice) }}"
                          method="POST"
                          class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">

                            <div>
                                <label for="amount" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Payment Amount (Rs.) <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="amount"
                                       id="amount"
                                       min="0.01"
                                       max="{{ number_format($balance, 2, '.', '') }}"
                                       step="0.01"
                                       value="{{ old('amount') }}"
                                       required
                                       placeholder="Enter payment amount"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                @error('amount')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="payment_method" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method"
                                        id="payment_method"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                    <option value="">Select payment method</option>
                                    <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                    <option value="credit" @selected(old('payment_method') === 'credit')>Credit</option>
                                </select>
                                @error('payment_method')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="payment_date" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Payment Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       name="payment_date"
                                       id="payment_date"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                       required
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                @error('payment_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="reference" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Transaction Reference
                                </label>
                                <input type="text"
                                       name="reference"
                                       id="reference"
                                       value="{{ old('reference') }}"
                                       placeholder="Bank transaction ID or reference number"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            </div>

                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Notes
                                </label>
                                <textarea name="notes"
                                          id="notes"
                                          rows="3"
                                          placeholder="Optional payment notes..."
                                          class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">{{ old('notes') }}</textarea>
                            </div>

                        </div>

                        <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-500">
                                The invoice balance and status will be updated automatically.
                            </p>

                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 print:hidden">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-emerald-100 p-2 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-emerald-800">Invoice Fully Paid</h3>
                            <p class="mt-1 text-sm text-emerald-700">
                                This invoice has no outstanding balance.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <style>
        @media print {
            body {
                background: white !important;
            }

            .min-h-screen {
                min-height: auto !important;
                background: white !important;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
@endsection
