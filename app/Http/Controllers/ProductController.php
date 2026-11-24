<?php

namespace App\Http\Controllers;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ((float) $data['selling_price'] < (float) $data['cost_price']) {
            return back()
                ->withInput()
                ->withErrors([
                    'selling_price' => 'Selling price cannot be less than cost price.',
                ]);
        }

        $openingStock = (int) $data['stock_quantity'];

        $data['is_active'] = $request->boolean('is_active');

        $product = DB::transaction(function () use ($data, $openingStock) {

            // Generate a temporary unique product code.
            $product = Product::create([
                'product_code' => 'TEMP-' . Str::uuid(),
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'description' => $data['description'] ?? null,
                'unit' => $data['unit'],
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'stock_quantity' => 0,
                'min_stock_quantity' => $data['min_stock_quantity'] ?? 0,
                'is_active' => $data['is_active'],
                'last_price_updated_at' => now(),
            ]);

            // Generate permanent product code using ID.
            $product->product_code = 'PRD' .
                str_pad($product->id, 6, '0', STR_PAD_LEFT);

            $product->qr_code = $product->product_code;

            $product->save();

            // Record opening stock.
            if ($openingStock > 0) {

                $product->stock_quantity = $openingStock;
                $product->save();

                StockTransaction::create([
                    'product_id' => $product->id,
                    'type' => 'stock_in',
                    'quantity' => $openingStock,
                    'unit_price' => $product->cost_price,
                    'note' => 'Opening stock',
                    'created_by' => auth()->id(),
                ]);
            }

            return $product;
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product created successfully. Code: ' . $product->product_code
            );
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ((float) $data['selling_price'] < (float) $data['cost_price']) {
            return back()
                ->withInput()
                ->withErrors([
                    'selling_price' => 'Selling price is below cost price. Confirm this is intentional before saving.',
                ]);
        }
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($data, $product) {

            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldPrice = (float) $product->selling_price;
            $newPrice = (float) $data['selling_price'];

            if (round($oldPrice, 2) !== round($newPrice, 2)) {

                PriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'updated_by' => auth()->id(),
                    'updated_at_price' => now(),
                ]);

                $data['last_price_updated_at'] = now();
            }


            // Do not change product code or stock quantity.
            $product->fill($data);
            $product->save();
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }



    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],
        ]);

        $category = DB::transaction(function () use ($data) {

            $category = Category::create([
                'name' => $data['name'],
                'code' => 'TEMP-' . Str::uuid(),
            ]);

            $category->code = 'CAT' .
                str_pad($category->id, 6, '0', STR_PAD_LEFT);

            $category->save();

            return $category;
        });

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
            'message' => 'Category created successfully.',
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
        'message' => 'Supplier created successfully.',
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
