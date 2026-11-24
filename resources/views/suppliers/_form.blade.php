
<div class="space-y-6">

    @if(isset($supplier))
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Supplier Code
            </label>

            <input type="text"
                   value="{{ $supplier->supplier_code }}"
                   readonly
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm">
        </div>
    @else
        <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-700">
            Supplier code will be generated automatically after saving.
        </div>
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            Supplier Name <span class="text-red-500">*</span>
        </label>

        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $supplier->name ?? '') }}"
               required
               placeholder="Enter supplier name"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
                Phone Number
            </label>

            <input type="text"
                   name="phone"
                   id="phone"
                   value="{{ old('phone', $supplier->phone ?? '') }}"
                   placeholder="Enter phone number"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">

            @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address
            </label>

            <input type="email"
                   name="email"
                   id="email"
                   value="{{ old('email', $supplier->email ?? '') }}"
                   placeholder="Enter email address"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">

            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div>
        <label for="address" class="block text-sm font-medium text-gray-700">
            Address
        </label>

        <textarea name="address"
                  id="address"
                  rows="4"
                  placeholder="Enter supplier address"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">{{ old('address', $supplier->address ?? '') }}</textarea>

        @error('address')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 border-t pt-5">

        <a href="{{ route('suppliers.index') }}"
           class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancel
        </a>

        <button type="submit"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
            {{ isset($supplier) ? 'Update Supplier' : 'Save Supplier' }}
        </button>

    </div>

</div>
