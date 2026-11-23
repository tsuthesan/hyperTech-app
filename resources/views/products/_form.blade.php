@php
    $product = $product ?? null;
@endphp

<div
    x-data="productQuickAdd(
        @js($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
        @js($suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()),
        @js(old('category_id', $product?->category_id)),
        @js(old('supplier_id', $product?->supplier_id))
    )"
>
    {{-- VALIDATION ERRORS --}}
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

        {{-- PRODUCT CODE --}}
        <div>
            <label class="mb-1 block font-medium">
                Product Code
            </label>

            @if ($product)
                <input
                    type="text"
                    value="{{ $product->product_code }}"
                    readonly
                    class="w-full rounded-lg border-slate-300 bg-slate-100"
                >
            @else
                <input
                    type="text"
                    value="Automatically generated when saved"
                    readonly
                    class="w-full rounded-lg border-slate-300 bg-slate-100 text-slate-500"
                >
            @endif

            <p class="mt-1 text-xs text-slate-500">
                Product code is generated automatically.
            </p>
        </div>

        {{-- PRODUCT NAME --}}
        <div>
            <label for="name" class="mb-1 block font-medium">
                Product Name *
            </label>

            <input
                id="name"
                name="name"
                value="{{ old('name', $product?->name) }}"
                required
                maxlength="255"
                placeholder="Enter product name"
                class="w-full rounded-lg border-slate-300"
            >

            @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- CATEGORY --}}
        <div>
            <label for="category_id" class="mb-1 block font-medium">
                Category
            </label>

            <div class="flex gap-2">
                <select
                    id="category_id"
                    name="category_id"
                    x-model="selectedCategory"
                    class="min-w-0 flex-1 rounded-lg border-slate-300"
                >
                    <option value="">No category</option>

                    <template x-for="category in categories" :key="category.id">
                        <option
                            :value="String(category.id)"
                            x-text="category.name"
                        ></option>
                    </template>
                </select>

                @can('categories.manage')
                    <button
                        type="button"
                        @click="openCategoryModal()"
                        class="rounded-lg bg-slate-200 px-3 hover:bg-slate-300"
                    >
                        + Add
                    </button>
                @endcan
            </div>
            @error('category_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- SUPPLIER --}}
        <div>
            <label for="supplier_id" class="mb-1 block font-medium">
                Supplier
            </label>

            <div class="flex gap-2">
                <select
                    id="supplier_id"
                    name="supplier_id"
                    x-model="selectedSupplier"
                    class="min-w-0 flex-1 rounded-lg border-slate-300"
                >
                    <option value="">No supplier</option>

                    <template x-for="supplier in suppliers" :key="supplier.id">
                        <option
                            :value="String(supplier.id)"
                            x-text="supplier.name"
                        ></option>
                    </template>
                </select>

                @can('suppliers.manage')
                    <button
                        type="button"
                        @click="openSupplierModal()"
                        class="rounded-lg bg-slate-200 px-3 hover:bg-slate-300"
                    >
                        + Add
                    </button>
                @endcan
            </div>

            @error('supplier_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- UNIT --}}
        <div>
            <label for="unit" class="mb-1 block font-medium">
                Unit *
            </label>

            <input
                id="unit"
                name="unit"
                value="{{ old('unit', $product?->unit ?? 'pcs') }}"
                required
                maxlength="50"
                placeholder="pcs, box, kg, litre"
                class="w-full rounded-lg border-slate-300"
            >

            @error('unit')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- COST PRICE --}}
        <div>
            <label for="cost_price" class="mb-1 block font-medium">
                Cost Price *
            </label>

            <input
                id="cost_price"
                name="cost_price"
                type="number"
                min="0"
                step="0.01"
                required
                value="{{ old('cost_price', $product?->cost_price ?? '0.00') }}"
                class="w-full rounded-lg border-slate-300"
            >

            @error('cost_price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- SELLING PRICE --}}
        <div>
            <label for="selling_price" class="mb-1 block font-medium">
                Selling Price *
            </label>

            <input
                id="selling_price"
                name="selling_price"
                type="number"
                min="0"
                step="0.01"
                required
                value="{{ old('selling_price', $product?->selling_price ?? '0.00') }}"
                class="w-full rounded-lg border-slate-300"
            >

            @error('selling_price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- OPENING STOCK --}}
        @if (!$product)
            <div>
                <label for="stock_quantity" class="mb-1 block font-medium">
                    Opening Stock *
                </label>

                <input
                    id="stock_quantity"
                    name="stock_quantity"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="{{ old('stock_quantity', 0) }}"
                    class="w-full rounded-lg border-slate-300"
                >

                @error('stock_quantity')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div>
                <label class="mb-1 block font-medium">
                    Current Stock
                </label>

                <p class="rounded-lg bg-slate-50 p-3">
                    {{ $product->stock_quantity }} {{ $product->unit }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Use stock receipt or adjustment to change quantity.
                </p>
            </div>
        @endif

        {{-- MINIMUM STOCK --}}
        <div>
            <label for="min_stock_quantity" class="mb-1 block font-medium">
                Minimum Stock Alert *
            </label>

            <input
                id="min_stock_quantity"
                name="min_stock_quantity"
                type="number"
                min="0"
                step="1"
                required
                value="{{ old('min_stock_quantity', $product?->min_stock_quantity ?? 5) }}"
                class="w-full rounded-lg border-slate-300"
            >

            @error('min_stock_quantity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ACTIVE --}}
        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                @checked((bool) old('is_active', $product?->is_active ?? true))
                class="rounded border-slate-300"
            >

            <label for="is_active">
                Active product
            </label>
        </div>

        {{-- DESCRIPTION --}}
        <div class="md:col-span-2">
            <label for="description" class="mb-1 block font-medium">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="3"
                class="w-full rounded-lg border-slate-300"
            >{{ old('description', $product?->description) }}</textarea>

            @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- FORM BUTTONS --}}
    <div class="mt-6 flex gap-3">
        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700"
        >
            {{ $product ? 'Save Changes' : 'Create Product' }}
        </button>

        <a
            href="{{ route('products.index') }}"
            class="rounded-lg bg-slate-200 px-5 py-2 hover:bg-slate-300"
        >
            Cancel
        </a>
    </div>

    {{-- CATEGORY MODAL --}}
    @can('categories.manage')
        <div
            x-show="showCategoryModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div
                @click.outside="closeCategoryModal()"
                @keydown.escape.window="closeCategoryModal()"
                @keydown.enter.prevent="saveCategory()"
                class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
            >
                <h3 class="mb-5 text-lg font-bold">
                    Add New Category
                </h3>

                <div>

                    <div class="mb-4">
                        <label class="mb-1 block font-medium">
                            Category Name *
                        </label>

                        <input
                            type="text"
                            x-model="categoryForm.name"
                            :disabled="!showCategoryModal"
                            :required="showCategoryModal"
                            maxlength="255"
                            class="w-full rounded-lg border-slate-300"
                            placeholder="Enter category name"
                        >
                    </div>

                    <p class="mb-4 text-xs text-slate-500">
                        Category code will be generated automatically.
                    </p>

                    <div
                        x-show="categoryError"
                        x-text="categoryError"
                        class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600"
                    ></div>

                    <div class="flex justify-end gap-2">

                        <button
                            type="button"
                            @click="closeCategoryModal()"
                            :disabled="savingCategory"

                            class="rounded-lg bg-slate-200 px-4 py-2"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="saveCategory()"
                            :disabled="savingCategory"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                        >
                            <span x-text="savingCategory ? 'Saving...' : 'Save Category'"></span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- SUPPLIER MODAL --}}
    @can('suppliers.manage')
        <div
            x-show="showSupplierModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div
                @click.outside="closeSupplierModal()"
                @keydown.escape.window="closeSupplierModal()"
                @keydown.enter.prevent="saveSupplier()"
                class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
            >
                <h3 class="mb-5 text-lg font-bold">
                    Add New Supplier
                </h3>

                <div>
                    <div class="mb-3">
                        <label class="mb-1 block font-medium">
                            Supplier Name *
                        </label>

                        <input
                            type="text"
                            x-model="supplierForm.name"
                            :disabled="!showSupplierModal"
                            :required="showSupplierModal"
                            maxlength="255"
                            class="w-full rounded-lg border-slate-300"
                            placeholder="Enter supplier name"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="mb-1 block font-medium">
                            Phone
                        </label>

                        <input
                            type="text"
                            :disabled="!showSupplierModal"
                            x-model="supplierForm.phone"
                            maxlength="50"
                            class="w-full rounded-lg border-slate-300"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="mb-1 block font-medium">
                            Email
                        </label>

                        <input
                            type="email"

                            :disabled="!showSupplierModal"
                            x-model="supplierForm.email"
                            maxlength="255"
                            class="w-full rounded-lg border-slate-300"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block font-medium">
                            Address
                        </label>

                        <textarea
                            :disabled="!showSupplierModal"
                            x-model="supplierForm.address"
                            rows="2"
                            class="w-full rounded-lg border-slate-300"
                        ></textarea>
                    </div>

                    <p class="mb-4 text-xs text-slate-500">
                        Supplier code will be generated automatically.
                    </p>

                    <div
                        x-show="supplierError"
                        x-text="supplierError"
                        class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600"
                    ></div>

                    <div class="flex justify-end gap-2">

                        <button
                            type="button"
                            @click="closeSupplierModal()"
                            :disabled="savingSupplier"
                            class="rounded-lg bg-slate-200 px-4 py-2"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="saveSupplier()"
                            :disabled="savingSupplier"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                        >
                            <span x-text="savingSupplier ? 'Saving...' : 'Save Supplier'"></span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    @endcan

