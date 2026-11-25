
<div class="space-y-6">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Monthly Invoice Value</p>
            <p class="mt-3 text-2xl font-bold text-indigo-600">
                Rs. {{ number_format($monthlyInvoiceValue, 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Received Against This Month's Invoices</p>
            <p class="mt-3 text-2xl font-bold text-emerald-600">
                Rs. {{ number_format($monthlyReceived, 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Received</p>
            <p class="mt-3 text-2xl font-bold text-blue-600">
                Rs. {{ number_format($totalReceived, 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Outstanding Balance</p>
            <p class="mt-3 text-2xl font-bold text-amber-600">
                Rs. {{ number_format($totalOutstanding, 2) }}
            </p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Invoice Value</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">
                Rs. {{ number_format($totalInvoiceValue, 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm text-amber-700">Partial Invoices</p>
            <p class="mt-2 text-2xl font-bold text-amber-800">{{ $partialInvoices }}</p>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm text-red-700">Overdue Invoices</p>
            <p class="mt-2 text-2xl font-bold text-red-800">{{ $overdueInvoices }}</p>
        </div>

    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="font-bold text-slate-900">Outstanding Invoices</h2>
                <p class="mt-1 text-xs text-slate-400">Invoices with remaining balances</p>
            </div>

            <a href="{{ route('invoices.index', ['status' => 'partial']) }}"
               class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                View Invoices
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Invoice</th>
                    <th class="px-5 py-3 text-left">Customer</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-right">Invoice Total</th>
                    <th class="px-5 py-3 text-right">Balance</th>
                    <th class="px-5 py-3 text-center">Action</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($unpaidInvoices as $invoice)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-indigo-600">
                            {{ $invoice->invoice_no }}
                        </td>

                        <td class="px-5 py-4 text-slate-700">
                            {{ $invoice->customer?->name ?? 'Walk-in Customer' }}
                        </td>

                        <td class="px-5 py-4 whitespace-nowrap text-slate-500">
                            {{ $invoice->invoice_date?->format('d M Y') }}
                        </td>

                        <td class="px-5 py-4 text-right">
                            Rs. {{ number_format($invoice->grand_total, 2) }}
                        </td>

                        <td class="px-5 py-4 text-right font-bold text-amber-600">
                            Rs. {{ number_format($invoice->balance_amount, 2) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            No outstanding invoices found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
