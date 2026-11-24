
@extends('layouts.app')

@section('content')

    <div class="px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Supplier Details
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $supplier->supplier_code }}
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('suppliers.index') }}"
                   class="rounded-lg border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Back
                </a>

                <a href="{{ route('suppliers.edit', $supplier) }}"
                   class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                    Edit Supplier
                </a>

            </div>

        </div>

        <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2">

            <div class="rounded-xl border bg-white p-6 shadow-sm">

                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Supplier Information
                </h2>

                <dl class="space-y-4">

                    <div>
                        <dt class="text-sm text-gray-500">Supplier Code</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ $supplier->supplier_code }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Supplier Name</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ $supplier->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Phone</dt>
                        <dd class="text-gray-900">
                            {{ $supplier->phone ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="text-gray-900">
                            {{ $supplier->email ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Address</dt>
                        <dd class="text-gray-900">
                            {{ $supplier->address ?? '-' }}
                        </dd>
                    </div>

                </dl>

            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">

                <h2 class="mb-3 text-lg font-semibold text-gray-900">
                    Supplier Summary
                </h2>

                <p class="text-sm text-gray-500">
                    Total products linked to this supplier
                </p>

                <p class="mt-2 text-3xl font-bold text-indigo-600">
                    {{ $products->total() }}
                </p>

            </div>

        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">

            <div class="border-b p-5">
                <h2 class="text-lg font-semibold text-gray-900">
                    Supplied Products
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Product Code
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Product Name
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Cost Price
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Stock
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                    @forelse($products as $product)

                        <tr>
                            <td class="px-6 py-4 text-sm">
                                {{ $product->product_code }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium">
                                {{ $product->name }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                Rs. {{ number_format($product->cost_price, 2) }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4"
                                class="px-6 py-8 text-center text-sm text-gray-500">
                                No products linked to this supplier.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t p-4">
                {{ $products->links() }}
            </div>

        </div>

    </div>

@endsection