</div>

{{-- HIDE ALPINE CONTENT BEFORE INITIALIZATION --}}
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

{{-- ALPINE.JS COMPONENT --}}
<script>
    window.productQuickAdd = function (
        initialCategories,
        initialSuppliers,
        initialCategory,
        initialSupplier
    ) {
        return {

            categories: initialCategories,
            suppliers: initialSuppliers,

            selectedCategory: initialCategory
                ? String(initialCategory)
                : '',

            selectedSupplier: initialSupplier
                ? String(initialSupplier)
                : '',

            showCategoryModal: false,
            showSupplierModal: false,

            savingCategory: false,
            savingSupplier: false,

            categoryError: '',
            supplierError: '',

            categoryForm: {
                name: ''
            },

            supplierForm: {
                name: '',
                phone: '',
                email: '',
                address: ''
            },

            async sendRequest(url, payload) {

                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content;

                if (!csrfToken) {
                    throw new Error('CSRF token not found. Check your app layout.');
                }

                const response = await fetch(url, {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },

                    body: JSON.stringify(payload)
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {

                    const message = result.errors
                        ? Object.values(result.errors).flat().join(' ')
                        : result.message || 'Unable to create record.';

                    throw new Error(message);
                }

                return result;
            },

            // CATEGORY METHODS

            openCategoryModal() {

                this.categoryError = '';

                this.categoryForm = {
                    name: ''
                };

                this.showCategoryModal = true;
            },

            closeCategoryModal() {

                if (this.savingCategory) return;

                this.showCategoryModal = false;
            },

            async saveCategory() {

                if (this.savingCategory) return;

                this.savingCategory = true;
                this.categoryError = '';

                try {

                    const result = await this.sendRequest(
                        @json(route('categories.quick-create')),
                        this.categoryForm
                    );
                    // Add new category to dropdown.

                    this.categories.push({
                        id: result.id,
                        name: result.name
                    });

                    // Automatically select new category.
                    this.selectedCategory = String(result.id);

                    // close popup.

                    this.showCategoryModal = false;

                    this.categoryForm = {
                        name: ''
                    };

                } catch (error) {

                    this.categoryError = error.message;

                } finally {

                    this.savingCategory = false;
                }
            },

            // SUPPLIER METHODS

            openSupplierModal() {

                this.supplierError = '';

                this.supplierForm = {
                    name: '',
                    phone: '',
                    email: '',
                    address: ''
                };

                this.showSupplierModal = true;
            },

            closeSupplierModal() {

                if (this.savingSupplier) return;

                this.showSupplierModal = false;
            },

            async saveSupplier() {

                if (this.savingSupplier) return;

                this.savingSupplier = true;
                this.supplierError = '';

                try {

                    const result = await this.sendRequest(
                        @json(route('suppliers.quick-create')),
                        this.supplierForm
                    );

                    this.suppliers.push({
                        id: result.id,
                        name: result.name
                    });

                    // Automatically select new supplier.
                    this.selectedSupplier = String(result.id);

                    this.showSupplierModal = false;

                    this.supplierForm = {
                        name: '',
                        phone: '',
                        email: '',
                        address: ''
                    };

                } catch (error) {

                    this.supplierError = error.message;

                } finally {

                    this.savingSupplier = false;
                }
            }
        };
    };
</script>
