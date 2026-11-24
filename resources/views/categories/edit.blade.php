
@extends('layouts.app')

@section('content')

    <div class="mx-auto max-w-3xl px-4 py-8">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Category
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Update category information.
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <form action="{{ route('categories.update', $category) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('categories._form')

            </form>

        </div>

    </div>

@endsection
