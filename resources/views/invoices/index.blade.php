@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Invoices
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage customer invoices and payments
                    </p>
                </div>

                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center justify-center px-3 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-900">
                    + Create Invoice
                </a>
            </div>

            <!-- Messages -->
            @if(session('success'))
                <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">

                <form method="GET"
                      action="{{ route('invoices.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Search
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Invoice number or customer"
                                   class="w-full rounded-lg border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Status
                            </label>

                            <select name="status"
                                    class="w-full rounded-lg border-gray-300">

                                <option value="">All Statuses</option>
                                <option value="sent" @selected(request('status') === 'sent')>
                                    Sent
                                </option>
                                <option value="partial" @selected(request('status') === 'partial')>
                                    Partial
                                </option>
                                <option value="paid" @selected(request('status') === 'paid')>
                                    Paid
                                </option>
                                <option value="overdue" @selected(request('status') === 'overdue')>
                                    Overdue
                                </option>
                                <option value="draft" @selected(request('status') === 'draft')>
                                    Draft
                                </option>

                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                From Date
                            </label>

                            <input type="date"
                                   name="from_date"
                                   value="{{ request('from_date') }}"
                                   class="w-full rounded-lg border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                To Date
                            </label>

                            <input type="date"
                                   name="to_date"
                                   value="{{ request('to_date') }}"
                                   class="w-full rounded-lg border-gray-300">
                        </div>

                    </div>

                    <div class="flex gap-2 mt-4">

                        <button type="submit"
                                class="px-5 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                            Search
                        </button>

                        <a href="{{ route('invoices.index') }}"
                           class="px-5 py-2 border rounded-lg hover:bg-gray-50">
                            Clear
                        </a>

                    </div>

                </form>
            </div>

            <!-- Invoice Table -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-4">Invoice</th>
                            <th class="text-left p-4">Customer</th>
                            <th class="text-left p-4">Date</th>
                            <th class="text-right p-4">Total</th>
                            <th class="text-right p-4">Paid</th>
                            <th class="text-right p-4">Balance</th>
                            <th class="text-center p-4">Status</th>
                            <th class="text-center p-4">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                        @forelse($invoices as $invoice)

                            <tr class="hover:bg-gray-50">

                                <td class="p-4 font-semibold text-blue-700">
                                    {{ $invoice->invoice_no }}
                                </td>

                                <td class="p-4">
                                    <div class="font-medium text-gray-800">
                                        {{ $invoice->customer?->name ?? 'Customer unavailable' }}
                                    </div>
                                </td>

                                <td class="p-4 text-gray-600">
                                    {{ $invoice->invoice_date?->format('d M Y') }}
                                </td>

                                <td class="p-4 text-right font-medium">
                                    Rs. {{ number_format($invoice->grand_total, 2) }}
                                </td>

                                <td class="p-4 text-right text-green-700">
                                    Rs. {{ number_format($invoice->amount_paid, 2) }}
                                </td>

                                <td class="p-4 text-right text-red-600">
                                    Rs. {{ number_format($invoice->balance_amount, 2) }}
                                </td>

                                <td class="p-4 text-center">

                                    @php
                                        $statusColors = [
                                            'paid' => 'bg-green-100 text-green-700',
                                            'partial' => 'bg-yellow-100 text-yellow-700',
                                            'sent' => 'bg-blue-100 text-blue-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            'draft' => 'bg-gray-100 text-gray-700',
                                        ];

                                        $statusColor = $statusColors[$invoice->status]
                                            ?? 'bg-gray-100 text-gray-700';
                                    @endphp

                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>

                                </td>

                                <td class="p-4">
                                    <div class="flex justify-center gap-3">

                                        <a href="{{ route('invoices.show', $invoice) }}"
                                           class="text-blue-600 hover:underline">
                                            View
                                        </a>

                                        <a href="{{ route('invoices.pdf', $invoice) }}"
                                           class="text-green-600 hover:underline">
                                            PDF
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8"
                                    class="p-10 text-center text-gray-500">
                                    No invoices found.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="p-4 border-t">
                    {{ $invoices->links() }}
                </div>

            </div>

        </div>
    </div>

@endsection
