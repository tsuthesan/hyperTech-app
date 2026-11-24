
<div class="space-y-6">

    @if(isset($category))
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Category Code
            </label>

            <input
                type="text"
                value="{{ $category->code }}"
                readonly
                class="w-full rounded-lg border-gray-300 bg-gray-100"
            >
        </div>
    @else
        <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-700">
            Category code will be generated automatically after saving.
        </div>
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
            Category Name *
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $category->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Enter category name"
            class="w-full rounded-lg border-gray-300 shadow-sm"
        >

        @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end gap-3 border-t pt-5">

        <a href="{{ route('categories.index') }}"
           class="rounded-lg border px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
            Cancel
        </a>

        <button
            type="submit"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
        >
            {{ isset($category) ? 'Update Category' : 'Save Category' }}
        </button>

    </div>

</div>
