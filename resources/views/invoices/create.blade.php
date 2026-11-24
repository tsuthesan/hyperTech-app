@extends('layouts.app')

@section('content')
    @php
        $invoiceProducts = $products->map(fn ($product) => [
            'id' => $product->id,
            'code' => $product->product_code,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'stock' => (int) $product->stock_quantity,
            'unit' => $product->unit,
        ])->values();

        $invoiceCustomers = $customers->map(fn ($customer) => [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ])->values();
    @endphp

    <div
        x-data="invoiceForm(
        @js($invoiceProducts),
        @js($invoiceCustomers),
        @js(old('customer_id', ''))
    )"
        class="min-h-screen bg-slate-50 py-6 sm:py-8"
    >
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 mb-8">

                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 12h6m-6 4h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">
                            Hyper Tech POS
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                            Create Invoice
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">
                            Create a new customer invoice and manage products.
                        </p>
                    </div>
                </div>

                <a href="{{ route('invoices.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7 7-7M3 12h18"/>
                    </svg>
                    Back to Invoices
                </a>
            </div>

            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                    <div class="flex gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">!</span>
                        <div>
                            <h3 class="font-bold text-red-800">Please correct the following errors</h3>
                            <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- SUCCESS --}}
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('invoices.store') }}"
                  method="POST"
                  @submit="return validateInvoice($event)">
                @csrf

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

                    {{-- LEFT CONTENT --}}
                    <div class="xl:col-span-8 space-y-6">

                        {{-- CUSTOMER AND INVOICE DETAILS --}}
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-slate-900">Invoice Details</h2>
                                    <p class="text-xs text-slate-500">Customer, date and payment information</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                                {{-- CUSTOMER --}}
                                <div class="md:col-span-2">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <label class="text-sm font-semibold text-slate-700">
                                            Customer <span class="text-red-500">*</span>
                                        </label>

                                        <button type="button"
                                                @click="openCustomerModal()"
                                                class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-50">+</span>
                                            Add Customer
                                        </button>
                                    </div>

                                    <select name="customer_id"
                                            x-model="selectedCustomer"
                                            required
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                        <option value="">Select a customer</option>

                                        <template x-for="customer in customers" :key="customer.id">
                                            <option :value="String(customer.id)"
                                                    x-text="customer.name + (customer.phone ? ' - ' + customer.phone : '')">
                                            </option>
                                        </template>
                                    </select>

                                    @error('customer_id')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                    <p class="mt-2 text-xs text-slate-400">
                                        Select an existing customer or create a new customer.
                                    </p>
                                </div>

                                {{-- DATE --}}
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                                        Invoice Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                           name="invoice_date"
                                           value="{{ old('invoice_date', now()->toDateString()) }}"
                                           required
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                </div>

                                {{-- PAYMENT METHOD --}}
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                                        Payment Method <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payment_method"
                                            required
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                        <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                        <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                        <option value="credit" @selected(old('payment_method') === 'credit')>Credit</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        {{-- PRODUCT SELECTION --}}
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                            <div class="border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M3 3h2l2 12h10l3-8H6m3 12a1 1 0 100 2 1 1 0 000-2zm9 0a1 1 0 100 2 1 1 0 000-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-slate-900">Select Products</h2>
                                        <p class="text-xs text-slate-500">Search by product name or barcode/product code</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">

                                {{-- SEARCH --}}
                                <div class="relative mb-5">
                                    <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                                        <path d="m16 16 4 4" stroke-width="2" stroke-linecap="round"/>
                                    </svg>

                                    <input type="text"
                                           x-model="search"
                                           placeholder="Search product name or product code..."
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                </div>

                                {{-- PRODUCT RESULTS --}}
                                <div class="grid max-h-[430px] grid-cols-1 gap-3 overflow-y-auto pr-1 sm:grid-cols-2">

                                    <template x-for="product in filteredProducts()" :key="product.id">
                                        <button type="button"
                                                @click="addProduct(product)"
                                                class="group rounded-xl border border-slate-200 bg-white p-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50/40 hover:shadow-md">

                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <h3 class="truncate text-sm font-bold text-slate-800"
                                                        x-text="product.name"></h3>
                                                    <p class="mt-1 text-xs text-slate-400"
                                                       x-text="product.code"></p>
                                                </div>

                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-lg font-bold text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                                +
                                            </span>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between gap-2">
                                            <span class="text-sm font-extrabold text-indigo-700"
                                                  x-text="money(product.price)"></span>

                                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                                      :class="product.stock > 5 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                                                Stock:
                                                <span x-text="product.stock"></span>
                                            </span>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <div x-show="filteredProducts().length === 0"
                                     x-cloak
                                     class="py-12 text-center">
                                    <div class="mb-3 text-4xl">📦</div>
                                    <p class="font-semibold text-slate-700">No products found</p>
                                    <p class="mt-1 text-sm text-slate-400">Try another product name or code.</p>
                                </div>
                            </div>
                        </section>

                        {{-- INVOICE ITEMS --}}
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                                <div>
                                    <h2 class="font-bold text-slate-900">Invoice Items</h2>
                                    <p class="mt-1 text-xs text-slate-500">Review products and adjust quantities</p>
                                </div>

                                <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700"
                                      x-text="items.length + ' Products'"></span>
                            </div>

                            <div x-show="formError"
                                 x-cloak
                                 class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                                 x-text="formError">
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[720px] text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold">Product</th>
                                        <th class="px-4 py-4 text-center font-semibold">Stock</th>
                                        <th class="px-4 py-4 text-center font-semibold">Quantity</th>
                                        <th class="px-4 py-4 text-right font-semibold">Unit Price</th>
                                        <th class="px-6 py-4 text-right font-semibold">Total</th>
                                        <th class="px-4 py-4"></th>
                                    </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">

                                    <template x-for="(item, index) in items" :key="item.product_id">
                                        <tr class="transition hover:bg-slate-50">

                                            <td class="min-w-48 px-6 py-4">
                                                <p class="font-bold text-slate-800" x-text="item.name"></p>
                                                <p class="mt-1 text-xs text-slate-400" x-text="item.code"></p>

                                                <input type="hidden"
                                                       :name="`items[${index}][product_id]`"
                                                       :value="item.product_id">
                                            </td>

                                            <td class="px-4 py-4 text-center text-slate-500"
                                                x-text="item.stock"></td>

                                            <td class="px-4 py-4 text-center">
                                                <input type="number"
                                                       min="1"
                                                       :max="item.stock"
                                                       :name="`items[${index}][quantity]`"
                                                       x-model.number="item.quantity"
                                                       @input="validateQuantity(item)"
                                                       class="w-20 rounded-lg border border-slate-200 py-2 text-center outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-50">
                                            </td>

                                            <td class="px-4 py-4 text-right">
                                                <input type="number"
                                                       min="0"
                                                       step="0.01"
                                                       :name="`items[${index}][unit_price]`"
                                                       x-model.number="item.unit_price"
                                                       class="w-28 rounded-lg border border-slate-200 py-2 text-right outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-50">
                                            </td>

                                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-800"
                                                x-text="money(lineTotal(item))"></td>

                                            <td class="px-4 py-4 text-center">
                                                <button type="button"
                                                        @click="removeProduct(index)"
                                                        title="Remove product"
                                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                                    ✕
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    </tbody>
                                </table>
                            </div>

                            {{-- EMPTY STATE --}}
                            <div x-show="items.length === 0"
                                 x-cloak
                                 class="px-6 py-14 text-center">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl">
                                    🛒
                                </div>
                                <p class="font-bold text-slate-700">Your invoice is empty</p>
                                <p class="mt-1 text-sm text-slate-400">Search and select products above to get started.</p>
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT SUMMARY SIDEBAR --}}
                    <div class="xl:col-span-4">
                        <div class="space-y-5 xl:sticky xl:top-6">

                            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                                <div class="bg-slate-900 p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-slate-300">Invoice Summary</p>
                                        <span class="rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-slate-200">
                                        New Invoice
                                    </span>
                                    </div>

                                    <p class="mt-3 text-3xl font-extrabold tracking-tight"
                                       x-text="money(grandTotal())"></p>

                                    <p class="mt-2 text-xs text-slate-400">Total invoice amount</p>
                                </div>

                                <div class="space-y-5 p-6">

                                    {{-- ITEM COUNT --}}
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Total Products</span>
                                        <span class="font-bold text-slate-800" x-text="items.length"></span>
                                    </div>

                                    {{-- SUBTOTAL --}}
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Subtotal</span>
                                        <span class="font-semibold text-slate-800"
                                              x-text="money(subTotal())"></span>
                                    </div>

                                    {{-- DISCOUNT --}}
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                                            Discount (Rs.)
                                        </label>
                                        <input type="number"
                                               name="discount"
                                               min="0"
                                               step="0.01"
                                               x-model.number="discount"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-right text-sm outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                    </div>

                                    {{-- TAX --}}
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                                            Tax (Rs.)
                                        </label>
                                        <input type="number"
                                               name="tax"
                                               min="0"
                                               step="0.01"
                                               x-model.number="tax"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-right text-sm outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                                    </div>

                                    {{-- TOTALS --}}
                                    <div class="space-y-3 border-t border-dashed border-slate-200 pt-5">

                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500">Discount</span>
                                            <span class="font-semibold text-red-600"
                                                  x-text="'- ' + money(discount)"></span>
                                        </div>

                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500">Tax</span>
                                            <span class="font-semibold text-emerald-600"
                                                  x-text="'+ ' + money(tax)"></span>
                                        </div>

                                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                            <span class="font-bold text-slate-900">Grand Total</span>
                                            <span class="text-xl font-extrabold text-indigo-700"
                                                  x-text="money(grandTotal())"></span>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                                        <div class="flex gap-3">
                                            <span class="text-lg">ℹ️</span>
                                            <p class="text-xs leading-relaxed text-blue-800">
                                                <strong>Payment information</strong><br>
                                                Initial payment is zero. You can record payments after creating the invoice.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- SUBMIT --}}
                                    <button type="submit"
                                            :disabled="items.length === 0"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-4 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Create Invoice
                                    </button>

                                    <p class="text-center text-xs text-slate-400">
                                        Please review the invoice before submitting.
                                    </p>
                                </div>
                            </section>

                            {{-- TIPS --}}
                            <section class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                                <h3 class="font-bold text-indigo-950">Invoice Tips</h3>
                                <ul class="mt-3 space-y-2 text-xs leading-relaxed text-indigo-800">
                                    <li>✓ Verify customer details.</li>
                                    <li>✓ Check available product quantities.</li>
                                    <li>✓ Review unit prices and discounts.</li>
                                    <li>✓ Confirm the final invoice amount.</li>
                                </ul>
                            </section>
                        </div>
                    </div>
                </div>
            </form>

            {{-- CUSTOMER MODAL --}}
            <div x-show="showCustomerModal"
                 x-cloak
                 x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                 @keydown.escape.window="closeCustomerModal()">

                <div @click.outside="closeCustomerModal()"
                     x-transition.scale
                     class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

                    {{-- MODAL HEADER --}}
                    <div class="flex items-center justify-between bg-indigo-600 px-6 py-5 text-white">
                        <div>
                            <h2 class="text-xl font-bold">Add New Customer</h2>
                            <p class="mt-1 text-sm text-indigo-100">Enter customer information</p>
                        </div>

                        <button type="button"
                                @click="closeCustomerModal()"
                                :disabled="savingCustomer"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-2xl hover:bg-white/25">
                            &times;
                        </button>
                    </div>

                    {{-- MODAL BODY --}}
                    <div class="space-y-4 p-6">

                        <div x-show="customerError"
                             x-cloak
                             class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                             x-text="customerError"></div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   x-model="customerForm.name"
                                   maxlength="255"
                                   placeholder="Enter customer name"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Phone Number</label>
                                <input type="text"
                                       x-model="customerForm.phone"
                                       maxlength="255"
                                       placeholder="Phone number"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:bg-white">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">WhatsApp Number</label>
                                <input type="text"
                                       x-model="customerForm.whatsapp_number"
                                       maxlength="255"
                                       placeholder="WhatsApp number"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email Address</label>
                            <input type="email"
                                   x-model="customerForm.email"
                                   maxlength="255"
                                   placeholder="example@email.com"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:bg-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Address</label>
                            <textarea x-model="customerForm.address"
                                      maxlength="255"
                                      rows="3"
                                      placeholder="Enter customer address"
                                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:bg-white"></textarea>
                        </div>
                    </div>

                    {{-- MODAL FOOTER --}}
                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">

                        <button type="button"
                                @click="closeCustomerModal()"
                                :disabled="savingCustomer"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            Cancel
                        </button>

                        <button type="button"
                                @click="saveCustomer()"
                                :disabled="savingCustomer"
                                class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-indigo-700 disabled:opacity-50">
                            <span x-show="!savingCustomer">Save Customer</span>
                            <span x-show="savingCustomer">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALPINE.JS --}}
        <script>
            function invoiceForm(products, initialCustomers, initialCustomer = '') {
                return {
                    products: products,
                    customers: initialCustomers,
                    selectedCustomer: initialCustomer ? String(initialCustomer) : '',

                    items: [],
                    search: '',
                    discount: 0,
                    tax: 0,
                    formError: '',

                    showCustomerModal: false,
                    savingCustomer: false,
                    customerError: '',

                    customerForm: {
                        name: '',
                        phone: '',
                        whatsapp_number: '',
                        email: '',
                        address: '',
                    },

                    openCustomerModal() {
                        this.customerError = '';
                        this.customerForm = {
                            name: '',
                            phone: '',
                            whatsapp_number: '',
                            email: '',
                            address: '',
                        };
                        this.showCustomerModal = true;
                    },

                    closeCustomerModal() {
                        if (this.savingCustomer) return;
                        this.showCustomerModal = false;
                        this.customerError = '';
                    },

                    async saveCustomer() {
                        if (this.savingCustomer) return;

                        const name = this.customerForm.name.trim();

                        if (!name) {
                            this.customerError = 'Please enter customer name.';
                            return;
                        }

                        this.savingCustomer = true;
                        this.customerError = '';

                        try {
                            const csrfToken = document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content;

                            if (!csrfToken) {
                                throw new Error('CSRF token not found. Check your layout.');
                            }

                            const response = await fetch(
                                @json(route('invoices.customers.quick-create')),
                                {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify(this.customerForm),
                                }
                            );

                            const result = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                const message = result.errors
                                    ? Object.values(result.errors).flat().join(' ')
                                    : result.message || 'Unable to create customer.';

                                throw new Error(message);
                            }

                            const customer = result.customer;

                            if (!customer || !customer.id) {
                                throw new Error('Invalid customer response from server.');
                            }

                            this.customers.push({
                                id: customer.id,
                                name: customer.name,
                                phone: customer.phone,
                            });

                            this.selectedCustomer = String(customer.id);
                            this.showCustomerModal = false;

                            this.customerForm = {
                                name: '',
                                phone: '',
                                whatsapp_number: '',
                                email: '',
                                address: '',
                            };

                        } catch (error) {
                            this.customerError = error.message;
                        } finally {
                            this.savingCustomer = false;
                        }
                    },

                    filteredProducts() {
                        const term = this.search.trim().toLowerCase();

                        const availableProducts = this.products.filter(
                            product => product.stock > 0
                        );

                        if (!term) {
                            return availableProducts.slice(0, 15);
                        }

                        return availableProducts.filter(product =>
                            String(product.name || '').toLowerCase().includes(term) ||
                            String(product.code || '').toLowerCase().includes(term)
                        ).slice(0, 30);
                    },

                    addProduct(product) {
                        this.formError = '';

                        const existing = this.items.find(
                            item => item.product_id === product.id
                        );

                        if (existing) {
                            if (existing.quantity >= existing.stock) {
                                this.formError = 'Maximum available stock reached.';
                                return;
                            }

                            existing.quantity++;
                        } else {
                            this.items.push({
                                product_id: product.id,
                                name: product.name,
                                code: product.code,
                                stock: product.stock,
                                unit: product.unit,
                                quantity: 1,
                                unit_price: product.price,
                            });
                        }

                        this.search = '';
                    },

                    removeProduct(index) {
                        this.items.splice(index, 1);
                        this.formError = '';
                    },

                    validateQuantity(item) {
                        let quantity = Number(item.quantity);

                        if (!Number.isFinite(quantity)) {
                            quantity = 1;
                        }

                        quantity = Math.floor(quantity);

                        item.quantity = Math.max(
                            1,
                            Math.min(quantity, item.stock)
                        );
                    },

                    lineTotal(item) {
                        return Math.max(0, Number(item.quantity) || 0) *
                            Math.max(0, Number(item.unit_price) || 0);
                    },

                    subTotal() {
                        return this.items.reduce(
                            (total, item) => total + this.lineTotal(item),
                            0
                        );
                    },

                    grandTotal() {
                        const subtotal = this.subTotal();
                        const discount = Math.max(0, Number(this.discount) || 0);
                        const tax = Math.max(0, Number(this.tax) || 0);

                        return Math.max(0, subtotal - discount + tax);
                    },

                    money(value) {
                        return 'Rs. ' + Number(value || 0).toLocaleString(
                            'en-LK',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }
                        );
                    },

                    validateInvoice(event) {
                        this.formError = '';

                        const fail = (message) => {
                            this.formError = message;
                            event.preventDefault();
                            return false;
                        };

                        if (!this.selectedCustomer) {
                            return fail('Please select a customer.');
                        }

                        if (this.items.length === 0) {
                            return fail('Please add at least one product.');
                        }

                        const discount = Number(this.discount);
                        const tax = Number(this.tax);

                        if (!Number.isFinite(discount) || discount < 0 ||
                            !Number.isFinite(tax) || tax < 0) {
                            return fail('Discount and tax must be valid non-negative amounts.');
                        }

                        if (discount > this.subTotal()) {
                            return fail('Discount cannot exceed subtotal.');
                        }

                        for (const item of this.items) {
                            if (
                                !Number.isInteger(Number(item.quantity)) ||
                                item.quantity < 1 ||
                                item.quantity > item.stock
                            ) {
                                return fail('Please check product quantities.');
                            }

                            if (
                                !Number.isFinite(Number(item.unit_price)) ||
                                Number(item.unit_price) < 0
                            ) {
                                return fail('Please enter valid product prices.');
                            }
                        }

                        return true;
                    },
                };
            }
        </script>
    </div>
@endsection
