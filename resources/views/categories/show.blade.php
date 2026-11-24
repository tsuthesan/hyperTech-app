
@extends('layouts.app')

@section('content')

    <div class="px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Category Details
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $category->code }}
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('categories.index') }}"
                   class="rounded-lg border px-4 py-2 text-sm text-gray-700">
                    Back
                </a>

                <a href="{{ route('categories.edit', $category) }}"
                   class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white">
                    Edit Category
                </a>

            </div>

        </div>

        <div class="mb-8 rounded-xl border bg-white p-6 shadow-sm">

            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Category Information
            </h2>

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <p class="text-sm text-gray-500">Category Code</p>
                    <p class="font-semibold text-gray-900">
                        {{ $category->code }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Category Name</p>
                    <p class="font-semibold text-gray-900">
                        {{ $category->name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total Products</p>
                    <p class="text-2xl font-bold text-indigo-600">
                        {{ $products->total() }}
                    </p>
                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">

            <div class="border-b p-5">
                <h2 class="text-lg font-semibold text-gray-900">
                    Products in this Category
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
                            Selling Price
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
                                Rs. {{ number_format($product->selling_price, 2) }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                No products found in this category.
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
