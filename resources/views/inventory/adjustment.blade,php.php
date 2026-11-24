
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Adjustment | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-2xl p-6">
    <h1 class="mb-6 text-2xl font-bold">Stock Adjustment</h1>

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

    <form method="POST" action="{{ route('inventory.adjustment.store') }}"
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
                        data-stock="{{ $product->stock_quantity }}"
                        @selected(old('product_id') == $product->id)>
                {{ $product->product_code }} - {{ $product->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="rounded-lg bg-slate-50 p-3 text-sm">
            Current Stock:
            <strong id="current-stock">Select a product</strong>
        </div>

        <div>
            <label for="new_quantity" class="mb-1 block font-medium">
                Actual Physical Quantity *
            </label>
            <input type="number" name="new_quantity" id="new_quantity"
                   min="0" step="1" required
                   value="{{ old('new_quantity') }}"
                   class="w-full rounded-lg border-slate-300">
        </div>

        <div>
            <label for="note" class="mb-1 block font-medium">
                Reason for Adjustment *
            </label>
            <textarea name="note" id="note" rows="3" required
                      placeholder="Physical count, damaged goods, stock correction..."
                      class="w-full rounded-lg border-slate-300">{{ old('note') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button class="rounded-lg bg-amber-600 px-5 py-2 text-white">
                Save Adjustment
            </button>
            <a href="{{ route('inventory.index') }}"
               class="rounded-lg bg-slate-200 px-5 py-2">
                Cancel
            </a>
        </div>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('product_id');
        const display = document.getElementById('current-stock');

        function updateStock() {
            const option = select.selectedOptions[0];

            display.textContent = option && option.value
                ? option.dataset.stock
                : 'Select a product';
        }

        select.addEventListener('change', updateStock);
        updateStock();
    });
</script>
</body>
</html>
