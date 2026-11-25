<div class="space-y-6">

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('invoices.create') }}"
           class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700">
            + Create New Invoice
        </a>

        <a href="{{ route('invoices.index') }}"
           class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            View Invoices
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach([
            ['Today Sales', $todaySales, 'indigo'],
            ['Received Today', $todayReceived, 'emerald'],
            ['Outstanding Today', $todayOutstanding, 'amber'],
        ] as [$label, $value, $color])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-2xl font-bold text-{{ $color }}-600">
                    Rs. {{ number_format($value, 2) }}
                </p>
            </div>
        @endforeach

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Invoices Today</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">
                {{ number_format($todayInvoiceCount) }}
            </p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">New Customers Today</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $todayCustomers }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Your Recent Sales</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $recentInvoices->count() }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <h2 class="font-bold text-slate-900">Your Recent Invoices</h2>
            <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-indigo-600">
                View All
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Invoice</th>
                    <th class="px-5 py-3 text-left">Customer</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3 text-center">Status</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($recentInvoices as $invoice)
                    <tr>
                        <td class="px-5 py-4">
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-indigo-600">
                                {{ $invoice->invoice_no }}
                            </a>
                        </td>
                        <td class="px-5 py-4">{{ $invoice->customer?->name ?? 'Walk-in Customer' }}</td>
                        <td class="px-5 py-4">{{ $invoice->invoice_date?->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-right font-semibold">
                            Rs. {{ number_format($invoice->grand_total, 2) }}
                        </td>
                        <td class="px-5 py-4 text-center capitalize">{{ $invoice->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-400">No invoices created by you yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
