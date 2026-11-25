<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Determine the dashboard based on the user's role.
        if ($user->hasRole('admin')) {
            $dashboardType = 'admin';
        } elseif ($user->hasRole('cashier')) {
            $dashboardType = 'cashier';
        } elseif ($user->hasRole('accountant')) {
            $dashboardType = 'accountant';
        } elseif ($user->hasRole('inventory staff')) {
            $dashboardType = 'inventory';
        } else {
            abort(403, 'You do not have permission to access the dashboard.');
        }

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Common product and stock information.
        $totalProducts = Product::where('is_active', 1)->count();

        $lowStockCount = Product::where('is_active', 1)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->count();

        $lowStockProducts = Product::where('is_active', 1)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ADMIN DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($dashboardType === 'admin') {

            $salesInvoices = Invoice::where('status', '!=', 'draft');

            $todaySales = (clone $salesInvoices)
                ->whereDate('invoice_date', $today)
                ->sum('grand_total');

            $monthlySales = (clone $salesInvoices)
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->sum('grand_total');

            $totalSales = (clone $salesInvoices)->sum('grand_total');

            $totalReceived = (clone $salesInvoices)->sum('amount_paid');

            $totalOutstanding = (clone $salesInvoices)->sum('balance_amount');

            $totalInvoices = Invoice::count();

            $todayInvoices = Invoice::whereDate('invoice_date', $today)->count();

            $totalCustomers = Customer::count();

            $totalSuppliers = Supplier::count();

            $totalUsers = User::count();

            $recentInvoices = Invoice::with('customer')
                ->latest('id')
                ->limit(8)
                ->get();

            $salesByDay = Invoice::where('status', '!=', 'draft')
                ->whereBetween('invoice_date', [
                    $today->copy()->subDays(6),
                    $today,
                ])
                ->selectRaw('DATE(invoice_date) as sale_date, SUM(grand_total) as total')
                ->groupBy(DB::raw('DATE(invoice_date)'))
                ->pluck('total', 'sale_date');

            $salesChart = collect(range(6, 0))->map(function ($daysAgo) use ($today, $salesByDay) {
                $date = $today->copy()->subDays($daysAgo);

                return [
                    'label' => $date->format('D'),
                    'date' => $date->format('d M'),
                    'total' => (float) ($salesByDay[$date->toDateString()] ?? 0),
                ];
            });

            $topProducts = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_items.product_id', '=', 'products.id')
                ->where('invoices.status', '!=', 'draft')
                ->select(
                    'products.id',
                    'products.name',
                    'products.product_code',
                    DB::raw('SUM(invoice_items.quantity) as total_sold')
                )
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.product_code'
                )
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            $data = compact(
                'todaySales',
                'monthlySales',
                'totalSales',
                'totalReceived',
                'totalOutstanding',
                'totalInvoices',
                'todayInvoices',
                'totalCustomers',
                'totalSuppliers',
                'totalUsers',
                'recentInvoices',
                'salesChart',
                'topProducts',
                'totalProducts',
                'lowStockCount',
                'lowStockProducts'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CASHIER DASHBOARD
        |--------------------------------------------------------------------------
        */

        elseif ($dashboardType === 'cashier') {

            $todayInvoicesQuery = Invoice::where('created_by', $user->id)
                ->whereDate('invoice_date', $today)
                ->where('status', '!=', 'draft');

            $todaySales = (clone $todayInvoicesQuery)->sum('grand_total');

            $todayReceived = (clone $todayInvoicesQuery)->sum('amount_paid');

            $todayInvoiceCount = (clone $todayInvoicesQuery)->count();

            $todayOutstanding = (clone $todayInvoicesQuery)->sum('balance_amount');

            $todayCustomers = Customer::whereDate('created_at', $today)->count();

            $recentInvoices = Invoice::with('customer')
                ->where('created_by', $user->id)
                ->latest('id')
                ->limit(10)
                ->get();

            $data = compact(
                'todaySales',
                'todayReceived',
                'todayInvoiceCount',
                'todayOutstanding',
                'todayCustomers',
                'recentInvoices'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ACCOUNTANT DASHBOARD
        |--------------------------------------------------------------------------
        */

        elseif ($dashboardType === 'accountant') {

            $financialInvoices = Invoice::where('status', '!=', 'draft');

            $monthlyInvoiceValue = (clone $financialInvoices)
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->sum('grand_total');

            $monthlyReceived = (clone $financialInvoices)
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->sum('amount_paid');

            $totalReceived = (clone $financialInvoices)->sum('amount_paid');

            $totalOutstanding = (clone $financialInvoices)->sum('balance_amount');

            $totalInvoiceValue = (clone $financialInvoices)->sum('grand_total');

            $partialInvoices = Invoice::where('status', 'partial')->count();

            $overdueInvoices = Invoice::where('status', 'overdue')->count();

            $unpaidInvoices = Invoice::where('balance_amount', '>', 0)
                ->where('status', '!=', 'draft')
                ->with('customer')
                ->orderByDesc('invoice_date')
                ->limit(10)
                ->get();

            $data = compact(
                'monthlyInvoiceValue',
                'monthlyReceived',
                'totalReceived',
                'totalOutstanding',
                'totalInvoiceValue',
                'partialInvoices',
                'overdueInvoices',
                'unpaidInvoices'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | INVENTORY STAFF DASHBOARD
        |--------------------------------------------------------------------------
        */

        else {

            $outOfStockCount = Product::where('is_active', 1)
                ->where('stock_quantity', '<=', 0)
                ->count();

            $totalStockUnits = Product::where('is_active', 1)
                ->sum('stock_quantity');

            $totalSuppliers = Supplier::count();

            $stockInToday = DB::table('stock_transactions')
                ->where('type', 'stock_in')
                ->whereDate('created_at', $today)
                ->sum('quantity');

            $stockOutToday = DB::table('stock_transactions')
                ->whereIn('type', ['stock_out', 'sale'])
                ->whereDate('created_at', $today)
                ->selectRaw('COALESCE(SUM(ABS(quantity)), 0) as total')
                ->value('total');

            $recentStockTransactions = DB::table('stock_transactions')
                ->join('products', 'stock_transactions.product_id', '=', 'products.id')
                ->leftJoin('users', 'stock_transactions.created_by', '=', 'users.id')
                ->select(
                    'stock_transactions.id',
                    'stock_transactions.type',
                    'stock_transactions.quantity',
                    'stock_transactions.note',
                    'stock_transactions.created_at',
                    'products.name as product_name',
                    'products.product_code',
                    'users.name as user_name'
                )
                ->latest('stock_transactions.id')
                ->limit(10)
                ->get();

            $data = compact(
                'totalProducts',
                'lowStockCount',
                'lowStockProducts',
                'outOfStockCount',
                'totalStockUnits',
                'totalSuppliers',
                'stockInToday',
                'stockOutToday',
                'recentStockTransactions'
            );
        }

        return view('dashboard.index', array_merge(
            $data,
            ['dashboardType' => $dashboardType]
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
