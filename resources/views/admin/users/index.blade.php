
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-7xl p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                User Management
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage staff accounts and access roles.
            </p>
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
            + Add User
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-4">Name</th>
                <th class="p-4">Email</th>
                <th class="p-4">Role</th>
                <th class="p-4">Created</th>
                <th class="p-4">Action</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <tr>
                    <td class="p-4 font-medium text-slate-900">
                        {{ $user->name }}
                    </td>
                    <td class="p-4 text-slate-600">
                        {{ $user->email }}
                    </td>
                    <td class="p-4">
                        @forelse ($user->roles as $role)
                            <span class="inline-block rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                        {{ ucfirst($role->name) }}
                                    </span>
                        @empty
                            <span class="text-red-600">No role assigned</span>
                        @endforelse
                    </td>
                    <td class="p-4 text-slate-600">
                        {{ $user->created_at?->format('Y-m-d') }}
                    </td>
                    <td class="p-4">
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="font-medium text-blue-600 hover:underline">
                            Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500">
                        No user accounts found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</main>
</body>
</html>
