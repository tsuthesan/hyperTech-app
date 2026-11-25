
<div class="space-y-6">

    {{-- Main Statistics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach([
            ['Today Sales', $todaySales, 'text-indigo-600'],
            ['Monthly Sales', $monthlySales, 'text-blue-600'],
            ['Total Received', $totalReceived, 'text-emerald-600'],
            ['Outstanding', $totalOutstanding, 'text-amber-600'],
        ] as [$label, $value, $colorClass])

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <p class="text-sm font-medium text-slate-500">
                    {{ $label }}
                </p>

                <p class="mt-3 text-2xl font-bold {{ $colorClass }}">
                    Rs. {{ number_format($value, 2) }}
                </p>

            </div>

        @endforeach

    </div>

    {{-- Keep the remaining Admin dashboard sections below this point --}}

</div>
