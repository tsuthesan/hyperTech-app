
<div class="space-y-6">

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('products.index') }}"
           class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700">
            Manage Products
        </a>

        <a href="{{ route('products.create') }}"
           class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            + Add Product
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Active Products</p>
            <p class="mt-3 text-2xl font-bold text-indigo-600">{{ number_format($totalProducts) }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Stock Units</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($totalStockUnits) }}</p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm text-amber-700">Low Stock Products</p>
            <p class="mt-3 text-2xl font-bold text-amber-800">{{ number_format($lowStockCount) }}</p>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm text-red-700">Out of Stock</p>
            <p class="mt-3 text-2xl font-bold text-red-800">{{ number_format($outOfStockCount) }}</p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm text-emerald-700">Stock Received Today</p>
            <p class="mt-2 text-2xl font-bold text-emerald-800">
                {{ number_format($stockInToday) }}
            </p>
        </div>

        <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5">
            <p class="text-sm text-orange-700">Stock Out Today</p>
            <p class="mt-2 text-2xl font-bold text-orange-800">
                {{ number_format($stockOutToday) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Suppliers</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">
                {{ number_format($totalSuppliers) }}
            </p>
        </div>

    </div>

    {{-- Low Stock Products --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="font-bold text-slate-900">Low Stock Alerts</h2>
                <p class="mt-1 text-xs text-slate-400">Products below their minimum stock level</p>
            </div>
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                {{ $lowStockCount }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Product Code</th>
                    <th class="px-5 py-3 text-right">Available Stock</th>
                    <th class="px-5 py-3 text-right">Minimum Stock</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($lowStockProducts as $product)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $product->name }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $product->product_code }}</td>
                        <td class="px-5 py-4 text-right font-bold text-red-600">
                            {{ $product->stock_quantity }}
                        </td>
                        <td class="px-5 py-4 text-right text-slate-600">
                            {{ $product->min_stock_quantity }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            No low-stock products.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Stock Transactions --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h2 class="font-bold text-slate-900">Recent Stock Transactions</h2>
            <p class="mt-1 text-xs text-slate-400">Latest inventory movements</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Transaction</th>
                    <th class="px-5 py-3 text-right">Quantity</th>
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($recentStockTransactions as $transaction)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-800">{{ $transaction->product_name }}</p>
                            <p class="text-xs text-slate-400">{{ $transaction->product_code }}</p>
                        </td>

                        <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">
                                    {{ str_replace('_', ' ', $transaction->type) }}
                                </span>
                        </td>

                        <td class="px-5 py-4 text-right font-bold text-slate-800">
                            {{ number_format(abs($transaction->quantity)) }}
                        </td>

                        <td class="px-5 py-4 text-slate-600">
                            {{ $transaction->user_name ?? 'System' }}
                        </td>

                        <td class="px-5 py-4 whitespace-nowrap text-slate-500">
                            {{ $transaction->created_at
                                ? \Carbon\Carbon::parse($transaction->created_at)->format('d M Y H:i')
                                : 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">
                            No stock transactions found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
