
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock In | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-2xl p-6">
    <h1 class="mb-6 text-2xl font-bold">Receive Stock</h1>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.stock-in.store') }}"
          class="space-y-5 rounded-xl bg-white p-6 shadow-sm">
        @csrf

        <div>
            <label for="product_id" class="mb-1 block font-medium">
                Product *
            </label>
            <select name="product_id" id="product_id" required
                    class="w-full rounded-lg border-slate-300">
                <option value="">Select product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}"
                        @selected(old('product_id') == $product->id)>
                        {{ $product->product_code }} - {{ $product->name }}
                        (Current: {{ $product->stock_quantity }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="quantity" class="mb-1 block font-medium">
                Quantity Received *
            </label>
            <input type="number" id="quantity" name="quantity"
                   min="1" step="1" required value="{{ old('quantity') }}"
                   class="w-full rounded-lg border-slate-300">
        </div>

        <div>
            <label for="unit_price" class="mb-1 block font-medium">
                Cost Price per Unit
            </label>
            <input type="number" id="unit_price" name="unit_price"
                   min="0" step="0.01" value="{{ old('unit_price') }}"
                   class="w-full rounded-lg border-slate-300">
        </div>

        <div>
            <label for="note" class="mb-1 block font-medium">
                Supplier / Reference / Note
            </label>
            <textarea id="note" name="note" rows="3"
                      class="w-full rounded-lg border-slate-300">{{ old('note') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-5 py-2 text-white">
                Save Stock In
            </button>
            <a href="{{ route('inventory.index') }}"
               class="rounded-lg bg-slate-200 px-5 py-2">
                Cancel
            </a>
        </div>
    </form>
</main>
</body>
</html>
