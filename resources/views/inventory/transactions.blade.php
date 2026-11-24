
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock History | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-7xl p-6">
    <h1 class="mb-6 text-2xl font-bold">Stock Transaction History</h1>

    <form method="GET" class="mb-5 flex flex-wrap gap-3 rounded-xl bg-white p-4">
        <input name="search" value="{{ request('search') }}"
               placeholder="Search product name or code"
               class="min-w-60 flex-1 rounded-lg border-slate-300">

        <select name="type" class="rounded-lg border-slate-300">
            <option value="">All transaction types</option>
            @foreach (['stock_in', 'stock_out', 'adjustment', 'sale', 'return'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>
                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                </option>
            @endforeach
        </select>

        <button class="rounded-lg bg-slate-800 px-4 py-2 text-white">
            Filter
        </button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
            <tr>
                <th class="p-4">Date</th>
                <th class="p-4">Product</th>
                <th class="p-4">Type</th>
                <th class="p-4">Quantity</th>
                <th class="p-4">Unit Price</th>
                <th class="p-4">Note</th>
                <th class="p-4">User</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($transactions as $transaction)
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
                    <td class="p-4 font-semibold">
                        {{ $transaction->quantity }}
                    </td>
                    <td class="p-4">
                        {{ number_format((float) $transaction->unit_price, 2) }}
                    </td>
                    <td class="p-4">{{ $transaction->note }}</td>
                    <td class="p-4">
                        {{ $transaction->creator?->name ?? 'System' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-500">
                        No transactions found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $transactions->links() }}</div>
</main>
</body>
</html>
