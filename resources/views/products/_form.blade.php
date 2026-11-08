
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="product_code" class="mb-1 block font-medium">
            Product Code *
        </label>
        <input id="product_code" name="product_code"
               value="{{ old('product_code', $product?->product_code) }}"
               required maxlength="100"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="name" class="mb-1 block font-medium">
            Product Name *
        </label>
        <input id="name" name="name"
               value="{{ old('name', $product?->name) }}"
               required maxlength="255"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="category_id" class="mb-1 block font-medium">
            Category
        </label>
        <div class="flex gap-2">
            <select id="category_id" name="category_id"
                    class="min-w-0 flex-1 rounded-lg border-slate-300">
                <option value="">No category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @can('categories.manage')
                <button type="button" id="add-category"
                        class="rounded-lg bg-slate-200 px-3">
                    + Add
                </button>
            @endcan
        </div>
    </div>

    <div>
        <label for="supplier_id" class="mb-1 block font-medium">
            Supplier
        </label>
        <div class="flex gap-2">
            <select id="supplier_id" name="supplier_id"
                    class="min-w-0 flex-1 rounded-lg border-slate-300">
                <option value="">No supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                        @selected((string) old('supplier_id', $product?->supplier_id) === (string) $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>

            @can('suppliers.manage')
                <button type="button" id="add-supplier"
                        class="rounded-lg bg-slate-200 px-3">
                    + Add
                </button>
            @endcan
        </div>
    </div>

    <div>
        <label for="unit" class="mb-1 block font-medium">Unit *</label>
        <input id="unit" name="unit"
               value="{{ old('unit', $product?->unit ?? 'pcs') }}"
               required maxlength="50"
               placeholder="pcs, box, kg, litre"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="cost_price" class="mb-1 block font-medium">
            Cost Price *
        </label>
        <input id="cost_price" name="cost_price" type="number"
               min="0" step="0.01" required
               value="{{ old('cost_price', $product?->cost_price ?? '0.00') }}"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="selling_price" class="mb-1 block font-medium">
            Selling Price *
        </label>
        <input id="selling_price" name="selling_price" type="number"
               min="0" step="0.01" required
               value="{{ old('selling_price', $product?->selling_price ?? '0.00') }}"
               class="w-full rounded-lg border-slate-300">
    </div>

    @if (! $product)
        <div>
            <label for="stock_quantity" class="mb-1 block font-medium">
                Opening Stock *
            </label>
            <input id="stock_quantity" name="stock_quantity" type="number"
                   min="0" step="1" required
                   value="{{ old('stock_quantity', 0) }}"
                   class="w-full rounded-lg border-slate-300">
        </div>
    @else
        <div>
            <label class="mb-1 block font-medium">Current Stock</label>
            <p class="rounded-lg bg-slate-50 p-3">
                {{ $product->stock_quantity }} {{ $product->unit }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Use stock receipt or adjustment to change quantity.
            </p>
        </div>
    @endif

    <div>
        <label for="min_stock_quantity" class="mb-1 block font-medium">
            Minimum Stock Alert *
        </label>
        <input id="min_stock_quantity" name="min_stock_quantity"
               type="number" min="0" step="1" required
               value="{{ old('min_stock_quantity', $product?->min_stock_quantity ?? 5) }}"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" value="1"
               @checked((bool) old('is_active', $product?->is_active ?? true))
               class="rounded border-slate-300">
        <label for="is_active">Active product</label>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-1 block font-medium">
            Description
        </label>
        <textarea id="description" name="description" rows="3"
                  class="w-full rounded-lg border-slate-300">{{ old('description', $product?->description) }}</textarea>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white">
        {{ $product ? 'Save Changes' : 'Create Product' }}
    </button>
    <a href="{{ route('products.index') }}"
       class="rounded-lg bg-slate-200 px-5 py-2">
        Cancel
    </a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        async function quickCreate(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = result.message
                    || Object.values(result.errors || {}).flat().join('\n')
                    || 'Could not create the record.';
                throw new Error(message);
            }

            return result;
        }

        const categoryButton = document.getElementById('add-category');

        if (categoryButton) {
            categoryButton.addEventListener('click', async () => {
                const name = prompt('Enter category name:');
                if (!name || !name.trim()) return;

                const code = prompt('Enter unique category code:');
                if (!code || !code.trim()) return;

                try {
                    const category = await quickCreate(
                        @json(route('categories.quick-create')),
                        { name: name.trim(), code: code.trim() }
                    );

                    const option = new Option(category.name, category.id, true, true);
                    document.getElementById('category_id').add(option);
                } catch (error) {
                    alert(error.message);
                }
            });
        }

        const supplierButton = document.getElementById('add-supplier');

        if (supplierButton) {
            supplierButton.addEventListener('click', async () => {
                const name = prompt('Enter supplier name:');
                if (!name || !name.trim()) return;

                const phone = prompt('Enter supplier phone (optional):') || '';

                try {
                    const supplier = await quickCreate(
                        @json(route('suppliers.quick-create')),
                        { name: name.trim(), phone: phone.trim() }
                    );

                    const option = new Option(supplier.name, supplier.id, true, true);
                    document.getElementById('supplier_id').add(option);
                } catch (error) {
                    alert(error.message);
                }
            });
        }
    });
</script>
