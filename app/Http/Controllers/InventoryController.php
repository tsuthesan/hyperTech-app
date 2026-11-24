<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalProducts = Product::where('is_active', true)->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->count();

        $totalStockValue = Product::where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock_quantity * cost_price), 0) as total')
            ->value('total');

        $recentTransactions = StockTransaction::with([
            'product',
            'creator',
        ])
            ->latest()
            ->limit(10)
            ->get();

        return view('inventory.index', compact(
            'totalProducts',
            'lowStockCount',
            'totalStockValue',
            'recentTransactions'
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

    public function stockInForm(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.stock-in', compact('products'));
    }

    public function storeStockIn(
        Request $request,
        InventoryService $inventory
    ): RedirectResponse {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $inventory->stockIn(
            (int) $data['product_id'],
            (int) $data['quantity'],
            isset($data['unit_price'])
                ? (float) $data['unit_price']
                : null,
            $data['note'] ?? 'Stock received'
        );

        return redirect()
            ->route('inventory.stock-in')
            ->with('success', 'Stock received successfully.');
    }

    public function adjustmentForm(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.adjustment', compact('products'));
    }

    public function storeAdjustment(
        Request $request,
        InventoryService $inventory
    ): RedirectResponse {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $inventory->adjust(
            (int) $data['product_id'],
            (int) $data['new_quantity'],
            $data['note']
        );

        return redirect()
            ->route('inventory.adjustment')
            ->with('success', 'Stock adjusted successfully.');
    }

    public function lowStock(): View
    {
        $products = Product::with(['category', 'supplier'])
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->orderBy('stock_quantity')
            ->paginate(20);

        return view('inventory.low-stock', compact('products'));
    }

    public function transactions(Request $request): View
    {
        $query = StockTransaction::with(['product', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->whereHas('product', function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $transactions = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('inventory.transactions', compact('transactions'));
    }
}
