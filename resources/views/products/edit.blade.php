
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Product | Hyper Tech Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
<main class="mx-auto max-w-4xl p-6">
    <h1 class="text-2xl font-bold text-slate-900">Edit Product</h1>
    <p class="mb-6 mt-1 text-sm text-slate-500">
        Update {{ $product->name }}.
    </p>

    <div class="rounded-xl bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('products._form')
        </form>
    </div>
</main>
</body>
</html>
