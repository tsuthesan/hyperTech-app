
@extends('layouts.app')

@section('content')

    <div class="mx-auto max-w-4xl px-4 py-8">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Add Supplier</h1>
            <p class="mt-1 text-sm text-gray-500">
                Register a new supplier in your inventory system.
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                @include('suppliers._form')

            </form>

        </div>

    </div>

@endsection
