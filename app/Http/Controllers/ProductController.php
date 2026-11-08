<?php

namespace App\Http\Controllers;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->filled('search')){
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortOptions = [
                        'name',
                        'product_code',
                        'category',
                        'supplier',
                        'cost_price',
                        'selling_price',
                        'stock_quantity',
                        'created_at',
        ];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');

        if(!in_array($sort, $sortOptions, true)){
            $sort = 'name';
        }

        if(!in_array($direction, ['asc', 'desc'], true)){
            $direction = 'asc';
        }

        $products = $query
                    ->orderBy($sort, $direction)
                    ->paginate(15)
                    ->withQueryString();

        return view('products.index', compact('products', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories =  Category::orderBy('name')->get();
        $suppliers =  Supplier::orderBy('name')->get();

        return view('products.create',compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_code' => 'required|string|max:255|unique:products,product_code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean|nullable',
        ]);

        if ((float) $data['selling_price'] < (float) $data['cost_price']) {
            return back()
                ->withInput()
                ->withErrors([
                    'selling_price' => 'Selling price cannot be less than cost price.'
                ]);
        }

        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function() use ($data) {
            $openingStock = (int)$data['stock_quantity'];

            // Create initial stock transaction
            $product = Product::create([
                'product_code' => $data['product_code'],
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'description' => $data['description'] ?? null,
                'unit' => $data['unit'] ?? null,
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'stock_quantity' => 0, // Set to 0 initially
                'min_stock_quantity' => $data['min_stock_quantity'] ?? 0,
                'is_active' => (bool)($data['is_active'] ?? true),
                'last_price_updated_at' => now(),
            ]);
            // Record opening stock through the stock ledger.
                if($openingStock > 0){
                    $product->stock_quantity = $openingStock;
                    $product->save();

                    StockTransaction::create([
                        'product_id' => $product->id,
                        'type' => 'stock_in',
                        'quantity' => $openingStock,
                        'unit_price' => $product->cost_price,
                        'note' => 'Opening stock ',
                        'created_by' => auth()->id(),
                    ]);

                }

        });
                return redirect()
                    ->route('products.index')
                    ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories =  Category::orderBy('name')->get();
        $suppliers =  Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data =$request->validate([
            'product_code' => ['required','string','max:255',Rule::unique('products','product_code')->ignore($product->id)],
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean|nullable',
        ]);

        if ((float) $data['selling_price'] < (float) $data['cost_price']) {
            return back()
                ->withInput()
                ->withErrors([
                    'selling_price' =>
                        'Selling price is below cost price. Confirm this is intentional before saving.',
                ]);
        }

        DB::transaction(function () use ($data, $product) {
            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldPrice = (float) $product->selling_price;
            $newPrice = (float) $data['selling_price'];

            if (round($oldPrice, 2) !== round($newPrice, 2)) {
                PriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $product->selling_price,
                    'new_price' => $data['selling_price'],
                    'updated_by' => auth()->id(),
                    'updated_at_price' => now(),
                ]);

                $data['last_price_updated_at'] = now();
            }

            $data['is_active'] = (bool) ($data['is_active'] ?? false);

            // Stock is intentionally excluded from this edit form.
            $product->fill($data);
            $product->save();
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');


    }

    public function storecategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'code' => 'required|string|max:100|unique:categories,code',

        ]);

        $category = Category::create($data);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
        ], 201);
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier = Supplier::create($data);

        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
