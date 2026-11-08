
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Products | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-7xl p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Products</h1>
            <p class="text-sm text-slate-500">
                Manage product details, prices, and stock levels.
            </p>
        </div>

        @can('products.create')
        <a href="{{ route('products.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">
            + Add Product
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <form method="GET" action="{{ route('products.index') }}"
          class="mb-5 flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm">
        <input name="search" value="{{ request('search') }}"
               placeholder="Search product name or code..."
               class="min-w-60 flex-1 rounded-lg border-slate-300">

        <select name="sort" class="rounded-lg border-slate-300">
            @foreach ([
            'name' => 'Name',
            'product_code' => 'Product Code',
            'cost_price' => 'Cost Price',
            'selling_price' => 'Selling Price',
            'stock_quantity' => 'Stock Quantity',
            'created_at' => 'Date Added',
            ] as $key => $label)
            <option value="{{ $key }}" @selected($sort === $key)>
                Sort by {{ $label }}
            </option>
            @endforeach
        </select>

        <select name="direction" class="rounded-lg border-slate-300">
            <option value="asc" @selected($direction === 'asc')>Ascending</option>
            <option value="desc" @selected($direction === 'desc')>Descending</option>
        </select>

        <button class="rounded-lg bg-slate-800 px-4 py-2 text-white">
            Search
        </button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-4">Code</th>
                <th class="p-4">Product</th>
                <th class="p-4">Category</th>
                <th class="p-4">Cost</th>
                <th class="p-4">Selling Price</th>
                <th class="p-4">Stock</th>
                <th class="p-4">Status</th>
                <th class="p-4">Action</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($products as $product)
            <tr>
                <td class="p-4">{{ $product->product_code }}</td>
                <td class="p-4 font-medium">{{ $product->name }}</td>
                <td class="p-4">{{ $product->category?->name ?? '—' }}</td>
                <td class="p-4">
                    {{ number_format((float) $product->cost_price, 2) }}
                </td>
                <td class="p-4">
                    {{ number_format((float) $product->selling_price, 2) }}
                </td>
                <td class="p-4">
                        <span class="{{ $product->isLowStock() ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                    @if ($product->isLowStock())
                    <span class="block text-xs text-red-600">Low stock</span>
                    @endif
                </td>
                <td class="p-4">
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </td>
                <td class="p-4">
                    @can('products.edit')
                    <a href="{{ route('products.edit', $product) }}"
                       class="font-medium text-blue-600 hover:underline">
                        Edit
                    </a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="p-8 text-center text-slate-500">
                    No products found.
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
