
@extends('layouts.app')

@section('content')

    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-7xl">

            {{-- Dashboard Header --}}
            <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-sm font-semibold text-indigo-600">
                        Hyper Tech Solutions (Pvt) Ltd
                    </p>

                    <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">
                        {{ ucfirst($dashboardType) }} Dashboard
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Welcome, {{ auth()->user()->name }}.
                        Here's your overview for {{ now()->format('d M Y') }}.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm">
                    {{ now()->format('l, d F Y') }}
                </div>

            </div>

            {{-- Role Specific Dashboard --}}
            @include('dashboard.roles.' . $dashboardType)

        </div>

    </div>

@endsection
