
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-2xl p-6">
    <h1 class="mb-2 text-2xl font-bold text-slate-900">
        Edit Staff Account
    </h1>
    <p class="mb-6 text-sm text-slate-500">
        Update {{ $user->name }}'s details and role.
    </p>

    <div class="rounded-xl bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            @include('admin.users._form')
        </form>
    </div>
</main>
</body>
</html>
