
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Low Stock | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-7xl p-6">
    <h1 class="mb-6 text-2xl font-bold">Low Stock Products</h1>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
            <tr>
                <th class="p-4">Product Code</th>
                <th class="p-4">Product</th>
                <th class="p-4">Category</th>
                <th class="p-4">Current Stock</th>
                <th class="p-4">Minimum Stock</th>
                <th class="p-4">Shortage</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($products as $product)
                <tr>
                    <td class="p-4">{{ $product->product_code }}</td>
                    <td class="p-4 font-medium">{{ $product->name }}</td>
                    <td class="p-4">{{ $product->category?->name ?? '—' }}</td>
                    <td class="p-4 font-semibold text-red-600">
                        {{ $product->stock_quantity }} {{ $product->unit }}
                    </td>
                    <td class="p-4">
                        {{ $product->min_stock_quantity }}
                    </td>
                    <td class="p-4">
                        {{ max(0, $product->min_stock_quantity - $product->stock_quantity) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-green-700">
                        No low-stock products found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $products->links() }}</div>
</main>
</body>
</html>
