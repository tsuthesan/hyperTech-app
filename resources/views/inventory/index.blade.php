
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-7xl p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            Inventory Management
        </h1>
        <p class="text-sm text-slate-500">
            Monitor stock levels and inventory movements.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Active Products</p>
            <p class="mt-2 text-3xl font-bold">{{ $totalProducts }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Low Stock Products</p>
            <p class="mt-2 text-3xl font-bold text-red-600">
                {{ $lowStockCount }}
            </p>
            <a href="{{ route('inventory.low-stock') }}"
               class="mt-3 inline-block text-sm text-blue-600">
                View low stock →
            </a>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Inventory Cost Value</p>
            <p class="mt-2 text-3xl font-bold">
                Rs. {{ number_format((float) $totalStockValue, 2) }}
            </p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        @can('stock.receive')
            <a href="{{ route('inventory.stock-in') }}"
               class="rounded-lg bg-blue-600 px-4 py-2 text-white">
                + Stock In
            </a>
        @endcan

        @can('stock.adjust')
            <a href="{{ route('inventory.adjustment') }}"
               class="rounded-lg bg-amber-600 px-4 py-2 text-white">
                Stock Adjustment
            </a>
        @endcan

        @can('stock.view')
            <a href="{{ route('inventory.transactions') }}"
               class="rounded-lg bg-slate-800 px-4 py-2 text-white">
                Stock History
            </a>
        @endcan
    </div>

    <div class="mt-8 overflow-x-auto rounded-xl bg-white shadow-sm">
        <div class="border-b p-4 font-semibold">Recent Stock Movements</div>

        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
            <tr>
                <th class="p-4">Date</th>
                <th class="p-4">Product</th>
                <th class="p-4">Type</th>
                <th class="p-4">Quantity</th>
                <th class="p-4">User</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($recentTransactions as $transaction)
                <tr>
                    <td class="p-4">
                        {{ $transaction->created_at?->format('Y-m-d H:i') }}
                    </td>
                    <td class="p-4">
                        {{ $transaction->product?->name ?? 'Product unavailable' }}
                    </td>
                    <td class="p-4">
                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                    </td>
                    <td class="p-4 font-medium">
                        {{ $transaction->quantity }}
                    </td>
                    <td class="p-4">
                        {{ $transaction->creator?->name ?? 'System' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-slate-500">
                        No stock movements recorded yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
