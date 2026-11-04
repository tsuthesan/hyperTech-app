
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add User | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-2xl p-6">
    <h1 class="mb-2 text-2xl font-bold text-slate-900">
        Add Staff Account
    </h1>
    <p class="mb-6 text-sm text-slate-500">
        Create a login and assign an access role.
    </p>

    <div class="rounded-xl bg-white p-6 shadow-sm">
        @php($user = null)

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form')
        </form>
    </div>
</main>
</body>
</html>
