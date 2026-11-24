
@extends('layouts.app')

@section('content')

    <div class="px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Categories
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Manage product categories.
                </p>
            </div>

            <a href="{{ route('categories.create') }}"
               class="rounded-lg bg-indigo-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-indigo-700">
                + Add Category
            </a>

        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-5 rounded-xl border bg-white p-4 shadow-sm">

            <form action="{{ route('categories.index') }}"
                  method="GET"
                  class="flex flex-col gap-3 sm:flex-row">

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search category code or name..."
                    class="w-full rounded-lg border-gray-300"
                >

                <button
                    type="submit"
                    class="rounded-lg bg-gray-800 px-6 py-2 text-sm font-medium text-white"
                >
                    Search
                </button>

                <a href="{{ route('categories.index') }}"
                   class="rounded-lg border px-6 py-2 text-center text-sm">
                    Reset
                </a>

            </form>

        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Category Code
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Category Name
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Products
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                            Actions
                        </th>

                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                    @forelse($categories as $category)

                        <tr class="hover:bg-gray-50">

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $category->code }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $category->name }}
                            </td>

                            <td class="px-6 py-4 text-center text-sm text-gray-600">
                                {{ $category->products_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">

                                <div class="flex justify-end gap-3">

                                    <a href="{{ route('categories.show', $category) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800">
                                        View
                                    </a>

                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="font-medium text-indigo-600 hover:text-indigo-800">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('categories.destroy', $category) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this category?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="font-medium text-red-600 hover:text-red-800"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                No categories found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t px-6 py-4">
                {{ $categories->links() }}
            </div>

        </div>

    </div>

@endsection
