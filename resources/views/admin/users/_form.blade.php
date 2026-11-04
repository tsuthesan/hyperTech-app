
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">
            Full Name *
        </label>
        <input id="name" name="name" type="text"
               value="{{ old('name', $user?->name) }}"
               required maxlength="255"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium">
            Email Address *
        </label>
        <input id="email" name="email" type="email"
               value="{{ old('email', $user?->email) }}"
               required maxlength="255"
               autocomplete="email"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="role" class="mb-1 block text-sm font-medium">
            User Role *
        </label>
        <select id="role" name="role" required
                class="w-full rounded-lg border-slate-300">
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}"
                    @selected(old('role', $user?->roles->first()?->name) === $role->name)>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="password" class="mb-1 block text-sm font-medium">
            Password {{ $user ? '(leave blank to keep current password)' : '*' }}
        </label>
        <input id="password" name="password" type="password"
               {{ $user ? '' : 'required' }}
               autocomplete="new-password"
               class="w-full rounded-lg border-slate-300">
    </div>

    <div>
        <label for="password_confirmation"
               class="mb-1 block text-sm font-medium">
            Confirm Password {{ $user ? '' : '*' }}
        </label>
        <input id="password_confirmation" name="password_confirmation"
               type="password" autocomplete="new-password"
               class="w-full rounded-lg border-slate-300">
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700">
        {{ $user ? 'Save Changes' : 'Create Account' }}
    </button>

    <a href="{{ route('admin.users.index') }}"
       class="rounded-lg bg-slate-200 px-5 py-2 text-slate-800">
        Cancel
    </a>
</div>
