
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6"
         x-data="posSystem()">

        <h1 class="text-2xl font-bold mb-6">Point of Sale</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pos.checkout') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Product selection --}}
                <div class="lg:col-span-2 bg-white p-5 rounded shadow">

                    <h2 class="font-semibold mb-3">Select Products</h2>

                    <input type="text"
                           x-model="search"
                           @input.debounce.400ms="searchProducts()"
                           placeholder="Search product name or code..."
                           class="w-full border rounded p-3 mb-3">

                    <div class="border rounded divide-y max-h-64 overflow-y-auto">
                        <template x-for="product in results"
                                  :key="product.id">
                            <div class="p-3 flex justify-between items-center">
                                <div>
                                    <div class="font-semibold"
                                         x-text="product.name"></div>
                                    <small class="text-gray-500">
                                        <span x-text="product.product_code"></span>
                                        |
                                        Stock: <span x-text="product.stock_quantity"></span>
                                    </small>
                                    <div>
                                        Rs. <span x-text="money(product.selling_price)"></span>
                                    </div>
                                </div>

                                <button type="button"
                                        @click="addProduct(product)"
                                        class="bg-blue-600 text-white px-3 py-2 rounded">
                                    Add
                                </button>
                            </div>
                        </template>
                    </div>

                    <h2 class="font-semibold mt-6 mb-3">Invoice Items</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Product</th>
                                <th class="p-2">Price</th>
                                <th class="p-2">Qty</th>
                                <th class="p-2">Total</th>
                                <th class="p-2"></th>
                            </tr>
                            </thead>

                            <tbody>
                            <template x-for="(item, index) in cart"
                                      :key="item.id">
                                <tr class="border-b">
                                    <td class="p-2">
                                        <span x-text="item.name"></span>

                                        <input type="hidden"
                                               :name="`items[${index}][product_id]`"
                                               :value="item.id">
                                    </td>

                                    <td class="p-2"
                                        x-text="'Rs. ' + money(item.selling_price)">
                                    </td>

                                    <td class="p-2">
                                        <input type="number"
                                               min="1"
                                               :max="item.stock_quantity"
                                               :name="`items[${index}][quantity]`"
                                               x-model.number="item.quantity"
                                               class="w-20 border rounded p-1">
                                    </td>

                                    <td class="p-2"
                                        x-text="'Rs. ' + money(item.selling_price * item.quantity)">
                                    </td>

                                    <td class="p-2">
                                        <button type="button"
                                                @click="removeItem(index)"
                                                class="text-red-600">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payment details --}}
                <div class="bg-white p-5 rounded shadow">

                    <h2 class="font-semibold mb-4">Customer & Payment</h2>

                    <label class="block mb-2">Customer</label>

                    <select name="customer_id"
                            required
                            class="w-full border rounded p-2 mb-4">
                        <option value="">Select Customer</option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }}
                                {{ $customer->phone ? ' - '.$customer->phone : '' }}
                            </option>
                        @endforeach
                    </select>

                    <label class="block mb-2">Discount (Rs.)</label>
                    <input type="number"
                           name="discount"
                           min="0"
                           step="0.01"
                           x-model.number="discount"
                           class="w-full border rounded p-2 mb-3">

                    <label class="block mb-2">Tax (Rs.)</label>
                    <input type="number"
                           name="tax"
                           min="0"
                           step="0.01"
                           x-model.number="tax"
                           class="w-full border rounded p-2 mb-3">

                    <div class="border-t pt-3 space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <strong x-text="'Rs. ' + money(subtotal)"></strong>
                        </div>

                        <div class="flex justify-between">
                            <span>Discount</span>
                            <span x-text="'Rs. ' + money(discount)"></span>
                        </div>

                        <div class="flex justify-between">
                            <span>Tax</span>
                            <span x-text="'Rs. ' + money(tax)"></span>
                        </div>

                        <div class="flex justify-between text-lg border-t pt-2">
                            <strong>Grand Total</strong>
                            <strong x-text="'Rs. ' + money(grandTotal)"></strong>
                        </div>
                    </div>

                    <label class="block mt-4 mb-2">Payment Method</label>

                    <select name="payment_method"
                            x-model="paymentMethod"
                            required
                            class="w-full border rounded p-2">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>

                    <label class="block mt-4 mb-2">Amount Paid (Rs.)</label>

                    <input type="number"
                           name="amount_paid"
                           min="0"
                           step="0.01"
                           x-model.number="amountPaid"
                           required
                           class="w-full border rounded p-2">

                    <div class="flex justify-between mt-3">
                        <strong>Balance</strong>
                        <strong x-text="'Rs. ' + money(balance)"></strong>
                    </div>

                    <button type="submit"
                            :disabled="cart.length === 0 || !validCart || !validPayment || submitting"
                            @click="submitting = true"
                            class="w-full mt-5 bg-green-600 text-white p-3 rounded disabled:opacity-50">
                        Complete Sale
                    </button>

                </div>
            </div>
        </form>
    </div>

    <script>
        function posSystem() {
            return {
                search: '',
                results: [],
                cart: [],
                discount: 0,
                tax: 0,
                amountPaid: 0,
                paymentMethod: 'cash',
                submitting: false,

                get subtotal() {
                    return this.cart.reduce((sum, item) => {
                        return sum + (Number(item.selling_price) * Number(item.quantity));
                    }, 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - Number(this.discount || 0)
                        + Number(this.tax || 0));
                },

                get balance() {
                    return Math.max(0, this.grandTotal - Number(this.amountPaid || 0));
                },

                get validCart() {
                    return this.cart.length > 0 && this.cart.every(item =>
                        Number.isInteger(Number(item.quantity)) &&
                        Number(item.quantity) >= 1 &&
                        Number(item.quantity) <= Number(item.stock_quantity)
                    );
                },

                get validPayment() {
                    return Number(this.discount) >= 0 &&
                        Number(this.discount) <= this.subtotal &&
                        Number(this.tax) >= 0 &&
                        Number(this.amountPaid) >= 0 &&
                        Number(this.amountPaid) <= this.grandTotal;
                },

                money(value) {
                    return Number(value || 0).toFixed(2);
                },

                async searchProducts() {
                    if (this.search.trim().length < 2) {
                        this.results = [];
                        return;
                    }

                    const response = await fetch(
                        '{{ route("pos.products.search") }}?search='
                        + encodeURIComponent(this.search)
                    );

                    if (response.ok) {
                        this.results = await response.json();
                    }
                },

                addProduct(product) {
                    const existing = this.cart.find(item => item.id === product.id);

                    if (existing) {
                        if (existing.quantity < product.stock_quantity) {
                            existing.quantity++;
                        }
                        return;
                    }

                    this.cart.push({
                        ...product,
                        quantity: 1
                    });
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                }
            };
        }
    </script>
@endsection
